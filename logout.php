<?php
session_start(); // Inicia a sessão para poder acessá-la
session_unset(); // Remove todas as variáveis da sessão
session_destroy(); // Destrói a sessão

// Redireciona o usuário para a página de login
header('Location: login.php');
exit;
?>