<?php
session_start();

$action = $_GET['action'] ?? '';

// --- LÓGICA DE LOGIN ---
if ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Credenciais fixas, conforme solicitado
    $valid_user = 'admin';
    $valid_pass = '123';

    // ATENÇÃO: Armazenar senhas em texto puro é uma falha grave de segurança.
    // Em um sistema real, use password_hash() e password_verify().
    
    if ($username === $valid_user && $password === $valid_pass) {
        // Autenticação bem-sucedida
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        // Falha na autenticação
        header('Location: login.php?error=1');
        exit;
    }
}

// --- LÓGICA DE LOGOUT ---
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Se nenhuma ação válida for fornecida, redireciona para o login
header('Location: login.php');
exit;