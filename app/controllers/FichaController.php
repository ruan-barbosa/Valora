<?php

// Cadastro de produtos, serviços, revendas e respectivas fichas técnicas.
class FichaController extends Controller
{
    public function index(): void
    {
        $idEmpresa = Auth::empresaId();
        $status = $this->get('status', 'ativos');
        if (!in_array($status, ['ativos', 'inativos', 'todos'], true)) {
            $status = 'ativos';
        }

        $produtos = CustoService::produtosDaEmpresa($idEmpresa, [
            'incluir_inativos' => $status !== 'ativos',
        ]);

        if ($status === 'inativos') {
            $produtos = array_values(array_filter($produtos, static fn(array $p): bool => !$p['ativo']));
        } elseif ($status === 'ativos') {
            $produtos = array_values(array_filter($produtos, static fn(array $p): bool => $p['ativo']));
        }

        $busca = $this->get('busca');
        if ($busca !== '') {
            $produtos = array_values(array_filter($produtos, static function (array $p) use ($busca): bool {
                return function_exists('mb_stripos')
                    ? mb_stripos($p['nome'], $busca, 0, 'UTF-8') !== false
                    : stripos($p['nome'], $busca) !== false;
            }));
        }

        $this->view('fichas/index', [
            'produtos' => $produtos,
            'status'   => $status,
            'busca'    => $busca,
        ]);
    }

    public function novo(): void
    {
        $empresa = Empresa::buscar(Auth::empresaId());
        $tipoPadrao = ($empresa['segmento'] ?? '') === 'servicos' ? 'servico' : 'produto';

        $this->formulario([
            'id_produto'   => 0,
            'nome'         => '',
            'tipo'         => $tipoPadrao,
            'preco_venda'  => '',
            'margem_minima'=> '',
            'id_fornecedor'=> '',
            'preco_compra' => '',
        ]);
    }

    public function editar($id): void
    {
        $idEmpresa = Auth::empresaId();
        $produto = Produto::buscar((int) $id, $idEmpresa);

        if (!$produto) {
            $this->erro404();
            return;
        }

        $produto['id_fornecedor'] = '';
        $produto['preco_compra'] = '';
        $composicao = Composicao::listarDoProduto((int) $id);

        // Revendas possuem uma linha interna, criada automaticamente, que não deve ser editada como insumo.
        if ($produto['tipo'] === 'revenda') {
            foreach ($composicao as $linha) {
                if (($linha['tipo_item'] ?? '') === 'revenda') {
                    $produto['id_fornecedor'] = $linha['id_ultimo_fornecedor'] ?? '';
                    $produto['preco_compra'] = $linha['preco_atual'];
                    break;
                }
            }
            $composicao = [];
        }

        $this->formulario($produto, $composicao);
    }

    private function formulario(array $produto, array $composicao = []): void
    {
        $this->view('fichas/form', [
            'produto'     => $produto,
            'composicao'  => $composicao,
            // Itens inativos continuam disponíveis para preservar fichas existentes; itens internos de revenda são ocultados.
            'itens'       => Item::listar(Auth::empresaId(), true),
            'unidades'    => UnidadeMedida::listar(),
            'fornecedores'=> Fornecedor::listar(Auth::empresaId()),
        ]);
    }

    /** Cria ou atualiza produto/serviço/revenda e substitui a composição em uma única transação. */
    public function salvar(): void
    {
        $idEmpresa = Auth::empresaId();
        $idUsuario = Auth::id();
        $idTexto = Validador::texto('id_produto');
        $id = $idTexto !== '' && ctype_digit($idTexto) ? (int) $idTexto : 0;
        $nome = Validador::texto('nome');
        $tipo = Validador::texto('tipo');
        $precoVendaTexto = Validador::texto('preco_venda');
        $margemTexto = Validador::texto('margem_minima');
        $idFornecedor = (int) Validador::texto('id_fornecedor') ?: null;
        $precoCompraTexto = Validador::texto('preco_compra');
        $itensPost = $_POST['itens'] ?? [];

        $editando = $id > 0;
        $retorno = $editando ? 'fichas/editar/' . $id : 'fichas/novo';
        $antigos = [
            'nome'          => $nome,
            'tipo'          => $tipo,
            'preco_venda'   => $precoVendaTexto,
            'margem_minima' => $margemTexto,
            'id_fornecedor' => $idFornecedor ?? '',
            'preco_compra'  => $precoCompraTexto,
            'itens'         => is_array($itensPost) ? $itensPost : [],
        ];

        $produtoExistente = $editando ? Produto::buscar($id, $idEmpresa) : null;
        if ($editando && !$produtoExistente) {
            $this->redirecionar('fichas', 'Produto não encontrado.', 'erro');
        }

        $erros = [];
        if ($idTexto !== '' && (!ctype_digit($idTexto) || $id <= 0)) {
            $erros[] = 'Identificador do cadastro inválido. Recarregue a página e tente novamente.';
        }
        if (!Validador::tamanho($nome, 2, 100)) {
            $erros[] = 'Informe o nome do produto/serviço (2 a 100 caracteres).';
        } elseif (Produto::nomeExiste($idEmpresa, $nome, $id)) {
            $erros[] = 'Já existe um produto ou serviço com esse nome.';
        }

        if (!in_array($tipo, Produto::TIPOS, true)) {
            $erros[] = 'Escolha um tipo válido: produto, serviço ou revenda.';
        }

        $precoVenda = Validador::dinheiro($precoVendaTexto);
        if ($precoVenda !== null) {
            $precoVenda = round($precoVenda, 2);
        }
        if ($precoVenda === null || $precoVenda <= 0) {
            $erros[] = 'Informe um preço de venda válido e maior que zero.';
        }

        $margemMinima = null;
        if ($margemTexto !== '') {
            $margemMinima = Validador::percentual($margemTexto);
            if ($margemMinima !== null) {
                $margemMinima = round($margemMinima, 2);
            }
            if ($margemMinima === null) {
                $erros[] = 'A margem mínima deve ser um número entre 0 e 100.';
            }
        }

        $linhas = [];
        if ($tipo === 'revenda') {
            if ($idFornecedor === null || !Fornecedor::ativoDaEmpresa($idFornecedor, $idEmpresa)) {
                $erros[] = 'Selecione um fornecedor ativo da sua empresa para a revenda.';
            }
            $precoCompra = Validador::dinheiro($precoCompraTexto);
            if ($precoCompra === null) {
                $erros[] = 'Informe um preço de compra válido e não negativo.';
            }
        } elseif (in_array($tipo, ['produto', 'servico'], true)) {
            $resultadoComposicao = $this->validarComposicao($idEmpresa, $itensPost);
            $linhas = $resultadoComposicao['linhas'];
            $erros = array_merge($erros, $resultadoComposicao['erros']);

            if (!$linhas) {
                $erros[] = 'Adicione pelo menos um insumo à ficha técnica.';
            }
        }

        if ($erros) {
            $this->redirecionarComErros($retorno, array_values(array_unique($erros)), $antigos);
        }

        $pdo = Database::conectar();
        $alertaGerado = false;
        try {
            $pdo->beginTransaction();

            if ($editando) {
                Produto::atualizar($id, $idEmpresa, $nome, $tipo, $precoVenda, $margemMinima);
                $idProduto = $id;
            } else {
                $idProduto = Produto::criar($idEmpresa, $nome, $tipo, $precoVenda, $margemMinima);
            }

            $composicaoAnterior = $editando ? Composicao::listarDoProduto($idProduto) : [];
            $itemInternoAnterior = $this->itemInternoRevenda($composicaoAnterior);

            if ($tipo === 'revenda') {
                $idItemRevenda = $this->salvarItemInternoRevenda(
                    $idProduto,
                    $idEmpresa,
                    $idUsuario,
                    $idFornecedor,
                    $precoCompra,
                    $itemInternoAnterior
                );

                $unidade = UnidadeMedida::buscarPorSigla('un');
                if (!$unidade) {
                    throw new RuntimeException('A unidade de medida "un" não foi encontrada no banco de dados.');
                }

                Composicao::substituir($idProduto, [[
                    'id_item'          => $idItemRevenda,
                    'id_unidade'       => (int) $unidade['id_unidade'],
                    'quantidade'       => 1,
                    'percentual_perda' => 0,
                ]]);
            } else {
                Composicao::substituir($idProduto, $linhas);

                // Ao mudar uma revenda para produto/serviço, desativa o item interno antigo se ele não for mais utilizado.
                if ($itemInternoAnterior && !Item::emUso((int) $itemInternoAnterior['id_item'])) {
                    Item::definirAtivo((int) $itemInternoAnterior['id_item'], $idEmpresa, false);
                }
            }

            // Produto novo ou ficha alterada: avalia a margem resultante e registra o alerta, se necessário.
            $alertaGerado = AlertaService::avaliarProduto($idProduto, $idEmpresa);
            $pdo->commit();
        } catch (PDOException $erro) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if (($erro->errorInfo[1] ?? 0) === 1062) {
                $this->redirecionarComErros($retorno, ['Já existe um produto ou item com esse nome.'], $antigos);
            }
            throw $erro;
        } catch (Throwable $erro) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $erro;
        }

        $mensagem = $editando ? 'Produto/ficha técnica atualizado com sucesso.' : 'Produto/ficha técnica cadastrado com sucesso.';
        if ($alertaGerado) {
            $mensagem .= ' A margem ficou abaixo do mínimo e um alerta foi registrado no painel.';
        }
        $this->redirecionar('fichas', $mensagem);
    }

    public function inativar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) Validador::texto('id_produto');
        $produto = Produto::buscar($id, $idEmpresa);

        if (!$produto) {
            $this->redirecionar('fichas', 'Produto não encontrado.', 'erro');
        }
        if (!$produto['ativo']) {
            $this->redirecionar('fichas', 'Este produto já está inativo.', 'erro');
        }

        Produto::definirAtivo($id, $idEmpresa, false);
        Alerta::resolverProduto($id);
        $this->redirecionar('fichas', 'Cadastro inativado. Seu histórico foi preservado.');
    }

    public function reativar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) Validador::texto('id_produto');
        $produto = Produto::buscar($id, $idEmpresa);
        if (!$produto) {
            $this->redirecionar('fichas', 'Cadastro não encontrado.', 'erro');
        }
        Produto::definirAtivo($id, $idEmpresa, true);
        AlertaService::avaliarProduto($id, $idEmpresa);
        $this->redirecionar('fichas', 'Cadastro reativado e margem recalculada.');
    }

    /** Endpoint JSON usado pelo formulário para atualizar custo e margem enquanto os campos são preenchidos. */
    public function calcular(): void
    {
        $idEmpresa = Auth::empresaId();
        $tipo = Validador::texto('tipo');
        $precoVenda = Validador::dinheiro(Validador::texto('preco_venda'));
        if ($precoVenda !== null) {
            $precoVenda = round($precoVenda, 2);
        }
        $margemTexto = Validador::texto('margem_minima');
        $empresa = Empresa::buscar($idEmpresa);

        if (!$empresa || !in_array($tipo, Produto::TIPOS, true) || $precoVenda === null) {
            $this->json(['erro' => 'Preencha o tipo e um preço de venda válido para calcular.'], 422);
        }

        if ($margemTexto === '') {
            $margemMinima = (float) $empresa['margem_minima_padrao'];
        } else {
            $margemMinima = Validador::percentual($margemTexto);
            if ($margemMinima !== null) {
                $margemMinima = round($margemMinima, 2);
            }
            if ($margemMinima === null) {
                $this->json(['erro' => 'A margem mínima deve estar entre 0 e 100.'], 422);
            }
        }

        $percentualIndiretos = (float) $empresa['percentual_custos_indiretos'];

        if ($tipo === 'revenda') {
            $precoCompra = Validador::dinheiro(Validador::texto('preco_compra'));
            if ($precoCompra === null) {
                $this->json(['erro' => 'Informe um preço de compra válido para calcular a revenda.'], 422);
            }

            // Na revenda a composição automática é uma unidade comprada; assim o custo direto é o preço de compra.
            $custoDireto = $precoCompra;
            $custoIndireto = $custoDireto * $percentualIndiretos / 100;
            $custoTotal = $custoDireto + $custoIndireto;
            $margem = CustoService::margem($precoVenda, $custoTotal);
        } else {
            $resultadoComposicao = $this->validarComposicao($idEmpresa, $_POST['itens'] ?? []);
            if ($resultadoComposicao['erros']) {
                $this->json(['erro' => implode(' ', $resultadoComposicao['erros'])], 422);
            }
            if (!$resultadoComposicao['linhas']) {
                $this->json(['erro' => 'Adicione pelo menos um insumo para calcular a ficha técnica.'], 422);
            }

            try {
                $conta = CustoService::calcular(
                    $resultadoComposicao['linhas'],
                    $precoVenda,
                    $percentualIndiretos
                );
            } catch (Throwable $erro) {
                $this->json(['erro' => $erro->getMessage()], 422);
            }

            $custoDireto = $conta['custo_direto'];
            $custoIndireto = $conta['custo_indireto'];
            $custoTotal = $conta['custo_total'];
            $margem = $conta['margem'];
        }

        $this->json([
            'custo_direto'   => round($custoDireto, 4),
            'custo_indireto' => round($custoIndireto, 4),
            'custo_total'    => round($custoTotal, 4),
            'margem'         => round($margem, 2),
            'margem_minima'  => $margemMinima,
            'abaixo_margem'  => CustoService::abaixoDaMargem($margem, $margemMinima),
        ]);
    }

    /** Valida as linhas e devolve os campos já prontos para CustoService e Composicao::substituir. */
    private function validarComposicao(int $idEmpresa, $itensPost): array
    {
        if (!is_array($itensPost)) {
            return ['linhas' => [], 'erros' => ['A composição enviada é inválida.']];
        }

        $linhas = [];
        $erros = [];
        $idsUsados = [];

        foreach ($itensPost as $indice => $linha) {
            if (!is_array($linha)) {
                $erros[] = 'Uma das linhas da composição está inválida.';
                continue;
            }

            $idItemTexto = trim((string) ($linha['id_item'] ?? ''));
            $idUnidadeTexto = trim((string) ($linha['id_unidade'] ?? ''));
            $quantidadeTexto = trim((string) ($linha['quantidade'] ?? ''));
            $perdaTexto = trim((string) ($linha['percentual_perda'] ?? '0'));

            // Linha inteiramente vazia pode ser ignorada; linha parcialmente preenchida deve ser corrigida.
            if ($idItemTexto === '' && $idUnidadeTexto === '' && $quantidadeTexto === '') {
                continue;
            }

            $idItem = ctype_digit($idItemTexto) ? (int) $idItemTexto : 0;
            $idUnidade = ctype_digit($idUnidadeTexto) ? (int) $idUnidadeTexto : 0;
            $quantidade = Validador::decimal($quantidadeTexto);
            $perda = Validador::percentual($perdaTexto === '' ? '0' : $perdaTexto);

            if ($idItem <= 0 || $idUnidade <= 0 || $quantidade === null || $quantidade <= 0 || $quantidade > 999999.999999) {
                $erros[] = 'Preencha o insumo, a unidade e uma quantidade maior que zero em todas as linhas.';
                continue;
            }
            if ($perda === null) {
                $erros[] = 'O percentual de perda deve estar entre 0 e 100.';
                continue;
            }
            if (isset($idsUsados[$idItem])) {
                $erros[] = 'O mesmo insumo não pode aparecer duas vezes na mesma ficha técnica.';
                continue;
            }

            $item = Item::buscar($idItem, $idEmpresa);
            $unidade = UnidadeMedida::buscar($idUnidade);
            if (!$item || !$unidade) {
                $erros[] = 'Um dos insumos ou uma das unidades selecionadas não existe nesta empresa.';
                continue;
            }
            if ($item['tipo'] === 'revenda') {
                $erros[] = 'Itens internos de revenda não podem ser adicionados manualmente à composição.';
                continue;
            }
            if ($item['grupo'] !== $unidade['grupo']) {
                $erros[] = 'A unidade escolhida não é compatível com o insumo "' . $item['nome'] . '".';
                continue;
            }

            $quantidade = round($quantidade, 6);
            $perda = round($perda, 2);
            $idsUsados[$idItem] = true;
            $linhas[] = [
                'id_item'          => $idItem,
                'id_unidade'       => $idUnidade,
                'quantidade'       => $quantidade,
                'percentual_perda' => $perda,
                'preco_atual'      => (float) $item['preco_atual'],
                'grupo_linha'      => $unidade['grupo'],
                'fator_linha'      => (float) $unidade['fator_conversao'],
                'grupo_item'       => $item['grupo'],
                'fator_item'       => (float) $item['fator_conversao'],
            ];
        }

        return ['linhas' => $linhas, 'erros' => array_values(array_unique($erros))];
    }

    /** Encontra a linha que representa a composição automática de uma revenda. */
    private function itemInternoRevenda(array $linhas): ?array
    {
        foreach ($linhas as $linha) {
            if (($linha['tipo_item'] ?? '') === 'revenda') {
                return [
                    'id_item'              => (int) $linha['id_item'],
                    'preco_atual'          => (float) $linha['preco_atual'],
                    'id_ultimo_fornecedor' => $linha['id_ultimo_fornecedor'] !== null
                        ? (int) $linha['id_ultimo_fornecedor']
                        : null,
                ];
            }
        }

        return null;
    }

    /** Cria ou atualiza o item técnico da revenda; o fornecedor e o preço ficam registrados no histórico de preços. */
    private function salvarItemInternoRevenda(
        int $idProduto,
        int $idEmpresa,
        int $idUsuario,
        ?int $idFornecedor,
        float $precoCompra,
        ?array $itemAnterior
    ): int {
        if ($idFornecedor === null) {
            throw new RuntimeException('Fornecedor obrigatório para revenda.');
        }

        $unidade = UnidadeMedida::buscarPorSigla('un');
        if (!$unidade) {
            throw new RuntimeException('A unidade de medida "un" não foi encontrada no banco de dados.');
        }

        $nomeInterno = 'Revenda #' . $idProduto;
        if ($itemAnterior) {
            $idItem = (int) $itemAnterior['id_item'];
            Item::atualizar($idItem, $idEmpresa, $nomeInterno, 'revenda', (int) $unidade['id_unidade']);
            Item::definirAtivo($idItem, $idEmpresa, true);

            $mesmoPreco = abs($precoCompra - (float) $itemAnterior['preco_atual']) < 0.00005;
            $mesmoFornecedor = (int) ($itemAnterior['id_ultimo_fornecedor'] ?? 0) === $idFornecedor;
            if (!$mesmoPreco || !$mesmoFornecedor) {
                Item::atualizarPreco($idItem, $idEmpresa, $idUsuario, $precoCompra, $idFornecedor);
            }

            return $idItem;
        }

        return Item::criar(
            $idEmpresa,
            $idUsuario,
            $nomeInterno,
            'revenda',
            (int) $unidade['id_unidade'],
            $precoCompra,
            $idFornecedor
        );
    }
}
