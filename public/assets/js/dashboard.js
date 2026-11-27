document.addEventListener('DOMContentLoaded', function () {
    let chartMargem = null;
    let chartEvolucao = null;

    const btnAtualizar = document.getElementById('btn-atualizar-graficos');
    const inputInicio = document.getElementById('filtro_inicio');
    const inputFim = document.getElementById('filtro_fim');
    const selectInsumo = document.getElementById('filtro_insumo');
    const listaAlertas = document.getElementById('lista-alertas');
    const contadorProdutos = document.getElementById('contador-produtos-abaixo-margem');
    const contadorAlertas = document.getElementById('contador-alertas-nao-vistos');
    const badgeAlertas = document.getElementById('badge-alertas-nao-vistos');
    const ctxMargem = document.getElementById('graficoMargem');
    const ctxEvolucao = document.getElementById('graficoEvolucao');
    const statusMargem = document.getElementById('statusGraficoMargem');
    const statusEvolucao = document.getElementById('statusGraficoEvolucao');

    function elemento(tag, classe, texto) {
        const node = document.createElement(tag);
        if (classe) node.className = classe;
        if (texto !== undefined && texto !== null) node.textContent = String(texto);
        return node;
    }

    function dataBrasileira(valor) {
        const partes = String(valor || '').slice(0, 10).split('-');
        return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : '';
    }

    function renderizarAlertas(dados) {
        if (contadorProdutos) contadorProdutos.textContent = dados.produtos_abaixo_margem ?? 0;
        if (contadorAlertas) contadorAlertas.textContent = dados.alertas_nao_vistos ?? 0;
        if (badgeAlertas) badgeAlertas.textContent = `${dados.alertas_nao_vistos ?? 0} novos`;
        if (!listaAlertas) return;

        listaAlertas.replaceChildren();
        if (!Array.isArray(dados.alertas) || dados.alertas.length === 0) {
            listaAlertas.appendChild(elemento('li', 'list-group-item p-4 text-center text-muted', 'Nenhum alerta recente.'));
            return;
        }

        dados.alertas.forEach(alerta => {
            const visto = Boolean(alerta.visto);
            const cor = Number(alerta.margem_calculada) < Number(alerta.margem_minima) ? 'text-danger' : 'text-warning';
            const item = elemento('li', `list-group-item p-3${visto ? ' bg-light' : ''}`);
            const cabecalho = elemento('div', 'd-flex justify-content-between align-items-start mb-1');
            cabecalho.appendChild(elemento('strong', visto ? 'text-muted' : 'text-dark', alerta.produto));
            cabecalho.appendChild(elemento('small', 'text-muted', dataBrasileira(alerta.data_alerta)));
            item.appendChild(cabecalho);

            const descricao = elemento('p', 'mb-2 small text-muted');
            descricao.appendChild(document.createTextNode('Novo custo calculado: '));
            descricao.appendChild(elemento('strong', cor, Number(alerta.custo_calculado).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })));
            descricao.appendChild(document.createElement('br'));
            descricao.appendChild(document.createTextNode('Margem resultante: '));
            descricao.appendChild(elemento('strong', cor, `${Number(alerta.margem_calculada).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })}%`));
            if (alerta.insumo) {
                descricao.appendChild(document.createElement('br'));
                descricao.appendChild(elemento('em', '', `Impactado por: ${alerta.insumo}`));
            }
            item.appendChild(descricao);

            if (!visto) {
                const formulario = elemento('form', 'd-flex justify-content-end');
                formulario.method = 'post';
                formulario.action = window.valoraUrl('alertas/visto');
                const csrf = elemento('input');
                csrf.type = 'hidden'; csrf.name = 'csrf';
                csrf.value = listaAlertas.dataset.csrf || window.valoraCsrf();
                const id = elemento('input');
                id.type = 'hidden'; id.name = 'id_alerta'; id.value = alerta.id_alerta;
                const botao = elemento('button', 'btn btn-sm btn-outline-success', '✔ Marcar como visto');
                botao.type = 'submit';
                formulario.append(csrf, id, botao);
                item.appendChild(formulario);
            }

            listaAlertas.appendChild(item);
        });
    }

    function carregarAlertas() {
        if (!listaAlertas) return;
        fetch(window.valoraUrl('dashboard/alertas'), {
            headers: window.valoraCabecalhos(),
            cache: 'no-store'
        })
            .then(response => {
                if (!response.ok) throw new Error('Não foi possível atualizar os alertas.');
                return response.json();
            })
            .then(renderizarAlertas)
            .catch(error => console.error(error));
    }

    if (listaAlertas) {
        carregarAlertas();
        window.setInterval(carregarAlertas, 3000);
    }

    function definirStatus(elementoStatus, canvas, mensagem, erro) {
        if (elementoStatus) {
            elementoStatus.textContent = mensagem || '';
            elementoStatus.className = erro
                ? 'small text-danger text-center mt-2 mb-0'
                : 'small text-muted text-center mt-2 mb-0';
        }
        if (canvas) canvas.style.display = mensagem ? 'none' : '';
    }

    function renderizarGraficoMargem(dados) {
        if (chartMargem) {
            chartMargem.destroy();
            chartMargem = null;
        }
        if (!Array.isArray(dados) || dados.length === 0) {
            definirStatus(statusMargem, ctxMargem, 'Ainda não existem produtos com fichas técnicas para exibir.');
            return;
        }
        definirStatus(statusMargem, ctxMargem, '');

        chartMargem = new Chart(ctxMargem, {
            type: 'bar',
            data: {
                labels: dados.map(item => item.nome),
                datasets: [{
                    label: 'Margem de lucro (%)',
                    data: dados.map(item => Number(item.margem)),
                    backgroundColor: dados.map(item => Number(item.margem) >= Number(item.margem_minima) ? '#198754' : '#dc3545'),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    }

    function renderizarGraficoEvolucao(dados) {
        if (chartEvolucao) {
            chartEvolucao.destroy();
            chartEvolucao = null;
        }
        if (!dados || !Array.isArray(dados.datas) || dados.datas.length === 0) {
            definirStatus(statusEvolucao, ctxEvolucao, 'Não há alterações de preço deste insumo no período selecionado.');
            return;
        }
        definirStatus(statusEvolucao, ctxEvolucao, '');

        chartEvolucao = new Chart(ctxEvolucao, {
            type: 'line',
            data: {
                labels: dados.datas,
                datasets: [{
                    label: 'Preço (R$)',
                    data: dados.precos.map(Number),
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
                scales: { y: { beginAtZero: false } }
            }
        });
    }

    function carregarGraficos() {
        if (!ctxMargem || !ctxEvolucao) return;

        const dataInicio = inputInicio ? inputInicio.value : '';
        const dataFim = inputFim ? inputFim.value : '';
        if (dataInicio && dataFim && dataInicio > dataFim) {
            definirStatus(statusMargem, ctxMargem, '');
            definirStatus(statusEvolucao, ctxEvolucao, 'A data inicial deve ser anterior ou igual à data final.', true);
            return;
        }

        const parametros = new URLSearchParams({
            periodo_inicio: dataInicio,
            periodo_fim: dataFim,
            id_item: selectInsumo ? selectInsumo.value : ''
        });
        const url = `${window.valoraUrl('dashboard/graficos')}?${parametros.toString()}`;

        if (btnAtualizar) btnAtualizar.disabled = true;
        fetch(url, { headers: window.valoraCabecalhos(), cache: 'no-store' })
            .then(response => {
                if (!response.ok) throw new Error('Não foi possível carregar os gráficos.');
                return response.json();
            })
            .then(dados => {
                renderizarGraficoMargem(dados.margem_produtos);
                renderizarGraficoEvolucao(dados.evolucao_insumo);
            })
            .catch(error => {
                definirStatus(statusMargem, ctxMargem, error.message || 'Erro ao carregar o gráfico de margens.', true);
                definirStatus(statusEvolucao, ctxEvolucao, error.message || 'Erro ao carregar o histórico de preços.', true);
                console.error(error);
            })
            .finally(() => {
                if (btnAtualizar) btnAtualizar.disabled = false;
            });
    }

    if (btnAtualizar) btnAtualizar.addEventListener('click', carregarGraficos);
    if (selectInsumo) selectInsumo.addEventListener('change', carregarGraficos);
    carregarGraficos();
});
