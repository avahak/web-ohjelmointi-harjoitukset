<?php
function tulostaTyylit() {
    echo "<style>
        body {background-color: #ccc;}
        .valkoinen {background-color: white; width: 30px; height: 30px;}
        .musta {background-color: black; width: 30px; height: 30px;}
        </style>";
}

function tervehdi($nimi) {
    echo "Hei, $nimi!";
}

function kerto($luku1, $luku2) {
    return $luku1*$luku2;
}

function potenssi($kantaluku, $eksponentti) {
    return $kantaluku**$eksponentti;
}

function shakkilauta() {
    echo "<table style=\"border: 1px solid black;border-collapse: collapse;\"><tbody>";
    for ($row = 0; $row < 8; $row++) {
        echo "<tr>";
        for ($col = 0; $col < 8; $col++) {
            $x = ($row+$col) % 2 == 0 ? "valkoinen" : "musta";
            echo "<td class=\"$x\"></td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table>";
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 1. -->
    <?php tulostaTyylit() ?>
</head>

<body>
    <?php
        echo "<br>2. ";
        tervehdi("maailma");

        echo "<br>3. ";
        echo "8*9 = " . (kerto(8, 9)) . ".";

        echo "<br>4. ";
        echo "3**5 = " . (potenssi(3, 5)) . ".";

        echo "<br>5.<br>";
        shakkilauta();
    ?>
</body>
</html>