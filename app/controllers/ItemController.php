<?php

// Insumos (material, mão de obra e máquina), preços e histórico de preços.
class ItemController extends Controller
{
    public function index(): void
    {
        $idEmpresa = Auth::empresaId();
        $incluirInativos = $this->get('inativos') === '1';

        $this->view('insumos/index', [
            'insumos'      => Item::listar($idEmpresa, $incluirInativos),
            'fornecedores' => Fornecedor::listar($idEmpresa),
            'incluirInativos' => $incluirInativos,
        ]);
    }

    public function novo(): void
    {
        $this->formulario([
            'id_item' => 0, 'nome' => '', 'tipo' => 'material', 'id_unidade' => 0,
            'preco_atual' => 0.0, 'id_ultimo_fornecedor' => null,
        ]);
    }

    public function editar($id): void
    {
        $insumo = Item::buscar((int) $id, Auth::empresaId());
        if (!$insumo) {
            $this->erro404();
            return;
        }

        $this->formulario($insumo);
    }

    private function formulario(array $insumo): void
    {
        $this->view('insumos/form', [
            'insumo'       => $insumo,
            'unidades'     => UnidadeMedida::listar(),
            'fornecedores' => Fornecedor::listar(Auth::empresaId()),
            'tipos'        => Item::TIPOS,
        ]);
    }

    /** Cria o insumo (sem id_item) ou edita um existente (com id_item). */
    public function salvar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id          = (int) Validador::texto('id_item');
        $nome        = Validador::texto('nome');
        $tipo        = Validador::texto('tipo');
        $idUnidade   = (int) Validador::texto('id_unidade');
        $precoTexto  = Validador::texto('preco');
        $idFornecedor = (int) Validador::texto('id_fornecedor') ?: null;

        $editando = $id > 0;
        $retorno  = $editando ? "insumos/editar/{$id}" : 'insumos/novo';
        $antigos  = ['nome' => $nome, 'tipo' => $tipo, 'id_unidade' => $idUnidade, 'preco' => $precoTexto, 'id_fornecedor' => $idFornecedor];

        $existente = null;
        if ($editando) {
            $existente = Item::buscar($id, $idEmpresa);
            if (!$existente) {
                $this->redirecionar('insumos', 'Insumo não encontrado.', 'erro');
            }
        }

        $erros = [];

        if (!Validador::tamanho($nome, 2, 100)) {
            $erros[] = 'Informe o nome do insumo (2 a 100 caracteres).';
        } elseif (Item::nomeExiste($idEmpresa, $nome, $id)) {
            $erros[] = 'Já existe um insumo com esse nome.';
        }
        if (!in_array($tipo, Item::TIPOS, true)) {
            $erros[] = 'Escolha o tipo do insumo.';
        }
        if (!UnidadeMedida::existe($idUnidade)) {
            $erros[] = 'Escolha a unidade de medida.';
        } elseif ($existente && $idUnidade !== (int) $existente['id_unidade'] && Item::emUso($id)) {
            $erros[] = 'Este insumo já faz parte de fichas técnicas, então a unidade não pode ser alterada.';
        }

        // No cadastro o preço é obrigatório. Na edição ele só vale se for diferente do atual.
        $preco = null;
        if ($precoTexto !== '' || !$editando) {
            $preco = Validador::dinheiro($precoTexto);
            if ($preco === null) {
                $erros[] = 'Informe um preço válido (número, sem negativo).';
            }
        }
        $fornecedorMudou = $editando && $idFornecedor !== $existente['id_ultimo_fornecedor'];
        $registraPreco = $preco !== null && (!$editando || !$this->mesmoPreco($preco, (float) $existente['preco_atual']) || $fornecedorMudou);

        $erroFornecedor = $registraPreco ? $this->validarFornecedor($idFornecedor, $tipo, $idEmpresa) : null;
        if ($erroFornecedor) {
            $erros[] = $erroFornecedor;
        }

        if ($erros) {
            $this->redirecionarComErros($retorno, $erros, $antigos);
        }

        $pdo = Database::conectar();
        $alertasGerados = 0;

        try {
            $pdo->beginTransaction();

            if ($editando) {
                Item::atualizar($id, $idEmpresa, $nome, $tipo, $idUnidade);
                if ($registraPreco) {
                    Item::atualizarPreco($id, $idEmpresa, Auth::id(), $preco, $idFornecedor);
                    // Recalcula os produtos afetados e grava os alertas na mesma transação do preço.
                    $alertasGerados = AlertaService::recalcularPorItem($id, $idEmpresa);
                }
                $mensagem = 'Insumo atualizado.';
            } else {
                Item::criar($idEmpresa, Auth::id(), $nome, $tipo, $idUnidade, $preco, $idFornecedor);
                $mensagem = 'Insumo cadastrado.';
            }

            $pdo->commit();
        } catch (Throwable $erro) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($erro instanceof PDOException && ($erro->errorInfo[1] ?? 0) === 1062) {
                $this->redirecionarComErros($retorno, ['Já existe um insumo com esse nome.'], $antigos);
            }
            throw $erro;
        }

        if ($alertasGerados === 1) {
            $mensagem .= ' Um produto ficou abaixo da margem mínima e seu alerta foi registrado/atualizado no painel.';
        } elseif ($alertasGerados > 1) {
            $mensagem .= ' ' . $alertasGerados . ' produtos ficaram abaixo da margem mínima e seus alertas foram registrados/atualizados no painel.';
        }

        $this->redirecionar('insumos', $mensagem);
    }

    public function atualizarPreco(): void
    {
        $idEmpresa = Auth::empresaId();
        $id           = (int) Validador::texto('id_item');
        $preco        = Validador::dinheiro(Validador::texto('preco'));
        $idFornecedor = (int) Validador::texto('id_fornecedor') ?: null;

        $item = Item::buscar($id, $idEmpresa);
        if (!$item) {
            $this->redirecionar('insumos', 'Insumo não encontrado.', 'erro');
        }
        if (!$item['ativo']) {
            $this->redirecionar('insumos', 'Este insumo está inativo.', 'erro');
        }
        if ($preco === null) {
            $this->redirecionar('insumos', 'Informe um preço válido (número, sem negativo).', 'erro');
        }

        $erroFornecedor = $this->validarFornecedor($idFornecedor, $item['tipo'], $idEmpresa);
        if ($erroFornecedor) {
            $this->redirecionar('insumos', $erroFornecedor, 'erro');
        }

        // Se nada mudou (nem o preço, nem o fornecedor), não há o que registrar.
        $mesmoFornecedor = $idFornecedor === $item['id_ultimo_fornecedor'];
        if ($this->mesmoPreco($preco, $item['preco_atual']) && $mesmoFornecedor) {
            $this->redirecionar('insumos', 'O preço informado é igual ao atual.', 'erro');
        }

        $pdo = Database::conectar();
        try {
            $pdo->beginTransaction();
            Item::atualizarPreco($id, $idEmpresa, Auth::id(), $preco, $idFornecedor);
            // O custo e os alertas são atualizados antes do commit, evitando preço sem recálculo.
            $alertasGerados = AlertaService::recalcularPorItem($id, $idEmpresa);
            $pdo->commit();
        } catch (Throwable $erro) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $erro;
        }

        $mensagem = 'Preço de ' . $item['nome'] . ' atualizado.';
        if ($alertasGerados === 1) {
            $mensagem .= ' Um produto ficou abaixo da margem mínima e seu alerta foi registrado/atualizado no painel.';
        } elseif ($alertasGerados > 1) {
            $mensagem .= ' ' . $alertasGerados . ' produtos ficaram abaixo da margem mínima e seus alertas foram registrados/atualizados no painel.';
        }

        $this->redirecionar('insumos', $mensagem);
    }

    public function inativar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) Validador::texto('id_item');

        if (!Item::buscar($id, $idEmpresa)) {
            $this->redirecionar('insumos', 'Insumo não encontrado.', 'erro');
        }

        Item::definirAtivo($id, $idEmpresa, false);
        $this->redirecionar('insumos', 'Insumo inativado.');
    }

    public function reativar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) Validador::texto('id_item');
        if (!Item::buscar($id, $idEmpresa)) {
            $this->redirecionar('insumos', 'Insumo não encontrado.', 'erro');
        }
        Item::definirAtivo($id, $idEmpresa, true);
        $alertas = AlertaService::recalcularPorItem($id, $idEmpresa);
        $mensagem = 'Insumo reativado.';
        if ($alertas > 0) $mensagem .= ' Há produtos abaixo da margem mínima; confira o painel.';
        $this->redirecionar('insumos', $mensagem);
    }

    /** JSON com o histórico de preços de um insumo: GET /insumos/historico?id_item=N */
    public function historico(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) $this->get('id_item');

        $item = Item::buscar($id, $idEmpresa);
        if (!$item) {
            $this->json(['erro' => 'Insumo não encontrado.'], 404);
        }

        $this->json([
            'item' => [
                'id_item' => $item['id_item'],
                'nome'    => $item['nome'],
                'tipo'    => $item['tipo'],
                'sigla'   => $item['sigla'],
            ],
            'historico' => HistoricoPreco::listarPorItem($id, $idEmpresa),
        ]);
    }

    /** Material exige fornecedor; mão de obra e máquina não. Se vier um, ele precisa ser da empresa e estar ativo. */
    private function validarFornecedor(?int $idFornecedor, string $tipo, int $idEmpresa): ?string
    {
        if ($idFornecedor === null) {
            return $tipo === 'material' ? 'Escolha o fornecedor (obrigatório para material).' : null;
        }

        return Fornecedor::ativoDaEmpresa($idFornecedor, $idEmpresa) ? null : 'Fornecedor inválido ou inativo.';
    }

    private function mesmoPreco(float $a, float $b): bool
    {
        return abs($a - $b) < 0.00005;
    }
}
