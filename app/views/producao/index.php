<?php include __DIR__ . '/../layout/header.php'; ?>
<?php
$produtos = $produtos ?? [];
$lancamentos = $lancamentos ?? [];
$csrf = $csrf ?? '';
$isAdmin = ($usuarioLogado['perfil'] ?? '') === 'administrador';
$servicos = ($segmentoEmpresa ?? '') === 'servicos';
$termo = $servicos ? 'execução' : 'produção';
$termoPlural = $servicos ? 'execuções' : 'produções';
$unidade = $servicos ? 'horas' : 'unidades';
?>
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div><h1 class="h3 fw-bold mb-1"><?= $servicos ? 'Execução de serviços' : 'Registro de produção' ?></h1>
            <p class="text-muted mb-0">Registre quantidades e preserve o custo unitário calculado no momento do lançamento.</p></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3">Novo registro</h2>
            <?php if (!$produtos): ?>
                <div class="alert alert-info mb-0">Não há produtos ou serviços ativos com ficha técnica e custo calculado. Cadastre uma ficha antes de registrar <?= e($termo) ?>.</div>
            <?php else: ?>
                <form method="post" action="<?= e(url('producao/registrar')) ?>" class="row g-3 align-items-end">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <div class="col-12 col-lg-7"><label class="form-label fw-semibold" for="id_produto"><?= $servicos ? 'Serviço executado' : 'Produto produzido' ?> *</label>
                        <select class="form-select" id="id_produto" name="id_produto" required><option value="">Selecione...</option>
                            <?php foreach ($produtos as $p): ?><option value="<?= (int) $p['id_produto'] ?>"><?= e($p['nome']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-5 col-lg-3"><label class="form-label fw-semibold" for="quantidade"><?= $servicos ? 'Horas executadas' : 'Quantidade' ?> *</label>
                        <input class="form-control" type="number" id="quantidade" name="quantidade" min="0.001" max="999999999.999" step="0.001" required placeholder="Ex.: 1,5">
                        <div class="form-text">Unidade de referência: <?= e($unidade) ?>.</div>
                    </div>
                    <div class="col-12 col-lg-2 d-grid"><button class="btn btn-primary" type="submit">Salvar registro</button></div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-3 py-3">
            <div><h2 class="h5 fw-bold mb-0">Histórico de <?= e($termoPlural) ?></h2><small class="text-muted">Até 200 registros, do mais recente para o mais antigo.</small></div>
            <form method="get" action="<?= e(url('producao')) ?>" class="row g-2 align-items-end">
                <div class="col-auto"><label class="form-label small mb-1" for="inicio">De</label><input type="date" class="form-control form-control-sm" id="inicio" name="inicio" value="<?= e($periodoInicio ?? '') ?>"></div>
                <div class="col-auto"><label class="form-label small mb-1" for="fim">Até</label><input type="date" class="form-control form-control-sm" id="fim" name="fim" value="<?= e($periodoFim ?? '') ?>"></div>
                <div class="col-auto"><button class="btn btn-sm btn-outline-primary" type="submit">Filtrar</button></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-3">Data e hora</th><th><?= $servicos ? 'Serviço' : 'Produto' ?></th><th class="text-end">Qtd. (<?= e($unidade) ?>)</th><th class="text-end">Custo unitário salvo</th><th class="text-end">Custo total</th><th>Responsável</th><?php if ($isAdmin): ?><th class="text-end pe-3">Ações</th><?php endif; ?></tr></thead>
                <tbody>
                <?php if (!$lancamentos): ?>
                    <tr><td colspan="<?= $isAdmin ? 7 : 6 ?>" class="text-center text-muted py-5">Nenhum registro encontrado no período selecionado.</td></tr>
                <?php else: foreach ($lancamentos as $l): ?>
                    <tr>
                        <td class="ps-3 text-nowrap"><?= date('d/m/Y H:i', strtotime($l['data_lancamento'])) ?></td>
                        <td class="fw-semibold"><?= e($l['produto']) ?></td>
                        <td class="text-end"><?= number_format((float) $l['quantidade'], 3, ',', '.') ?></td>
                        <td class="text-end text-nowrap">R$ <?= number_format((float) $l['custo_unitario'], 4, ',', '.') ?></td>
                        <td class="text-end text-nowrap fw-semibold">R$ <?= number_format((float) $l['custo_total'], 2, ',', '.') ?></td>
                        <td><?= e($l['usuario']) ?></td>
                        <?php if ($isAdmin): ?>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <form method="post" action="<?= e(url('producao/atualizar')) ?>" class="d-flex gap-1" aria-label="Corrigir quantidade do registro">
                                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id_lancamento" value="<?= (int) $l['id_lancamento'] ?>">
                                    <input class="form-control form-control-sm" style="width:90px" type="number" name="quantidade" min="0.001" step="0.001" value="<?= e(number_format((float) $l['quantidade'], 3, '.', '')) ?>" required>
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Salvar</button>
                                </form>
                                <form method="post" action="<?= e(url('producao/excluir')) ?>" onsubmit="return confirm('Excluir definitivamente este registro?');">
                                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id_lancamento" value="<?= (int) $l['id_lancamento'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($isAdmin): ?><p class="small text-muted mt-2">A correção de quantidade recalcula somente o custo total usando o custo unitário salvo. O custo unitário histórico não é atualizado.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
