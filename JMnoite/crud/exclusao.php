<?php
include_once("config.php");

//obtém o id do contato da URL
$id =$_GET["id"];

//query para deletar o contato
$sql = "DELETE FROM contatos WHERE id = " .$id;

if(mysqli_query(mysql: $conexao, query: $sql)) {
    header("Location: index.php");
} else {
    echo "Erro ao exccluir contato: " . mysqli_error(mysql: $conexao);
} 

mysqli_close(mysql: $conexao);
?>
