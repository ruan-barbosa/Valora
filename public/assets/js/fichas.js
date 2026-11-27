document.addEventListener('DOMContentLoaded', function () {
    const selectTipo = document.getElementById('tipo');
    const secaoRevenda = document.getElementById('secao-revenda');
    const secaoComposicao = document.getElementById('secao-composicao');
    const campoFornecedor = document.getElementById('id_fornecedor');
    const campoPrecoCompra = document.getElementById('preco_compra');
    const btnAddInsumo = document.getElementById('btn-add-insumo');
    const tabelaBody = document.querySelector('#tabela-composicao tbody');
    const templateLinha = document.getElementById('template-linha');
    const formFicha = document.getElementById('form-ficha');

    if (!formFicha || !selectTipo) return;

    let indexLinha = document.querySelectorAll('.linha-insumo').length;
    let timeoutCalculo;
    let requisicaoAtual = 0;

    function formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(Number(valor) || 0);
    }

    function atualizarVisibilidade() {
        const revenda = selectTipo.value === 'revenda';

        if (secaoRevenda) secaoRevenda.classList.toggle('d-none', !revenda);
        if (secaoComposicao) secaoComposicao.classList.toggle('d-none', revenda);

        if (campoPrecoCompra) {
            campoPrecoCompra.required = revenda;
        }
        if (campoFornecedor) {
            campoFornecedor.required = revenda;
        }

        document.querySelectorAll('.linha-insumo .item-required').forEach(function (campo) {
            campo.required = !revenda;
        });

        recalcularServidor();
    }

    function aplicarResultado(data) {
        document.getElementById('lbl_custo_direto').textContent = formatarMoeda(data.custo_direto);
        document.getElementById('lbl_custo_indireto').textContent = formatarMoeda(data.custo_indireto);
        document.getElementById('lbl_custo_total').textContent = formatarMoeda(data.custo_total);

        const badgeMargem = document.getElementById('badge_margem');
        badgeMargem.textContent = Number(data.margem).toLocaleString('pt-BR', {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        }) + '%';
        badgeMargem.className = 'badge fs-4 px-4 py-2 w-100 ' + (data.abaixo_margem ? 'bg-danger' : 'bg-success');
    }

    function mostrarCalculoPendente() {
        const badgeMargem = document.getElementById('badge_margem');
        badgeMargem.textContent = '—';
        badgeMargem.className = 'badge bg-secondary fs-4 px-4 py-2 w-100';
    }

    function recalcularServidor() {
        clearTimeout(timeoutCalculo);
        timeoutCalculo = setTimeout(async function () {
            const numeroRequisicao = ++requisicaoAtual;
            const formData = new FormData(formFicha);

            try {
                const resposta = await fetch(window.valoraUrl('fichas/calcular'), {
                    method: 'POST',
                    headers: window.valoraCabecalhos(),
                    body: formData
                });

                const data = await resposta.json();
                if (numeroRequisicao !== requisicaoAtual) return;

                if (!resposta.ok) {
                    mostrarCalculoPendente();
                    return;
                }

                aplicarResultado(data);
            } catch (erro) {
                if (numeroRequisicao === requisicaoAtual) {
                    mostrarCalculoPendente();
                    console.error('Não foi possível calcular a ficha técnica:', erro);
                }
            }
        }, 350);
    }

    function adicionarLinha() {
        if (!tabelaBody || !templateLinha) return;

        const html = templateLinha.innerHTML.replace(/INDEX/g, String(indexLinha));
        tabelaBody.insertAdjacentHTML('beforeend', html);
        const novaLinha = tabelaBody.lastElementChild;

        novaLinha.querySelector('.btn-remover-linha')?.addEventListener('click', function () {
            novaLinha.remove();
            recalcularServidor();
        });
        novaLinha.querySelectorAll('.input-calculo').forEach(function (campo) {
            campo.addEventListener('input', recalcularServidor);
            campo.addEventListener('change', recalcularServidor);
        });
        novaLinha.querySelectorAll('.item-required').forEach(function (campo) {
            campo.required = selectTipo.value !== 'revenda';
        });

        document.getElementById('aviso-composicao-vazia')?.remove();
        indexLinha++;
        recalcularServidor();
    }

    if (btnAddInsumo) {
        btnAddInsumo.addEventListener('click', adicionarLinha);
    }

    document.querySelectorAll('.linha-insumo .btn-remover-linha').forEach(function (botao) {
        botao.addEventListener('click', function () {
            botao.closest('tr')?.remove();
            recalcularServidor();
        });
    });

    formFicha.querySelectorAll('.input-calculo').forEach(function (campo) {
        campo.addEventListener('input', recalcularServidor);
        campo.addEventListener('change', recalcularServidor);
    });

    selectTipo.addEventListener('change', atualizarVisibilidade);
    atualizarVisibilidade();
});
