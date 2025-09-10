<?php
// Exibe todos os erros (útil para debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require __DIR__ . '/../src/config/db.php'; // Caminho corrigido

$method = $_SERVER['REQUEST_METHOD'];

// O restante do código permanece o mesmo...
if ($method === 'POST') {
    // ...
} else if ($method === 'GET') {
    // ...
}