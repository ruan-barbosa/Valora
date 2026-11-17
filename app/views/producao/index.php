<?php 
include __DIR__ . '/../layout/header.php'; 

// Mock de dados para fase de desenvolvimento (Seção 12 e 8.6)
$produtos = $produtos ?? [
    ['id_produto' => 1, 'nome' => 'Bolo de Cenoura c/ Chocolate'],
    ['id_produto' => 2, 'nome' => 'Pão Francês 50g (Lote 100)']
];

$lancamentos = $lancamentos ?? [
    ['data_lancamento' => '2026-11-17 08:30:00', 'produto' => 'Pão Francês 50g (Lote 100)', 'quantidade' => 5, 'custo_unitario' => 28.00, 'custo_total' => 140.00, 'usuario' => 'Carlos (Produção)'],
    ['data_lancamento' => '2026-11-16 14:15:00', 'produto' => 'Bolo de Cenoura c/ Chocolate', 'quantidade' => 10, 'custo_unitario' => 6.27, 'custo_total' => 62.70, 'usuario' => 'Ana (Produção)']
];

$csrf = $csrf ?? 'token_de_teste';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-dark mb-1">Chão de Fábrica</h1>
            <p class="text-muted mb-0">Registe a produção finalizada (o custo exato deste momento ficará congelado no histórico).</p>
        </div>
    </div>

    <!-- Formulário Rápido com Campos Grandes (Seção 8.6) -->
    <div class="card border-0 shadow-sm mb-5 border-top border-primary border-4 bg-white">
        <div class="card-body p-4 p-md-5">
            <form method="post" action="<?= htmlspecialchars(url('producao/registrar')) ?>" class="row g-4 align-items-end">
                <!-- Proteção CSRF obrigatória (Seção 10) -->
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                
                <div class="col-md-7">
                    <label for="id_produto" class="form-label fw-bold fs-5 text-dark">O que foi produzido? *</label>
                    <select id="id_produto" name="id_produto" class="form-select form-select-lg shadow-sm" required>
                        <option value="">Toque para selecionar o produto...</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= (int)$p['id_produto'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="quantidade" class="form-label fw-bold fs-5 text-dark">Qtd *</label>
                    <input type="number" id="quantidade" name="quantidade" class="form-control form-control-lg text-center shadow-sm" min="1" placeholder="Ex: 10" required>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm py-3">
                        <span class="fs-5">Salvar Registo</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Histórico -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white border-bottom pt-4 pb-3">
            <h5 class="fw-bold text-dark mb-0">Histórico de Produção</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4">Data e Hora</th>
                            <th>Produto</th>
                            <th class="text-center">Qtd</th>
                            <th class="text-end">Custo Unitário Salvo</th>
                            <th class="text-end">Custo Total</th>
                            <th class="text-center pe-4">Responsável</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lancamentos)): ?>
                            <tr>
                                <td colspan="6" class="text-center p-5 text-muted">
                                    <span class="fs-3 mb-2 d-block">🏭</span>
                                    Nenhuma produção registada até ao momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lancamentos as $l): ?>
                            <tr>
                                <td class="ps-4 text-muted">
                                    <?= date('d/m/Y', strtotime($l['data_lancamento'])) ?> <br>
                                    <small><?= date('H:i', strtotime($l['data_lancamento'])) ?></small>
                                </td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($l['produto']) ?></td>
                                <td class="text-center fw-bold fs-5">
                                    <span class="badge bg-light text-dark border"><?= (int)$l['quantidade'] ?></span>
                                </td>
                                <!-- O custo unitário é exibido sem formatação condicional de margem, pois é histórico congelado -->
                                <td class="text-end text-muted">
                                    R$ <?= number_format($l['custo_unitario'], 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    R$ <?= number_format($l['custo_total'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center pe-4">
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                        <?= htmlspecialchars($l['usuario']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>