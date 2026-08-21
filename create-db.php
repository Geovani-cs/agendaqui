<?php
// Helper de uso unico: cria o banco definido no .env.
// Rode uma vez com:  php create-db.php
// Depois pode apagar este arquivo.

$envPath = __DIR__ . '/.env';
$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v);
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$db   = $env['DB_DATABASE'] ?? 'agendaqui';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "OK: banco '$db' criado (ou ja existia) em $host:$port\n";
} catch (Throwable $e) {
    echo "ERRO ao conectar no MySQL em $host:$port como '$user'.\n";
    echo "Detalhe: " . $e->getMessage() . "\n";
    echo "Confira no DBngin: o MySQL esta rodando? a porta e $port? definiu senha?\n";
}
