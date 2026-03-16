<?php
session_start();
session_destroy();
header("Location: login.php");
exit();
/**
 * Logout - Remédio Já
 * 
 * Script responsável por encerrar a sessão do usuário
 * e redirecionar para a página de login.
 * 
 * @author Remédio Já Team
 * @version 1.0
 */

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
?>

