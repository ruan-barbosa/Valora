<?php

// Cálculo de custo e margem. É a única versão da conta: ficha técnica, reajuste de preço, simulador e produção usam esta classe.
//
// Regras:
//   quantidade usada = quantidade convertida para a unidade do insumo × (1 + perda / 100)
//   custo direto     = soma de (quantidade usada × preço atual do insumo)
//   custo indireto   = custo direto × percentual de custos indiretos da empresa
//   custo total      = custo direto + custo indireto
//   margem           = (preço de venda − custo total) ÷ preço de venda × 100
class CustoService
{
    /**
     * Custo de uma lista de linhas da ficha técnica.
     * Cada linha tem: id_item, quantidade, percentual_perda, preco_atual, grupo_linha, fator_linha, grupo_item, fator_item.
     * $precos (id_item => preço) troca o preço atual de alguns insumos; o simulador usa isso.
     */
    public static function calcular(array $linhas, float $precoVenda, float $percentualIndiretos, array $precos = []): array
    {
        $direto = 0.0;

        foreach ($linhas as $linha) {
            $preco = (float) ($precos[(int) $linha['id_item']] ?? $linha['preco_atual']);

            $quantidade = ConversaoService::converter(
                (float) $linha['quantidade'],
                ['grupo' => $linha['grupo_linha'], 'fator_conversao' => $linha['fator_linha']],
                ['grupo' => $linha['grupo_item'], 'fator_conversao' => $linha['fator_item']]
            );
            if ($quantidade === null) {
                throw new RuntimeException('Unidade incompatível na ficha técnica (insumo ' . $linha['id_item'] . ').');
            }

            $quantidade *= 1 + ((float) $linha['percentual_perda'] / 100);
            $direto += $quantidade * $preco;
        }

        $indireto = $direto * $percentualIndiretos / 100;
        $total = $direto + $indireto;

        return [
            'custo_direto'   => $direto,
            'custo_indireto' => $indireto,
            'custo_total'    => $total,
            'margem'         => self::margem($precoVenda, $total),
        ];
    }

    public static function margem(float $precoVenda, float $custoTotal): float
    {
        return $precoVenda > 0 ? (($precoVenda - $custoTotal) / $precoVenda) * 100 : 0.0;
    }

    /** Compara a margem como ela aparece na tela (2 casas), para não alertar por uma diferença que ninguém vê. */
    public static function abaixoDaMargem(float $margem, float $margemMinima): bool
    {
        return round($margem, 2) < $margemMinima;
    }

    /**
     * Produtos da empresa com custo e margem calculados.
     * Opções: 'ids' (só esses produtos), 'incluir_inativos' (true/false) e 'precos' (id_item => preço, para simular).
     * Cada produto volta com: custo_direto, custo_indireto, custo_total, margem, margem_minima (a do produto ou a da empresa),
     * abaixo_margem e tem_ficha (se há ao menos um insumo na composição).
     */
    public static function produtosDaEmpresa(int $idEmpresa, array $opcoes = []): array
    {
        $empresa = Empresa::buscar($idEmpresa);
        if (!$empresa) {
            return [];
        }

        $indiretos = (float) $empresa['percentual_custos_indiretos'];
        $margemPadrao = (float) $empresa['margem_minima_padrao'];

        $produtos = Produto::listar($idEmpresa, (bool) ($opcoes['incluir_inativos'] ?? false), $opcoes['ids'] ?? null);
        $linhasPorProduto = Composicao::linhasPorProdutos(array_column($produtos, 'id_produto'));

        foreach ($produtos as &$produto) {
            $linhas = $linhasPorProduto[$produto['id_produto']] ?? [];
            $conta = self::calcular($linhas, $produto['preco_venda'], $indiretos, $opcoes['precos'] ?? []);
            $minima = $produto['margem_minima'] !== null ? $produto['margem_minima'] : $margemPadrao;

            $produto['custo_direto']   = round($conta['custo_direto'], 4);
            $produto['custo_indireto'] = round($conta['custo_indireto'], 4);
            $produto['custo_total']    = round($conta['custo_total'], 4);
            $produto['margem']         = round($conta['margem'], 2);
            $produto['margem_minima']  = $minima;
            $produto['abaixo_margem']  = self::abaixoDaMargem($conta['margem'], $minima);
            $produto['tem_ficha']      = count($linhas) > 0;
        }

        return $produtos;
    }
}
