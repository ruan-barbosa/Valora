<?php 
include __DIR__ . '/../layout/header.php'; 

// Mock de dados (Seção 12)
$usuarioLogado =$usuarioLogado ?? ['nome' => 'Admin', 'perfil' => 'administrador'];
$perfil =$usuarioLogado['perfil'];

$resumo =$resumo ?? [
    'produtos_abaixo_margem' => 2,
    'alertas_nao_vistos' => 3,
    'producao_mes' => 1450
];

$alertas =$alertas ?? [
    ['id_alerta' => 1, 'produto' => 'Bolo de Cenoura', 'insumo' => 'Farinha de Trigo', 'margem_calculada' => 21.6, 'custo_calculado' => 6.27, 'data_alerta' => '2026-11-15', 'visto' => false],
    ['id_alerta' => 2, 'produto' => 'Refrigerante Cola 2L', 'insumo' => null, 'margem_calculada' => 29.1, 'custo_calculado' => 8.50, 'data_alerta' => '2026-11-16', 'visto' => false]
];

$insumos_filtro =$insumos_filtro ?? [
    ['id_item' => 1, 'nome' => 'Farinha de Trigo']
]; // Usado para o select do gráfico de evolução

$csrf =$csrf ?? 'token_de_teste';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Visão geral do desempenho e alertas do sistema.</p>
        </div>
        
        <!-- Filtro de Período (Ponto em aberto da Seção 14 resolvido com datas livres) -->
        <div class="d-flex gap-2 align-items-center bg-white p-2 rounded shadow-sm border">
            <label class="text-muted fw-semibold ms-2 small">Período:</label>
            <input type="date" id="filtro_inicio" class="form-control form-control-sm border-0 bg-light" value="<?= date('Y-m-01') ?>">
            <span class="text-muted">a</span>
            <input type="date" id="filtro_fim" class="form-control form-control-sm border-0 bg-light" value="<?= date('Y-m-d') ?>">
            <button id="btn-atualizar-graficos" class="btn btn-sm btn-primary fw-bold">Atualizar</button>
        </div>
    </div>

    <!-- Cards de Resumo (Comuns a perfis de gestão/admin) -->
    <?php if (in_array(\$perfil, ['administrador', 'gestor'])): ?>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1">Produtos Abaixo da Margem</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= (int)\$resumo['produtos_abaixo_margem'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1">Alertas Não Vistos</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= (int)\$resumo['alertas_nao_vistos'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1">Produção do Mês (Unid.)</h6>
                    <h2 class="display-5 fw-bold mb-0"><?= number_format(\$resumo['producao_mes'], 0, ',', '.') ?></h2>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <!-- ÁREA ESQUERDA: Alertas e Ações -->
        <div class="col-lg-5">
            
            <?php if (in_array(\$perfil, ['administrador', 'producao'])): ?>
            <!-- Atalho Produção (Seção 8.3) -->
            <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Chão de Fábrica</h5>
                        <p class="mb-0 opacity-75">Registe o volume produzido rapidamente.</p>
                    </div>
                    <a href="<?= htmlspecialchars(url('producao')) ?>" class="btn btn-light text-primary fw-bold">Lançar Produção</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array(\$perfil, ['administrador', 'comprador'])): ?>
            <!-- Atalho Comprador (Seção 8.3) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Histórico de Preços</h5>
                        <p class="text-muted mb-0">Consulte os últimos reajustes registados.</p>
                    </div>
                    <a href="<?= htmlspecialchars(url('insumos')) ?>" class="btn btn-outline-primary fw-bold">Ver Insumos</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array(\$perfil, ['administrador', 'gestor'])): ?>
            <!-- Alertas (Seção 8.3) -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Últimos Alertas</h5>
                    <span class="badge bg-warning text-dark"><?= (int)\$resumo['alertas_nao_vistos'] ?> novos</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty(\$alertas)): ?>
                            <li class="list-group-item p-4 text-center text-muted">Nenhum alerta recente.</li>
                        <?php else: ?>
                            <?php foreach ($alertas as$alerta): ?>
                                <?php 
                                    // A cor indica se a margem caiu abaixo do mínimo (Seção 8.3)
                                    $corTexto =$alerta['margem_calculada'] < 30 ? 'text-danger' : 'text-warning'; 
                                ?>
                                <li class="list-group-item p-3 <?= \$alerta['visto'] ? 'bg-light' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <strong class="<?= \$alerta['visto'] ? 'text-muted' : 'text-dark' ?>">
                                            <?= htmlspecialchars(\$alerta['produto']) ?>
                                        </strong>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime(\$alerta['data_alerta'])) ?></small>
                                    </div>
                                    <p class="mb-2 small text-muted">
                                        Novo custo calculado: <strong class="<?= \$corTexto ?>">R$<?= number_format($alerta['custo_calculado'], 2, ',', '.') ?></strong><br>
                                        Margem resultante: <strong class="<?= $corTexto ?>"><?= number_format($alerta['margem_calculada'], 1, ',', '.') ?>%</strong>
                                        <?php if (!empty(\$alerta['insumo'])): ?>
                                            <br><em>Impactado por: <?= htmlspecialchars(\$alerta['insumo']) ?></em>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!\$alerta['visto']): ?>
                                    <form method="post" action="<?= htmlspecialchars(url('alertas/visto')) ?>" class="d-flex justify-content-end">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(\$csrf) ?>">
                                        <input type="hidden" name="id_alerta" value="<?= (int)\$alerta['id_alerta'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">✔ Marcar como visto</button>
                                    </form>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ÁREA DIREITA: Gráficos -->
        <?php if (in_array(\$perfil, ['administrador', 'gestor'])): ?>
        <div class="col-lg-7">
            <!-- Gráfico 1: Margem por Produto -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <h5 class="fw-bold text-dark mb-0">Margem por Produto</h5>
                </div>
                <div class="card-body p-3">
                    <canvas id="graficoMargem" height="250"></canvas>
                </div>
            </div>

            <!-- Gráfico 2: Evolução de Preço -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Evolução do Preço de Insumo</h5>
                    <select id="filtro_insumo" class="form-select form-select-sm" style="max-width: 200px;">
                        <?php foreach ($insumos_filtro as$insumo): ?>
                            <option value="<?= $insumo['id_item'] ?>"><?= htmlspecialchars($insumo['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="card-body p-3">
                    <canvas id="graficoEvolucao" height="250"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Script exclusivo do Dashboard (Seção 7 e 11) -->
<?php if (in_array(\$perfil, ['administrador', 'gestor'])): ?>
    <script src="<?= htmlspecialchars(asset('js/dashboard.js')) ?>"></script>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>