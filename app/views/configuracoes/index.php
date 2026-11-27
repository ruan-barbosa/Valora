<?php include __DIR__ . '/../layout/header.php'; ?>
<?php
$empresa = $empresa ?? [];
$usuarios = $usuarios ?? [];
$csrf = $csrf ?? '';
$usuarioLogado = $usuarioLogado ?? [];
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Configurações</h1>
        <p class="text-muted mb-0">Gerencie os dados da empresa, parâmetros de custos e acessos.</p>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item"><button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#aba-empresa" type="button">Dados da empresa</button></li>
        <li class="nav-item"><button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#aba-usuarios" type="button">Usuários e perfis</button></li>
    </ul>

    <div class="tab-content">
        <section class="tab-pane fade show active" id="aba-empresa">
            <div class="card border-0 shadow-sm"><div class="card-body p-4">
                <form method="post" action="<?= e(url('configuracoes/empresa')) ?>" class="row g-3">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="nome">Nome da empresa</label><input class="form-control" id="nome" name="nome" maxlength="100" value="<?= e($empresa['nome'] ?? '') ?>" required></div>
                    <div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="segmento">Segmento</label>
                        <select class="form-select" id="segmento" name="segmento" required>
                            <option value="industria" <?= ($empresa['segmento'] ?? '') === 'industria' ? 'selected' : '' ?>>Indústria</option>
                            <option value="comercio" <?= ($empresa['segmento'] ?? '') === 'comercio' ? 'selected' : '' ?>>Comércio</option>
                            <option value="servicos" <?= ($empresa['segmento'] ?? '') === 'servicos' ? 'selected' : '' ?>>Serviços</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6"><label class="form-label fw-semibold" for="margem_minima_padrao">Margem mínima padrão (%)</label><input class="form-control" type="number" min="0" max="100" step="0.01" id="margem_minima_padrao" name="margem_minima_padrao" value="<?= e($empresa['margem_minima_padrao'] ?? '0') ?>" required></div>
                    <div class="col-12 col-md-6"><label class="form-label fw-semibold" for="percentual_custos_indiretos">Custos indiretos (%)</label><input class="form-control" type="number" min="0" max="100" step="0.01" id="percentual_custos_indiretos" name="percentual_custos_indiretos" value="<?= e($empresa['percentual_custos_indiretos'] ?? '0') ?>" required></div>
                    <div class="col-12"><p class="form-text mb-0">Ao salvar, as margens de todos os cadastros ativos serão recalculadas com os novos parâmetros.</p></div>
                    <div class="col-12 text-end"><button class="btn btn-primary" type="submit">Salvar dados da empresa</button></div>
                </form>
            </div></div>
        </section>

        <section class="tab-pane fade" id="aba-usuarios">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center gap-2 flex-wrap py-3">
                    <div><h2 class="h5 fw-bold mb-0">Usuários cadastrados</h2><small class="text-muted">O administrador pode criar, editar, inativar e reativar acessos.</small></div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" data-modo="novo">+ Novo usuário</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-3">Nome</th><th>Login</th><th>Perfil</th><th>Situação</th><th class="text-end pe-3">Ações</th></tr></thead>
                        <tbody>
                        <?php if (!$usuarios): ?><tr><td colspan="5" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td></tr>
                        <?php else: foreach ($usuarios as $u): ?>
                            <tr>
                                <td class="ps-3 fw-semibold"><?= e($u['nome']) ?></td><td>@<?= e($u['login']) ?></td>
                                <td><?= e(ucfirst(str_replace('_', ' ', $u['perfil']))) ?></td>
                                <td><?= $u['ativo'] ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" data-modo="editar"
                                            data-id="<?= (int) $u['id_usuario'] ?>" data-nome="<?= e($u['nome']) ?>" data-login="<?= e($u['login']) ?>" data-perfil="<?= e($u['perfil']) ?>">Editar</button>
                                        <?php if ($u['ativo'] && (int) $u['id_usuario'] !== (int) ($usuarioLogado['id_usuario'] ?? 0)): ?>
                                            <form method="post" action="<?= e(url('usuarios/inativar')) ?>" onsubmit="return confirm('Inativar este usuário?');"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>"><button type="submit" class="btn btn-sm btn-outline-danger">Inativar</button></form>
                                        <?php elseif (!$u['ativo']): ?>
                                            <form method="post" action="<?= e(url('usuarios/reativar')) ?>"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>"><button type="submit" class="btn btn-sm btn-outline-success">Reativar</button></form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="tituloModalUsuario" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" action="<?= e(url('usuarios/salvar')) ?>">
            <div class="modal-header"><h2 class="modal-title fs-5 fw-bold" id="tituloModalUsuario">Novo usuário</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id_usuario" id="id_usuario" value="">
                <div class="mb-3"><label class="form-label" for="nomeUsuario">Nome *</label><input class="form-control" id="nomeUsuario" name="nome" maxlength="100" required></div>
                <div class="mb-3"><label class="form-label" for="loginUsuario">Login *</label><input class="form-control" id="loginUsuario" name="login" maxlength="50" pattern="[a-z0-9._-]{3,50}" required><div class="form-text">Use letras minúsculas, números, ponto, traço ou sublinhado.</div></div>
                <div class="mb-3"><label class="form-label" for="perfilUsuario">Perfil *</label><select class="form-select" id="perfilUsuario" name="perfil" required><option value="administrador">Administrador</option><option value="gestor">Gestor</option><option value="comprador">Comprador</option><option value="producao">Produção</option></select></div>
                <div><label class="form-label" for="senhaUsuario">Senha <span id="senhaObrigatoria">*</span></label><input class="form-control" id="senhaUsuario" name="senha" type="password" minlength="8" maxlength="72" autocomplete="new-password"><div class="form-text" id="ajudaSenha">Mínimo de 8 caracteres.</div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar usuário</button></div>
        </form>
    </div></div>
</div>
<script src="<?= e(asset('js/configuracoes.js')) ?>"></script>
<?php include __DIR__ . '/../layout/footer.php'; ?>
