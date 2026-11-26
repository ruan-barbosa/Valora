<?php

// Conta do próprio usuário logado (trocar a senha).
class ContaController extends Controller
{
    public function formSenha(): void
    {
        $this->view('conta/senha');
    }

    public function salvarSenha(): void
    {
        $atual        = Validador::senha('senha_atual');
        $nova         = Validador::senha('senha_nova');
        $confirmacao  = Validador::senha('senha_confirmacao');
        $chave        = 'senha|' . Auth::id();

        if (LimiteTentativas::segundosBloqueado($chave) > 0) {
            $this->redirecionar('conta/senha', 'Muitas tentativas. Aguarde alguns minutos e tente de novo.', 'erro');
        }

        $usuario = Usuario::buscarPorId(Auth::id(), Auth::empresaId());
        if (!$usuario) {
            Auth::sair();
            Session::iniciar();
            $this->redirecionar('login');
        }

        $erros = [];

        if (!Auth::conferirSenha($atual, $usuario['senha_hash'])) {
            LimiteTentativas::registrar($chave, 5, 900, 900);
            $erros[] = 'A senha atual está incorreta.';
        }
        if (!Validador::senhaValida($nova)) {
            $erros[] = 'A nova senha deve ter de 8 a 72 caracteres.';
        } elseif ($nova !== $confirmacao) {
            $erros[] = 'A confirmação da nova senha não confere.';
        } elseif ($nova === $atual) {
            $erros[] = 'A nova senha deve ser diferente da atual.';
        }

        if ($erros) {
            $this->redirecionarComErros('conta/senha', $erros);
        }

        Usuario::atualizarHash(Auth::id(), Auth::hash($nova));
        LimiteTentativas::limpar($chave);
        Session::regenerar();

        $this->redirecionar('conta/senha', 'Senha alterada com sucesso.');
    }
}
