document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalUsuario');
    if (!modal) return;
    const id = document.getElementById('id_usuario');
    const nome = document.getElementById('nomeUsuario');
    const login = document.getElementById('loginUsuario');
    const perfil = document.getElementById('perfilUsuario');
    const senha = document.getElementById('senhaUsuario');
    const senhaObrigatoria = document.getElementById('senhaObrigatoria');
    const ajudaSenha = document.getElementById('ajudaSenha');
    const titulo = document.getElementById('tituloModalUsuario');

    modal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        if (button?.dataset.modo === 'editar') {
            id.value = button.dataset.id || '';
            nome.value = button.dataset.nome || '';
            login.value = button.dataset.login || '';
            perfil.value = button.dataset.perfil || 'gestor';
            senha.value = '';
            senha.required = false;
            senhaObrigatoria.textContent = '(opcional)';
            ajudaSenha.textContent = 'Deixe em branco para manter a senha atual. Se preencher, use no mínimo 8 caracteres.';
            titulo.textContent = 'Editar usuário';
        } else {
            id.value = '';
            nome.value = '';
            login.value = '';
            perfil.value = 'gestor';
            senha.value = '';
            senha.required = true;
            senhaObrigatoria.textContent = '*';
            ajudaSenha.textContent = 'Mínimo de 8 caracteres.';
            titulo.textContent = 'Novo usuário';
        }
    });
});
