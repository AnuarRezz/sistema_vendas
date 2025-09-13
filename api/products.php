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
        case 'GET':
            // Lógica para buscar produtos (GET)
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $product = $stmt->fetch();
                echo json_encode($product);
            } else {
                $stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
                $products = $stmt->fetchAll();
                echo json_encode($products);
            }
            break;

        case 'POST':
            // Lógica para criar/atualizar produtos (POST)
            if (empty($input['name']) || !isset($input['price']) || !isset($input['stock'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Dados incompletos.']);
                exit;
            }

            if (!empty($input['id'])) {
                $sql = "UPDATE products SET name = ?, size = ?, color = ?, price = ?, stock = ?, image = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$input['name'], $input['size'], $input['color'], $input['price'], $input['stock'], $input['image'], $input['id']]);
            } else {
                $sql = "INSERT INTO products (name, size, color, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$input['name'], $input['size'], $input['color'], $input['price'], $input['stock'], $input['image']]);
            }
            echo json_encode(['status' => 'success', 'id' => $input['id'] ?? $pdo->lastInsertId()]);
            break;

        case 'DELETE':
            // --- LÓGICA DE EXCLUSÃO CORRIGIDA ---
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do produto não fornecido.']);
                exit;
            }

            // 1. Verifica se o produto está em alguma venda
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM sale_items WHERE product_id = ?");
            $checkStmt->execute([$id]);
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                // 2. Se estiver, retorna um erro amigável (409 Conflict)
                http_response_code(409); 
                echo json_encode(['error' => 'Este produto não pode ser excluído, pois já faz parte do histórico de vendas. Considere zerar o estoque para desativá-lo.']);
                exit;
            }
            
            // 3. Se não estiver em nenhuma venda, exclui o produto
            $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $deleteStmt->execute([$id]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    // Retorna o erro específico do banco de dados, caso seja outro problema
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
}