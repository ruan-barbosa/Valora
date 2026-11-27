<?php $csrf = $csrf ?? ''; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo - Valora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset('css/app.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center py-5" style="min-height: 100vh;">
    
    <main class="w-100 m-auto" style="max-width: 650px;">
        <div class="card border-0 shadow-sm p-4 p-md-5 bg-white rounded-4">
            <div class="text-center mb-4">
                <h1 class="h2 fw-bold text-primary mb-1">Crie a sua Conta</h1>
                <p class="text-muted">Configure o ambiente da sua empresa no Valora.</p>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger mt-3 shadow-sm">
                    <ul class="mb-0">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars(url('cadastro')) ?>" class="row g-4">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                
                <!-- Secção Empresa -->
                <div class="col-12 mb-2">
                    <h5 class="fw-bold border-bottom pb-2 text-dark">1. Dados da Empresa</h5>
                </div>
                
                <div class="col-md-12">
                    <label for="empresa_nome" class="form-label fw-bold">Nome da Empresa / Indústria *</label>
                    <input type="text" id="empresa_nome" name="empresa_nome" class="form-control form-control-lg bg-light" required>
                </div>
                <div class="col-md-6">
                    <label for="empresa_segmento" class="form-label fw-bold">Segmento *</label>
                    <select id="empresa_segmento" name="empresa_segmento" class="form-select form-select-lg bg-light" required>
                        <option value="">Selecione...</option>
                        <option value="industria">Indústria / Fabrico</option>
                        <option value="comercio">Comércio / Retalho</option>
                        <option value="servicos">Serviços / Restauração</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="empresa_margem_minima" class="form-label fw-bold">Margem Mínima Esperada (%) *</label>
                    <input type="number" step="0.1" id="empresa_margem_minima" name="empresa_margem_minima" class="form-control form-control-lg bg-light" value="30" required>
                </div>

                <!-- Secção Administrador -->
                <div class="col-12 mt-4 mb-2">
                    <h5 class="fw-bold border-bottom pb-2 text-dark">2. Perfil de Administrador</h5>
                </div>
                
                <div class="col-md-6">
                    <label for="nome" class="form-label fw-bold">O seu Nome *</label>
                    <input type="text" id="nome" name="nome" class="form-control bg-light" required>
                </div>
                <div class="col-md-6">
                    <label for="login" class="form-label fw-bold">Utilizador (Login) *</label>
                    <input type="text" id="login" name="login" class="form-control bg-light" required>
                </div>
                <div class="col-md-6">
                    <label for="senha" class="form-label fw-bold">Senha *</label>
                    <input type="password" id="senha" name="senha" class="form-control bg-light" required>
                </div>
                <div class="col-md-6">
                    <label for="senha_confirmacao" class="form-label fw-bold">Confirmar Senha *</label>
                    <input type="password" id="senha_confirmacao" name="senha_confirmacao" class="form-control bg-light" required>
                </div>

                <div class="col-12 mt-5">
                    <button class="w-100 btn btn-lg btn-success fw-bold shadow-sm py-3" type="submit">Concluir Registo</button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <a href="<?= htmlspecialchars(url('login')) ?>" class="text-decoration-none text-muted">Já tem uma conta? <strong class="text-primary">Faça login aqui</strong></a>
            </div>
        </div>
    </main>

</body>
</html>