<?php 
include __DIR__ . '/../layout/header.php'; 

$insumos = $insumos ?? [];
$fornecedores = $fornecedores ?? [];
$incluirInativos = $incluirInativos ?? false;
$csrf = $csrf ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Insumos e Preços</h1>
            <p class="text-muted mb-0">Gestão de materiais, mão de obra, máquinas e histórico de custos.</p>
        </div>
        <a href="<?= htmlspecialchars(url('insumos/novo')) ?>" class="btn btn-primary fw-bold shadow-sm">
            <span class="fs-5 me-1">+</span> Novo Insumo
        </a>
    </div>

    <!-- Navegação em Abas (Seção 8.5) -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active fw-bold" href="<?= htmlspecialchars(url('insumos')) ?>">Insumos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-muted" href="<?= htmlspecialchars(url('insumos/fornecedores')) ?>">Fornecedores</a>
        </li>
    </ul>

    <div class="d-flex justify-content-end mb-2"><form method="get" action="<?= htmlspecialchars(url('insumos')) ?>" class="form-check"><input class="form-check-input" type="checkbox" name="inativos" value="1" id="mostrar-inativos-insumos" <?= $incluirInativos ? 'checked' : '' ?> onchange="this.form.submit()"><label class="form-check-label small text-muted" for="mostrar-inativos-insumos">Mostrar insumos inativos</label></form></div>
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-4">Nome</th>
                        <th>Tipo</th>
                        <th>Preço Atual</th>
                        <th>Unidade</th>
                        <th>Último Fornecedor</th><th>Situação</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($insumos)): ?>
                        <tr>
                            <td colspan="7" class="text-center p-5 text-muted">
                                <p class="fs-4 mb-2">🌾</p>
                                <p class="mb-3">Nenhum insumo cadastrado.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($insumos as $i): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($i['nome']) ?></td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    <?= htmlspecialchars(str_replace('_', ' ', ucfirst($i['tipo']))) ?>
                                </span>
                            </td>
                            <td class="text-primary fw-bold">R$ <?= number_format($i['preco_atual'], 2, ',', '.') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($i['sigla']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($i['ultimo_fornecedor'] ?? '-') ?></td>
                            <td><?= !empty($i['ativo']) ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <!-- Atualizar Preço (Abre Modal) -->
                                    <button type="button" class="btn btn-sm btn-success text-white fw-bold" 
                                            data-bs-toggle="modal" data-bs-target="#modalPreco" 
                                            data-id="<?= (int)$i['id_item'] ?>" 
                                            data-nome="<?= htmlspecialchars($i['nome']) ?>"
                                            data-tipo="<?= htmlspecialchars($i['tipo']) ?>"
                                            title="Atualizar Preço">
                                        $
                                    </button>
                                    
                                    <!-- Ver Histórico -->
                                    <button type="button" class="btn btn-sm btn-outline-info js-abrir-historico"
                                            data-bs-toggle="modal" data-bs-target="#modalHistoricoPrecos"
                                            data-id="<?= (int) $i['id_item'] ?>"
                                            data-nome="<?= htmlspecialchars($i['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                            title="Consultar histórico de preços" aria-label="Consultar histórico de preços de <?= htmlspecialchars($i['nome'], ENT_QUOTES, 'UTF-8') ?>">
                                        📊
                                    </button>
                                    
                                    <a href="<?= htmlspecialchars(url('insumos/editar/' . $i['id_item'])) ?>" class="btn btn-sm btn-outline-primary" title="Editar">✏️</a>
                                    
                                    <?php if (!empty($i['ativo'])): ?>
                                        <form method="post" action="<?= htmlspecialchars(url('insumos/inativar')) ?>" class="d-inline" onsubmit="return confirm('Inativar este insumo?');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="id_item" value="<?= (int)$i['id_item'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Inativar">Inativar</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="<?= htmlspecialchars(url('insumos/reativar')) ?>" class="d-inline">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="id_item" value="<?= (int)$i['id_item'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Reativar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Atualizar Preço (Formulário Padrão - Seção 10) -->
<div class="modal fade" id="modalPreco" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= htmlspecialchars(url('insumos/atualizar-preco')) ?>">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold">Atualizar Preço: <span id="modalPrecoNome" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="id_item" id="modalPrecoId" value="">
                    
                    <div class="mb-3">
                        <label for="preco" class="form-label fw-semibold">Novo Preço (R$) *</label>
                        <input type="number" step="0.01" id="preco" name="preco" class="form-control" required>
                    </div>
                    
                    <div class="mb-3" id="divFornecedorModal">
                        <label for="id_fornecedor" class="form-label fw-semibold">Fornecedor</label>
                        <select id="id_fornecedor" name="id_fornecedor" class="form-select">
                            <option value="">Selecione... (Opcional p/ Mão de obra/Máquina)</option>
                            <?php foreach ($fornecedores as $f): ?>
                                <option value="<?= $f['id_fornecedor'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted">Obrigatório para insumos do tipo Material.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Salvar Novo Preço</button>
                </div>
            </form>
        </div>
    </div>
</div>




<!-- Modal de consulta do histórico de preços -->
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
                <div id="statusHistoricoPrecos" class="alert alert-info py-2" role="status" aria-live="polite">Carregando histórico...</div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data da alteração</th>
                                <th class="text-end">Preço</th>
                                <th>Fornecedor</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaHistoricoPrecos">
                            <tr><td colspan="4" class="text-center text-muted py-4">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars(asset('js/insumos.js')) ?>"></script>

<?php include __DIR__ . '/../layout/footer.php'; ?>