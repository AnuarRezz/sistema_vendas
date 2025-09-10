<?php
header('Content-Type: application/json');
require '../src/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM products ORDER BY name, size");
        $products = $stmt->fetchAll();
        echo json_encode($products);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if ($id) { // Atualização
            $sql = "UPDATE products SET name = ?, size = ?, color = ?, price = ?, stock = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['name'], $data['size'], $data['color'], $data['price'], $data['stock'], $id]);
        } else { // Inserção
            $sql = "INSERT INTO products (name, size, color, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['name'], $data['size'], $data['color'], $data['price'], $data['stock'], $data['image']]);
            $id = $pdo->lastInsertId();
        }
        echo json_encode(['status' => 'success', 'id' => $id]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID do produto não fornecido.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        break;
}