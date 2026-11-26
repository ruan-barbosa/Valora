<?php

// Limita tentativas repetidas (login, cadastro) e bloqueia por um tempo.
// Guarda o controle em arquivos em storage/tentativas, para não depender da sessão do navegador.
// Se a pasta não for gravável, o limite é ignorado e o erro vai para o log.
class LimiteTentativas
{
    private static function arquivo(string $chave): ?string
    {
        $pasta = BASE_PATH . '/storage/tentativas';

        if (!is_dir($pasta)) {
            @mkdir($pasta, 0755, true);
            @file_put_contents(dirname($pasta) . '/.htaccess', "Require all denied\n");
        }

        if (!is_dir($pasta) || !is_writable($pasta)) {
            error_log('LimiteTentativas: storage/tentativas não está gravável.');
            return null;
        }

        return $pasta . '/' . hash('sha256', $chave) . '.json';
    }

    private static function ler(string $arquivo): array
    {
        $json = is_file($arquivo) ? @file_get_contents($arquivo) : false;
        $dados = $json ? json_decode($json, true) : null;
        return is_array($dados) ? $dados : ['n' => 0, 'inicio' => 0, 'ate' => 0];
    }

    /** Quantos segundos faltam para o bloqueio acabar (0 = liberado). */
    public static function segundosBloqueado(string $chave): int
    {
        $arquivo = self::arquivo($chave);
        if ($arquivo === null) {
            return 0;
        }

        $dados = self::ler($arquivo);

        if ($dados['ate'] > time()) {
            return $dados['ate'] - time();
        }

        if ($dados['ate'] > 0) {
            @unlink($arquivo);   // o bloqueio acabou: recomeça do zero
        }

        return 0;
    }

    /** Conta uma tentativa. Ao chegar em $maximo dentro de $janela segundos, bloqueia por $bloqueio segundos. */
    public static function registrar(string $chave, int $maximo, int $janela, int $bloqueio): void
    {
        $arquivo = self::arquivo($chave);
        if ($arquivo === null) {
            return;
        }

        $dados = self::ler($arquivo);
        $agora = time();

        if ($dados['inicio'] === 0 || $agora - $dados['inicio'] > $janela) {
            $dados = ['n' => 0, 'inicio' => $agora, 'ate' => 0];
        }

        $dados['n']++;
        if ($dados['n'] >= $maximo) {
            $dados['ate'] = $agora + $bloqueio;
        }

        @file_put_contents($arquivo, json_encode($dados), LOCK_EX);
    }

    public static function limpar(string $chave): void
    {
        $arquivo = self::arquivo($chave);
        if ($arquivo !== null && is_file($arquivo)) {
            @unlink($arquivo);
        }
    }
}
