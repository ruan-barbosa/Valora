document.addEventListener('DOMContentLoaded', function() {
    // Instâncias do Chart.js
    let chartMargem = null;
    let chartEvolucao = null;

    // Elementos do DOM
    const btnAtualizar = document.getElementById('btn-atualizar-graficos');
    const inputInicio = document.getElementById('filtro_inicio');
    const inputFim = document.getElementById('filtro_fim');
    const selectInsumo = document.getElementById('filtro_insumo');

    // Inicializa gráficos vazios
    const ctxMargem = document.getElementById('graficoMargem');
    const ctxEvolucao = document.getElementById('graficoEvolucao');

    if (!ctxMargem || !ctxEvolucao) return; // Segurança caso o perfil não tenha gráficos

    // Função para pedir dados ao servidor (Seção 7)
    function carregarGraficos() {
        const dataInicio = inputInicio.value;
        const dataFim = inputFim.value;
        const idItem = selectInsumo.value;

        // Monta a querystring para GET /dashboard/graficos
        const url = `/dashboard/graficos?periodo_inicio=${dataInicio}&periodo_fim=${dataFim}&id_item=${idItem}`;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Erro ao carregar dados dos gráficos.');
                return response.json();
            })
            .then(dados => {
                renderizarGraficoMargem(dados.margem_produtos);
                renderizarGraficoEvolucao(dados.evolucao_insumo);
            })
            .catch(error => {
                console.error(error);
                // MOCK PARA FASE DE DESENVOLVIMENTO (Seção 12)
                // Se a rota não existir, injeta dados fictícios para exibir o layout
                const mockMargem = [
                    { nome: 'Bolo de Cenoura', margem: 21.6, margem_minima: 30 },
                    { nome: 'Pão Francês', margem: 42.5, margem_minima: 30 },
                    { nome: 'Refri Cola 2L', margem: 29.1, margem_minima: 30 }
                ];
                const mockEvolucao = {
                    datas: ['10/11', '12/11', '15/11', '17/11'],
                    precos: [5.00, 5.20, 5.40, 5.40]
                };
                renderizarGraficoMargem(mockMargem);
                renderizarGraficoEvolucao(mockEvolucao);
            });
    }

    function renderizarGraficoMargem(dados) {
        if (chartMargem) chartMargem.destroy();

        const labels = dados.map(d => d.nome);
        const margens = dados.map(d => d.margem);
        
        // Cores baseadas na margem mínima (Seção 6)
        const cores = dados.map(d => d.margem >= d.margem_minima ? '#198754' : '#dc3545');

        chartMargem = new Chart(ctxMargem, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Margem de Lucro (%)',
                    data: margens,
                    backgroundColor: cores,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    function renderizarGraficoEvolucao(dados) {
        if (chartEvolucao) chartEvolucao.destroy();

        chartEvolucao = new Chart(ctxEvolucao, {
            type: 'line',
            data: {
                labels: dados.datas,
                datasets: [{
                    label: 'Preço (R$)',
                    data: dados.precos,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: false }
                }
            }
        });
    }

    // Event Listeners
    btnAtualizar.addEventListener('click', carregarGraficos);
    selectInsumo.addEventListener('change', carregarGraficos);

    // Carrega na primeira abertura
    carregarGraficos();
});