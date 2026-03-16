<?php
$n1 = $_POST['numero1'];
$n2 = $_POST['numero2'];
$op = $_POST['operacao'];

switch ($op) {
    case '+':
        $resultado = $n1 + $n2;
        break;
    case '-':
        $resultado = $n1 - $n2;
        break;
    case '*':
        $resultado = $n1 * $n2;
        break;
    case '/':
        $resultado = $n2 != 0 ? $n1 / $n2 : 'Divisão por zero!';
        break;
    default:
        $resultado = 'Operação inválida!';
        break;
}
echo "o resultado é :$resultado";