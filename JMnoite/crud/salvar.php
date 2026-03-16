<?php
include_once("config.php");

//obtém os dados do formulário
$nome = $_POST["nome"];
$endereco = $_POST["endereco"];
$telefone = $_POST["telefone"];

//Query para inserir os dados no banco de dados
$sql ="INSERT INTO contatos (nome,endereco,telefone)
VALUES ('$nome', '$endereco', 'telefone')";

if (mysqli_query($conexao, $sql)) {
    header("Location: index.php"); // redireciona de volta para a lista
    exit;
} else {
    echo "Erro ao salvar contato: " . mysqli_error($conexao);
}