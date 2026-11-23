<?php

// Base de todos os controllers: mostra views, redireciona, responde JSON e lê dados enviados.
class Controller
{
    /** Mostra uma view (app/views/{nome}.php) com as variáveis comuns do layout mais as do controller. */
    protected function view(string $nome, array $dados = [], int $status = 200, string $textoAlternativo = ''): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');

        $arquivo = APP_PATH . '/views/' . $nome . '.php';

        // Enquanto o front-end não preencher o arquivo, mostra um aviso simples.
        if (!is_file($arquivo) || filesize($arquivo) === 0) {
            $this->viewPendente($nome, $textoAlternativo);
            return;
        }

        $variaveis = $dados + $this->variaveisComuns();

        // Função isolada: as views só enxergam as variáveis enviadas.
        (static function (string $__arquivo, array $__variaveis): void {
            extract($__variaveis, EXTR_SKIP);
            require $__arquivo;
        })($arquivo, $variaveis);
    }

    /** Variáveis disponíveis em todas as telas (combinadas com o front-end). */
    private function variaveisComuns(): array
    {
        $usuario = Auth::usuario();

        return [
            'usuarioLogado'    => $usuario ? ['id_usuario' => $usuario['id_usuario'], 'nome' => $usuario['nome'], 'perfil' => $usuario['perfil']] : null,
            'empresaNome'      => $usuario['empresa_nome'] ?? '',
            'alertasNaoVistos' => $this->contarAlertas(),
            'mensagem'         => Session::pegarFlash('mensagem'),
            'erros'            => Session::pegarFlash('erros', []),
            'antigos'          => Session::pegarFlash('antigos', []),
            'csrf'             => Csrf::token(),
        ];
    }

    /** Fica 0 até existir o model Alerta (fase E). */
    private function contarAlertas(): int
    {
        if (!Auth::logado() || !class_exists('Alerta') || !method_exists('Alerta', 'contarNaoVistos')) {
            return 0;
        }

        return (int) Alerta::contarNaoVistos(Auth::empresaId());
    }

    private function viewPendente(string $nome, string $textoAlternativo): void
    {
        $texto = $textoAlternativo !== ''
            ? $textoAlternativo
            : (Config::debug() ? "A tela app/views/{$nome}.php ainda não foi criada." : 'Página em construção.');

        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Valora</title></head><body>';
        echo '<p>' . e($texto) . '</p></body></html>';
    }

    /** Vai para outra rota. Se houver texto, aparece como mensagem na próxima tela. */
    public function redirecionar(string $rota, ?string $texto = null, string $tipo = 'sucesso'): void
    {
        if ($texto !== null) {
            Session::flash('mensagem', ['tipo' => $tipo, 'texto' => $texto]);
        }

        header('Location: ' . url($rota));
        exit;
    }

    /** Volta para a rota com a lista de erros e o que o usuário digitou. */
    protected function redirecionarComErros(string $rota, array $erros, array $antigos = []): void
    {
        Session::flash('erros', $erros);
        Session::flash('antigos', $antigos);
        $this->redirecionar($rota);
    }

    protected function json($dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Campo enviado por POST (texto sem espaços nas pontas; listas ficam como lista). */
    protected function post(string $campo, $padrao = '')
    {
        $valor = $_POST[$campo] ?? $padrao;
        return is_string($valor) ? trim($valor) : $valor;
    }

    /** Parâmetro da URL (?campo=valor). */
    protected function get(string $campo, $padrao = '')
    {
        $valor = $_GET[$campo] ?? $padrao;
        return is_string($valor) ? trim($valor) : $valor;
    }

    private function esperaJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function erro403(): void
    {
        if ($this->esperaJson()) {
            $this->json(['erro' => 'Acesso negado.'], 403);
        }
        $this->view('erros/403', [], 403, 'Você não tem permissão para acessar esta página.');
    }

    public function erro404(): void
    {
        if ($this->esperaJson()) {
            $this->json(['erro' => 'Não encontrado.'], 404);
        }
        $this->view('erros/404', [], 404, 'Página não encontrada.');
    }

    public function csrfInvalido(): void
    {
        if ($this->esperaJson()) {
            $this->json(['erro' => 'Sessão expirada. Recarregue a página.'], 403);
        }
        $this->view('erros/403', [], 403, 'Sessão expirada. Recarregue a página e tente de novo.');
    }

    /** Rota registrada cujo controller ainda não existe. */
    public function naoImplementado(string $acao): void
    {
        $texto = Config::debug() ? "Ainda não implementado: {$acao}" : 'Recurso em construção.';

        if ($this->esperaJson()) {
            $this->json(['erro' => $texto], 501);
        }

        http_response_code(501);
        header('Content-Type: text/html; charset=utf-8');
        echo '<p>' . e($texto) . '</p>';
    }
}