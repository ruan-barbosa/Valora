<?php 
$perfil = $usuarioLogado['perfil'] ?? '';
?>
<aside class="offcanvas-lg offcanvas-start bg-dark text-white border-end" tabindex="-1" id="sidebar-wrapper" aria-labelledby="sidebar-title" style="--bs-offcanvas-width: 250px; min-height: 100vh;">
    <div class="sidebar-heading p-3 border-bottom fs-4 fw-bold d-flex justify-content-between align-items-center"><span id="sidebar-title">Valora</span><button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" aria-label="Fechar menu"></button></div>
    <div class="list-group list-group-flush">
        
        <?php if (in_array($perfil, ['administrador', 'gestor', 'comprador', 'producao'])): ?>
            <a href="<?= htmlspecialchars(url('dashboard')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Dashboard</a>
        <?php endif; ?>

        <?php if (in_array($perfil, ['administrador', 'gestor'])): ?>
            <a href="<?= htmlspecialchars(url('fichas')) ?>" class="list-group-item list-group-item-action bg-dark text-white"><?= ($segmentoEmpresa ?? '') === 'servicos' ? 'Fichas de Serviços' : 'Fichas Técnicas' ?></a>
        <?php endif; ?>
        <?php if (in_array($perfil, ['administrador', 'gestor'])): ?>
            <a href="<?= htmlspecialchars(url('simulador')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Simulador</a>
        <?php endif; ?>

        <?php if (in_array($perfil, ['administrador', 'comprador'])): ?>
            <a href="<?= htmlspecialchars(url('insumos')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Insumos e Preços</a>
        <?php endif; ?>

        <?php if (in_array($perfil, ['administrador', 'producao'])): ?>
            <a href="<?= htmlspecialchars(url('producao')) ?>" class="list-group-item list-group-item-action bg-dark text-white"><?= ($segmentoEmpresa ?? '') === 'servicos' ? 'Registrar Execução' : 'Lançar Produção' ?></a>
        <?php endif; ?>

        <?php if ($perfil === 'administrador'): ?>
            <a href="<?= htmlspecialchars(url('configuracoes')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Configurações</a>
        <?php endif; ?>
        
    </div>
</aside>