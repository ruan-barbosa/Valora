<?php

// Lançamentos históricos: o custo unitário, o preço de venda e o custo total ficam congelados no momento do registro.
class LancamentoProducao
{
    public static function registrar(int $idProduto, int $idUsuario, float $quantidade, float $custoUnitario, float $custoTotal, float $precoVenda): void
    {
        $sql = 'insert into lancamento_producao (id_produto, id_usuario, quantidade, custo_unitario, custo_total, preco_venda)
                values (?, ?, ?, ?, ?, ?)';
        Database::conectar()->prepare($sql)->execute([$idProduto, $idUsuario, $quantidade, $custoUnitario, $custoTotal, $precoVenda]);
    }

    /** Lista paginada por período; datas são validadas pelo controller antes da chamada. */
    public static function listarRecentes(int $idEmpresa, int $limite = 100, ?string $inicio = null, ?string $fim = null, ?int $idUsuario = null): array
    {
        $sql = 'select l.id_lancamento, l.data_lancamento, p.nome as produto, l.quantidade, l.custo_unitario,
                       l.custo_total, l.preco_venda, u.nome as usuario
                from lancamento_producao l
                join produto p on p.id_produto = l.id_produto
                join usuario u on u.id_usuario = l.id_usuario
                where p.id_empresa = ?';
        $parametros = [$idEmpresa];
        if ($idUsuario !== null) {
            $sql .= ' and l.id_usuario = ?';
            $parametros[] = $idUsuario;
        }
        if ($inicio !== null) {
            $sql .= ' and l.data_lancamento >= ?';
            $parametros[] = $inicio . ' 00:00:00';
        }
        if ($fim !== null) {
            $sql .= ' and l.data_lancamento <= ?';
            $parametros[] = $fim . ' 23:59:59';
        }
        $sql .= ' order by l.data_lancamento desc, l.id_lancamento desc limit ' . max(1, min(500, $limite));
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute($parametros);
        $linhas = $consulta->fetchAll();
        foreach ($linhas as &$linha) {
            foreach (['id_lancamento'] as $campo) $linha[$campo] = (int) $linha[$campo];
            foreach (['quantidade', 'custo_unitario', 'custo_total', 'preco_venda'] as $campo) $linha[$campo] = (float) $linha[$campo];
        }
        return $linhas;
    }

    public static function buscar(int $idLancamento, int $idEmpresa): ?array
    {
        $sql = 'select l.id_lancamento, l.quantidade, l.custo_unitario, l.custo_total, l.preco_venda,
                       l.data_lancamento, p.nome as produto
                from lancamento_producao l join produto p on p.id_produto = l.id_produto
                where l.id_lancamento = ? and p.id_empresa = ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idLancamento, $idEmpresa]);
        $linha = $consulta->fetch();
        if (!$linha) return null;
        foreach (['quantidade', 'custo_unitario', 'custo_total', 'preco_venda'] as $campo) $linha[$campo] = (float) $linha[$campo];
        $linha['id_lancamento'] = (int) $linha['id_lancamento'];
        return $linha;
    }

    /** Edita quantidade sem recalcular o custo unitário histórico. */
    public static function atualizarQuantidade(int $idLancamento, int $idEmpresa, float $quantidade): bool
    {
        $sql = 'update lancamento_producao l
                join produto p on p.id_produto = l.id_produto
                set l.quantidade = ?, l.custo_total = round(l.custo_unitario * ?, 2)
                where l.id_lancamento = ? and p.id_empresa = ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$quantidade, $quantidade, $idLancamento, $idEmpresa]);
        return $consulta->rowCount() > 0;
    }

    public static function excluir(int $idLancamento, int $idEmpresa): bool
    {
        $sql = 'delete l from lancamento_producao l
                join produto p on p.id_produto = l.id_produto
                where l.id_lancamento = ? and p.id_empresa = ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idLancamento, $idEmpresa]);
        return $consulta->rowCount() > 0;
    }

    public static function totalDoMes(int $idEmpresa): float
    {
        $sql = 'select coalesce(sum(l.quantidade), 0) from lancamento_producao l
                join produto p on p.id_produto = l.id_produto
                where p.id_empresa = ? and l.data_lancamento >= ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa, date('Y-m-01 00:00:00')]);
        return (float) $consulta->fetchColumn();
    }
}
