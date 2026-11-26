<?php

// Tabela usuario. As consultas feitas depois do login sempre filtram pela empresa do usuário logado.
class Usuario
{
    /** Usado só no login (o login é único no banco inteiro). Traz também o nome da empresa. */
    public static function buscarPorLogin(string $login): ?array
    {
        $sql = 'select u.id_usuario, u.id_empresa, u.nome, u.login, u.senha_hash, u.perfil, u.ativo, e.nome as empresa_nome
                from usuario u
                join empresa e on e.id_empresa = u.id_empresa
                where u.login = ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$login]);

        return $consulta->fetch() ?: null;
    }

    public static function buscarPorId(int $idUsuario, int $idEmpresa): ?array
    {
        $sql = 'select id_usuario, id_empresa, nome, login, senha_hash, perfil, ativo
                from usuario where id_usuario = ? and id_empresa = ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idUsuario, $idEmpresa]);

        return $consulta->fetch() ?: null;
    }

    public static function listarPorEmpresa(int $idEmpresa): array
    {
        $sql = 'select id_usuario, nome, login, perfil, ativo
                from usuario where id_empresa = ?
                order by ativo desc, nome';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa]);

        $usuarios = $consulta->fetchAll();
        foreach ($usuarios as &$usuario) {
            $usuario['ativo'] = (bool) $usuario['ativo'];
        }

        return $usuarios;
    }

    /** O login é único em todo o sistema, não só na empresa. */
    public static function loginExiste(string $login, ?int $ignorarId = null): bool
    {
        $sql = 'select 1 from usuario where login = ? and id_usuario <> ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$login, $ignorarId ?? 0]);

        return (bool) $consulta->fetchColumn();
    }

    public static function criar(int $idEmpresa, string $nome, string $login, string $senhaHash, string $perfil): int
    {
        $pdo = Database::conectar();
        $sql = 'insert into usuario (id_empresa, nome, login, senha_hash, perfil) values (?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$idEmpresa, $nome, $login, $senhaHash, $perfil]);

        return (int) $pdo->lastInsertId();
    }

    /** Se $senhaHash for null, a senha continua a mesma. */
    public static function atualizar(int $idUsuario, int $idEmpresa, string $nome, string $login, string $perfil, ?string $senhaHash): void
    {
        $pdo = Database::conectar();

        if ($senhaHash === null) {
            $sql = 'update usuario set nome = ?, login = ?, perfil = ? where id_usuario = ? and id_empresa = ?';
            $pdo->prepare($sql)->execute([$nome, $login, $perfil, $idUsuario, $idEmpresa]);
            return;
        }

        $sql = 'update usuario set nome = ?, login = ?, perfil = ?, senha_hash = ? where id_usuario = ? and id_empresa = ?';
        $pdo->prepare($sql)->execute([$nome, $login, $perfil, $senhaHash, $idUsuario, $idEmpresa]);
    }

    public static function atualizarHash(int $idUsuario, string $senhaHash): void
    {
        $sql = 'update usuario set senha_hash = ? where id_usuario = ?';
        Database::conectar()->prepare($sql)->execute([$senhaHash, $idUsuario]);
    }

    public static function definirAtivo(int $idUsuario, int $idEmpresa, bool $ativo): void
    {
        $sql = 'update usuario set ativo = ? where id_usuario = ? and id_empresa = ?';
        Database::conectar()->prepare($sql)->execute([$ativo ? 1 : 0, $idUsuario, $idEmpresa]);
    }

    /** Quantos administradores ativos a empresa tem, sem contar o usuário informado. */
    public static function contarAdministradoresAtivos(int $idEmpresa, int $exceto = 0): int
    {
        $sql = "select count(*) from usuario
                where id_empresa = ? and perfil = 'administrador' and ativo = 1 and id_usuario <> ?";
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa, $exceto]);

        return (int) $consulta->fetchColumn();
    }
}
