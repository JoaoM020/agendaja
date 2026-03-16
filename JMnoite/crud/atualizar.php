<?php
include_once("config.php");
//obtém os dados do formulário
$id = $_POST["id"];
$id = $_POST["nome"];
$id = $_POST["endereco"];
$id = $_POST["telefone"];
//query para atualizar os dados no banco de daodos
$sql ="UPDATE contatos SET nome = '$nome', endereco = '$endereco',
telefone = '$telefone' WHERE id = " .$id;
if(mysqli_query(mysql: $conexao, query: $sql)){
    header(header:"location: index.php"); //
} else{
    echo "Erro ao atualizar contato: " . mysqli_error(mysql:$conexao);    
}
mysqli_close(mysql: $conexao);
?>