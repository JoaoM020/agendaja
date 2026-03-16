<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>CRUD de Contatos</title>
</head>
<body>
    <h1>Lista de Contatos</h1>

    <a href="criar.php">Adicionar Novo Contato</a>

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
            include_once("config.php"); // Inclui o arquivo de configuração do banco de dados

            // Consulta para selecionar todos os contatos
            $sql = "SELECT * FROM contatos";
            $resultado = mysqli_query($conexao, $sql);

            // Loop para exibir cada contato na tabela
            if (mysqli_num_rows($resultado) > 0) {
                while ($linha = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>" . $linha["id"] . "</td>";
                    echo "<td>" . $linha["nome"] . "</td>";
                    echo "<td>" . $linha["endereco"] . "</td>";
                    echo "<td>" . $linha["telefone"] . "</td>";
                    echo "<td>
                            <a href='editar.php?id=" . 
                            $linha["id"] . "'>Editar</a> |
                            <a href='excluir.php?id=" . $linha["id"] .
                             "'>Excluir</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Nenhum contato encontrado.
                </td></tr>";
            }
            mysqli_close($conexao); // Fecha a conexão com o banco de dados
            ?>
        </tbody>
    </table></body></html>
---------------
editar.php

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Contato</title>
</head>
<body>
    <h1>Editar Contato</h1>

    <?php
    include_once("config.php");

    // Obtém o ID do contato da URL
    $id = $_GET["id"];

    // Consulta para selecionar o contato com o ID especificado
    $sql = "SELECT * FROM contatos WHERE id = " . $id;
    $resultado = mysqli_query($conexao, $sql);

    // Verifica se o contato foi encontrado
    if (mysqli_num_rows($resultado) > 0) {
        $linha = mysqli_fetch_assoc($resultado);
    ?>

    <form action="atualizar.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $linha["id"]; ?>">

        <label for="nome">Nome:</label><br>
        <input type="text" id="nome" name="nome" value="<?php echo $linha["nome"]; ?>"><br><br>

        <label for="endereco">Endereço:</label><br>
        <input type="text" id="endereco" name="endereco" value="<?php echo $linha["endereco"]; ?>"><br><br>

        <label for="telefone">Telefone:</label><br>
        <input type="text" id="telefone" name="telefone" value="<?php echo $linha["telefone"]; ?>"><br><br>

        <input type="submit" value="Atualizar">
    </form>

    <?php
    } else {
        echo "Contato não encontrado.";
    }

    mysqli_close($conexao);
    ?>

    <a href="index.php">Voltar para a Lista</a>
</body>
</html>
