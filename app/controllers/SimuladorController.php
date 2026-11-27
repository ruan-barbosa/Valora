<?php

// Simulador de reajuste: mostra como ficam custo e margem dos produtos se o preço de um insumo mudar. Não grava nada.
class SimuladorController extends Controller
{
    public function index(): void
    {
        $itens = array_map(
            fn($i) => ['id_item' => $i['id_item'], 'nome' => $i['nome']],
            Item::listar(Auth::empresaId())
        );

        $this->view('simulador/index', ['itens' => $itens]);
    }

    /** POST /simulador/simular (JSON). Campos: id_item e percentual_reajuste (ex.: 10 ou -5). */
    public function simular(): void
    {
        $idEmpresa = Auth::empresaId();
        $idItem = (int) Validador::texto('id_item');

        $item = Item::buscar($idItem, $idEmpresa);
        if (!$item) {
            $this->json(['erro' => 'Insumo não encontrado.'], 404);
        }

        $percentual = $this->percentual(Validador::texto('percentual_reajuste'));
        if ($percentual === null) {
            $this->json(['erro' => 'Informe um percentual entre -99 e 1000 (ex.: 10 ou -5).'], 422);
        }

        $novoPreco = round($item['preco_atual'] * (1 + $percentual / 100), 4);
        $ids = Produto::idsQueUsamItem($idItem, $idEmpresa);

        $antes = CustoService::produtosDaEmpresa($idEmpresa, ['ids' => $ids]);
        $depois = [];
        foreach (CustoService::produtosDaEmpresa($idEmpresa, ['ids' => $ids, 'precos' => [$idItem => $novoPreco]]) as $p) {
            $depois[$p['id_produto']] = $p;
        }

        $resultado = [];
        foreach ($antes as $a) {
            $d = $depois[$a['id_produto']];
            $resultado[] = [
                'produto'        => $a['nome'],
                'custo_antes'    => $a['custo_total'],
                'custo_depois'   => $d['custo_total'],
                'margem_antes'   => $a['margem'],
                'margem_depois'  => $d['margem'],
                'abaixo_margem'  => $d['abaixo_margem'],
                'situacao'       => $d['abaixo_margem'] ? 'abaixo do mínimo' : 'ok',
            ];
        }

        $this->json([
            'item' => ['nome' => $item['nome'], 'preco_atual' => $item['preco_atual'], 'preco_novo' => $novoPreco],
            'resultado' => $resultado,
        ]);
    }

    /** Aceita vírgula ou ponto e sinal (+ ou -). Entre -99 e 1000. */
    private function percentual(string $texto): ?float
    {
        $texto = str_replace(',', '.', trim($texto));
        if (!preg_match('/^[+-]?\d+(\.\d+)?$/', $texto)) {
            return null;
        }

        $valor = (float) $texto;
        return ($valor > -100 && $valor <= 1000) ? $valor : null;
    }
}
