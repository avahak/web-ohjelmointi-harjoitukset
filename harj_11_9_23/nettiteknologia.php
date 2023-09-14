<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #aaa;
            text-align: center;
            margin: 50px;
            font-size: 30px;
        }
    </style>
</head>

<body>
    <?php
    if (isset($_GET['tekno'])) {
        $x = $_GET['tekno'];
        if ($x === "html")
            echo "HTML kuvaa dokumentin rakenteen.";
        elseif ($x === "css")
            echo "CSS määrittää dokumentin ulkoasun.";
        elseif ($x === "javascript")
            echo "Javascript on selainpuolen kieli.";
        elseif ($x === "php")
            echo "PHP on palvelinpuolen kieli.";
        else 
            echo "Haluamaasi teknologiaa ei löydy";
        echo "<br><a href=\"nettiteknologia.php\">Back</a>";
    } else {
        echo "<a href=\"nettiteknologia.php?tekno=html\">HTML</a><br>";
        echo "<a href=\"nettiteknologia.php?tekno=css\">CSS</a><br>";
        echo "<a href=\"nettiteknologia.php?tekno=javascript\">Javascript</a><br>";
        echo "<a href=\"nettiteknologia.php?tekno=php\">PHP</a><br>";
    }
    ?>
</body>
</html>