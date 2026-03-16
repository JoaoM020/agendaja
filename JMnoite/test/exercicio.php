<?


function calcularMedia($notas)   {
    $soma = array_sum($notas);
    $quantidade = count($notas);
    return $soma / $quantidade;
}

$notas = [7,8.5,9,6,10];
echo "Média: " . calcularMedia($notas);

?>
