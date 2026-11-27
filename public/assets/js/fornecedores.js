document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalFornecedor');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const modo = button?.dataset.modo || 'novo';
        const id = document.getElementById('id_fornecedor');
        const nome = document.getElementById('nomeFornecedor');
        const contato = document.getElementById('contatoFornecedor');
        const titulo = document.getElementById('tituloModalFornecedor');
        if (modo === 'editar') {
            id.value = button.dataset.id || '';
            nome.value = button.dataset.nome || '';
            contato.value = button.dataset.contato || '';
            titulo.textContent = 'Editar fornecedor';
        } else {
            id.value = '';
            nome.value = '';
            contato.value = '';
            titulo.textContent = 'Novo fornecedor';
        }
    });
});
