<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="container-fluid py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">
    <span class="display-1 mb-3">🧭</span>
    <h1 class="h2 fw-bold text-dark mb-2">Página não encontrada</h1>
    <p class="text-muted fs-5 text-center mb-4" style="max-width: 500px;">
        Desculpe, o recurso que está a tentar aceder não existe, foi movido ou a rota está incorreta.
    </p>
    <button onclick="history.back()" class="btn btn-outline-secondary fw-bold px-4">
        Voltar à página anterior
    </button>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>