<?php include __DIR__ . '/../layout/header.php'; ?>

<?php
// Variáveis: $insumo, $unidades, $fornecedores, $tipos, $antigos (dados digitados em caso de erro), $csrf
$editando = (int) ($insumo['id_item'] ?? 0) > 0;
$antigos  = $antigos ?? [];

$valor = fn(string $campo, $padrao = '') => $antigos[$campo] ?? $padrao;

$nome         = $valor('nome', $insumo['nome'] ?? '');
$tipo         = $valor('tipo', $insumo['tipo'] ?? 'material');
$idUnidade    = (int) $valor('id_unidade', $insumo['id_unidade'] ?? 0);
$preco        = $valor('preco', isset($insumo['preco_atual']) && $editando ? $insumo['preco_atual'] : '');
$idFornecedor = (int) $valor('id_fornecedor', $insumo['id_ultimo_fornecedor'] ?? 0);

$nomesTipo = ['material' => 'Material', 'mao_de_obra' => 'Mão de obra', 'maquina' => 'Máquina'];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1"><?= $editando ? 'Editar Insumo' : 'Novo Insumo' ?></h1>
            <p class="text-muted mb-0">Material, mão de obra ou máquina usados nas fichas técnicas.</p>
        </div>
        <a href="<?= e(url('insumos')) ?>" class="btn btn-outline-secondary">Voltar</a>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 720px;">
        <div class="card-body p-4">
            <form method="post" action="<?= e(url('insumos/salvar')) ?>">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="id_item" value="<?= (int) ($insumo['id_item'] ?? 0) ?>">

                <div class="mb-3">
                    <label for="nome" class="form-label fw-semibold">Nome *</label>
                    <input type="text" id="nome" name="nome" class="form-control" maxlength="100"
                           value="<?= e($nome) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="tipo" class="form-label fw-semibold">Tipo *</label>
                        <select id="tipo" name="tipo" class="form-select" required>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= e($t) ?>" <?= $tipo === $t ? 'selected' : '' ?>>
                                    <?= e($nomesTipo[$t] ?? $t) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="id_unidade" class="form-label fw-semibold">Unidade de medida *</label>
                        <select id="id_unidade" name="id_unidade" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($unidades as $u): ?>
                                <option value="<?= (int) $u['id_unidade'] ?>" <?= $idUnidade === (int) $u['id_unidade'] ? 'selected' : '' ?>>
                                    <?= e($u['sigla']) ?> (<?= e($u['grupo']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($editando): ?>
                            <div class="form-text">A unidade não pode mudar se o insumo já estiver em alguma ficha técnica.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="preco" class="form-label fw-semibold">Preço (R$) <?= $editando ? '' : '*' ?></label>
                        <input type="text" id="preco" name="preco" class="form-control" inputmode="decimal"
                               value="<?= e($preco) ?>" <?= $editando ? '' : 'required' ?>>
                        <?php if ($editando): ?>
                            <div class="form-text">Se o preço mudar, o novo valor entra no histórico.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="id_fornecedor" class="form-label fw-semibold">Fornecedor</label>
                        <select id="id_fornecedor" name="id_fornecedor" class="form-select">
                            <option value="">Nenhum</option>
                            <?php foreach ($fornecedores as $f): ?>
                                <option value="<?= (int) $f['id_fornecedor'] ?>" <?= $idFornecedor === (int) $f['id_fornecedor'] ? 'selected' : '' ?>>
                                    <?= e($f['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Obrigatório para material.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= e(url('insumos')) ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary fw-bold">Salvar insumo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
