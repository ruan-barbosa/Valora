// Medição simples de latência HTTP. Exemplo:
// VALORA_URL=http://localhost:8000/login VALORA_REQUISICOES=100 node testes/performance/check-p95.mjs
const url = process.env.VALORA_URL || 'http://localhost:8000/login';
const total = Math.max(20, Math.min(1000, Number(process.env.VALORA_REQUISICOES || 100)));
const cookie = process.env.VALORA_COOKIE || '';
const tempos = [];

for (let i = 0; i < total; i++) {
  const inicio = performance.now();
  const resposta = await fetch(url, {
    headers: cookie ? { Cookie: cookie } : {},
    redirect: 'manual',
    cache: 'no-store',
  });
  await resposta.arrayBuffer();
  tempos.push(performance.now() - inicio);
}

tempos.sort((a, b) => a - b);
const p95 = tempos[Math.ceil(tempos.length * 0.95) - 1];
const media = tempos.reduce((a, b) => a + b, 0) / tempos.length;
console.log(`URL: ${url}`);
console.log(`Requisições: ${total}`);
console.log(`Média: ${media.toFixed(1)} ms`);
console.log(`P95: ${p95.toFixed(1)} ms`);
console.log(`Critério RNF-01 (P95 <= 2000 ms): ${p95 <= 2000 ? 'ATENDIDO NESTA MEDIÇÃO' : 'NÃO ATENDIDO NESTA MEDIÇÃO'}`);
