// JavaScript comum a todas as telas (carregado pelo footer.php).

// Monta o endereço de uma rota do sistema. Ex.: valoraUrl('fichas/calcular').
// O endereço base vem do <meta name="base-url"> do header.php, então funciona em qualquer ambiente.
window.valoraUrl = function (caminho) {
    const base = document.querySelector('meta[name="base-url"]')?.content || '/';
    return base + String(caminho).replace(/^\/+/, '');
};

// Token CSRF da página (usado nos fetch que alteram dados).
window.valoraCsrf = function () {
    return document.querySelector('meta[name="csrf"]')?.content || '';
};

// Cabeçalhos padrão dos fetch: pedem resposta em JSON (erros também voltam em JSON) e enviam o token.
window.valoraCabecalhos = function () {
    return {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token': window.valoraCsrf()
    };
};
