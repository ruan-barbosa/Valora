<?php 
include __DIR__ . '/../layout/header.php'; 

// A lista vem do banco; sem itens, o formulário permanece indisponível.
$itens = $itens ?? [];
$csrf = $csrf ?? '';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-dark mb-1">Simulador de Reajustes</h1>
            <p class="text-muted mb-0">Antecipe o impacto das alterações de preços dos insumos na margem final dos seus <?= e(strtolower($termoPrincipalPlural ?? 'produtos')) ?>.</p>
        </div>
    </div>

    <!-- Aviso exigido pela Seção 8.7 -->
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
        <span class="fs-4 me-3">ℹ️</span>
        <div>
            <strong>Atenção:</strong> Esta é uma ferramenta de simulação. Os resultados exibidos abaixo <b>não alteram</b> os preços reais no sistema nem atualizam as fichas técnicas.
        </div>
    </div>

    <!-- Formulário de Simulação (Envia para o JS, não recarrega a página) -->
    <div class="card border-0 shadow-sm mb-5 bg-white">
        <div class="card-body p-4">
            <form id="form-simulador" class="row g-4 align-items-end">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                
                <div class="col-md-5">
                    <label for="id_item" class="form-label fw-bold fs-6">Selecione o insumo</label>
                    <select id="id_item" name="id_item" class="form-select form-select-lg" required>
                        <option value="">Escolha um insumo para simular...</option>
                        <?php foreach ($itens as $item): ?>
                            <option value="<?= $item['id_item'] ?>"><?= htmlspecialchars($item['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="percentual_reajuste" class="form-label fw-bold fs-6">Percentual de reajuste</label>
                    <div class="input-group input-group-lg">
                        <input type="number" step="0.1" id="percentual_reajuste" name="percentual_reajuste" class="form-control" placeholder="Ex: 10 (Aumento) ou -5 (Queda)" required>
                        <span class="input-group-text bg-light">%</span>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" id="btn-simular" <?= empty($itens) ? 'disabled' : '' ?>>
                        Simular Impacto
                    </button>
                </div>
            </form>
            <?php if (!$itens): ?><div class="alert alert-info mt-3 mb-0">Cadastre ao menos um insumo ativo para utilizar o simulador.</div><?php endif; ?>
            <div id="erro-simulador" class="alert alert-danger mt-3 mb-0 d-none" role="alert"></div>
        </div>
    </div>

    <!-- Tabela de Resultados (Escondida por padrão) -->
    <div class="card border-0 shadow-sm d-none" id="card-resultados">
        <div class="card-header bg-dark text-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><?= e($termoPrincipalPlural ?? 'Produtos') ?> afetados</h5>
            <span class="badge bg-light text-dark fs-6" id="badge-reajuste"></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabela-resultados">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4">Produto</th>
                            <th class="text-end">Custo Atual</th>
                            <th class="text-end text-primary">Novo Custo Simulado</th>
                            <th class="text-end">Margem Atual</th>
                            <th class="text-end text-primary">Nova Margem Sim.</th>
                            <th class="text-center pe-4">Status de Alerta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- As linhas serão preenchidas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Mensagem caso o insumo não esteja a ser usado (Estado Vazio) -->
        <div class="card-footer bg-light p-4 text-center d-none" id="mensagem-vazio">
            <span class="text-muted fs-5">Este insumo não compõe nenhum cadastro ativo no sistema no momento.</span>
        </div>
    </div>
</div>

<!-- Script exclusivo do Simulador (Seção 7 e 11) -->
<script src="<?= htmlspecialchars(asset('js/simulador.js')) ?>"></script>

<?php include __DIR__ . '/../layout/footer.php'; ?>