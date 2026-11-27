<?php

// Conversão entre unidades do mesmo grupo (massa, volume, comprimento, tempo ou unidade).
// Cada unidade é um array com 'grupo' e 'fator_conversao' (quanto vale em relação à unidade base do grupo).
class ConversaoService
{
    public static function mesmoGrupo(array $a, array $b): bool
    {
        return $a['grupo'] === $b['grupo'];
    }

    /** Converte a quantidade de $de para $para. Devolve null se forem de grupos diferentes (ex.: kg para litro). */
    public static function converter(float $quantidade, array $de, array $para): ?float
    {
        $fatorPara = (float) $para['fator_conversao'];

        if (!self::mesmoGrupo($de, $para) || $fatorPara <= 0) {
            return null;
        }

        return $quantidade * (float) $de['fator_conversao'] / $fatorPara;
    }
}
