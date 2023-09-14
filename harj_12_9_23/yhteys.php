<?php

$db = "autokanta";
require "../config/sql_connect.php";

// Henkilo: (hetu, nimi, osoite, puhelinnumero)
// Ajoneuvo: (rekisterinro, vari, vuosimalli, omistaja)
// Sakko: (id, ajoneuvo, henkilo, pvm, summa, syy)

// apufunktio, hakee hetun (Henkilo pääavain) nimen perusteella
function hae_hetu($conn, $nimi) {
    $stmt = "SELECT hetu FROM Henkilo WHERE nimi=\"$nimi\"";
    $result = substitute_and_execute($conn, $stmt);
    $row = $result['value']->fetch_assoc();
    if ($row)
        return $row["hetu"];
    return "";
}

function instruct($conn, $stmt) {
    echo "Attempting to execute: <span style='color:#900;font-weight:bold;font-family:monospace'>$stmt</span>";
    $result = substitute_and_execute($conn, $stmt);
    echo "<br>";
    if ($result['status']) { 
        if ($result['value'])
            echo "Result: " . $result['value'];
        else
            echo "Result: success!";
    } else {
        echo "ERROR: " . $result['value'];
    }
    echo "<hr>";
}

// echo hae_hetu($conn, "Anne Autoilija");

// alikyselyllä:
$cmd1 = 'INSERT INTO Sakko (ajoneuvo, henkilo, pvm, summa, syy) VALUES ("CES-528", (SELECT hetu FROM Henkilo WHERE nimi="Anne Autoilija"), "2012-07-30", "50.00", "Virheellinen pysäköinti")';
// tai apufunktiolla hae_hetu:
$cmd1 = 'INSERT INTO Sakko (ajoneuvo, henkilo, pvm, summa, syy) VALUES ("CES-528", "' . hae_hetu($conn, "Anne Autoilija") . '", "2012-07-30", "50.00", "Virheellinen pysäköinti")';
instruct($conn, $cmd1);

// exit();

$cmd2 = 'DELETE FROM Henkilo WHERE nimi="Tapio Tamminen"'; 
// ei onnistu koska Ajoneuvo.henkilo on "ON DELETE RESTRICT" ja Tapio omistaa auton "HUT-444"

$cmd3 = 'UPDATE Henkilo SET osoite="Mäkelänkatu 15" WHERE nimi="Matti Miettinen"';
instruct($conn, $cmd3);

$cmd4 = 'UPDATE Ajoneuvo SET omistaja=(SELECT hetu FROM Henkilo WHERE nimi="Teemu Tamminen") WHERE rekisterinro="HUT-444"';
instruct($conn, $cmd4);
// nyt voidaan poistaa Tapio Tamminen
instruct($conn, $cmd2);

$cmd5 = 'INSERT INTO Ajoneuvo (rekisterinro, vari, vuosimalli, omistaja) VALUES ("DAU-781", "musta", 2007, (SELECT hetu FROM Henkilo WHERE nimi="Matti Miettinen"))';
instruct($conn, $cmd5);

$cmd6 = 'INSERT INTO Sakko (ajoneuvo, henkilo, pvm, summa, syy) VALUES ("DAU-781", "200292-195H", "2023-07-30", "250.00", "Rikkinäinen takavalo.")';
// Tässä sakko annetaan Teemu Tammiselle vaikka hän ei autoa omistakaan - ehkä ajanut kaverin autolla. Tietokanta sallii.
instruct($conn, $cmd6);

$conn->close();
?>