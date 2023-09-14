<?php
    // global variable
    $maakunnat = ["Ahvenanmaa", "Etelä-Karjala", "Etelä-Pohjanmaa", "Etelä-Savo", "Kainuu", "Kanta-Häme", "Keski-Pohjanmaa", "Keski-Suomi", "Kymenlaakso", "Lappi", "Pirkanmaa", "Pohjanmaa", "Pohjois-Karjala", "Pohjois-Pohjanmaa", "Pohjois-Savo", "Päijät-Häme", "Satakunta", "Uusimaa", "Varsinais-Suomi"];

    $nimi = "";
    $email = "";
    $pw = "";
    $kuvaus = "";
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lemmikit</title>
    <!-- Lisää Bootstrap CSS-tiedosto -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <!-- Lisää oma CSS-tyylitiedosto -->
    <link href="styles.css" rel="stylesheet">
    <script defer src="script.js"></script>
</head>
<body>
    <div class="container mt-5">
        <?php
            // Tässä saadaan form data POST-metodilla ja se tulee tarkistaa 
            // (server side validation) ja tehdä jotain riippuen siitä meneekö tarkistus läpi.

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                function validateEmail($email) {
                    return filter_var($email, FILTER_VALIDATE_EMAIL);
                }

                function validatePw($pw) {
                    define('PASSWORD_REGEX', '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@#$%^&+=!])(.{8,})$/');
                    return preg_match(PASSWORD_REGEX, $pw);
                }

                function validateSukupuoli($sukupuoli) {
                    return ($sukupuoli === "mies") || ($sukupuoli === "nainen") || ($sukupuoli === "muu");
                }

                function validateName($name) {
                    return strlen($name) >= 4;
                }

                function validateMaakunta($value) {
                    global $maakunnat;
                    return ($value >= 0) && ($value < count($maakunnat));
                }

                function validateOmistaa($omistaa) {
                    // Depends on how we use this, let's just leave empty for now.
                    return true;
                }

                function validateKuvaus($kuvaus) {
                    // could check profanity here or something
                    return true;
                }

                function validateOsasto($osasto) {
                    return $osasto === "Espoo";
                }

                $osasto = $_POST["osasto"];
                $nimi = $_POST["nimi"];
                $email = $_POST["email"];
                $pw = $_POST["password"];
                $sukupuoli = $_POST["sukupuoli"];
                $maakunta = $_POST["maakunta"];
                $omistaa = isset($_POST["omistaa"]) ? $_POST["omistaa"] : [];
                $kuvaus = $_POST["kuvaus"];

                echo "<div class=\"alert alert-primary alert-dismissible\" role=\"alert\">";
                echo "<h2 class=\"h2\">Syötetyt tiedot:</h2>";
                echo "Osasto (hidden): " . $osasto . "<br>";
                echo "Nimi: " . $nimi . "<br>";
                echo "Email: " . $email . "<br>";
                echo "Password: " . $pw . "<br>";
                echo "Sukupuoli: " . $sukupuoli . "<br>";
                echo "Maakunta: " . $maakunta . " " . (validateMaakunta($maakunta) ? $maakunnat[$maakunta] : "(invalid value)") . "<br>";
                echo "Omistaa lkm: " . count($omistaa) . "<br>";
                echo "Kuvaus: " . $kuvaus . "<br>";
                echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                echo "</button></div>";

                // Server-side validation:
                $puutteet = [];      // lista löydetyistä puutteista formissa
                if (!validateOsasto($osasto))
                    $puutteet[] = "Puutteellinen osasto.";
                if (!validateName($nimi))
                    $puutteet[] = "Puutteellinen nimi.";
                if (!validateEmail($email))
                    $puutteet[] = "Puutteellinen email.";
                if (!validatePw($pw))
                    $puutteet[] = "Puutteellinen salasana.";
                if (!validateSukupuoli($sukupuoli))
                    $puutteet[] = "Puutteellinen sukupuoli.";
                if (!validateMaakunta($maakunta))
                    $puutteet[] = "Puutteellinen maakunta.";
                if (!validateOmistaa($omistaa))
                    $puutteet[] = "Puutteellinen omistus.";
                if (!validateKuvaus($kuvaus))
                    $puutteet[] = "Puutteellinen kuvaus.";

                if ($puutteet) {
                    // puutteita löytyi
                    echo "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\">";
                    if (count($puutteet) == 1)
                        echo $puutteet[0];
                    else {
                        echo "<h2 class=\"h2\">Puutteet:</h2>";
                        foreach ($puutteet as $puute) 
                            echo "<li>$puute</li>";
                    }
                    echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                    echo "</button></div>";
                } else {
                    // puutteita ei löytynyt - lomake hyväksytään
                    echo "<div class=\"alert alert-success alert-dismissible\" role=\"alert\">Great success!";
                    echo "<button class=\"btn-close\" aria-label=\"close\" data-bs-dismiss=\"alert\">";
                    echo "</button></div>";
                    // Tässä tiedot voisi tallentaa tietokantaan ja ohjata käyttäjä toiselle sivulle.
                }
            }
        ?>
        <h1>Yhteystiedot ja palaute</h1>
        <!-- Lomake alkaa tästä -->
        <form id="contactForm" class="needs-validation" novalidate method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Piilotettu kenttä -->
            <input type="hidden" name="osasto" value="Espoo">
            <!-- Nimi ja sähköpostiosoite -->
            <div class="row mb-3">
                <div class="col-sm-6">
                    <label for="nimi" class="form-label">Nimi:</label>
                    <input type="text" class="form-control" id="nimi" name="nimi" value="<?= $nimi; ?>" placeholder="Etunimi Sukunimi" required>
                    <div class="invalid-feedback">
                        Nimi on pakollinen.
                    </div>
                </div>
                <div class="col-sm-6">
                    <label for="email" class="form-label">Sähköpostiosoite:</label>
                    <input type="email" class="form-control" id="email" name="email" name="nimi" value="<?= $email; ?>" required>
                    <div class="invalid-feedback">
                        Syötä kelvollinen sähköpostiosoite.
                    </div>
                </div>
            </div>
            <!-- Sukupuoli -->
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Sukupuoli:</label>
                    <div class="form-group">
                        <input type="radio" class="form-check-input" name="sukupuoli" value="mies" id="mies" required>
                        <label class="form-check-label">Mies</label>
                    </div>
                    <div class="form-group">
                        <input type="radio" class="form-check-input" name="sukupuoli" value="nainen" id="nainen">
                        <label class="form-check-label">Nainen</label>
                    </div>
                    <div class="form-group">
                        <input type="radio" class="form-check-input" name="sukupuoli" value="muu" id="muu">
                        <label class="form-check-label">Muu</label>
                        <div class="invalid-feedback">
                            Valitse yksi näistä.
                        </div>
                    </div>
                </div>
                <!-- Maakunta -->
                <div class="col">
                    <label for="maakunta" class="form-label">Maakunta:</label>
                    <select class="form-select selectpicker" id="maakunta" name="maakunta" required>
                        <option value="" disabled selected>Valitse maakunta</option>
                        <?php
                            global $maakunnat;
                            foreach ($maakunnat as $x => $y) {
                                echo "<option value=$x>$y</option>";
                            }
                        ?>
                    </select>
                    <div class="invalid-feedback">
                        Valitse maakunta.
                    </div>
                </div>
            </div>
            <!-- Salasana -->
            <div class="mb-3">
                <label for="password" class="form-label">Salasana:</label>
                <input type="password" class="form-control" id="password" name="password" value="<?= $pw; ?>" pattern="^.{8,}$" required>
                <div class="invalid-feedback">
                    Salasana on pakollinen (vähintään 8 merkkiä).
                </div>
            </div>
            <!-- Lemmikit -->
            <div class="mb-3">
                <label class="form-label">Mitä lemmikkejä omistat?</label>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="omistaa[]" value="Koira" id="omistaaKoira">
                        <label class="form-check-label" for="omistaaKoira">Koira</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="omistaa[]" value="Kissa" id="omistaaKissa">
                        <label class="form-check-label" for="omistaaKissa">Kissa</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="omistaa[]" value="Matelija" id="omistaaMatelija">
                        <label class="form-check-label" for="omistaaMatelija">Matelija</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="omistaa[]" value="Jyrsija" id="omistaaJyrsija">
                        <label class="form-check-label" for="omistaaJyrsija">Jyrsijä</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="omistaa[]" value="Kala" id="omistaaKala">
                        <label class="form-check-label" for="omistaaKala">Kala</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="omistaa[]" value="Muu" id="omistaaMuu">
                        <label class="form-check-label" for="omistaaMuu">Muu</label>
                    </div>
                </div>
            </div>
            <!-- Kuvaus -->
            <div class="mb-3">
                <label for="feedback" class="form-label">Kuvaus itsestäsi:</label>
                <textarea class="form-control" id="kuvaus" name="kuvaus" value="<?= $kuvaus; ?>" placeholder="Vapaaehtoinen kuvaus." rows="4"></textarea>
                <div class="invalid-feedback">
                    Anna kuvaus.
                </div>
            </div>
            <!-- Lähetyspainike -->
            <button type="submit" class="btn btn-primary btn-lg">Lähetä</button>
            <!-- lisää btn-lg -->
        </form>
        <!-- Lomake päättyy tässä -->
    </div>
</body>
</html>
