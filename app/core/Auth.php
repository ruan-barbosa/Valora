<?php

// Quem está logado e quais são as permissões. O login em si (consulta ao banco) vem na fase B.
class Auth
{
    /** Guarda o usuário na sessão. Receba um array com id_usuario, id_empresa, nome, perfil e empresa_nome. */
    public static function entrar(array $usuario): void
    {
        Session::regenerar();
        Csrf::renovar();
        Session::set('usuario', [
            'id_usuario'   => (int) $usuario['id_usuario'],
            'id_empresa'   => (int) $usuario['id_empresa'],
            'nome'         => (string) $usuario['nome'],
            'perfil'       => (string) $usuario['perfil'],
            'empresa_nome' => (string) ($usuario['empresa_nome'] ?? ''),
        ]);
    }

    public static function sair(): void
    {
        Session::destruir();
    }

    public static function logado(): bool
    {
        return Session::get('usuario') !== null;
    }

    public static function usuario(): ?array
    {
        return Session::get('usuario');
    }

    public static function id(): int
    {
        return (int) (self::usuario()['id_usuario'] ?? 0);
    }

    /** Empresa do usuário logado. Toda consulta ao banco deve filtrar por ela. */
    public static function empresaId(): int
    {
        return (int) (self::usuario()['id_empresa'] ?? 0);
    }

    public static function perfil(): string
    {
        return (string) (self::usuario()['perfil'] ?? '');
    }

    /** O administrador pode tudo; os demais, só os perfis listados. */
    public static function permite(array $perfis): bool
    {
        return self::perfil() === 'administrador' || in_array(self::perfil(), $perfis, true);
    }

    public static function hash(string $senha): string
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    public static function conferirSenha(string $senha, string $hash): bool
    {
        return password_verify($senha, $hash);
    }
}
