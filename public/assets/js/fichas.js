document.addEventListener('DOMContentLoaded', function() {
    const selectTipo = document.getElementById('tipo');
    const secaoRevenda = document.getElementById('secao-revenda');
    const secaoComposicao = document.getElementById('secao-composicao');
    const btnAddInsumo = document.getElementById('btn-add-insumo');
    const tabelaBody = document.querySelector('#tabela-composicao tbody');
    const templateLinha = document.getElementById('template-linha');
    const formFicha = document.getElementById('form-ficha');
    
    let indexLinha = document.querySelectorAll('.linha-insumo').length || 0;
    let timeoutCalculo;

    // 1. ALTERNAR VISIBILIDADE: Revenda vs Produto/Serviço
    function atualizarVisibilidade() {
        if (!selectTipo) return;
        const tipo = selectTipo.value;
        
        if (tipo === 'revenda') {
            secaoRevenda.classList.remove('d-none');
            secaoComposicao.classList.add('d-none');
            
            document.getElementById('preco_compra').setAttribute('required', 'required');
            document.getElementById('id_fornecedor').setAttribute('required', 'required');
            
            document.querySelectorAll('.item-required').forEach(el => el.removeAttribute('required'));
        } else {
            secaoRevenda.classList.add('d-none');
            secaoComposicao.classList.remove('d-none');
            
            document.getElementById('preco_compra').removeAttribute('required');
            document.getElementById('id_fornecedor').removeAttribute('required');
            
            document.querySelectorAll('.linha-insumo .item-required').forEach(el => el.setAttribute('required', 'required'));
        }
        recalcularServidor();
    }

    if (selectTipo) {
        selectTipo.addEventListener('change', atualizarVisibilidade);
        atualizarVisibilidade(); // Corre na inicialização
    }

    // 2. ADICIONAR NOVA LINHA DE INSUMO
    if (btnAddInsumo) {
        btnAddInsumo.addEventListener('click', function() {
            const novaLinhaHTML = templateLinha.innerHTML.replace(/INDEX/g, indexLinha);
            tabelaBody.insertAdjacentHTML('beforeend', novaLinhaHTML);
            
            const novaLinha = tabelaBody.lastElementChild;
            
            // Evento para remover linha
            novaLinha.querySelector('.btn-remover-linha').addEventListener('click', function() {
                novaLinha.remove();
                recalcularServidor();
            });

            // Evento para recalcular ao alterar valor
            novaLinha.querySelectorAll('.input-calculo').forEach(input => {
                input.addEventListener('change', recalcularServidor);
                input.addEventListener('keyup', recalcularServidor);
            });

            indexLinha++;
        });
    }

    // 3. COMUNICAR COM O BACK-END PARA CALCULAR (Fetch API)
    function recalcularServidor() {
        if (!formFicha) return;
        clearTimeout(timeoutCalculo);
        
        // Debounce de 500ms para não sobrecarregar o servidor
        timeoutCalculo = setTimeout(() => {
            const formData = new FormData(formFicha);

            fetch(window.valoraUrl('fichas/calcular'), {
                method: 'POST',
                headers: window.valoraCabecalhos(),
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Erro na resposta');
                return response.json();
            })
            .then(data => {
                // Atualiza os labels com os dados calculados (Seção 8.4)
                document.getElementById('lbl_custo_direto').textContent = formatarMoeda(data.custo_direto);
                document.getElementById('lbl_custo_indireto').textContent = formatarMoeda(data.custo_indireto);
                document.getElementById('lbl_custo_total').textContent = formatarMoeda(data.custo_total);
                
                const badgeMargem = document.getElementById('badge_margem');
                badgeMargem.textContent = parseFloat(data.margem).toFixed(1) + '%';
                
                // Formatação visual da margem
                const margemMinima = parseFloat(document.getElementById('margem_minima').value || 30);
                badgeMargem.className = `badge fs-4 px-4 py-2 w-100 ${data.margem >= margemMinima ? 'bg-success' : 'bg-danger'}`;
            })
            .catch(error => {
                console.log('Modo de desenvolvimento ou erro de servidor:', error);
            });
        }, 500);
    }

    // Liga o recálculo aos campos principais
    document.querySelectorAll('.input-calculo').forEach(input => {
        input.addEventListener('input', recalcularServidor);
    });

    function formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
    }
});