<?php

// Tabela empresa.
class Empresa
{
    public static function criar(string $nome, string $segmento, float $margemMinima): int
    {
        $pdo = Database::conectar();
        $sql = 'insert into empresa (nome, segmento, margem_minima_padrao) values (?, ?, ?)';
        $pdo->prepare($sql)->execute([$nome, $segmento, $margemMinima]);

        return (int) $pdo->lastInsertId();
    }

    public static function buscar(int $idEmpresa): ?array
    {
        $sql = 'select id_empresa, nome, segmento, margem_minima_padrao, percentual_custos_indiretos
                from empresa where id_empresa = ?';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute([$idEmpresa]);

        return $consulta->fetch() ?: null;
    }

    public static function atualizar(int $idEmpresa, string $nome, string $segmento, float $margemMinima, float $percentualIndiretos): void
    {
        $sql = 'update empresa
                set nome = ?, segmento = ?, margem_minima_padrao = ?, percentual_custos_indiretos = ?
                where id_empresa = ?';
        Database::conectar()->prepare($sql)->execute([$nome, $segmento, $margemMinima, $percentualIndiretos, $idEmpresa]);
    }
}