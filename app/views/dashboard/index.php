<?php 
include __DIR__ . '/../layout/header.php'; 

$usuarioLogado = $usuarioLogado ?? null;
$perfil = $usuarioLogado['perfil'] ?? '';
$resumo = $resumo ?? ['produtos_abaixo_margem' => 0, 'alertas_nao_vistos' => 0, 'producao_mes' => 0];
$alertas = $alertas ?? [];
$insumos_filtro = $insumos_filtro ?? [];
$csrf = $csrf ?? '';
$servicos = ($segmentoEmpresa ?? '') === 'servicos';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Visão geral do desempenho e alertas do sistema.</p>
        </div>
        
        <?php if (in_array($perfil, ['administrador', 'gestor'])): ?>
        <!-- O filtro controla os dois gráficos do painel de gestão. -->
        <div class="d-flex flex-wrap gap-2 align-items-center bg-white p-2 rounded shadow-sm border">
            <label class="text-muted fw-semibold ms-2 small" for="filtro_inicio">Período dos gráficos:</label>
            <input type="date" id="filtro_inicio" class="form-control form-control-sm border-0 bg-light" value="<?= date('Y-m-01') ?>">
            <span class="text-muted">a</span>
            <input type="date" id="filtro_fim" class="form-control form-control-sm border-0 bg-light" value="<?= date('Y-m-d') ?>">
            <button id="btn-atualizar-graficos" class="btn btn-sm btn-primary fw-bold">Atualizar</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Cards de Resumo (Comuns a perfis de gestão/admin) -->
    <?php if (in_array($perfil, ['administrador', 'gestor'])): ?>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1"><?= $servicos ? 'Serviços Abaixo da Margem' : 'Produtos Abaixo da Margem' ?></h6>
                    <h2 id="contador-produtos-abaixo-margem" class="display-5 fw-bold mb-0"><?= (int)$resumo['produtos_abaixo_margem'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1">Alertas Não Vistos</h6>
                    <h2 id="contador-alertas-nao-vistos" class="display-5 fw-bold mb-0"><?= (int)$resumo['alertas_nao_vistos'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1"><?= $servicos ? 'Horas Executadas no Mês' : 'Produção do Mês (Unid.)' ?></h6>
                    <h2 class="display-5 fw-bold mb-0"><?= number_format($resumo['producao_mes'], 0, ',', '.') ?></h2>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <!-- ÁREA ESQUERDA: Alertas e Ações -->
        <div class="col-lg-5">
            
            <?php if (in_array($perfil, ['administrador', 'producao'])): ?>
            <!-- Atalho Produção (Seção 8.3) -->
            <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1"><?= $servicos ? 'Execução de Serviços' : 'Chão de Fábrica' ?></h5>
                        <p class="mb-0 opacity-75"><?= $servicos ? 'Registre as horas executadas.' : 'Registre o volume produzido rapidamente.' ?></p>
                    </div>
                    <a href="<?= htmlspecialchars(url('producao')) ?>" class="btn btn-light text-primary fw-bold"><?= $servicos ? 'Registrar Execução' : 'Lançar Produção' ?></a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (in_array($perfil, ['administrador', 'comprador'])): ?>
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

            <?php if (in_array($perfil, ['administrador', 'gestor'])): ?>
            <!-- Alertas (Seção 8.3) -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Últimos Alertas</h5>
                    <span id="badge-alertas-nao-vistos" class="badge bg-warning text-dark"><?= (int)$resumo['alertas_nao_vistos'] ?> novos</span>
                </div>
                <div class="card-body p-0">
                    <ul id="lista-alertas" data-csrf="<?= htmlspecialchars($csrf) ?>" class="list-group list-group-flush">
                        <?php if (empty($alertas)): ?>
                            <li class="list-group-item p-4 text-center text-muted">Nenhum alerta recente.</li>
                        <?php else: ?>
                            <?php foreach ($alertas as$alerta): ?>
                                <?php 
                                    // A cor indica se a margem caiu abaixo do mínimo (Seção 8.3)
                                    $corTexto = $alerta['margem_calculada'] < $alerta['margem_minima'] ? 'text-danger' : 'text-warning'; 
                                ?>
                                <li class="list-group-item p-3 <?= $alerta['visto'] ? 'bg-light' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <strong class="<?= $alerta['visto'] ? 'text-muted' : 'text-dark' ?>">
                                            <?= htmlspecialchars($alerta['produto']) ?>
                                        </strong>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($alerta['data_alerta'])) ?></small>
                                    </div>
                                    <p class="mb-2 small text-muted">
                                        Novo custo calculado: <strong class="<?= $corTexto ?>">R$<?= number_format($alerta['custo_calculado'], 2, ',', '.') ?></strong><br>
                                        Margem resultante: <strong class="<?= $corTexto ?>"><?= number_format($alerta['margem_calculada'], 1, ',', '.') ?>%</strong>
                                        <?php if (!empty($alerta['insumo'])): ?>
                                            <br><em>Impactado por: <?= htmlspecialchars($alerta['insumo']) ?></em>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!$alerta['visto']): ?>
                                    <form method="post" action="<?= htmlspecialchars(url('alertas/visto')) ?>" class="d-flex justify-content-end">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="id_alerta" value="<?= (int)$alerta['id_alerta'] ?>">
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
        <?php if (in_array($perfil, ['administrador', 'gestor'])): ?>
        <div class="col-lg-7">
            <!-- Gráfico 1: Margem por <?= $servicos ? 'Serviço' : 'Produto' ?> -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Margem por <?= $servicos ? 'Serviço' : 'Produto' ?></h5>
                        <small class="text-muted">Última margem registrada dentro do período selecionado.</small>
                    </div>
                </div>
                <div class="card-body p-3">
                    <canvas id="graficoMargem" height="250"></canvas>
                    <p id="statusGraficoMargem" class="small text-muted text-center mt-2 mb-0" role="status" aria-live="polite"></p>
                </div>
            </div>

            <!-- Gráfico 2: Evolução de Preço -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Evolução do Preço de Insumo</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <select id="filtro_insumo" class="form-select form-select-sm" style="max-width: 200px;" <?= empty($insumos_filtro) ? 'disabled' : '' ?>>
                            <?php if (empty($insumos_filtro)): ?>
                                <option value="">Nenhum insumo cadastrado</option>
                            <?php else: ?>
                                <?php foreach ($insumos_filtro as $insumo): ?>
                                    <option value="<?= (int) $insumo['id_item'] ?>"><?= htmlspecialchars($insumo['nome']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-info js-abrir-historico"
                                data-bs-toggle="modal" data-bs-target="#modalHistoricoPrecos"
                                data-item-select="filtro_insumo" <?= empty($insumos_filtro) ? 'disabled' : '' ?>>
                            Histórico completo
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <canvas id="graficoEvolucao" height="250"></canvas>
                    <p id="statusGraficoEvolucao" class="small text-muted text-center mt-2 mb-0" role="status" aria-live="polite"></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal compartilhado de consulta detalhada do histórico de preços -->
<div class="modal fade" id="modalHistoricoPrecos" tabindex="-1" aria-labelledby="tituloHistoricoPrecos" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="tituloHistoricoPrecos">Histórico de preços</h5>
                    <p class="text-muted small mb-0" id="nomeHistoricoPrecos"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="statusHistoricoPrecos" class="alert alert-info py-2" role="status" aria-live="polite">Selecione um insumo.</div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Data da alteração</th><th class="text-end">Preço</th><th>Fornecedor</th><th>Registrado por</th></tr>
                        </thead>
                        <tbody id="tabelaHistoricoPrecos">
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhum histórico carregado.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button></div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars(asset('js/insumos.js')) ?>"></script>

<!-- Script exclusivo do Dashboard (Seção 7 e 11) -->
<?php if (in_array($perfil, ['administrador', 'gestor'])): ?>
    <script src="<?= htmlspecialchars(asset('js/dashboard.js')) ?>"></script>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>