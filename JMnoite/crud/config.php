<?php

//Informaçoes de conexão com o banco de dados
$host = "localhost"; //Endereço do servidor MySQL
$usuario = "root";
$senha = "senha"();
$banco = "pessoas";

//conexão com o banco de dados 
$conexao = mysqli_connect(hostname:$hos,
    username: $usuario,
    password: $senha,
    database: $banco);

//verifica se a cnoxão foi bem sucedida
if (!$conexao){
    die("Falha na conexão com op banco de dados" .
    mysqli_connect_error());
}