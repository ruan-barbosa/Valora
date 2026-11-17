<?php if (!empty($mensagem)): ?>
    <div class="alert alert-<?= $mensagem['tipo'] === 'sucesso' ? 'success' : 'danger' ?> alert-dismissible fade show mt-3" role="alert">
        <?= htmlspecialchars($mensagem['texto']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($erros)): ?>
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            <?php foreach ($erros as $erro): ?>
                <li><?= htmlspecialchars($erro) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>