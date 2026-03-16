<?php 
        //esse código armazena 10 números//
        for ($i=1; $i<=10;$i++) {
            $numeros[$i] = rand(min:1,max:20);
            $numeros2[$i] = $numeros[$i] * 2;
        }

        //numeros do primeiro vetor:
        echo "Numeros do primeiro vetor:";
        echo "<table border>";
        echo "<tr>";
        for ($i=1; $i<=50;$i++) {
            echo "<td>";
            echo @$numeros[$i] . " ";
        }
        echo "</tr>";
        echo "</table>";

        echo "<br>";

        //numeros do segundo vetor:
        for ($i=1; $i<=10;$i++) {
            echo @$numeros2[$i] . " ";

        }



?>