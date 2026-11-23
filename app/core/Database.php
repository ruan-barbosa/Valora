<?php

// Cria a conexão com o banco (PDO) uma única vez e devolve a mesma para quem pedir.
class Database
{
    private static ?PDO $conexao = null;

    public static function conectar(): PDO
    {
        if (self::$conexao === null) {
            $c = require __DIR__ . '/../../config/config.php';

            $dsn = "mysql:host={$c['host']};dbname={$c['banco']};charset=utf8mb4";

            try {
                self::$conexao = new PDO($dsn, $c['usuario'], $c['senha'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // erros viram exceções
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // resultados como array com nomes
                    PDO::ATTR_EMULATE_PREPARES   => false,                   // consultas preparadas de verdade
                    // As datas gravadas pelo banco (current_timestamp) seguem o horário de Brasília.
                    PDO::MYSQL_ATTR_INIT_COMMAND => "set time_zone = '-03:00'",
                ]);
            } catch (PDOException $e) {
                // O erro original pode conter usuário e servidor: vai para o log, não para a tela.
                error_log('Falha ao conectar ao banco: ' . $e->getMessage());
                throw new RuntimeException('Não foi possível conectar ao banco de dados.', 0, $e);
            }
        }

        return self::$conexao;
    }
}