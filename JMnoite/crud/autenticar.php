<?php
//session_start();//limpa as variaveis de sessao
//session_destroy();//destroi a sessao
session_start();


include_once("config.php");


$usuario = $_POST['usuario'];
$senha = $_POST['senha'];
$sql = "select * from usuarios WHERE usuario =
 '$usuario' and senha = '$senha'";
 $resultado = mysqli_query(mysql: $conexao, query: $sql);
 if (mysqli_num_rows(result: $resultado) > 0) {
    $_SESSION['usuario'] = $usuario;
    header(header: "Location: index.php");
    exit();
 } else {
    $_SESSION['erro'] = "Usuário ou senha inválidos.";
    header(header: "Location: login.php");
    exit();
}  