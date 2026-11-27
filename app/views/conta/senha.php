<?php include __DIR__ . '/../layout/header.php'; ?>

<main class="container-fluid py-4" style="max-width: 760px;">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Trocar senha</h1>
            <p class="text-muted mb-0">Atualize a senha da sua conta Valora.</p>
        </div>
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('dashboard')) ?>">Voltar ao painel</a>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="post" action="<?= htmlspecialchars(url('conta/senha')) ?>" autocomplete="on">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">

                <div class="mb-3">
                    <label for="senha_atual" class="form-label">Senha atual</label>
                    <input class="form-control" type="password" id="senha_atual" name="senha_atual"
                           autocomplete="current-password" minlength="8" maxlength="72" required>
                </div>

                <div class="mb-3">
                    <label for="senha_nova" class="form-label">Nova senha</label>
                    <input class="form-control" type="password" id="senha_nova" name="senha_nova"
                           autocomplete="new-password" minlength="8" maxlength="72" required>
                    <div class="form-text">Use uma senha com pelo menos 8 caracteres.</div>
                </div>

                <div class="mb-4">
                    <label for="senha_confirmacao" class="form-label">Confirme a nova senha</label>
                    <input class="form-control" type="password" id="senha_confirmacao" name="senha_confirmacao"
                           autocomplete="new-password" minlength="8" maxlength="72" required>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit">Salvar nova senha</button>
                    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(url('dashboard')) ?>">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>
