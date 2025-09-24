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
    // Vendas do Dia
    $stmt = $pdo->query("SELECT SUM(total) as dailySalesValue FROM sales WHERE DATE(sale_date) = CURDATE()");
    $dailySalesValue = $stmt->fetchColumn() ?: 0;

    // Vendas do Mês
    $stmt = $pdo->query("SELECT SUM(total) as monthlySalesValue FROM sales WHERE YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())");
    $monthlySalesValue = $stmt->fetchColumn() ?: 0;

    // Vendas Totais (Valor) -
    $stmt = $pdo->query("SELECT SUM(total) as totalSalesValue FROM sales");
    $totalSalesValue = $stmt->fetchColumn() ?: 0;

    // Vendas Realizadas (Contagem) -
    $stmt = $pdo->query("SELECT COUNT(id) as totalSalesCount FROM sales");
    $totalSalesCount = $stmt->fetchColumn() ?: 0;

    // Itens em Estoque -
    $stmt = $pdo->query("SELECT SUM(stock) as totalStock FROM products");
    $totalStock = $stmt->fetchColumn() ?: 0;
    
    // --- NOVO: Vendas por Mês no Ano ---
    $monthlySalesQuery = "
        SELECT 
            MONTH(sale_date) as month, 
            SUM(total) as total 
        FROM sales 
        WHERE YEAR(sale_date) = YEAR(CURDATE()) 
        GROUP BY MONTH(sale_date) 
        ORDER BY MONTH(sale_date) ASC
    ";
    $stmt = $pdo->query($monthlySalesQuery);
    $salesByMonth = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formata o array para ser mais fácil de usar no front-end (garante que todos os 12 meses existam)
    $formattedSalesByMonth = array_fill(1, 12, 0);
    foreach ($salesByMonth as $row) {
        $formattedSalesByMonth[(int)$row['month']] = (float)$row['total'];
    }


    echo json_encode([
        'dailySalesValue' => (float)$dailySalesValue,
        'monthlySalesValue' => (float)$monthlySalesValue,
        'totalSalesValue' => (float)$totalSalesValue,
        'totalSalesCount' => (int)$totalSalesCount,
        'totalStock' => (int)$totalStock,
        'salesByMonth' => $formattedSalesByMonth // Novo dado adicionado
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar dados do dashboard: ' . $e->getMessage()]);
}