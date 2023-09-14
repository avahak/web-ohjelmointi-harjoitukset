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
    </style>
</head>

<body>
    <div class="osuus"></div>
    <?php
        $ostoslista = ["maitoa", "leipää", "jauhelihaa", "riisiä"];
        // print_r($ostoslista);
        $ostoslista[] = "omenoita";
        $index = array_search("maitoa", $ostoslista);
        $ostoslista[$index] = "rasvatonta maitoa";
        sort($ostoslista);
        echo "<ul>";
        foreach ($ostoslista as $x) {
            echo "<li>$x</li>";
        }
        echo "</ul>";
    ?>

    <div class="osuus"></div>
    <?php
        $arr = [];
        for ($k = 1; $k <= 100; $k++)
            $arr[] = $k;
        shuffle($arr);
        // print_r(array_slice($myArray, 0, 5))
        echo "Viisi ensimmäistä ovat: ";
        for ($k = 0; $k < 5; $k++)
            echo $arr[$k] . ($k == 4 ? "." : ", ");
    ?>

    <div class="osuus"></div>
    <?php
        $paakaupungit = array( "Italia"=>"Rooma", "Tanska"=>"Kööpenhamina", "Suomi"=>"Helsinki", "Ranska" => "Pariisi", "Saksa" => "Berliini", "Kreikka" => "Ateena", "Irlanti"=>"Dublin", "Hollanti"=>"Amsterdam", "Espanja"=>"Madrid", "Ruotsi"=>"Tukholma", "Iso-Britannia"=>"Lontoo", "Viro"=>"Tallinna", "Unkari"=>"Budapest", "Itävalta" => "Vienna", "Puola"=>"Varsova");
        // sort järjestää arvon mukaan, ksort järjestää avaimen mukaan
        ksort($paakaupungit);
        echo "<div style=\"height:150px;border:2px solid black;overflow:auto;background-color:#eee;\">";
        foreach ($paakaupungit as $key => $value)
            echo "$key: $value<br>";
        echo "</div>"
    ?>

    <div class="osuus"></div>    
    <?php
        function summaTaulukosta($taulukko) {
            // count($arr) alkioiden lkm
            $total = 0;
            foreach ($taulukko as $x) 
                $total += $x;
            return $total;
        }
        function testaa4($taulukko) {
            echo "Taulukon ";
            print_r($taulukko);
            echo " arvojen summa on: " . summaTaulukosta($taulukko) . "<br>";
        }
        testaa4([1, 2, 3, 4, 5]);
        testaa4([2, 3, 20]);
    ?>

    <div class="osuus"></div>    
    <?php
        function arvoTaulukossa($taulukko, $arvo) {
            foreach ($taulukko as $x)
                if ($x === $arvo)
                    return true;
            return false;
        }
        function arvoTaulukossa_($taulukko, $arvo) {
            return array_search($arvo, $taulukko) !== false;
        }
        function testaa5($taulukko, $arvo) {
            $palautus = arvoTaulukossa($taulukko, $arvo);
            if ($palautus === true) {
                echo "Taulukossa ";
                print_r($taulukko);
                echo " on ainakin yhdellä arvo " . $arvo . ".<br>";
            } else {
                echo "Taulukossa ";
                print_r($taulukko);
                echo " ei ole arvoa " . $arvo . ".<br>";
            }
        }
        testaa5([1,2,3,4,5], 3);
        testaa5([1,2,3,4,5], 6);
        testaa5(["kissa",2,3,"b",5], "b");
        testaa5(["kissa",2,3,"b",5], "a");
    ?>
</body>