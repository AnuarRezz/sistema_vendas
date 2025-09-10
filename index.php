<?php
// Inclui o header
include 'src/includes/header.php';

// Sistema de roteamento básico
$page = isset($_GET['page']) ? preg_replace('/[^a-z_]/', '', $_GET['page']) : 'dashboard';
$allowed_pages = ['dashboard', 'pdv', 'products', 'reports'];

// Verifica se a página solicitada é válida
if (in_array($page, $allowed_pages)) {
    $view_file = 'src/views/' . $page . '.php';

    // Verifica se o arquivo da view existe
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        // Página não encontrada
        include 'src/views/error.php';
    }
} else {
    // Página não permitida, mostra erro
    include 'src/views/error.php';
}

// Inclui os modais
include 'src/includes/modals.php';

// Inclui o footer
include 'src/includes/footer.php';