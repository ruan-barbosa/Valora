document.addEventListener('DOMContentLoaded', function() {
    
    const formSimulador = document.getElementById('form-simulador');
    const btnSimular = document.getElementById('btn-simular');
    const cardResultados = document.getElementById('card-resultados');
    const tabelaBody = document.querySelector('#tabela-resultados tbody');
    const theadResultados = document.querySelector('#tabela-resultados thead');
    const mensagemVazio = document.getElementById('mensagem-vazio');
    const badgeReajuste = document.getElementById('badge-reajuste');
    const selectInsumo = document.getElementById('id_item');
    const inputReajuste = document.getElementById('percentual_reajuste');

    // Utilitário de formatação de moeda (Seção 3)
    function formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
    }

    formSimulador.addEventListener('submit', function(e) {
        e.preventDefault(); // Impede o envio tradicional
        
        // UX: Mostra que está a carregar
        const textoOriginalBtn = btnSimular.innerHTML;
        btnSimular.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> A simular...';
        btnSimular.disabled = true;

        const formData = new FormData(formSimulador);

        // Fetch para a rota definida na Seção 8.7
        fetch('/simulador/simular', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Erro na resposta do servidor.');
            return response.json();
        })
        .then(data => {
            // A rota devolve o $resultado (array de objetos) - Seção 8.7
            const resultados = data.resultado || data; 
            
            // Limpa tabela anterior e mostra o card
            tabelaBody.innerHTML = ''; 
            cardResultados.classList.remove('d-none');
            
            // Atualiza o emblema no topo da tabela
            const nomeInsumo = selectInsumo.options[selectInsumo.selectedIndex].text;
            const valorReajuste = parseFloat(inputReajuste.value);
            const sinal = valorReajuste > 0 ? '+' : '';
            badgeReajuste.textContent = `${nomeInsumo}: ${sinal}${valorReajuste}%`;
            badgeReajuste.className = `badge fs-6 ${valorReajuste > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'}`;

            if (resultados.length === 0) {
                // Nenhum produto usa este insumo (Estado vazio da Seção 9)
                mensagemVazio.classList.remove('d-none');
                theadResultados.classList.add('d-none');
            } else {
                mensagemVazio.classList.add('d-none');
                theadResultados.classList.remove('d-none');

                resultados.forEach(item => {
                    // Define a formatação da margem com base na situação (Seção 6 e 8.7)
                    // Situacao esperada: 'ok' ou 'abaixo do mínimo'
                    const isAbaixo = item.situacao === 'abaixo do mínimo';
                    const corNovaMargem = isAbaixo ? 'text-danger' : 'text-success';
                    const badgeAlerta = isAbaixo 
                        ? '<span class="badge bg-danger">Abaixo da Margem Mínima</span>' 
                        : '<span class="badge bg-success">Margem Saudável</span>';

                    // Cor do custo simulado (vermelho se aumentou, verde se diminuiu)
                    const corNovoCusto = item.custo_depois > item.custo_antes ? 'text-danger fw-bold' : 'text-success fw-bold';

                    const rowHTML = `
                        <tr>
                            <td class="ps-4 fw-bold text-dark">${item.produto}</td>
                            <td class="text-end text-muted">${formatarMoeda(item.custo_antes)}</td>
                            <td class="text-end ${corNovoCusto}">${formatarMoeda(item.custo_depois)}</td>
                            <td class="text-end text-muted">${parseFloat(item.margem_antes).toFixed(1)}%</td>
                            <td class="text-end fw-bold ${corNovaMargem} fs-6">${parseFloat(item.margem_depois).toFixed(1)}%</td>
                            <td class="text-center pe-4">${badgeAlerta}</td>
                        </tr>
                    `;
                    tabelaBody.insertAdjacentHTML('beforeend', rowHTML);
                });
            }
        })
        .catch(error => {
            console.error('Erro ao comunicar com o servidor:', error);
            
            // Tratamento visual para o utilizador no caso de o backend ainda não existir (Seção 12)
            alert('Aviso (Modo Frontend): Como a rota POST /simulador/simular ainda não está criada, não existem dados para mostrar.');
            
            // Restaura o botão em caso de erro
            btnSimular.innerHTML = textoOriginalBtn;
            btnSimular.disabled = false;
        })
        .finally(() => {
            // Restaura o estado normal do botão
            if (btnSimular.disabled) {
                btnSimular.innerHTML = textoOriginalBtn;
                btnSimular.disabled = false;
            }
        });
    });
});