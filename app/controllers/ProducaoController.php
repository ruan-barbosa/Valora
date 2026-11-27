<?php

// Registra produção ou execução e mantém os custos históricos congelados.
class ProducaoController extends Controller
{
    public function index(): void
    {
        $idEmpresa = Auth::empresaId();
        $inicio = $this->dataValida($this->get('inicio'), date('Y-m-01'));
        $fim = $this->dataValida($this->get('fim'), date('Y-m-d'));
        if ($inicio > $fim) [$inicio, $fim] = [$fim, $inicio];

        $produtosCalculados = CustoService::produtosDaEmpresa($idEmpresa);
        $produtos = [];
        foreach ($produtosCalculados as $produto) {
            if ($produto['ativo'] && $produto['tem_ficha'] && $produto['custo_total'] > 0) {
                $produtos[] = [
                    'id_produto' => $produto['id_produto'],
                    'nome' => $produto['nome'],
                    'tipo' => $produto['tipo'],
                ];
            }
        }

        $this->view('producao/index', [
            'produtos' => $produtos,
            'lancamentos' => LancamentoProducao::listarRecentes(
                $idEmpresa, 200, $inicio, $fim,
                Auth::perfil() === 'producao' ? Auth::id() : null
            ),
            'periodoInicio' => $inicio,
            'periodoFim' => $fim,
        ]);
    }

    public function registrar(): void
    {
        $idEmpresa = Auth::empresaId();
        $idProduto = (int) Validador::texto('id_produto');
        $quantidade = Validador::decimal(Validador::texto('quantidade'));
        $produto = Produto::buscar($idProduto, $idEmpresa);

        if (!$produto || !$produto['ativo']) {
            $this->redirecionar('producao', 'Escolha um produto ou serviço ativo.', 'erro');
        }
        if ($quantidade === null || $quantidade <= 0 || $quantidade > 999999999.999) {
            $this->redirecionar('producao', 'Informe uma quantidade maior que zero.', 'erro');
        }
        $quantidade = round($quantidade, 3);
        $calculado = CustoService::produtosDaEmpresa($idEmpresa, ['ids' => [$idProduto]])[0] ?? null;
        if (!$calculado || !$calculado['tem_ficha'] || $calculado['custo_total'] <= 0) {
            $this->redirecionar('producao', 'Este cadastro ainda não tem ficha técnica com custo calculado.', 'erro');
        }

        $custoUnitario = (float) $calculado['custo_total'];
        $custoTotal = round($custoUnitario * $quantidade, 2);
        if ($custoTotal > 999999999999.99) {
            $this->redirecionar('producao', 'Quantidade grande demais para registrar.', 'erro');
        }

        LancamentoProducao::registrar($idProduto, Auth::id(), $quantidade, $custoUnitario, $custoTotal, (float) $produto['preco_venda']);
        $termo = Auth::perfil() === 'administrador' || Auth::perfil() === 'producao' ? 'Registro salvo' : 'Lançamento salvo';
        $this->redirecionar('producao', $termo . ': ' . $produto['nome'] . '. O custo deste momento foi preservado.');
    }

    /** Apenas o administrador corrige um lançamento; o custo unitário antigo não muda. */
    public function atualizar(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) Validador::texto('id_lancamento');
        $quantidade = Validador::decimal(Validador::texto('quantidade'));
        $registro = LancamentoProducao::buscar($id, $idEmpresa);
        if (!$registro) $this->redirecionar('producao', 'Registro não encontrado.', 'erro');
        if ($quantidade === null || $quantidade <= 0 || $quantidade > 999999999.999) {
            $this->redirecionar('producao', 'Informe uma quantidade maior que zero.', 'erro');
        }
        $quantidade = round($quantidade, 3);
        if (round($quantidade * $registro['custo_unitario'], 2) > 999999999999.99) {
            $this->redirecionar('producao', 'Quantidade grande demais.', 'erro');
        }
        LancamentoProducao::atualizarQuantidade($id, $idEmpresa, $quantidade);
        $this->redirecionar('producao', 'Quantidade corrigida. O custo unitário original foi mantido.');
    }

    /** Exclusão de lançamento fica restrita ao administrador da empresa. */
    public function excluir(): void
    {
        $id = (int) Validador::texto('id_lancamento');
        if (!LancamentoProducao::excluir($id, Auth::empresaId())) {
            $this->redirecionar('producao', 'Registro não encontrado.', 'erro');
        }
        $this->redirecionar('producao', 'Registro de produção/execução excluído.');
    }

    private function dataValida(string $texto, string $padrao): string
    {
        $data = DateTime::createFromFormat('!Y-m-d', $texto);
        return ($data && $data->format('Y-m-d') === $texto) ? $texto : $padrao;
    }
}
