<?php
// Configurações do banco de dados
$host = 'localhost'; // ou o host do seu banco de dados
$dbname = 'sistema_vendas';
$user = 'root'; // seu usuário
$pass = ''; // sua senha
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Opções do PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Cria a instância do PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Em caso de erro, exibe a mensagem e encerra a aplicação
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Falha na conexão com o banco de dados: ' . $e->getMessage()]);
    exit;
}