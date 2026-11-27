<?php include __DIR__ . '/../layout/header.php'; ?>
<?php
$fornecedores = $fornecedores ?? [];
$csrf = $csrf ?? '';
$incluirInativos = $incluirInativos ?? false;
?>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Insumos e Preços</h1>
            <p class="text-muted mb-0">Cadastre e mantenha os fornecedores utilizados nos preços dos insumos.</p>
        </div>
        <button class="btn btn-primary fw-bold" type="button" data-bs-toggle="modal" data-bs-target="#modalFornecedor" data-modo="novo">+ Novo fornecedor</button>
    </div>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link" href="<?= e(url('insumos')) ?>">Insumos</a></li>
        <li class="nav-item"><a class="nav-link active fw-bold" href="<?= e(url('insumos/fornecedores')) ?>">Fornecedores</a></li>
    </ul>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold">Fornecedores cadastrados</span>
            <form method="get" action="<?= e(url('insumos/fornecedores')) ?>" class="d-flex align-items-center gap-2">
                <label for="filtro-fornecedor-inativos" class="small text-muted">Exibir inativos</label>
                <input type="checkbox" class="form-check-input" id="filtro-fornecedor-inativos" name="inativos" value="1" <?= $incluirInativos ? 'checked' : '' ?> onchange="this.form.submit()">
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">Fornecedor</th><th>Contato</th><th>Situação</th><th class="text-end pe-4">Ações</th></tr></thead>
                <tbody>
                <?php if (!$fornecedores): ?>
                    <tr><td colspan="4" class="text-center text-muted py-5">Nenhum fornecedor encontrado.</td></tr>
                <?php else: foreach ($fornecedores as $f): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= e($f['nome']) ?></td>
                        <td><?= e($f['contato']) ?></td>
                        <td><?= $f['ativo'] ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFornecedor"
                                        data-modo="editar" data-id="<?= (int) $f['id_fornecedor'] ?>" data-nome="<?= e($f['nome']) ?>" data-contato="<?= e($f['contato']) ?>">Editar</button>
                                <?php if ($f['ativo']): ?>
                                    <form method="post" action="<?= e(url('fornecedores/inativar')) ?>" onsubmit="return confirm('Inativar este fornecedor?');">
                                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id_fornecedor" value="<?= (int) $f['id_fornecedor'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Inativar</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?= e(url('fornecedores/reativar')) ?>">
                                        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id_fornecedor" value="<?= (int) $f['id_fornecedor'] ?>">
                                        <button class="btn btn-sm btn-outline-success" type="submit">Reativar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFornecedor" tabindex="-1" aria-labelledby="tituloModalFornecedor" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" action="<?= e(url('fornecedores/salvar')) ?>">
            <div class="modal-header"><h2 class="modal-title fs-5 fw-bold" id="tituloModalFornecedor">Novo fornecedor</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id_fornecedor" id="id_fornecedor" value="">
                <div class="mb-3"><label class="form-label" for="nomeFornecedor">Nome *</label><input class="form-control" id="nomeFornecedor" name="nome" maxlength="100" required></div>
                <div><label class="form-label" for="contatoFornecedor">Contato</label><input class="form-control" id="contatoFornecedor" name="contato" maxlength="100" placeholder="Telefone ou e-mail"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar fornecedor</button></div>
        </form>
    </div></div>
</div>
<script src="<?= e(asset('js/fornecedores.js')) ?>"></script>
<?php include __DIR__ . '/../layout/footer.php'; ?>
