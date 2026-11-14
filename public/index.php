<?php
// PÁGINA DE TESTE TEMPORÁRIA: confere se a conexão com o banco funciona.
// Será substituída pelo ponto de entrada real (roteador) nas próximas etapas.
 
declare(strict_types=1);
 
require __DIR__ . '/../app/core/Database.php';
 
header('Content-Type: text/html; charset=utf-8');
 
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
 
try {
    $pdo = Database::conectar();
    $unidades = $pdo
        ->query('select sigla, grupo, fator_conversao from unidade_medida order by grupo, fator_conversao')
        ->fetchAll();
} catch (Throwable $erro) {
    error_log('Valora: ' . $erro->getMessage());
    http_response_code(500);
    echo '<h1>Erro interno</h1><p>Tente novamente mais tarde.</p>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Valora - teste de conexão</title>
</head>
<body>
    <h1>Conexão com o banco funcionando</h1>
    <p>Unidades de medida cadastradas:</p>
    <table border="1" cellpadding="6">
        <tr><th>Sigla</th><th>Grupo</th><th>Fator</th></tr>
        <?php foreach ($unidades as $u): ?>
            <tr>
                <td><?= e($u['sigla']) ?></td>
                <td><?= e($u['grupo']) ?></td>
                <td><?= e((string) $u['fator_conversao']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>