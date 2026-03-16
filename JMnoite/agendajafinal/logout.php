<?php
require_once 'database_config.php';
logout();
header('Location: login.php');
exit;


// Inicia a sessão para poder destruí-la
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Se desejar destruir a sessão completamente, apague também o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroi a sessão
session_destroy();

// Redireciona para a página de login com parâmetro de logout
header("Location: login.php?logout=success");
exit();


