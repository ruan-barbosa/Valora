<?php include __DIR__ . '/../layout/header.php'; ?>

<?php
$servicos = ($segmentoEmpresa ?? '') === 'servicos';
$podeEditar = in_array($usuarioLogado['perfil'] ?? '', ['administrador', 'gestor'], true);
$rotulosTipo = [
    'produto' => 'Produto',
    'servico' => 'Serviço',
    'revenda' => 'Revenda',
];
?>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1"><?= $servicos ? 'Serviços e composições' : 'Produtos e fichas técnicas' ?></h1>
            <p class="text-muted mb-0"><?= $servicos ? 'Cadastre serviços, recursos e custos por execução.' : 'Cadastre produtos, serviços e itens de revenda, acompanhe custos e margens.' ?></p>
        </div>
        <?php if ($podeEditar): ?><a href="<?= e(url('fichas/novo')) ?>" class="btn btn-primary fw-bold shadow-sm">
            <span class="fs-5 me-1">+</span> Novo cadastro
        </a><?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <form method="get" action="<?= e(url('fichas')) ?>" class="row g-2 align-items-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <label class="visually-hidden" for="busca">Buscar produto</label>
                    <input type="search" id="busca" name="busca" class="form-control" placeholder="Buscar por nome..." value="<?= e($busca ?? '') ?>">
                </div>
                <div class="col-8 col-md-4 col-lg-3">
                    <label class="visually-hidden" for="status">Situação</label>
                    <select id="status" name="status" class="form-select">
                        <option value="ativos" <?= ($status ?? 'ativos') === 'ativos' ? 'selected' : '' ?>>Somente ativos</option>
                        <option value="inativos" <?= ($status ?? '') === 'inativos' ? 'selected' : '' ?>>Somente inativos</option>
                        <option value="todos" <?= ($status ?? '') === 'todos' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </div>
                <div class="col-4 col-md-2 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.4px;">
                    <tr>
                        <th class="ps-4">Nome</th>
                        <th>Tipo</th>
                        <th><?= $servicos ? 'Preço cobrado' : 'Preço de venda' ?></th>
                        <th>Custo total</th>
                        <th>Margem</th>
                        <th>Situação</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produtos)): ?>
                        <tr>
                            <td colspan="7" class="text-center p-5 text-muted">
                                <p class="fs-4 mb-2">📦</p>
                                <p class="mb-3">Nenhum cadastro encontrado para esse filtro.</p>
                                <?php if ($podeEditar): ?><a href="<?= e(url('fichas/novo')) ?>" class="btn btn-outline-primary">Criar primeiro cadastro</a><?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($produtos as $p): ?>
                            <?php
                            $abaixo = !empty($p['abaixo_margem']);
                            $temFicha = !empty($p['tem_ficha']);
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-dark"><?= e($p['nome']) ?></td>
                                <td><span class="badge bg-light text-secondary border"><?= e($rotulosTipo[$p['tipo']] ?? ucfirst($p['tipo'])) ?></span></td>
                                <td class="text-nowrap">R$ <?= number_format((float) $p['preco_venda'], 2, ',', '.') ?></td>
                                <td class="text-nowrap fw-semibold <?= $abaixo ? 'text-danger' : 'text-dark' ?>">
                                    R$ <?= number_format((float) $p['custo_total'], 2, ',', '.') ?>
                                    <?php if (!$temFicha): ?>
                                        <div><small class="text-warning">Sem composição cadastrada</small></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $abaixo ? 'bg-danger' : 'bg-success' ?> px-2 py-1 fs-6">
                                        <?= number_format((float) $p['margem'], 1, ',', '.') ?>%
                                    </span>
                                    <div><small class="text-muted">Mín.: <?= number_format((float) $p['margem_minima'], 1, ',', '.') ?>%</small></div>
                                </td>
                                <td>
                                    <?php if ($p['ativo']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if ($podeEditar): ?><div class="d-flex justify-content-end gap-2">
                                        <a href="<?= e(url('fichas/editar/' . (int) $p['id_produto'])) ?>" class="btn btn-sm btn-outline-primary" title="Editar" aria-label="Editar <?= e($p['nome']) ?>">Editar</a>
                                        <?php if ($p['ativo']): ?>
                                            <form method="post" action="<?= e(url('fichas/inativar')) ?>" class="d-inline" onsubmit="return confirm('Deseja realmente inativar este cadastro?');">
                                                <input type="hidden" name="csrf" value="<?= e($csrf ?? '') ?>"><input type="hidden" name="id_produto" value="<?= (int) $p['id_produto'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Inativar</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?= e(url('fichas/reativar')) ?>" class="d-inline">
                                                <input type="hidden" name="csrf" value="<?= e($csrf ?? '') ?>"><input type="hidden" name="id_produto" value="<?= (int) $p['id_produto'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success">Reativar</button>
                                            </form>
                                        <?php endif; ?>
                                    </div><?php else: ?><span class="text-muted small">Somente consulta</span><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
