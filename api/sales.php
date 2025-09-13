<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require __DIR__ . '/../src/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'POST':
            if (empty($input['items']) || !isset($input['total']) || empty($input['payments'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Dados da venda incompletos.']);
                exit;
            }
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO sales (sale_date, total) VALUES (NOW(), ?)");
            $stmt->execute([$input['total']]);
            $saleId = $pdo->lastInsertId();
            $itemStmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
            $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            foreach ($input['items'] as $item) {
                $itemStmt->execute([$saleId, $item['id'], $item['quantity'], $item['price']]);
                $stockStmt->execute([$item['quantity'], $item['id']]);
            }
            $paymentStmt = $pdo->prepare("INSERT INTO payments (sale_id, method, amount) VALUES (?, ?, ?)");
            foreach ($input['payments'] as $payment) {
                $methodName = $payment['method'];
                if (strpos($methodName, 'Cartão') !== false) {
                    $methodName = 'Cartão';
                }
                $paymentStmt->execute([$saleId, $methodName, $payment['amount']]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success', 'sale_id' => $saleId]);
            break;

        case 'GET':
            if (isset($_GET['id'])) {
                // Busca uma única venda para o modal de edição
                $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $sale = $stmt->fetch();
                echo json_encode($sale);
            } else {
                // Lógica de filtro para o relatório
                 $whereClauses = [];
                $params = [];
                $filter = $_GET['filter'] ?? 'all';
                $date = $_GET['date'] ?? null;
                $paymentMethod = $_GET['payment_method'] ?? null;

                switch ($filter) {
                    case 'today': $whereClauses[] = "DATE(sale_date) = CURDATE()"; break;
                    case 'this_month': $whereClauses[] = "YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())"; break;
                    case 'custom_day': if ($date) { $whereClauses[] = "DATE(sale_date) = ?"; $params[] = $date; } break;
                    case 'custom_month': if ($date) { $whereClauses[] = "DATE_FORMAT(sale_date, '%Y-%m') = ?"; $params[] = $date; } break;
                }
                if ($paymentMethod) { $whereClauses[] = "id IN (SELECT sale_id FROM payments WHERE method = ?)"; $params[] = $paymentMethod; }
                $whereSql = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";
                
                $idQuery = "SELECT id FROM sales" . $whereSql;
                $stmt = $pdo->prepare($idQuery);
                $stmt->execute($params);
                $saleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $sales = []; $totalValue = 0; $byMethodResults = [];

                if (!empty($saleIds)) {
                    $placeholders = implode(',', array_fill(0, count($saleIds), '?'));
                    $salesQuery = "SELECT s.id, s.sale_date, s.total, GROUP_CONCAT(DISTINCT p.method SEPARATOR ', ') as payment_methods FROM sales s LEFT JOIN payments p ON s.id = p.sale_id WHERE s.id IN ($placeholders) GROUP BY s.id, s.sale_date, s.total ORDER BY s.sale_date DESC";
                    $stmt = $pdo->prepare($salesQuery);
                    $stmt->execute($saleIds);
                    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $itemsStmt = $pdo->prepare("SELECT si.quantity, p.name FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
                    foreach ($sales as $key => $sale) {
                        $itemsStmt->execute([$sale['id']]);
                        $sales[$key]['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
                    }

                    $totalValueStmt = $pdo->prepare("SELECT SUM(total) FROM sales WHERE id IN ($placeholders)");
                    $totalValueStmt->execute($saleIds);
                    $totalValue = $totalValueStmt->fetchColumn();
                    $byMethodQuery = "SELECT method, SUM(amount) as total FROM payments WHERE sale_id IN ($placeholders) GROUP BY method";
                    $byMethodStmt = $pdo->prepare($byMethodQuery);
                    $byMethodStmt->execute($saleIds);
                    $byMethodResults = $byMethodStmt->fetchAll(PDO::FETCH_KEY_PAIR);
                }

                echo json_encode([
                    'sales' => $sales,
                    'summary' => ['totalValue' => (float)($totalValue ?: 0), 'totalCount' => count($saleIds), 'byMethod' => $byMethodResults ?: []]
                ]);
            }
            break;
        
        case 'PUT':
            // Atualiza a data da venda
            if (!isset($input['id']) || !isset($input['sale_date'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Dados de atualização incompletos.']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE sales SET sale_date = ? WHERE id = ?");
            $stmt->execute([$input['sale_date'], $input['id']]);
            echo json_encode(['status' => 'success']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID da venda não fornecido.']);
                exit;
            }
            
            $pdo->beginTransaction();

            // 1. Pega os itens da venda para devolver ao estoque
            $itemsStmt = $pdo->prepare("SELECT product_id, quantity FROM sale_items WHERE sale_id = ?");
            $itemsStmt->execute([$id]);
            $items = $itemsStmt->fetchAll();

            // 2. Devolve os itens ao estoque
            $stockStmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            foreach ($items as $item) {
                $stockStmt->execute([$item['quantity'], $item['product_id']]);
            }

            // 3. Exclui a venda (o ON DELETE CASCADE cuidará dos itens e pagamentos)
            $deleteStmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
            $deleteStmt->execute([$id]);

            $pdo->commit();
            echo json_encode(['status' => 'success']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
}