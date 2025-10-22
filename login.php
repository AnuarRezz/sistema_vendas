<?php
session_start();
require __DIR__ . '/src/config/db.php'; // Inclui a conexão com o DB

$error_message = '';

// 1. Se o usuário já estiver logado, redireciona para o dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// 2. Processa a tentativa de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Por favor, preencha o usuário e a senha.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // 3. Verifica o usuário e a senha (usando password_verify)
            if ($user && password_verify($password, $user['password'])) {
                // Sucesso! Armazena os dados na sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                header('Location: index.php?page=dashboard');
                exit;
            } else {
                $error_message = 'Usuário ou senha inválidos.';
            }
        } catch (PDOException $e) {
            $error_message = 'Erro no banco de dados: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Vendas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="w-full max-w-sm bg-white p-8 rounded-xl shadow-xl">
        <div class="text-center mb-6">
            <i class="fas fa-store text-blue-500 text-5xl" aria-label="Loja"></i>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Sistema de Vendas</h1>
            <p class="text-gray-500">Faça login para continuar</p>
        </div>

        <form method="POST" action="login.php">
            <div class="mb-4">
                <label for="username" class="block mb-1 font-medium text-gray-700">Usuário</label>
                <input type="text" id="username" name="username" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block mb-1 font-medium text-gray-700">Senha</label>
                <input type="password" id="password" name="password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4 text-sm" role="alert">
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                Entrar
            </button>
        </form>
    </div>

</body>
</html>