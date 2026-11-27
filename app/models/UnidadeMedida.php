<?php

// Tabela unidade_medida (as unidades são as mesmas para todas as empresas).
class UnidadeMedida
{
    public static function listar(): array
    {
        $sql = 'select id_unidade, sigla, grupo, fator_conversao from unidade_medida order by grupo, fator_conversao';
        $unidades = Database::conectar()->query($sql)->fetchAll();

        foreach ($unidades as &$unidade) {
            $unidade['fator_conversao'] = (float) $unidade['fator_conversao'];
        }

        return $unidades;
    }

    public static function buscar(int $idUnidade): ?array
    {
        $consulta = Database::conectar()->prepare(
            'select id_unidade, sigla, grupo, fator_conversao from unidade_medida where id_unidade = ?'
        );
        $consulta->execute([$idUnidade]);
        $unidade = $consulta->fetch();

        if (!$unidade) {
            return null;
        }

        $unidade['id_unidade'] = (int) $unidade['id_unidade'];
        $unidade['fator_conversao'] = (float) $unidade['fator_conversao'];
        return $unidade;
    }

    public static function buscarPorSigla(string $sigla): ?array
    {
        $consulta = Database::conectar()->prepare(
            'select id_unidade, sigla, grupo, fator_conversao from unidade_medida where sigla = ?'
        );
        $consulta->execute([$sigla]);
        $unidade = $consulta->fetch();

        if (!$unidade) {
            return null;
        }

        $unidade['id_unidade'] = (int) $unidade['id_unidade'];
        $unidade['fator_conversao'] = (float) $unidade['fator_conversao'];
        return $unidade;
    }

    public static function existe(int $idUnidade): bool
    {
        $consulta = Database::conectar()->prepare('select 1 from unidade_medida where id_unidade = ?');
        $consulta->execute([$idUnidade]);

        return (bool) $consulta->fetchColumn();
    }
}
