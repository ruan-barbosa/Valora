<?php

// Tabela alerta: avisos ativos quando a margem de um produto fica abaixo do mínimo.
class Alerta
{
    public static function contarNaoVistos(int $idEmpresa): int
    {
        $sql = 'select count(*) from alerta a join produto p on p.id_produto = a.id_produto
                where p.id_empresa = ? and a.visto = 0 and a.resolvido = 0';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa]);
        return (int) $consulta->fetchColumn();
    }

    /** Alertas ainda ativos; não vistos aparecem primeiro. */
    public static function listarRecentes(int $idEmpresa, int $limite = 10): array
    {
        $sql = 'select a.id_alerta, p.nome as produto, i.nome as insumo, a.margem_calculada, a.custo_calculado,
                       a.data_alerta, a.visto, coalesce(p.margem_minima, e.margem_minima_padrao) as margem_minima
                from alerta a
                join produto p on p.id_produto = a.id_produto
                join empresa e on e.id_empresa = p.id_empresa
                left join item i on i.id_item = a.id_item
                where p.id_empresa = ? and a.resolvido = 0
                order by a.visto asc, a.data_alerta desc, a.id_alerta desc
                limit ' . max(1, min(100, $limite));
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa]);
        $alertas = $consulta->fetchAll();
        foreach ($alertas as &$alerta) {
            $alerta['id_alerta'] = (int) $alerta['id_alerta'];
            $alerta['margem_calculada'] = (float) $alerta['margem_calculada'];
            $alerta['custo_calculado'] = (float) $alerta['custo_calculado'];
            $alerta['margem_minima'] = (float) $alerta['margem_minima'];
            $alerta['visto'] = (bool) $alerta['visto'];
        }
        return $alertas;
    }

    /** Atualiza o alerta ativo mais recente do produto ou cria um novo. */
    public static function registrar(int $idProduto, ?int $idItem, float $margem, float $custo): void
    {
        $pdo = Database::conectar();
        $margem = max(-99999.99, min(99999.99, round($margem, 2)));
        $consulta = $pdo->prepare('select id_alerta from alerta where id_produto = ? and resolvido = 0 order by id_alerta desc limit 1');
        $consulta->execute([$idProduto]);
        $idAlerta = $consulta->fetchColumn();

        if ($idAlerta) {
            $sql = 'update alerta set id_item = ?, margem_calculada = ?, custo_calculado = ?, data_alerta = now(), visto = 0
                    where id_alerta = ?';
            $pdo->prepare($sql)->execute([$idItem, $margem, $custo, $idAlerta]);
            return;
        }

        $sql = 'insert into alerta (id_produto, id_item, margem_calculada, custo_calculado, resolvido)
                values (?, ?, ?, ?, 0)';
        $pdo->prepare($sql)->execute([$idProduto, $idItem, $margem, $custo]);
    }

    /** Quando a margem volta ao mínimo, encerra os avisos ativos daquele produto. */
    public static function resolverProduto(int $idProduto): void
    {
        $sql = 'update alerta set resolvido = 1, visto = 1 where id_produto = ? and resolvido = 0';
        Database::conectar()->prepare($sql)->execute([$idProduto]);
    }

    public static function marcarVisto(int $idAlerta, int $idEmpresa): bool
    {
        $sql = 'update alerta a join produto p on p.id_produto = a.id_produto
                set a.visto = 1 where a.id_alerta = ? and p.id_empresa = ? and a.resolvido = 0';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idAlerta, $idEmpresa]);
        return $consulta->rowCount() > 0;
    }
}
