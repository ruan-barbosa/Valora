<?php

// Tabela produto (produtos, serviços e itens de revenda). Toda consulta filtra pela empresa do usuário logado.
class Produto
{
    public const TIPOS = ['produto', 'servico', 'revenda'];

    public static function normalizar(array $produto): array
    {
        $produto['id_produto']    = (int) $produto['id_produto'];
        $produto['tipo']          = (string) $produto['tipo'];
        $produto['preco_venda']   = (float) $produto['preco_venda'];
        $produto['margem_minima'] = $produto['margem_minima'] !== null ? (float) $produto['margem_minima'] : null;
        $produto['ativo']         = (bool) $produto['ativo'];

        return $produto;
    }

    /** Lista de produtos. Com $ids, devolve só esses (desde que sejam da empresa). */
    public static function listar(int $idEmpresa, bool $incluirInativos = false, ?array $ids = null): array
    {
        if ($ids !== null && !$ids) {
            return [];
        }

        $sql = 'select id_produto, nome, tipo, preco_venda, margem_minima, ativo from produto where id_empresa = ?';
        $parametros = [$idEmpresa];

        if (!$incluirInativos) {
            $sql .= ' and ativo = 1';
        }
        if ($ids !== null) {
            $sql .= ' and id_produto in (' . implode(',', array_fill(0, count($ids), '?')) . ')';
            foreach ($ids as $id) {
                $parametros[] = (int) $id;
            }
        }
        $sql .= ' order by ativo desc, nome';

        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute($parametros);

        return array_map([self::class, 'normalizar'], $consulta->fetchAll());
    }

    public static function buscar(int $idProduto, int $idEmpresa): ?array
    {
        $sql = 'select id_produto, nome, tipo, preco_venda, margem_minima, ativo
                from produto where id_produto = ? and id_empresa = ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idProduto, $idEmpresa]);

        $produto = $consulta->fetch();
        return $produto ? self::normalizar($produto) : null;
    }

    public static function nomeExiste(int $idEmpresa, string $nome, int $ignorarId = 0): bool
    {
        $consulta = Database::conectar()->prepare('select 1 from produto where id_empresa = ? and nome = ? and id_produto <> ?');
        $consulta->execute([$idEmpresa, $nome, $ignorarId]);

        return (bool) $consulta->fetchColumn();
    }

    public static function criar(int $idEmpresa, string $nome, string $tipo, float $precoVenda, ?float $margemMinima): int
    {
        $pdo = Database::conectar();
        $sql = 'insert into produto (id_empresa, nome, tipo, preco_venda, margem_minima) values (?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$idEmpresa, $nome, $tipo, $precoVenda, $margemMinima]);

        return (int) $pdo->lastInsertId();
    }

    public static function atualizar(int $idProduto, int $idEmpresa, string $nome, string $tipo, float $precoVenda, ?float $margemMinima): void
    {
        $sql = 'update produto set nome = ?, tipo = ?, preco_venda = ?, margem_minima = ? where id_produto = ? and id_empresa = ?';
        Database::conectar()->prepare($sql)->execute([$nome, $tipo, $precoVenda, $margemMinima, $idProduto, $idEmpresa]);
    }

    public static function definirAtivo(int $idProduto, int $idEmpresa, bool $ativo): void
    {
        $sql = 'update produto set ativo = ? where id_produto = ? and id_empresa = ?';
        Database::conectar()->prepare($sql)->execute([$ativo ? 1 : 0, $idProduto, $idEmpresa]);
    }

    /** Ids dos produtos ativos da empresa que usam o insumo na ficha técnica. */
    public static function idsQueUsamItem(int $idItem, int $idEmpresa): array
    {
        $sql = 'select distinct p.id_produto from composicao c
                join produto p on p.id_produto = c.id_produto
                where c.id_item = ? and p.id_empresa = ? and p.ativo = 1';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idItem, $idEmpresa]);

        return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    }
}
