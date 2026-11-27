<?php
return [
    'host'    => 'localhost',
    'banco'   => 'valora',
    'usuario' => '',
    'senha'   => '',
    'debug'   => false,   // true só no seu computador: mostra detalhes dos erros na tela
    'mysqldump' => 'mysqldump', // caminho do executável, se não estiver no PATH
    'backup_path' => __DIR__ . '/../storage/backups', // fora da pasta pública
    'backup_retencao_dias' => 14,
];