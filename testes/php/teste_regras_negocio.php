<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/services/ConversaoService.php';
require dirname(__DIR__, 2) . '/app/services/CustoService.php';

function verificar(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        fwrite(STDERR, "FALHOU: {$mensagem}\n");
        exit(1);
    }
    echo "OK: {$mensagem}\n";
}

$kg = ['grupo' => 'massa', 'fator_conversao' => 1000.0];
$g = ['grupo' => 'massa', 'fator_conversao' => 1.0];
$litro = ['grupo' => 'volume', 'fator_conversao' => 1000.0];

$quantidade = ConversaoService::converter(500.0, $g, $kg);
verificar($quantidade !== null && abs($quantidade - 0.5) < 0.000001, '500 g equivalem a 0,5 kg.');
verificar(ConversaoService::converter(1.0, $kg, $litro) === null, 'Unidades de grupos diferentes não são convertidas.');

$linhas = [[
    'id_item' => 10,
    'quantidade' => 500.0,
    'percentual_perda' => 10.0,
    'preco_atual' => 20.0,
    'grupo_linha' => 'massa',
    'fator_linha' => 1.0,
    'grupo_item' => 'massa',
    'fator_item' => 1000.0,
]];
$calculo = CustoService::calcular($linhas, 50.0, 15.0);
verificar(abs($calculo['custo_direto'] - 11.0) < 0.000001, 'Custo direto considera conversão e perda.');
verificar(abs($calculo['custo_indireto'] - 1.65) < 0.000001, 'Custos indiretos incidem sobre o custo direto.');
verificar(abs($calculo['custo_total'] - 12.65) < 0.000001, 'Custo total soma direto e indireto.');
verificar(abs($calculo['margem'] - 74.7) < 0.000001, 'Margem usa (preço de venda - custo total) / preço de venda.');
verificar(CustoService::abaixoDaMargem($calculo['margem'], 75.0), 'Alerta ocorre abaixo da margem mínima.');
verificar(!CustoService::abaixoDaMargem($calculo['margem'], 74.7), 'Margem igual ao mínimo não gera alerta.');

echo "\nTodos os testes isolados passaram.\n";
