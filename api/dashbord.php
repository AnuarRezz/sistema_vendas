<?php
header('Content-Type: application/json');
require '../src/config/db.php';

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