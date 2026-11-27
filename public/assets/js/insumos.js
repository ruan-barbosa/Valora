document.addEventListener('DOMContentLoaded', function () {
    // Preenche o modal de atualização de preço com o insumo selecionado.
    const modalPreco = document.getElementById('modalPreco');
    if (modalPreco) {
        modalPreco.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const id = document.getElementById('modalPrecoId');
            const nome = document.getElementById('modalPrecoNome');
            const fornecedor = document.getElementById('id_fornecedor');
            if (id) id.value = button.getAttribute('data-id') || '';
            if (nome) nome.textContent = button.getAttribute('data-nome') || '';

            if (fornecedor) {
                if (button.getAttribute('data-tipo') === 'material') {
                    fornecedor.setAttribute('required', 'required');
                } else {
                    fornecedor.removeAttribute('required');
                }
            }
        });
    }

    // O mesmo modal é usado na lista de insumos e no dashboard do gestor.
    const modalHistorico = document.getElementById('modalHistoricoPrecos');
    if (!modalHistorico) return;

    const tituloItem = document.getElementById('nomeHistoricoPrecos');
    const status = document.getElementById('statusHistoricoPrecos');
    const corpoTabela = document.getElementById('tabelaHistoricoPrecos');

    function celula(texto, classe) {
        const td = document.createElement('td');
        if (classe) td.className = classe;
        td.textContent = texto == null || texto === '' ? '—' : String(texto);
        return td;
    }

    function mostrarStatus(mensagem, tipo) {
        status.className = `alert alert-${tipo || 'info'} py-2`;
        status.textContent = mensagem;
        corpoTabela.replaceChildren();
    }

    function formatarData(valor) {
        const partes = String(valor || '').split(' ');
        const data = (partes[0] || '').split('-');
        if (data.length !== 3) return String(valor || '—');
        const diaMesAno = `${data[2]}/${data[1]}/${data[0]}`;
        const hora = partes[1] ? ` ${partes[1].slice(0, 5)}` : '';
        return diaMesAno + hora;
    }

    function renderizarHistorico(registros) {
        corpoTabela.replaceChildren();
        if (!Array.isArray(registros) || registros.length === 0) {
            mostrarStatus('Este insumo ainda não possui registros no histórico de preços.', 'secondary');
            return;
        }

        registros.forEach(registro => {
            const linha = document.createElement('tr');
            const preco = Number(registro.preco);
            linha.appendChild(celula(formatarData(registro.data_alteracao)));
            linha.appendChild(celula(Number.isFinite(preco)
                ? preco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
                : '—', 'text-end fw-semibold'));
            linha.appendChild(celula(registro.fornecedor));
            linha.appendChild(celula(registro.usuario));
            corpoTabela.appendChild(linha);
        });

        status.className = 'alert alert-success py-2';
        status.textContent = `${registros.length} registro(s), do mais recente para o mais antigo.`;
    }

    modalHistorico.addEventListener('show.bs.modal', function (event) {
        const botao = event.relatedTarget;
        if (!botao) return;

        // Na lista de insumos o ID vem do botão; no dashboard vem do seletor.
        let idItem = botao.getAttribute('data-id') || '';
        let nomeItem = botao.getAttribute('data-nome') || '';
        const seletorId = botao.getAttribute('data-item-select');
        if (seletorId) {
            const seletor = document.getElementById(seletorId);
            idItem = seletor ? seletor.value : '';
            nomeItem = seletor && seletor.selectedOptions.length
                ? seletor.selectedOptions[0].textContent
                : '';
        }

        if (tituloItem) tituloItem.textContent = nomeItem;
        if (!idItem) {
            mostrarStatus('Selecione um insumo para consultar o histórico.', 'warning');
            return;
        }

        mostrarStatus('Carregando histórico de preços...', 'info');
        const url = window.valoraUrl(`insumos/historico?id_item=${encodeURIComponent(idItem)}`);

        fetch(url, {
            method: 'GET',
            headers: window.valoraCabecalhos(),
            cache: 'no-store'
        })
            .then(async response => {
                const dados = await response.json();
                if (!response.ok) {
                    throw new Error(dados.erro || 'Não foi possível consultar o histórico.');
                }
                return dados;
            })
            .then(dados => renderizarHistorico(dados.historico))
            .catch(erro => {
                mostrarStatus(erro.message || 'Erro ao carregar o histórico de preços.', 'danger');
            });
    });
});
