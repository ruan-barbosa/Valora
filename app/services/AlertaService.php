<?php

// Recalcula margens, guarda o histórico e mantém os alertas coerentes com a situação atual.
class AlertaService
{
    public static function recalcularPorItem(int $idItem, int $idEmpresa): int
    {
        return self::avaliar($idEmpresa, Produto::idsQueUsamItem($idItem, $idEmpresa), $idItem);
    }

    public static function avaliarProduto(int $idProduto, int $idEmpresa): bool
    {
        return self::avaliar($idEmpresa, [$idProduto], null) > 0;
    }

    /** Recalcula todos os produtos quando parâmetros globais da empresa mudam. */
    public static function recalcularEmpresa(int $idEmpresa): int
    {
        $ids = array_map(static fn(array $p): int => (int) $p['id_produto'], Produto::listar($idEmpresa));
        return self::avaliar($idEmpresa, $ids, null);
    }

    private static function avaliar(int $idEmpresa, array $ids, ?int $idItem): int
    {
        if (!$ids) {
            return 0;
        }

        $abaixo = 0;
        foreach (CustoService::produtosDaEmpresa($idEmpresa, ['ids' => $ids]) as $produto) {
            $idProduto = (int) $produto['id_produto'];
            if ($produto['tem_ficha']) {
                HistoricoMargem::registrar(
                    $idProduto,
                    (float) $produto['custo_total'],
                    (float) $produto['margem'],
                    (float) $produto['margem_minima']
                );
            }

            if ($produto['tem_ficha'] && $produto['abaixo_margem']) {
                Alerta::registrar($idProduto, $idItem, $produto['margem'], $produto['custo_total']);
                $abaixo++;
            } else {
                Alerta::resolverProduto($idProduto);
            }
        }
        return $abaixo;
    }
}
