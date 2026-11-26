<?php

// Dados da empresa e usuários (só o administrador acessa).
class ConfiguracaoController extends Controller
{
    public function index(): void
    {
        $idEmpresa = Auth::empresaId();

        $this->view('configuracoes/index', [
            'empresa'  => Empresa::buscar($idEmpresa),
            'usuarios' => Usuario::listarPorEmpresa($idEmpresa),
        ]);
    }

    public function salvarEmpresa(): void
    {
        $nome      = Validador::texto('nome');
        $segmento  = Validador::texto('segmento');
        $margemTxt = Validador::texto('margem_minima_padrao');
        $indirTxt  = Validador::texto('percentual_custos_indiretos');

        $erros = [];

        if (!Validador::tamanho($nome, 2, 100)) {
            $erros[] = 'Informe o nome da empresa (2 a 100 caracteres).';
        }
        if (!in_array($segmento, Validador::SEGMENTOS, true)) {
            $erros[] = 'Escolha o segmento da empresa.';
        }
        $margem = Validador::percentual($margemTxt);
        if ($margem === null) {
            $erros[] = 'A margem mínima deve ser um número entre 0 e 100.';
        }
        $indiretos = Validador::percentual($indirTxt);
        if ($indiretos === null) {
            $erros[] = 'O percentual de custos indiretos deve ser um número entre 0 e 100.';
        }

        if ($erros) {
            $this->redirecionarComErros('configuracoes', $erros, [
                'nome' => $nome, 'segmento' => $segmento,
                'margem_minima_padrao' => $margemTxt, 'percentual_custos_indiretos' => $indirTxt,
            ]);
        }

        Empresa::atualizar(Auth::empresaId(), $nome, $segmento, $margem, $indiretos);
        $this->atualizarSessao(['empresa_nome' => $nome]);

        $this->redirecionar('configuracoes', 'Dados da empresa atualizados.');
    }

    /** Cria o usuário (sem id_usuario) ou edita um existente (com id_usuario). */
    public function salvarUsuario(): void
    {
        $idEmpresa = Auth::empresaId();
        $id     = (int) Validador::texto('id_usuario');
        $nome   = Validador::texto('nome');
        $login  = strtolower(Validador::texto('login'));
        $perfil = Validador::texto('perfil');
        $senha  = Validador::senha('senha');

        $antigos = ['nome' => $nome, 'login' => $login, 'perfil' => $perfil];
        $erros = [];

        $existente = null;
        if ($id > 0) {
            $existente = Usuario::buscarPorId($id, $idEmpresa);
            if (!$existente) {
                $this->redirecionar('configuracoes', 'Usuário não encontrado.', 'erro');
            }
        }

        if (!Validador::tamanho($nome, 2, 100)) {
            $erros[] = 'Informe o nome do usuário (2 a 100 caracteres).';
        }
        if (!Validador::loginValido($login)) {
            $erros[] = 'O login deve ter de 3 a 50 caracteres, só com letras minúsculas, números, ponto, traço ou sublinhado.';
        } elseif (Usuario::loginExiste($login, $id > 0 ? $id : null)) {
            $erros[] = 'Esse login já está em uso. Escolha outro.';
        }
        if (!in_array($perfil, Validador::PERFIS, true)) {
            $erros[] = 'Escolha o perfil do usuário.';
        }

        if ($existente === null || $senha !== '') {
            if (!Validador::senhaValida($senha)) {
                $erros[] = 'A senha deve ter de 8 a 72 caracteres.';
            }
        }

        // A empresa nunca pode ficar sem um administrador ativo.
        if ($existente && $existente['perfil'] === 'administrador' && $perfil !== 'administrador'
            && Usuario::contarAdministradoresAtivos($idEmpresa, $id) === 0) {
            $erros[] = 'A empresa precisa ter pelo menos um administrador ativo.';
        }

        if ($erros) {
            $this->redirecionarComErros('configuracoes', $erros, $antigos);
        }

        try {
            if ($existente) {
                Usuario::atualizar($id, $idEmpresa, $nome, $login, $perfil, $senha !== '' ? Auth::hash($senha) : null);
                if ($id === Auth::id()) {
                    $this->atualizarSessao(['nome' => $nome, 'perfil' => $perfil]);
                }
                $mensagem = 'Usuário atualizado.';
            } else {
                Usuario::criar($idEmpresa, $nome, $login, Auth::hash($senha), $perfil);
                $mensagem = 'Usuário criado.';
            }
        } catch (PDOException $erro) {
            if (($erro->errorInfo[1] ?? 0) === 1062) {
                $this->redirecionarComErros('configuracoes', ['Esse login já está em uso. Escolha outro.'], $antigos);
            }
            throw $erro;
        }

        $this->redirecionar('configuracoes', $mensagem);
    }

    public function inativarUsuario(): void
    {
        $idEmpresa = Auth::empresaId();
        $id = (int) Validador::texto('id_usuario');

        $usuario = Usuario::buscarPorId($id, $idEmpresa);
        if (!$usuario) {
            $this->redirecionar('configuracoes', 'Usuário não encontrado.', 'erro');
        }
        if ($id === Auth::id()) {
            $this->redirecionar('configuracoes', 'Você não pode inativar o seu próprio acesso.', 'erro');
        }
        if ($usuario['perfil'] === 'administrador' && Usuario::contarAdministradoresAtivos($idEmpresa, $id) === 0) {
            $this->redirecionar('configuracoes', 'A empresa precisa ter pelo menos um administrador ativo.', 'erro');
        }

        Usuario::definirAtivo($id, $idEmpresa, false);
        $this->redirecionar('configuracoes', 'Usuário inativado.');
    }

    /** Mantém o que está na sessão igual ao banco depois de uma alteração. */
    private function atualizarSessao(array $campos): void
    {
        Session::set('usuario', array_merge(Auth::usuario(), $campos));
    }
}
