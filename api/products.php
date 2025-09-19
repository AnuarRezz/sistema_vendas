<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require __DIR__ . '/../src/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Define o diretório de upload
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    switch ($method) {
        case 'GET':
            // --- LÓGICA 'GET' CORRIGIDA ---
            if (isset($_GET['id'])) {
                // Busca um único produto
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $product = $stmt->fetch();
                
                // Adiciona o caminho base para a imagem
                if ($product && !empty($product['image']) && !filter_var($product['image'], FILTER_VALIDATE_URL)) {
                    $product['image'] = 'api/uploads/' . $product['image'];
                }
                echo json_encode($product); // Envia o produto único

            } else {
                // Busca todos os produtos
                $stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Adiciona o caminho base para a imagem para o front-end
                foreach ($products as &$product) {
                    if (!empty($product['image']) && !filter_var($product['image'], FILTER_VALIDATE_URL)) {
                        $product['image'] = 'api/uploads/' . $product['image'];
                    }
                }
                unset($product); // Boa prática após usar referência em loop
                echo json_encode($products); // Envia a lista de produtos
            }
            break;
        // --- FIM DA CORREÇÃO ---

        case 'POST': 
            $input = $_POST;
            $imagePath = $input['existingImage'] ?? null;
            
            if (strpos($imagePath, 'placehold.co') !== false) {
                $imagePath = null;
            }

            if (empty($input['name']) || !isset($input['price']) || !isset($input['stock'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Dados incompletos.']);
                exit;
            }

            // --- LÓGICA DE UPLOAD DE IMAGEM ---
            if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
                $file = $_FILES['productImage'];
                $fileName = uniqid() . '-' . basename(preg_replace("/[^a-zA-Z0-9.\-_]/", "", $file['name']));
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $imagePath = $fileName; 
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Falha ao mover o arquivo de upload.']);
                    exit;
                }
            } else if (empty($input['id']) && !$imagePath) { 
                 $imagePath = 'https://placehold.co/300x300/e0e0e0/777?text=' . urlencode($input['name']);
            }
            // ---------------------------------

            if (!empty($input['id'])) {
                $sql = "UPDATE products SET name = ?, size = ?, color = ?, price = ?, stock = ?, image = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$input['name'], $input['size'], $input['color'], $input['price'], $input['stock'], $imagePath, $input['id']]);
            } else {
                $sql = "INSERT INTO products (name, size, color, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$input['name'], $input['size'], $input['color'], $input['price'], $input['stock'], $imagePath]);
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

            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM sale_items WHERE product_id = ?");
            $checkStmt->execute([$id]);
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                http_response_code(409); 
                echo json_encode(['error' => 'Este produto não pode ser excluído, pois já faz parte do histórico de vendas. Considere zerar o estoque para desativá-lo.']);
                exit;
            }
            
            $imgStmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $imgStmt->execute([$id]);
            $imageName = $imgStmt->fetchColumn();
            if ($imageName && !filter_var($imageName, FILTER_VALIDATE_URL)) {
                $imageFile = $uploadDir . $imageName;
                if (file_exists($imageFile)) {
                    unlink($imageFile); 
                }
            }
            
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
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
}