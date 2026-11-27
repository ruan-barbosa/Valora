<?php $csrf = $csrf ?? ''; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Valora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset('css/app.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center py-5" style="min-height: 100vh;">
    
    <main class="form-signin w-100 m-auto" style="max-width: 400px;">
        <div class="card border-0 shadow-sm p-4 bg-white rounded-4">
            <div class="text-center mb-4">
                <h1 class="h2 fw-bold text-primary mb-1">Valora</h1>
                <p class="text-muted">Gestão Inteligente de Custos</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger p-2 text-center shadow-sm" role="alert">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars(url('login')) ?>">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="login" name="login" placeholder="O seu utilizador" required>
                    <label for="login">Utilizador</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="A sua senha" required>
                    <label for="senha">Senha</label>
                </div>

                <button class="w-100 btn btn-lg btn-primary fw-bold mb-3 shadow-sm" type="submit">Entrar</button>
            </form>
            
            <div class="text-center mt-3 border-top pt-3">
                <span class="text-muted">Ainda não tem conta?</span><br>
                <a href="<?= htmlspecialchars(url('cadastro')) ?>" class="text-decoration-none fw-bold text-primary">Registe a sua Empresa</a>
            </div>
        </div>
    </main>

</body>
</html>