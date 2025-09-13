<?php
// Exibe todos os erros (útil para debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// --- Caminho Corrigido ---
// Usa __DIR__ para obter o caminho absoluto do diretório atual (api)
// e então volta um nível para encontrar src/config/db.php
require __DIR__ . '/../src/config/db.php';

try {
    // Vendas Totais (Valor)
    $stmt = $pdo->query("SELECT SUM(total) as totalSalesValue FROM sales");
    $totalSalesValue = $stmt->fetchColumn() ?: 0;

    // Vendas Realizadas (Contagem)
    $stmt = $pdo->query("SELECT COUNT(id) as totalSalesCount FROM sales");
    $totalSalesCount = $stmt->fetchColumn() ?: 0;

    // Itens em Estoque
    $stmt = $pdo->query("SELECT SUM(stock) as totalStock FROM products");
    $totalStock = $stmt->fetchColumn() ?: 0;

    echo json_encode([
        'totalSalesValue' => (float)$totalSalesValue,
        'totalSalesCount' => (int)$totalSalesCount,
        'totalStock' => (int)$totalStock
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar dados do dashboard: ' . $e->getMessage()]);
}