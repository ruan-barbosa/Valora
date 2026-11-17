document.addEventListener('DOMContentLoaded', function() {
    
    // 1. MODAL: HISTÓRICO COM CHART.JS (Apenas injeta se a view tiver o botão de histórico)
    const botoesHistorico = document.querySelectorAll('.btn-outline-info[title="Histórico"]');
    
    if (botoesHistorico.length > 0) {
        // Como o layout base não tinha o HTML do Modal de Histórico na última versão gerada, 
        // criamos dinamicamente se necessário, ou assumimos que o modalHistorico existe no HTML.
        // Aqui assumimos que ele fará o Fetch e atualizará um Canvas "graficoHistorico"
        
        let chartInstancia = null;

        botoesHistorico.forEach(btn => {
            btn.addEventListener('click', function() {
                const tr = this.closest('tr');
                const idItem = tr.querySelector('form input[name="id_item"]').value;
                const nomeItem = tr.querySelector('td.fw-bold').textContent;
                
                alert(`Ao ligar ao back-end, este botão fará um GET para /insumos/historico?id_item=${idItem} e abrirá o modal com o gráfico do insumo: ${nomeItem}.`);
                
                // Exemplo de como será a chamada real:
                /*
                fetch(`/insumos/historico?id_item=${idItem}`)
                    .then(res => res.json())
                    .then(data => {
                        // Renderizar Tabela e Chart.js
                        // ... lógica do Chart.js semelhante à do dashboard ...
                    });
                */
            });
        });
    }
});