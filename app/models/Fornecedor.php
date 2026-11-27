<?php

// Tabela fornecedor. Toda consulta filtra pela empresa do usuário logado.
class Fornecedor
{
    public static function listar(int $idEmpresa, bool $incluirInativos = false): array
    {
        // contato nunca volta vazio (null): as telas escapam o texto sem verificar.
        $sql = 'select id_fornecedor, nome, coalesce(contato, "") as contato, ativo
                from fornecedor
                where id_empresa = ?' . ($incluirInativos ? '' : ' and ativo = 1') . '
                order by ativo desc, nome';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa]);

        $fornecedores = $consulta->fetchAll();
        foreach ($fornecedores as &$fornecedor) {
            $fornecedor['ativo'] = (bool) $fornecedor['ativo'];
        }

        return $fornecedores;
    }

    public static function buscar(int $idFornecedor, int $idEmpresa): ?array
    {
        $sql = 'select id_fornecedor, nome, coalesce(contato, "") as contato, ativo
                from fornecedor where id_fornecedor = ? and id_empresa = ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idFornecedor, $idEmpresa]);

        $fornecedor = $consulta->fetch();
        if ($fornecedor) {
            $fornecedor['ativo'] = (bool) $fornecedor['ativo'];
        }

        return $fornecedor ?: null;
    }

    /** O fornecedor existe, é desta empresa e está ativo? Usado antes de ligá-lo a um preço. */
    public static function ativoDaEmpresa(int $idFornecedor, int $idEmpresa): bool
    {
        $sql = 'select 1 from fornecedor where id_fornecedor = ? and id_empresa = ? and ativo = 1';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idFornecedor, $idEmpresa]);

        return (bool) $consulta->fetchColumn();
    }

    public static function nomeExiste(int $idEmpresa, string $nome, int $ignorarId = 0): bool
    {
        $sql = 'select 1 from fornecedor where id_empresa = ? and nome = ? and id_fornecedor <> ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa, $nome, $ignorarId]);

        return (bool) $consulta->fetchColumn();
    }

    public static function criar(int $idEmpresa, string $nome, string $contato): int
    {
        $pdo = Database::conectar();
        $sql = 'insert into fornecedor (id_empresa, nome, contato) values (?, ?, ?)';
        $pdo->prepare($sql)->execute([$idEmpresa, $nome, $contato !== '' ? $contato : null]);

        return (int) $pdo->lastInsertId();
    }

    public static function atualizar(int $idFornecedor, int $idEmpresa, string $nome, string $contato): void
    {
        $sql = 'update fornecedor set nome = ?, contato = ? where id_fornecedor = ? and id_empresa = ?';
        Database::conectar()->prepare($sql)->execute([$nome, $contato !== '' ? $contato : null, $idFornecedor, $idEmpresa]);
    }

    public static function definirAtivo(int $idFornecedor, int $idEmpresa, bool $ativo): void
    {
        $sql = 'update fornecedor set ativo = ? where id_fornecedor = ? and id_empresa = ?';
        Database::conectar()->prepare($sql)->execute([$ativo ? 1 : 0, $idFornecedor, $idEmpresa]);
    }
}
