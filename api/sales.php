<?php
header('Content-Type: application/json');
require '../src/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $pdo->beginTransaction();
    try {
        // 1. Inserir a venda
        $sql = "INSERT INTO sales (sale_date, total) VALUES (NOW(), ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['total']]);
        $saleId = $pdo->lastInsertId();

        // 2. Inserir os itens da venda e atualizar o estoque
        foreach ($data['items'] as $item) {
            $sql = "INSERT INTO sale_items (sale_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$saleId, $item['id'], $item['quantity'], $item['price']]);

            // Atualizar estoque
            $sql_stock = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmt_stock = $pdo->prepare($sql_stock);
            $stmt_stock->execute([$item['quantity'], $item['id']]);
        }

        // 3. Inserir os pagamentos
        foreach ($data['payments'] as $payment) {
            $sql = "INSERT INTO payments (sale_id, method, amount) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$saleId, $payment['method'], $payment['amount']]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'sale_id' => $saleId]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao registrar venda: ' . $e->getMessage()]);
    }

} else if ($method === 'GET') {
    // Lógica para buscar relatórios (pode ser expandida)
    $stmt = $pdo->query("SELECT s.id, s.sale_date, s.total, GROUP_CONCAT(p.method SEPARATOR ', ') as payment_methods
                         FROM sales s
                         JOIN payments p ON s.id = p.sale_id
                         GROUP BY s.id
                         ORDER BY s.sale_date DESC");
    $sales = $stmt->fetchAll();
    echo json_encode($sales);
}