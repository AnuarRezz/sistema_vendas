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
            $stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
            $products = $stmt->fetchAll();
            echo json_encode($products);
            break;

        case 'POST':
            // Usado tanto para criar quanto para atualizar
            if (empty($input['name']) || !isset($input['price']) || !isset($input['stock'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Dados incompletos.']);
                exit;
            }

            if (!empty($input['id'])) {
                // Atualizar produto
                $sql = "UPDATE products SET name = ?, size = ?, color = ?, price = ?, stock = ?, image = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $input['name'],
                    $input['size'],
                    $input['color'],
                    $input['price'],
                    $input['stock'],
                    $input['image'],
                    $input['id']
                ]);
            } else {
                // Inserir novo produto
                $sql = "INSERT INTO products (name, size, color, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $input['name'],
                    $input['size'],
                    $input['color'],
                    $input['price'],
                    $input['stock'],
                    $input['image']
                ]);
            }
            echo json_encode(['status' => 'success', 'id' => $input['id'] ?? $pdo->lastInsertId()]);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do produto não fornecido.']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
}