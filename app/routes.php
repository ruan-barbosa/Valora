<?php

// Todas as rotas do sistema, seguindo o guia do front-end (seção 5 e 8).
// 3º parâmetro: Router::PUBLICA, null (qualquer usuário logado) ou lista de perfis.
// O administrador sempre tem acesso. Ajuste as permissões aqui quando o grupo confirmar.

$gestor     = ['gestor'];
$comprador  = ['comprador'];
$producao   = ['producao'];
$admin      = ['administrador'];

// Início e teste de funcionamento (remover /status antes de entregar)
$router->get('/', function () {
    (new Controller())->redirecionar(Auth::logado() ? 'dashboard' : 'login');
}, Router::PUBLICA);

$router->get('/status', function () {
    header('Content-Type: application/json; charset=utf-8');
    try {
        Database::conectar();
        echo json_encode(['app' => 'ok', 'banco' => 'ok']);
    } catch (Throwable $erro) {
        http_response_code(500);
        echo json_encode(['app' => 'ok', 'banco' => 'erro']);
    }
}, Router::PUBLICA);

// Acesso
$router->get('/login', 'AuthController@formLogin', Router::PUBLICA);
$router->post('/login', 'AuthController@login', Router::PUBLICA);
$router->get('/cadastro', 'AuthController@formCadastro', Router::PUBLICA);
$router->post('/cadastro', 'AuthController@cadastrar', Router::PUBLICA);
$router->get('/sair', 'AuthController@sair');
$router->get('/conta/senha', 'ContaController@formSenha');
$router->post('/conta/senha', 'ContaController@salvarSenha');

// Dashboard e alertas
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/graficos', 'DashboardController@graficos');
$router->post('/alertas/visto', 'AlertaController@marcarVisto', $gestor);

// Fichas técnicas (a produção só consulta)
$router->get('/fichas', 'FichaController@index', ['gestor', 'producao']);
$router->get('/fichas/novo', 'FichaController@novo', $gestor);
$router->get('/fichas/editar/{id}', 'FichaController@editar', $gestor);
$router->post('/fichas/salvar', 'FichaController@salvar', $gestor);
$router->post('/fichas/inativar', 'FichaController@inativar', $gestor);
$router->post('/fichas/calcular', 'FichaController@calcular', $gestor);

// Insumos e fornecedores
$router->get('/insumos', 'ItemController@index', $comprador);
$router->post('/insumos/salvar', 'ItemController@salvar', $comprador);
$router->post('/insumos/atualizar-preco', 'ItemController@atualizarPreco', $comprador);
$router->post('/insumos/inativar', 'ItemController@inativar', $comprador);
$router->get('/insumos/historico', 'ItemController@historico', ['comprador', 'gestor']);
$router->get('/fornecedores', 'FornecedorController@index', $comprador);
$router->post('/fornecedores/salvar', 'FornecedorController@salvar', $comprador);
$router->post('/fornecedores/inativar', 'FornecedorController@inativar', $comprador);

// Produção
$router->get('/producao', 'ProducaoController@index', $producao);
$router->post('/producao/registrar', 'ProducaoController@registrar', $producao);

// Simulador
$router->get('/simulador', 'SimuladorController@index', $gestor);
$router->post('/simulador/simular', 'SimuladorController@simular', $gestor);

// Configurações (só administrador)
$router->get('/configuracoes', 'ConfiguracaoController@index', $admin);
$router->post('/configuracoes/empresa', 'ConfiguracaoController@salvarEmpresa', $admin);
$router->post('/usuarios/salvar', 'ConfiguracaoController@salvarUsuario', $admin);
$router->post('/usuarios/inativar', 'ConfiguracaoController@inativarUsuario', $admin);
