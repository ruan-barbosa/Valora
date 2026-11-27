<?php include __DIR__ . '/../layout/header.php'; ?>

<?php
$servicos = ($segmentoEmpresa ?? '') === 'servicos';
$produto = $produto ?? [
    'id_produto' => 0,
    'nome' => '',
    'tipo' => 'produto',
    'preco_venda' => '',
    'margem_minima' => '',
    'id_fornecedor' => '',
    'preco_compra' => '',
];
$composicao = $composicao ?? [];
$itens = $itens ?? [];
$unidades = $unidades ?? [];
$fornecedores = $fornecedores ?? [];
$antigos = is_array($antigos ?? null) ? $antigos : [];

// Recupera os valores enviados quando o formulário retorna com erros de validação.
foreach (['nome', 'tipo', 'preco_venda', 'margem_minima', 'id_fornecedor', 'preco_compra'] as $campo) {
    if (array_key_exists($campo, $antigos)) {
        $produto[$campo] = $antigos[$campo];
    }
}
if (isset($antigos['itens']) && is_array($antigos['itens'])) {
    $composicao = [];
    foreach ($antigos['itens'] as $linha) {
        if (is_array($linha)) {
            $composicao[] = [
                'id_item' => (int) ($linha['id_item'] ?? 0),
                'id_unidade' => (int) ($linha['id_unidade'] ?? 0),
                'quantidade' => $linha['quantidade'] ?? '',
                'percentual_perda' => $linha['percentual_perda'] ?? '0',
            ];
        }
    }
}
$isEdicao = !empty($produto['id_produto']);
?>

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1"><?= $isEdicao ? 'Editar cadastro' : 'Novo cadastro' ?></h1>
            <p class="text-muted mb-0">Informe os dados e acompanhe o cálculo de custo e margem.</p>
        </div>
        <a href="<?= e(url('fichas')) ?>" class="btn btn-outline-secondary">Voltar</a>
    </div>

    <form method="post" action="<?= e(url('fichas/salvar')) ?>" id="form-ficha" novalidate>
        <input type="hidden" name="csrf" value="<?= e($csrf ?? '') ?>">
        <?php if ($isEdicao): ?>
            <input type="hidden" name="id_produto" value="<?= (int) $produto['id_produto'] ?>">
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom pt-3 pb-2">
                        <h5 class="fw-bold text-dark mb-0">Dados gerais</h5>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label fw-semibold"><?= $servicos ? 'Nome do serviço' : 'Nome do produto/serviço' ?> *</label>
                            <input type="text" id="nome" name="nome" maxlength="100" class="form-control" required value="<?= e($produto['nome']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="tipo" class="form-label fw-semibold">Tipo *</label>
                            <select id="tipo" name="tipo" class="form-select input-calculo" required>
                                <option value="produto" <?= $produto['tipo'] === 'produto' ? 'selected' : '' ?>><?= $servicos ? 'Produto (cadastro existente)' : 'Produto (produzido)' ?></option>
                                <option value="servico" <?= $produto['tipo'] === 'servico' ? 'selected' : '' ?>><?= $servicos ? 'Serviço (principal)' : 'Serviço' ?></option>
                                <option value="revenda" <?= $produto['tipo'] === 'revenda' ? 'selected' : '' ?>>Revenda</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="preco_venda" class="form-label fw-semibold"><?= $servicos ? 'Preço cobrado (R$)' : 'Preço de venda (R$)' ?> *</label>
                            <input type="number" min="0.01" step="0.01" id="preco_venda" name="preco_venda" class="form-control input-calculo" required value="<?= e($produto['preco_venda']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="margem_minima" class="form-label fw-semibold">Margem mínima específica (%)</label>
                            <input type="number" min="0" max="100" step="0.01" id="margem_minima" name="margem_minima" class="form-control input-calculo" placeholder="Vazio = padrão da empresa" value="<?= e($produto['margem_minima'] ?? '') ?>">
                            <div class="form-text">Quando vazio, será utilizada a margem mínima definida para a empresa.</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 d-none bg-info-subtle" id="secao-revenda">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2">
                        <h5 class="fw-bold text-dark mb-0">Dados de compra da revenda</h5>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label for="id_fornecedor" class="form-label fw-semibold">Fornecedor *</label>
                            <select id="id_fornecedor" name="id_fornecedor" class="form-select">
                                <option value="">Selecione...</option>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <option value="<?= (int) $fornecedor['id_fornecedor'] ?>" <?= (string) ($produto['id_fornecedor'] ?? '') === (string) $fornecedor['id_fornecedor'] ? 'selected' : '' ?>><?= e($fornecedor['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="preco_compra" class="form-label fw-semibold">Preço de compra (R$) *</label>
                            <input type="number" min="0" step="0.0001" id="preco_compra" name="preco_compra" class="form-control input-calculo" value="<?= e($produto['preco_compra'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-text">Ao salvar, o Valora criará automaticamente uma composição com uma unidade comprada e registrará o fornecedor e o preço no histórico.</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" id="secao-composicao">
                    <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-0"><?= $servicos ? 'Recursos e composição do serviço' : 'Ficha técnica (composição)' ?></h5>
                            <small class="text-muted">Use unidades compatíveis com a unidade cadastrada no insumo.</small>
                        </div>
                        <button type="button" id="btn-add-insumo" class="btn btn-sm btn-outline-primary fw-bold">+ Adicionar insumo</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabela-composicao">
                                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="ps-3">Insumo</th>
                                        <th style="width: 140px;">Quantidade</th>
                                        <th style="width: 110px;">Unidade</th>
                                        <th style="width: 120px;">Perda (%)</th>
                                        <th class="text-end pe-3" style="width: 90px;">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($composicao as $i => $linha): ?>
                                        <tr class="linha-insumo">
                                            <td class="ps-3">
                                                <select name="itens[<?= (int) $i ?>][id_item]" class="form-select form-select-sm input-calculo item-required">
                                                    <option value="">Selecione...</option>
                                                    <?php foreach ($itens as $item): ?>
                                                        <option value="<?= (int) $item['id_item'] ?>" <?= (int) $linha['id_item'] === (int) $item['id_item'] ? 'selected' : '' ?>><?= e($item['nome']) ?><?= empty($item['ativo']) ? ' (inativo)' : '' ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" min="0.000001" step="0.000001" name="itens[<?= (int) $i ?>][quantidade]" class="form-control form-control-sm input-calculo item-required" value="<?= e($linha['quantidade']) ?>">
                                            </td>
                                            <td>
                                                <select name="itens[<?= (int) $i ?>][id_unidade]" class="form-select form-select-sm input-calculo item-required">
                                                    <?php foreach ($unidades as $unidade): ?>
                                                        <option value="<?= (int) $unidade['id_unidade'] ?>" <?= (int) $linha['id_unidade'] === (int) $unidade['id_unidade'] ? 'selected' : '' ?>><?= e($unidade['sigla']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" min="0" max="100" step="0.01" name="itens[<?= (int) $i ?>][percentual_perda]" class="form-control form-control-sm input-calculo" value="<?= e($linha['percentual_perda']) ?>">
                                            </td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remover-linha" aria-label="Remover insumo">Remover</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($composicao)): ?>
                            <p class="text-muted text-center p-4 mb-0" id="aviso-composicao-vazia">Adicione pelo menos um insumo para montar a ficha técnica.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-5">
                    <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow-sm">Salvar cadastro</button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white border-bottom-0 py-3">
                        <h5 class="fw-bold mb-0 text-center">Resumo de custo</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-secondary fw-medium">Custo direto</span>
                            <span class="fw-bold fs-5" id="lbl_custo_direto">R$ 0,00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-secondary fw-medium">Custos indiretos</span>
                            <span class="fw-bold" id="lbl_custo_indireto">R$ 0,00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3 bg-light border-bottom-0">
                            <span class="text-dark fw-bold fs-5">Custo total</span>
                            <span class="fw-bold fs-4 text-primary" id="lbl_custo_total">R$ 0,00</span>
                        </li>
                    </ul>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-4 text-center">
                        <hr>
                        <p class="text-dark fw-bold mb-2">Margem de lucro</p>
                        <span id="badge_margem" class="badge bg-secondary fs-4 px-4 py-2 w-100">—</span>
                        <div class="mt-2 text-muted" style="font-size: 0.75rem;">O cálculo considera o preço cobrado, os recursos utilizados e os custos indiretos configurados para a empresa.</div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="template-linha">
    <tr class="linha-insumo">
        <td class="ps-3">
            <select name="itens[INDEX][id_item]" class="form-select form-select-sm input-calculo item-required">
                <option value="">Selecione...</option>
                <?php foreach ($itens as $item): ?>
                    <option value="<?= (int) $item['id_item'] ?>"><?= e($item['nome']) ?><?= empty($item['ativo']) ? ' (inativo)' : '' ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" min="0.000001" step="0.000001" name="itens[INDEX][quantidade]" class="form-control form-control-sm input-calculo item-required"></td>
        <td>
            <select name="itens[INDEX][id_unidade]" class="form-select form-select-sm input-calculo item-required">
                <?php foreach ($unidades as $unidade): ?>
                    <option value="<?= (int) $unidade['id_unidade'] ?>"><?= e($unidade['sigla']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" min="0" max="100" step="0.01" name="itens[INDEX][percentual_perda]" class="form-control form-control-sm input-calculo" value="0"></td>
        <td class="text-end pe-3"><button type="button" class="btn btn-sm btn-outline-danger btn-remover-linha" aria-label="Remover insumo">Remover</button></td>
    </tr>
</template>

<script src="<?= e(asset('js/fichas.js')) ?>"></script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
