<?php
function tipo_triangulo($lado1, $lado2, $lado3)
{
    if ($lado1 == $lado2 && $lado2 == $lado3 && $lado1 == $lado3) {
        return "equilatero";
    } else if ($lado1 != $lado2 && $lado2 != $lado3 && $lado1 != $lado3) {
        return "escaleno";
    } else {
        return "Isoceles";
    }
}

$retorno = tipo_triangulo(3, 3, 3);
echo $retorno;
?>
