<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require __DIR__ . '/../src/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['items']) || !isset($input['total']) || empty($input['payments'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados da venda incompletos.']);
            exit;
        }

        $pdo->beginTransaction();

        // 1. Inserir a venda
        $stmt = $pdo->prepare("INSERT INTO sales (sale_date, total) VALUES (NOW(), ?)");
        $stmt->execute([$input['total']]);
        $saleId = $pdo->lastInsertId();

        // 2. Inserir os itens da venda e atualizar o estoque
        $itemStmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
        $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($input['items'] as $item) {
            $itemStmt->execute([$saleId, $item['id'], $item['quantity'], $item['price']]);
            $stockStmt->execute([$item['quantity'], $item['id']]);
        }

        // 3. Inserir os pagamentos
        $paymentStmt = $pdo->prepare("INSERT INTO payments (sale_id, method, amount) VALUES (?, ?, ?)");
        foreach ($input['payments'] as $payment) {
            // Agrupando 'Cartão de Crédito' e 'Cartão de Débito' como 'Cartão'
            $method = $payment['method'];
            if (strpos($method, 'Cartão') !== false) {
                $method = 'Cartão';
            }
            $paymentStmt->execute([$saleId, $method, $payment['amount']]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'sale_id' => $saleId]);

    } else if ($method === 'GET') {
        // Consulta para obter vendas e agrupar os métodos de pagamento
        $sql = "
            SELECT 
                s.id, 
                s.sale_date, 
                s.total,
                GROUP_CONCAT(DISTINCT p.method SEPARATOR ', ') as payment_methods
            FROM sales s
            LEFT JOIN payments p ON s.id = p.sale_id
            GROUP BY s.id, s.sale_date, s.total
            ORDER BY s.sale_date DESC
        ";
        $stmt = $pdo->query($sql);
        $sales = $stmt->fetchAll();
        echo json_encode($sales);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
}