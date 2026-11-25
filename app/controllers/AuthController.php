<?php

// Login, cadastro (empresa + administrador) e saída.
class AuthController extends Controller
{
    // Hash de uma senha qualquer: serve para o tempo de resposta ser o mesmo
    // quando o login não existe, sem revelar quais logins estão cadastrados.
    private const HASH_FALSO = '$2y$10$mstKMFcXd.jSmjIBb37keu5bmPkE7R.cGiSd6jg6.stT7oI6Nffky';

    public function formLogin(): void
    {
        if (Auth::logado()) {
            $this->redirecionar('dashboard');
        }

        $this->view('auth/login', ['erro' => Session::pegarFlash('erro', '')]);
    }

    public function login(): void
    {
        $login = strtolower(Validador::texto('login'));
        $senha = Validador::senha('senha');
        $ip    = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $chaveUsuario = "login|{$login}|{$ip}";
        $chaveIp      = "login-ip|{$ip}";

        $espera = max(
            LimiteTentativas::segundosBloqueado($chaveUsuario),
            LimiteTentativas::segundosBloqueado($chaveIp)
        );
        if ($espera > 0) {
            $minutos = (int) ceil($espera / 60);
            $this->erroLogin("Muitas tentativas. Tente novamente em {$minutos} minuto(s).");
        }

        $usuario = ($login !== '' && $senha !== '') ? Usuario::buscarPorLogin($login) : null;

        // A senha é sempre conferida, mesmo sem usuário, para o tempo não denunciar se o login existe.
        $confere = Auth::conferirSenha($senha, $usuario['senha_hash'] ?? self::HASH_FALSO);

        if (!$usuario || !$confere) {
            LimiteTentativas::registrar($chaveUsuario, 5, 900, 900);
            LimiteTentativas::registrar($chaveIp, 20, 900, 900);
            $this->erroLogin('Login ou senha inválidos.');
        }

        if (!$usuario['ativo']) {
            $this->erroLogin('Este usuário está inativo. Fale com o administrador da empresa.');
        }

        LimiteTentativas::limpar($chaveUsuario);

        // Se o algoritmo de hash do PHP evoluiu, atualiza o hash guardado.
        if (password_needs_rehash($usuario['senha_hash'], PASSWORD_DEFAULT)) {
            Usuario::atualizarHash((int) $usuario['id_usuario'], Auth::hash($senha));
        }

        Auth::entrar($usuario);
        $this->redirecionar('dashboard');
    }

    private function erroLogin(string $texto): void
    {
        Session::flash('erro', $texto);
        $this->redirecionar('login');
    }

    public function sair(): void
    {
        Auth::sair();
        Session::iniciar();   // nova sessão, só para mostrar a mensagem
        $this->redirecionar('login', 'Você saiu do sistema.');
    }

    public function formCadastro(): void
    {
        if (Auth::logado()) {
            $this->redirecionar('dashboard');
        }

        $this->view('auth/cadastro');
    }

    /** Cria a empresa e o administrador juntos, na mesma transação. */
    public function cadastrar(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $chaveCadastro = "cadastro|{$ip}";

        $empresaNome    = Validador::texto('empresa_nome');
        $segmento       = Validador::texto('empresa_segmento');
        $margemTexto    = Validador::texto('empresa_margem_minima');
        $nome           = Validador::texto('nome');
        $login          = strtolower(Validador::texto('login'));
        $senha          = Validador::senha('senha');
        $confirmacao    = Validador::senha('senha_confirmacao');

        $antigos = [
            'empresa_nome'          => $empresaNome,
            'empresa_segmento'      => $segmento,
            'empresa_margem_minima' => $margemTexto,
            'nome'                  => $nome,
            'login'                 => $login,
        ];

        $erros = [];

        $espera = LimiteTentativas::segundosBloqueado($chaveCadastro);
        if ($espera > 0) {
            $minutos = (int) ceil($espera / 60);
            $erros[] = "Muitos cadastros feitos a partir deste endereço. Tente novamente em {$minutos} minuto(s).";
        }

        if (!Validador::tamanho($empresaNome, 2, 100)) {
            $erros[] = 'Informe o nome da empresa (2 a 100 caracteres).';
        }
        if (!in_array($segmento, Validador::SEGMENTOS, true)) {
            $erros[] = 'Escolha o segmento da empresa.';
        }
        $margem = Validador::percentual($margemTexto);
        if ($margem === null) {
            $erros[] = 'A margem mínima deve ser um número entre 0 e 100.';
        }
        if (!Validador::tamanho($nome, 2, 100)) {
            $erros[] = 'Informe o seu nome (2 a 100 caracteres).';
        }
        if (!Validador::loginValido($login)) {
            $erros[] = 'O login deve ter de 3 a 50 caracteres, só com letras minúsculas, números, ponto, traço ou sublinhado.';
        }
        if (!Validador::senhaValida($senha)) {
            $erros[] = 'A senha deve ter de 8 a 72 caracteres.';
        } elseif ($senha !== $confirmacao) {
            $erros[] = 'A confirmação da senha não confere.';
        }

        if (!$erros && Usuario::loginExiste($login)) {
            $erros[] = 'Esse login já está em uso. Escolha outro.';
        }

        if ($erros) {
            $this->redirecionarComErros('cadastro', $erros, $antigos);
        }

        $pdo = Database::conectar();

        try {
            $pdo->beginTransaction();
            $idEmpresa = Empresa::criar($empresaNome, $segmento, $margem);
            $idUsuario = Usuario::criar($idEmpresa, $nome, $login, Auth::hash($senha), 'administrador');
            $pdo->commit();
        } catch (PDOException $erro) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // 1062 = login repetido (duas pessoas cadastrando o mesmo login ao mesmo tempo)
            if (($erro->errorInfo[1] ?? 0) === 1062) {
                $this->redirecionarComErros('cadastro', ['Esse login já está em uso. Escolha outro.'], $antigos);
            }
            throw $erro;
        }

        LimiteTentativas::registrar($chaveCadastro, 5, 3600, 3600);

        Auth::entrar([
            'id_usuario'   => $idUsuario,
            'id_empresa'   => $idEmpresa,
            'nome'         => $nome,
            'perfil'       => 'administrador',
            'empresa_nome' => $empresaNome,
        ]);

        $this->redirecionar('dashboard', 'Empresa cadastrada com sucesso. Bem-vindo ao Valora!');
    }
}