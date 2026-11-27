document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-simulador');
    if (!form) return;

    const botao = document.getElementById('btn-simular');
    const card = document.getElementById('card-resultados');
    const corpo = document.querySelector('#tabela-resultados tbody');
    const cabecalhoTabela = document.querySelector('#tabela-resultados thead');
    const vazio = document.getElementById('mensagem-vazio');
    const badge = document.getElementById('badge-reajuste');
    const seletorItem = document.getElementById('id_item');
    const campoPercentual = document.getElementById('percentual_reajuste');
    const erro = document.getElementById('erro-simulador');

    const moeda = valor => Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const percentual = valor => `${Number(valor).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 2 })}%`;

    function celula(texto, classe = '') {
        const td = document.createElement('td');
        td.textContent = texto;
        if (classe) td.className = classe;
        return td;
    }

    function mostrarErro(mensagem) {
        erro.textContent = mensagem;
        erro.classList.remove('d-none');
    }

    form.addEventListener('submit', async function (evento) {
        evento.preventDefault();
        erro.textContent = '';
        erro.classList.add('d-none');
        const textoOriginal = botao.textContent;
        botao.disabled = true;
        botao.textContent = 'Simulando...';

        try {
            const resposta = await fetch(window.valoraUrl('simulador/simular'), {
                method: 'POST',
                headers: window.valoraCabecalhos(),
                body: new FormData(form),
                cache: 'no-store'
            });
            const dados = await resposta.json();
            if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível concluir a simulação.');

            const resultados = Array.isArray(dados.resultado) ? dados.resultado : [];
            corpo.replaceChildren();
            card.classList.remove('d-none');

            const item = dados.item || {};
            const valorReajuste = Number(campoPercentual.value);
            const sinal = valorReajuste > 0 ? '+' : '';
            badge.textContent = `${item.nome || seletorItem.selectedOptions[0].text}: ${sinal}${percentual(valorReajuste)}`;
            badge.className = `badge fs-6 ${valorReajuste > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'}`;

            if (resultados.length === 0) {
                vazio.classList.remove('d-none');
                cabecalhoTabela.classList.add('d-none');
                return;
            }

            vazio.classList.add('d-none');
            cabecalhoTabela.classList.remove('d-none');
            resultados.forEach(resultado => {
                const linha = document.createElement('tr');
                const abaixo = resultado.situacao === 'abaixo do mínimo';
                const novoCustoClasse = Number(resultado.custo_depois) > Number(resultado.custo_antes)
                    ? 'text-end text-danger fw-bold' : 'text-end text-success fw-bold';
                const margemClasse = abaixo ? 'text-end text-danger fw-bold' : 'text-end text-success fw-bold';

                linha.append(
                    celula(resultado.produto || 'Produto sem nome', 'ps-4 fw-semibold text-dark'),
                    celula(moeda(resultado.custo_antes), 'text-end text-muted'),
                    celula(moeda(resultado.custo_depois), novoCustoClasse),
                    celula(percentual(resultado.margem_antes), 'text-end text-muted'),
                    celula(percentual(resultado.margem_depois), margemClasse)
                );
                const status = document.createElement('td');
                status.className = 'text-center pe-4';
                const marcador = document.createElement('span');
                marcador.className = abaixo ? 'badge bg-danger' : 'badge bg-success';
                marcador.textContent = abaixo ? 'Abaixo da margem mínima' : 'Margem saudável';
                status.appendChild(marcador);
                linha.appendChild(status);
                corpo.appendChild(linha);
            });
        } catch (falha) {
            mostrarErro(falha.message || 'Erro ao comunicar com o servidor. Confira sua conexão e tente novamente.');
        } finally {
            botao.disabled = false;
            botao.textContent = textoOriginal;
        }
    });
});
