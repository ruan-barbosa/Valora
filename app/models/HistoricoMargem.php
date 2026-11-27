<?php

// Registra fotografias da margem sempre que um produto é recalculado.
// Isso permite que o filtro de período do dashboard use dados históricos reais.
class HistoricoMargem
{
    public static function registrar(int $idProduto, float $custo, float $margem, float $margemMinima): void
    {
        $sql = 'insert into historico_margem (id_produto, custo_total, margem_calculada, margem_minima)
                values (?, ?, ?, ?)';
        // A coluna decimal(7,2) aceita margens entre -99.999,99 e 99.999,99.
        $margem = max(-99999.99, min(99999.99, round($margem, 2)));
        Database::conectar()->prepare($sql)->execute([
            $idProduto,
            round($custo, 4),
            $margem,
            round($margemMinima, 2),
        ]);
    }

    /** Última fotografia de cada produto dentro do período informado. */
    public static function ultimasPorProduto(int $idEmpresa, string $inicio, string $fim): array
    {
        $sql = "select p.id_produto, p.nome, h.margem_calculada as margem, h.margem_minima,
                       h.custo_total, h.data_registro
                from historico_margem h
                join produto p on p.id_produto = h.id_produto
                where p.id_empresa = ?
                  and h.data_registro >= ? and h.data_registro <= ?
                  and h.id_historico_margem = (
                    select max(h2.id_historico_margem)
                    from historico_margem h2
                    where h2.id_produto = h.id_produto
                      and h2.data_registro >= ? and h2.data_registro <= ?
                  )
                order by p.nome";
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([
            $idEmpresa,
            $inicio . ' 00:00:00', $fim . ' 23:59:59',
            $inicio . ' 00:00:00', $fim . ' 23:59:59',
        ]);

        $linhas = $consulta->fetchAll();
        foreach ($linhas as &$linha) {
            $linha['margem'] = (float) $linha['margem'];
            $linha['margem_minima'] = (float) $linha['margem_minima'];
            $linha['custo_total'] = (float) $linha['custo_total'];
        }
        return $linhas;
    }
}
