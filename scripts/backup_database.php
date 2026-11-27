<?php
/**
 * Backup do MariaDB/MySQL por linha de comando.
 * Execute em horário programado (cron/Agendador de Tarefas): php scripts/backup_database.php
 * O arquivo gerado é armazenado fora da pasta pública e recebe permissão restrita.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

$base = dirname(__DIR__);
define('BASE_PATH', $base);
define('APP_PATH', $base . '/app');
require APP_PATH . '/core/Config.php';

try {
    $config = Config::todos();
} catch (Throwable $erro) {
    fwrite(STDERR, "Configuração não encontrada: {$erro->getMessage()}\n");
    exit(1);
}

$host = (string) ($config['host'] ?? 'localhost');
$banco = (string) ($config['banco'] ?? '');
$usuario = (string) ($config['usuario'] ?? '');
$senha = (string) ($config['senha'] ?? '');
$binario = (string) ($config['mysqldump'] ?? 'mysqldump');
$diretorio = (string) ($config['backup_path'] ?? ($base . '/storage/backups'));
$retencao = max(1, min(365, (int) ($config['backup_retencao_dias'] ?? 14)));

if ($banco === '' || $usuario === '') {
    fwrite(STDERR, "Defina banco e usuario em config/config.php.\n");
    exit(1);
}
if (!function_exists('proc_open')) {
    fwrite(STDERR, "A função proc_open está desativada; não é possível executar mysqldump.\n");
    exit(1);
}

if (!is_dir($diretorio) && !mkdir($diretorio, 0700, true) && !is_dir($diretorio)) {
    fwrite(STDERR, "Não foi possível criar o diretório de backup.\n");
    exit(1);
}
@chmod($diretorio, 0700);
$destino = rtrim($diretorio, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
    . 'valora-' . date('Ymd-His') . '.sql.gz';

$comando = [
    $binario,
    '--host=' . $host,
    '--user=' . $usuario,
    '--single-transaction',
    '--routines',
    '--triggers',
    '--default-character-set=utf8mb4',
    $banco,
];
$ambiente = getenv();
$ambiente['MYSQL_PWD'] = $senha;
$spec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
$processo = @proc_open($comando, $spec, $pipes, $base, $ambiente);
if (!is_resource($processo)) {
    fwrite(STDERR, "Não foi possível iniciar o mysqldump. Verifique o caminho do executável.\n");
    exit(1);
}
fclose($pipes[0]);

$gzip = gzopen($destino, 'wb9');
if (!$gzip) {
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_terminate($processo);
    fwrite(STDERR, "Não foi possível gravar o arquivo de backup.\n");
    exit(1);
}
while (!feof($pipes[1])) {
    $bloco = fread($pipes[1], 1024 * 1024);
    if ($bloco === false) break;
    if ($bloco !== '') gzwrite($gzip, $bloco);
}
fclose($pipes[1]);
$erro = stream_get_contents($pipes[2]);
fclose($pipes[2]);
$codigo = proc_close($processo);
gzclose($gzip);

if ($codigo !== 0 || !is_file($destino) || filesize($destino) === 0) {
    @unlink($destino);
    fwrite(STDERR, "Falha no backup: " . trim((string) $erro) . "\n");
    exit(1);
}
@chmod($destino, 0600);

// Remove backups mais antigos que o período de retenção.
foreach (glob(rtrim($diretorio, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'valora-*.sql.gz') ?: [] as $arquivo) {
    if (filemtime($arquivo) < time() - ($retencao * 86400)) @unlink($arquivo);
}

echo "Backup concluído: {$destino} (" . number_format(filesize($destino) / 1024, 1, ',', '.') . " KB)\n";
