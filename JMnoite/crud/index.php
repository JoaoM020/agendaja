<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>CRUD de contatos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php
    session_start(); 
    if(!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }
?>
    <h1>lista de contatos</h1>

    <a href="criar.php">Adcionar Novo contato</a>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Endereço</th>
                <th>Telefone</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php
        include_once("config.php"); //Inclui o arquivo de configuração

        //consulta para selecionar todos os contatos
        $sql = "SELECT * FROM contatos";
        $resultado = mysqli_query($conexao, $sql);

        //loop para exibir cada contato na tabela
        if(mysqli_num_rows($resultado) > 0){
            while($linha = mysqli_fetch_assoc($resultado)) {
                echo "<tr>";
                echo "<td>" . $linha["id"] . "</td>";
                echo "<td>" . $linha["nome"] . "</td>";
                echo "<td>" . $linha["endereco"] . "</td>";
                echo "<td>" . $linha["telefone"] . "</td>";
                echo "<td>
                          <a href='editar.php?id=" . 
                          $linha["id"] . "'>Editar</a> 
                          <a href='excluir.php?id=" . $linha["id"] .
                           "'>Excluir</a>
                      </td>";
                echo "</tr>";
            }
        }    else{
            echo"<tr><td colspan='5'>Nenhum contato encontrado</td>";
        }

        mysqli_close($conexao);//fecha a conexão com o banco
        ?>
        </tbody>
    </table>
</body>
</html>