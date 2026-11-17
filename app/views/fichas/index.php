<?php 
include __DIR__ . '/../layout/header.php'; 

// Mock de dados (Seção 12) - Simula o envio de $produtos pelo Controller
$produtos = $produtos ?? [
    ['id_produto' => 1, 'nome' => 'Bolo de Cenoura c/ Chocolate', 'tipo' => 'produto', 'preco_venda' => 25.00, 'custo_total' => 6.27, 'margem' => 21.6, 'margem_minima' => 30.0, 'abaixo_margem' => true, 'ativo' => true],
    ['id_produto' => 2, 'nome' => 'Pão Francês 50g (Lote 100)', 'tipo' => 'produto', 'preco_venda' => 80.00, 'custo_total' => 28.00, 'margem' => 42.5, 'margem_minima' => 30.0, 'abaixo_margem' => false, 'ativo' => true],
    ['id_produto' => 3, 'nome' => 'Refrigerante Cola 2L', 'tipo' => 'revenda', 'preco_venda' => 12.00, 'custo_total' => 8.50, 'margem' => 29.1, 'margem_minima' => 30.0, 'abaixo_margem' => true, 'ativo' => true]
];
$csrf = $csrf ?? 'token_de_teste';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Fichas Técnicas</h1>
            <p class="text-muted mb-0">Gestão de produtos, serviços, composição e precificação.</p>
        </div>
        <a href="<?= htmlspecialchars(url('fichas/novo')) ?>" class="btn btn-primary fw-bold shadow-sm">
            <span class="fs-5 me-1">+</span> Nova Ficha
        </a>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <!-- Filtros (Busca e Status) -->
        <div class="card-header bg-white border-bottom p-3 d-flex gap-3 bg-opacity-50">
            <div class="input-group" style="max-width: 350px;">
                <span class="input-group-text bg-white border-end-0 text-muted">🔍</span>
                <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar produto...">
            </div>
            <select class="form-select" style="max-width: 200px;">
                <option value="ativos">Apenas Ativos</option>
                <option value="inativos">Apenas Inativos</option>
                <option value="todos">Todos</option>
            </select>
        </div>
        
        <!-- Tabela -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4">Nome</th>
                        <th>Tipo</th>
                        <th>Preço de Venda</th>
                        <th>Custo Total</th>
                        <th>Margem Atual</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produtos)): ?>
                        <!-- Estado Vazio (Seção 9) -->
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <p class="fs-4 mb-2">📦</p>
                                <p class="mb-3">Nenhum produto cadastrado ainda.</p>
                                <a href="<?= htmlspecialchars(url('fichas/novo')) ?>" class="btn btn-outline-primary">Criar a primeira ficha</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($p['nome']) ?></td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= ucfirst(htmlspecialchars($p['tipo'])) ?></span>
                            </td>
                            <td class="text-muted">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></td>
                            
                            <!-- Destaque em vermelho se estiver abaixo da margem (Seção 6 e 8.3) -->
                            <td class="fw-bold <?= $p['abaixo_margem'] ? 'text-danger' : 'text-dark' ?>">
                                R$ <?= number_format($p['custo_total'], 2, ',', '.') ?>
                            </td>
                            
                            <td>
                                <?php 
                                    // Critério de cores: Vermelho (< min), Verde (>= min). (Âmbar pendente de definição - Seção 14)
                                    $badgeClass = $p['abaixo_margem'] ? 'bg-danger' : 'bg-success';
                                ?>
                                <span class="badge <?= $badgeClass ?> px-2 py-1 fs-6">
                                    <?= number_format($p['margem'], 1, ',', '.') ?>%
                                </span>
                            </td>
                            
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?= htmlspecialchars(url('fichas/editar/' . $p['id_produto'])) ?>" class="btn btn-sm btn-outline-primary" title="Editar">✏️</a>
                                    
                                    <!-- Inativar via POST (Seção 8.4) -->
                                    <form method="post" action="<?= htmlspecialchars(url('fichas/inativar')) ?>" class="d-inline" onsubmit="return confirm('Tem certeza que deseja inativar este produto?');">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="id_produto" value="<?= (int)$p['id_produto'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Inativar">🗑️</button>
                                    </form>
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

<?php include __DIR__ . '/../layout/footer.php'; ?>