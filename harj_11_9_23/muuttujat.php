<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style> 
        body {
            counter-reset: div-counter;
        }
        .osuus:before {
            counter-increment: div-counter;
            content: counter(div-counter) ".";
        }
        .osuus {
            text-align: center;
            font-weight: bold;
            padding: 5px;
            margin: 5px 0;
            border: 2px;
            border-style: solid;
            border-radius: 20px;
            border-color: black;
            background-color: #bbb;
        }
        table, tr, td {
            padding: 3px;
            text-align: center;
            border-collapse: collapse; 
            border: 1px solid black;
        }
    </style>
</head>

<body>
    <div class="osuus"></div>
    <?php echo "Hello, <a href=\"file:///c:/xampp/apache/logs/error.log\">file:///c:/xampp/apache/logs/error.log</a>"?>

    <div class="osuus"></div>
    <h2>Ohjelmointikielet</h2>
    <ul>
        <?php 
            $kielet = ["PHP", "Java", "Perl", "Javascript"];
            // list($php, $java, $perl, $js) = $kielet  // unpacking an array
            foreach ($kielet as $kieli) {
                echo "<li>$kieli</li>";
        }?>
    </ul>

    <div class="osuus"></div>
    <?php
        $luku1 = 1;
        $luku2 = 2;
        echo "$luku1 + $luku2 = " . ($luku1 + $luku2) . "<br>";
        echo "$luku1 - $luku2 = " . ($luku1 - $luku2) . "<br>";
        echo "$luku1 * $luku2 = " . ($luku1 * $luku2) . "<br>";
        echo "$luku1 / $luku2 = " . ($luku1 / $luku2) . "<br>";
        echo "$luku1 % $luku2 = " . ($luku1 % $luku2) . "<br>";
    ?>

    <div class="osuus"></div>
    <?php
        // Huom. muuttujan sijoitus palauttaa uuden arvon
        // &$x on tässä vittausmuuttuja.
        function f(&$x, $new_x, $msg) {
            echo "$msg Arvo on nyt " . ($x = $new_x) . ".<br>";
        }
        $luku = 8;
        f($luku, $luku+2, "Lisää 2.");
        f($luku, $luku-4, "Vähennä 4.");
        f($luku, $luku*5, "Kerro 5:llä.");
        f($luku, $luku/3, "Jaa 3:lla.");
        f($luku, ++$luku, "Inkrementoi (lisää) arvoa yhdellä.");
        f($luku, --$luku, "Dekrementoi (vähennä) arvoa yhdellä.");
    ?>

    <div class="osuus"></div>
    <?php
        $luku = rand(1, 10);
        echo "Luku on $luku. ";
        if ($luku <= 5) 
            echo "Pieni!<br>";
        else
            echo "Suuri!<br>";
    ?>

    <div class="osuus"></div>
    <?php
        $arvosana = rand(1, 3);
        echo "Arvosana on $arvosana. ";
        if ($arvosana == 3) 
            echo "Kiitettävä.";
        elseif ($arvosana == 2)
            echo "Hyvä.";
        else 
            echo "Tyydyttävä.";
    ?>

    <div class="osuus"></div>
    <?php
        $k = 0;
        while ($k++ < 5)
            echo "Matti Meikäläinen.<br>";
    ?>

    <div class="osuus"></div>
    <?php
        $n = 10;
        for ($k = 1; $k <= $n; $k++)
            echo "$k * $n = " . ($k*$n) . "<br>";
    ?>

    <div class="osuus"></div>
    <?php
        for ($k = 1; $k <= 10; $k++)
            echo "$k" . ($k < 10 ? "-" : "");
    ?>

    <div class="osuus"></div>
    <table>
    <tbody>
    <?php
        for ($row = 1; $row <= 10; $row++) {
            echo "<tr>";
            for ($col = 1; $col <= 10; $col++) 
                echo "<td>" . ($col*$row) . "</td>";
            echo "</tr>";
        }
    ?>
    </tbody>
    </table>

</body>
</html>