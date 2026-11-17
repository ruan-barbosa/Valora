<?php 
$empresaNome = $empresaNome ?? 'Empresa Exemplo Ltda';
$alertasNaoVistos = $alertasNaoVistos ?? 3;
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom w-100 px-3">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><?= htmlspecialchars($empresaNome) ?></span>
        
        <div class="ms-auto d-flex align-items-center">
            <!-- Sino de Alertas -->
            <?php if (in_array($usuarioLogado['perfil'], ['administrador', 'gestor'])): ?>
                <div class="position-relative me-4">
                    <span class="fs-5">🔔</span>
                    <?php if ($alertasNaoVistos > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= htmlspecialchars((string)$alertasNaoVistos) ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Menu do Usuário -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= htmlspecialchars($usuarioLogado['nome']) ?> (<?= htmlspecialchars($usuarioLogado['perfil']) ?>)
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                    <li><a class="dropdown-item" href="#">Trocar Senha</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= htmlspecialchars(url('sair')) ?>">Sair</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>