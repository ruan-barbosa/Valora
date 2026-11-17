<?php 
$usuarioLogado = $usuarioLogado ?? ['nome' => 'Usuário Teste', 'perfil' => 'administrador']; 
$perfil = $usuarioLogado['perfil'];
?>
<div class="bg-dark text-white border-end" id="sidebar-wrapper" style="width: 250px; min-height: 100vh;">
    <div class="sidebar-heading p-3 border-bottom fs-4 fw-bold">Valora</div>
    <div class="list-group list-group-flush">
        
        <?php if (in_array($perfil, ['administrador', 'gestor', 'comprador'])): ?>
            <a href="<?= htmlspecialchars(url('dashboard')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Dashboard</a>
        <?php endif; ?>

        <?php if (in_array($perfil, ['administrador', 'gestor'])): ?>
            <a href="<?= htmlspecialchars(url('fichas')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Fichas Técnicas</a>
            <a href="<?= htmlspecialchars(url('simulador')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Simulador</a>
        <?php endif; ?>

        <?php if (in_array($perfil, ['administrador', 'comprador'])): ?>
            <a href="<?= htmlspecialchars(url('insumos')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Insumos e Preços</a>
        <?php endif; ?>

        <?php if (in_array($perfil, ['administrador', 'producao'])): ?>
            <a href="<?= htmlspecialchars(url('producao')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Lançar Produção</a>
        <?php endif; ?>

        <?php if ($perfil === 'administrador'): ?>
            <a href="<?= htmlspecialchars(url('configuracoes')) ?>" class="list-group-item list-group-item-action bg-dark text-white">Configurações</a>
        <?php endif; ?>
        
    </div>
</div>