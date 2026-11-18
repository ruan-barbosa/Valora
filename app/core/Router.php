<?php

// Liga cada endereço a um método de controller e confere login, perfil e token CSRF antes de executar.
class Router
{
    public const PUBLICA = 'publica';

    private array $rotas = [];

    /**
     * $acao:   'FichaController@index' ou uma função.
     * $acesso: Router::PUBLICA (sem login), null (qualquer usuário logado) ou lista de perfis permitidos.
     *          O administrador sempre tem acesso.
     */
    public function get(string $caminho, $acao, $acesso = null): void
    {
        $this->adicionar('GET', $caminho, $acao, $acesso);
    }

    public function post(string $caminho, $acao, $acesso = null): void
    {
        $this->adicionar('POST', $caminho, $acao, $acesso);
    }

    private function adicionar(string $metodo, string $caminho, $acao, $acesso): void
    {
        // /fichas/editar/{id} vira uma expressão que captura o id.
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $caminho) . '$#';
        $this->rotas[] = compact('metodo', 'regex', 'acao', 'acesso');
    }

    public function despachar(string $metodo, string $caminho): void
    {
        $metodo = strtoupper($metodo);
        $base = new Controller();
        $metodosDoCaminho = [];

        foreach ($this->rotas as $rota) {
            if (!preg_match($rota['regex'], $caminho, $encontrados)) {
                continue;
            }

            $metodosDoCaminho[] = $rota['metodo'];
            if ($rota['metodo'] !== $metodo) {
                continue;
            }

            if (!$this->autorizado($rota['acesso'], $base)) {
                return;
            }

            if ($metodo === 'POST' && !Csrf::validar()) {
                $base->csrfInvalido();
                return;
            }

            $parametros = array_values(array_filter($encontrados, 'is_string', ARRAY_FILTER_USE_KEY));
            $this->executar($rota['acao'], $parametros, $base);
            return;
        }

        if ($metodosDoCaminho) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($metodosDoCaminho)));
            echo 'Método não permitido.';
            return;
        }

        $base->erro404();
    }

    private function autorizado($acesso, Controller $base): bool
    {
        if ($acesso === self::PUBLICA) {
            return true;
        }

        if (!Auth::logado()) {
            $base->redirecionar('login');
            return false;
        }

        if ($acesso === null || Auth::permite($acesso)) {
            return true;
        }

        $base->erro403();
        return false;
    }

    private function executar($acao, array $parametros, Controller $base): void
    {
        if ($acao instanceof Closure) {
            $acao(...$parametros);
            return;
        }

        [$classe, $metodo] = explode('@', $acao);

        if (!class_exists($classe) || !method_exists($classe, $metodo)) {
            $base->naoImplementado($acao);
            return;
        }

        (new $classe())->$metodo(...$parametros);
    }
}
