<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= htmlspecialchars(url()) ?>">
    <meta name="csrf" content="<?= htmlspecialchars($csrf ?? '') ?>">
    <title>Valora - Gestão de Custos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS da Aplicação -->
    <link href="<?= htmlspecialchars(asset('css/app.css')) ?>" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
<?php if (!empty($usuarioLogado)): ?>
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div class="flex-grow-1" id="page-content-wrapper" style="min-width: 0;">
            <?php include __DIR__ . '/topbar.php'; ?>
            <div class="px-4"><?php include __DIR__ . '/mensagens.php'; ?></div>
<?php else: ?>
        <div class="flex-grow-1" id="page-content-wrapper" style="min-width: 0;">
<?php endif; ?>
