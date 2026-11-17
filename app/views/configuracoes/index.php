<?php 
include __DIR__ . '/../layout/header.php'; 

// Mock de dados (Seção 12)
$empresa = $empresa ?? ['nome' => 'Indústria Exemplo', 'segmento' => 'industria', 'margem_minima_padrao' => 30.0, 'percentual_custos_indiretos' => 15.0];
$usuarios = $usuarios ?? [
    ['id_usuario' => 1, 'nome' => 'Admin Silva', 'login' => 'admin', 'perfil' => 'administrador', 'ativo' => true],
    ['id_usuario' => 2, 'nome' => 'João Gestor', 'login' => 'joao', 'perfil' => 'gestor', 'ativo' => true],
];
$csrf = $csrf ?? 'token_de_teste';
$usuarioLogado = $usuarioLogado ?? ['nome' => 'Admin', 'perfil' => 'administrador'];
$isAdmin = $usuarioLogado['perfil'] === 'administrador';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-dark mb-1">Configurações do Sistema</h1>
            <p class="text-muted mb-0">Faça a gestão dos parâmetros globais da empresa e dos acessos da equipa.</p>
        </div>
    </div>

    <!-- Navegação de Abas -->
    <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="empresa-tab" data-bs-toggle="tab" data-bs-target="#empresa" type="button" role="tab">Dados da Empresa</button>
        </li>
        <?php if ($isAdmin): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-muted" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button" role="tab">Usuários e Perfis</button>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="configTabsContent">
        
        <!-- ABA 1: EMPRESA -->
        <div class="tab-pane fade show active" id="empresa" role="tabpanel">
            <div class="card border-0 shadow-sm col-lg-8 bg-white">
                <div class="card-body p-4 p-md-5">
                    <form method="post" action="<?= htmlspecialchars(url('configuracoes/empresa')) ?>" class="row g-4">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="col-md-8">
                            <label for="nome" class="form-label fw-bold">Nome da Empresa</label>
                            <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($empresa['nome']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="segmento" class="form-label fw-bold">Segmento</label>
                            <select id="segmento" name="segmento" class="form-select" required>
                                <option value="industria" <?= $empresa['segmento'] === 'industria' ? 'selected' : '' ?>>Indústria</option>
                                <option value="comercio" <?= $empresa['segmento'] === 'comercio' ? 'selected' : '' ?>>Comércio</option>
                                <option value="servicos" <?= $empresa['segmento'] === 'servicos' ? 'selected' : '' ?>>Serviços</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <h5 class="fw-bold border-bottom pb-2">Parâmetros de Precificação Globais</h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="margem_minima_padrao" class="form-label fw-bold">Margem Mínima Padrão (%)</label>
                            <input type="number" step="0.1" id="margem_minima_padrao" name="margem_minima_padrao" class="form-control" value="<?= htmlspecialchars($empresa['margem_minima_padrao']) ?>" required>
                            <div class="form-text">Gera um alerta se o lucro de um produto for menor que isto.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="percentual_custos_indiretos" class="form-label fw-bold">Custos Indiretos (Rateio %)</label>
                            <input type="number" step="0.1" id="percentual_custos_indiretos" name="percentual_custos_indiretos" class="form-control" value="<?= htmlspecialchars($empresa['percentual_custos_indiretos']) ?>" required>
                            <div class="form-text">Percentagem fixa somada ao custo direto da ficha técnica.</div>
                        </div>
                        
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Configurações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ABA 2: USUÁRIOS (Apenas Administrador) -->
        <?php if ($isAdmin): ?>
        <div class="tab-pane fade" id="usuarios" role="tabpanel">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Equipa e Acessos</h5>
                    <button class="btn btn-primary fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario">+ Novo Usuário</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                            <tr>
                                <th class="ps-4">Nome</th>
                                <th>Login</th>
                                <th>Perfil</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($u['nome']) ?></td>
                                <td class="text-muted">@<?= htmlspecialchars($u['login']) ?></td>
                                <td><span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($u['perfil'])) ?></span></td>
                                <td class="text-end pe-4">
                                    <form method="post" action="<?= htmlspecialchars(url('usuarios/inativar')) ?>" class="d-inline" onsubmit="return confirm('Deseja inativar este acesso?');">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" <?= $u['id_usuario'] === $usuarioLogado['id_usuario'] ? 'disabled title="Não pode inativar-se a si próprio"' : '' ?>>Inativar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- Modal Novo Usuário -->
<?php if ($isAdmin): ?>
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= htmlspecialchars(url('usuarios/salvar')) ?>">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome *</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Login *</label>
                            <input type="text" name="login" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Senha Inicial *</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Perfil de Acesso *</label>
                        <select name="perfil" class="form-select" required>
                            <option value="gestor">Gestor</option>
                            <option value="comprador">Comprador</option>
                            <option value="producao">Produção</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn btn-primary fw-bold w-100">Criar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>