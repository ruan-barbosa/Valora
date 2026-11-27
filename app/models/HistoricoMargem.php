<?php

// Registra fotografias da margem sempre que um produto é recalculado.
// Isso permite que o filtro de período do dashboard use dados históricos reais.
class HistoricoMargem
{
    public static function registrar(int $idProduto, float $custo, float $margem, float $margemMinima): void
    {
        $pdo = Database::conectar();
        $custo = round($custo, 4);
        // A coluna decimal(7,2) aceita margens entre -99.999,99 e 99.999,99.
        $margem = max(-99999.99, min(99999.99, round($margem, 2)));
        $margemMinima = round($margemMinima, 2);

        // Não grava de novo se nada mudou desde a última fotografia deste produto.
        $ultima = $pdo->prepare('select custo_total, margem_calculada, margem_minima from historico_margem
                                 where id_produto = ? order by id_historico_margem desc limit 1');
        $ultima->execute([$idProduto]);
        $anterior = $ultima->fetch();
        if ($anterior
            && abs((float) $anterior['custo_total'] - $custo) < 0.00005
            && abs((float) $anterior['margem_calculada'] - $margem) < 0.005
            && abs((float) $anterior['margem_minima'] - $margemMinima) < 0.005) {
            return;
        }

        $sql = 'insert into historico_margem (id_produto, custo_total, margem_calculada, margem_minima)
                values (?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$idProduto, $custo, $margem, $margemMinima]);
    }

    /** Margem vigente de cada produto no fim do período (última fotografia até a data final). O início fica por compatibilidade: como só se grava quando algo muda, um produto sem mudanças no período continua com a margem anterior. */
    public static function ultimasPorProduto(int $idEmpresa, string $inicio, string $fim): array
    {
        $sql = "select p.id_produto, p.nome, h.margem_calculada as margem, h.margem_minima,
                       h.custo_total, h.data_registro
                from historico_margem h
                join produto p on p.id_produto = h.id_produto
                where p.id_empresa = ?
                  and p.ativo = 1
                  and h.id_historico_margem = (
                    select max(h2.id_historico_margem)
                    from historico_margem h2
                    where h2.id_produto = h.id_produto
                      and h2.data_registro <= ?
                  )
                order by p.nome";
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([
            $idEmpresa,
            $fim . ' 23:59:59',
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
