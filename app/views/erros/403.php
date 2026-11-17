<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="container-fluid py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">
    <span class="display-1 mb-3">⛔</span>
    <h1 class="h2 fw-bold text-dark mb-2">Acesso Negado</h1>
    <p class="text-muted fs-5 text-center mb-4" style="max-width: 500px;">
        O seu perfil atual (<strong><?= htmlspecialchars($usuarioLogado['perfil'] ?? 'Desconhecido') ?></strong>) não tem permissões para aceder a esta área do sistema.
    </p>
    <a href="<?= htmlspecialchars(url('dashboard')) ?>" class="btn btn-primary fw-bold px-4">
        Voltar ao Dashboard
    </a>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>