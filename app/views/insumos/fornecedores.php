<?php 
include __DIR__ . '/../layout/header.php'; 

// Mock de dados (Seção 12)
$fornecedores = $fornecedores ?? [
    ['id_fornecedor' => 1, 'nome' => 'Moinho Sul', 'contato' => 'vendas@moinhosul.com.br', 'ativo' => true],
    ['id_fornecedor' => 2, 'nome' => 'Embalagens Flex', 'contato' => '(11) 99999-0000', 'ativo' => true],
];
$csrf = $csrf ?? 'token_de_teste';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Insumos e Preços</h1>
            <p class="text-muted mb-0">Gestão de materiais, mão de obra, máquinas e histórico de custos.</p>
        </div>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFornecedor">
            <span class="fs-5 me-1">+</span> Novo Fornecedor
        </button>
    </div>

    <!-- Navegação em Abas -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link text-muted" href="<?= htmlspecialchars(url('insumos')) ?>">Insumos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-bold" href="<?= htmlspecialchars(url('insumos/fornecedores')) ?>">Fornecedores</a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-4">Nome do Fornecedor</th>
                        <th>Contato</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fornecedores)): ?>
                        <tr>
                            <td colspan="3" class="text-center p-5 text-muted">Nenhum fornecedor cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fornecedores as $f): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($f['nome']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($f['contato']) ?></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <!-- Edição rápida via Modal (Mock visual) -->
                                    <button class="btn btn-sm btn-outline-primary" title="Editar">✏️</button>
                                    
                                    <form method="post" action="<?= htmlspecialchars(url('fornecedores/inativar')) ?>" class="d-inline" onsubmit="return confirm('Inativar este fornecedor?');">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="id_fornecedor" value="<?= (int)$f['id_fornecedor'] ?>">
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

<!-- Modal Novo/Editar Fornecedor (POST /fornecedores/salvar - Seção 8.5) -->
<div class="modal fade" id="modalFornecedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= htmlspecialchars(url('fornecedores/salvar')) ?>">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold">Dados do Fornecedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <!-- Se for edição, id_fornecedor seria preenchido aqui -->
                    
                    <div class="mb-3">
                        <label for="nomeFornecedor" class="form-label fw-semibold">Nome *</label>
                        <input type="text" id="nomeFornecedor" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="contato" class="form-label fw-semibold">Contato (Telefone/Email)</label>
                        <input type="text" id="contato" name="contato" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Salvar Fornecedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>