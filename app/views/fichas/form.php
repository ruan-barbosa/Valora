<?php 
include __DIR__ . '/../layout/header.php'; 

// Mock (Seção 12)
$produto = $produto ?? ['id_produto' => '', 'nome' => '', 'tipo' => 'produto', 'preco_venda' => '', 'margem_minima' => '', 'id_fornecedor' => '', 'preco_compra' => ''];
$composicao = $composicao ?? [];
$itens = $itens ?? [['id_item' => 1, 'nome' => 'Farinha de Trigo'], ['id_item' => 2, 'nome' => 'Açúcar Refinado']];
$unidades = $unidades ?? [['id_unidade' => 1, 'sigla' => 'kg'], ['id_unidade' => 2, 'sigla' => 'g'], ['id_unidade' => 3, 'sigla' => 'un']];
$fornecedores = $fornecedores ?? [['id_fornecedor' => 1, 'nome' => 'Moinho Sul']];
$csrf = $csrf ?? 'token_de_teste';
$isEdicao = !empty($produto['id_produto']);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold text-dark mb-0"><?= $isEdicao ? 'Editar Ficha Técnica' : 'Nova Ficha Técnica' ?></h1>
        <a href="<?= htmlspecialchars(url('fichas')) ?>" class="btn btn-outline-secondary">Voltar</a>
    </div>

    <form method="post" action="<?= htmlspecialchars(url('fichas/salvar')) ?>" id="form-ficha">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <?php if ($isEdicao): ?>
            <input type="hidden" name="id_produto" value="<?= (int)$produto['id_produto'] ?>">
        <?php endif; ?>

        <div class="row g-4">
            <!-- Coluna da Esquerda (Formulários) -->
            <div class="col-lg-8">
                
                <!-- DADOS BÁSICOS -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom pt-3 pb-2">
                        <h5 class="fw-bold text-dark mb-0">Dados Gerais</h5>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label fw-semibold">Nome do Produto/Serviço *</label>
                            <input type="text" id="nome" name="nome" class="form-control" required value="<?= htmlspecialchars($produto['nome']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="tipo" class="form-label fw-semibold">Tipo *</label>
                            <select id="tipo" name="tipo" class="form-select input-calculo" required>
                                <option value="produto" <?= $produto['tipo'] === 'produto' ? 'selected' : '' ?>>Produto (Produzido)</option>
                                <option value="servico" <?= $produto['tipo'] === 'servico' ? 'selected' : '' ?>>Serviço</option>
                                <option value="revenda" <?= $produto['tipo'] === 'revenda' ? 'selected' : '' ?>>Revenda</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="preco_venda" class="form-label fw-semibold">Preço de Venda (R$) *</label>
                            <input type="number" step="0.01" id="preco_venda" name="preco_venda" class="form-control input-calculo" required value="<?= htmlspecialchars($produto['preco_venda']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="margem_minima" class="form-label fw-semibold">Margem Mín. Específica (%)</label>
                            <input type="number" step="0.1" id="margem_minima" name="margem_minima" class="form-control input-calculo" placeholder="Opcional. Usa o padrão se vazio" value="<?= htmlspecialchars($produto['margem_minima']) ?>">
                        </div>
                    </div>
                </div>

                <!-- DADOS DE COMPRA (Mostra apenas se for REVENDA) -->
                <div class="card border-0 shadow-sm mb-4 d-none bg-info-subtle border-info" id="secao-revenda">
                    <div class="card-header bg-transparent border-bottom pt-3 pb-2">
                        <h5 class="fw-bold text-dark mb-0">Dados de Compra (Revenda)</h5>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label for="id_fornecedor" class="form-label fw-semibold">Fornecedor *</label>
                            <select id="id_fornecedor" name="id_fornecedor" class="form-select">
                                <option value="">Selecione...</option>
                                <?php foreach ($fornecedores as $f): ?>
                                    <option value="<?= $f['id_fornecedor'] ?>" <?= $produto['id_fornecedor'] == $f['id_fornecedor'] ? 'selected' : '' ?>><?= htmlspecialchars($f['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="preco_compra" class="form-label fw-semibold">Preço de Compra (R$) *</label>
                            <input type="number" step="0.01" id="preco_compra" name="preco_compra" class="form-control input-calculo" value="<?= htmlspecialchars($produto['preco_compra']) ?>">
                        </div>
                    </div>
                </div>

                <!-- COMPOSIÇÃO (Mostra apenas se for PRODUTO ou SERVIÇO) -->
                <div class="card border-0 shadow-sm mb-4" id="secao-composicao">
                    <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0">Ficha Técnica (Composição)</h5>
                        <button type="button" id="btn-add-insumo" class="btn btn-sm btn-outline-primary fw-bold">+ Adicionar Insumo</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tabela-composicao">
                                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="ps-3">Insumo</th>
                                        <th style="width: 130px;">Quantidade</th>
                                        <th style="width: 100px;">Unidade</th>
                                        <th style="width: 110px;">Perda (%)</th>
                                        <th class="text-end pe-3" style="width: 70px;">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Renderização das linhas de edição (PHP) omitida para brevidade. O JS gere as novas linhas via clone do template abaixo. -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-5">
                    <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow-sm">Salvar Ficha Técnica</button>
                </div>
            </div>

            <!-- Coluna da Direita (Resumo Dinâmico - Seção 7) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white border-bottom-0 py-3">
                        <h5 class="fw-bold mb-0 text-center">Resumo de Custo</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-secondary fw-medium">Custo Direto</span>
                            <span class="fw-bold fs-5" id="lbl_custo_direto">R$ 0,00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-secondary fw-medium">Custos Indiretos</span>
                            <span class="fw-bold" id="lbl_custo_indireto">R$ 0,00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3 bg-light border-bottom-0">
                            <span class="text-dark fw-bold fs-5">Custo Total</span>
                            <span class="fw-bold fs-4 text-primary" id="lbl_custo_total">R$ 0,00</span>
                        </li>
                    </ul>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-4 text-center">
                        <hr>
                        <p class="text-dark fw-bold mb-2">Margem de Lucro</p>
                        <!-- O JS aplica bg-success ou bg-danger aqui -->
                        <span id="badge_margem" class="badge bg-secondary fs-4 px-4 py-2 w-100">0.0%</span>
                        <div class="mt-2 text-muted" style="font-size: 0.75rem;">(Cálculo automático baseado no preço de venda e composição)</div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- TEMPLATE ESCONDIDO PARA NOVA LINHA DA TABELA -->
<template id="template-linha">
    <tr class="linha-insumo">
        <td class="ps-3">
            <select name="itens[INDEX][id_item]" class="form-select form-select-sm input-calculo item-required">
                <option value="">Selecione...</option>
                <?php foreach ($itens as $item): ?>
                    <option value="<?= $item['id_item'] ?>"><?= htmlspecialchars($item['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" step="0.001" name="itens[INDEX][quantidade]" class="form-control form-control-sm input-calculo item-required">
        </td>
        <td>
            <select name="itens[INDEX][id_unidade]" class="form-select form-select-sm input-calculo item-required">
                <?php foreach ($unidades as $un): ?>
                    <option value="<?= $un['id_unidade'] ?>"><?= htmlspecialchars($un['sigla']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" step="0.1" name="itens[INDEX][percentual_perda]" class="form-control form-control-sm input-calculo" value="0">
        </td>
        <td class="text-end pe-3">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-linha">X</button>
        </td>
    </tr>
</template>

<!-- Script desta view (Seção 7 e 11) -->
<script src="<?= htmlspecialchars(asset('js/fichas.js')) ?>"></script>

<?php include __DIR__ . '/../layout/footer.php'; ?>