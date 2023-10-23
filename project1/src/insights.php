<?php 

require_once __DIR__ . "/shared_elements.php";
require_once __DIR__ . "/init.php";

init();

if (($_SERVER["REQUEST_METHOD"] == "GET") && (isset($_GET["logout"]))) {
    logout();
}

shared_script_start("Opittu");
?>

<div class="container mb-3" style="max-width:700px">

    <h1 class="my-3">Opetuksia ja kokemuksia projektista</h1>

    <ol>
        <li class="mt-3">
            <p class="m-0 fw-bold text-info">Aikataulu:</p>
            <p class="m-0">Kaiken tekeminen kestää kauemmin kuin voisi luulla.</p>
            <ul>
                <li>Uusien työkalujen, kirjastojen, ympäristöjen käytön opettelu.</li>
                <li>Monissa asioissa on piileviä yksityiskohtia, joiden huomioiminen voi
                    vaatia erityistarkasteluja. <br>
                    <div class="ms-5">
                    <small>Esimerkki: Mitä pitäisi tapahtua jos 
                    käyttäjä liittää profiilikuvan signup yhteydessä mutta server-puolen 
                    validointi epäonnistuu jostain toisestä syystä? Miten palvelin "muistaa"
                    profiilikuvan ettei sitä tarvitse lähettää uudelleen?</small>
                    </div>
                </li>
                <li>Bugien metsästys on ikuinen prosessi.
                </li>
            </ul>
        </li>

        <li class="mt-3">
            <p class="m-0 fw-bold text-info">Lomakkeiden automaattinen validointi (<a href="../../form_validation/file_upload_form.php">esimerkkejä</a>) (<a href="https://github.com/avahak/web-ohjelmointi-harjoitukset/blob/main/project1/src/form_validation/signup_form.json">signup_form.json</a>)</p>
            <p class="m-0">Paljon työtä mutta voi säästää aikaa pidemmän päälle.</p>
            <ul>
                <li>Esimerkki: uutta salasanaa syötettäessä salasanojen on oltava samat.</li>
                <li>Esimerkki: jos salasanan tulee olla riittävän vahva, tai sisältää sekä normaaleja että erikoismerkkejä.
                    Miten validoinnista kommunikoidaan käyttäjälle?
                <li>Tiedostojen liittäminen on monimutkainen prosessi jos sen haluaa
                    toteuttaa käyttäjäystävällisellä tavalla:
                    <ul>
                        <li>Palvelimen tulee tallettaa tiedostot väliaikaisesti ettei käyttäjän tarvitse lähettää niitä useaan kertaan.
                            Mitä tapahtuu väliaikaisille tiedostoille jos käyttäjä keskeyttää prosessin?
                        </li>
                        <li>Tiedostojen sisällön tarkistaminen - ei vain tiedostopäätteeseen luottaen.</li>
                        <li>Miten useampien tiedostojen liittäminen tulisi tapahtua?</li>
                    </ul>
            </ul>
        </li>

        <li class="mt-3">
            <p class="m-0 fw-bold text-info">Sähköpostien lähettäminen</p>
            <ul>
                <li>Mailtrap.io on erinomainen testaukseen mutta rajoitus max 100 testiä kuukaudessa.</li>
                <li>Gmail osoittesta lähettäminen onnistuu PHPMailer avulla mutta
                    käyttöön ottaminen vaatii lisävalmisteluja - Google project, 
                    OAuth Client ID, refresh token. Ohjeet:
                    <ul>
                        <li>Hyvät ohjeet PHPMailer GitHubissa: <a href="https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2">https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2</a>,
                        <a href="https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail_xoauth.phps">https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail_xoauth.phps</a></li>
                        <li>Minun <a href="https://github.com/avahak/web-ohjelmointi-harjoitukset/blob/main/project1/src/mail/gmail_send.php">gmail_send.php</a>, ohjeita sen kommenteissa. 
                        Itse PHP koodi on varsin yksinkertainen kun kaikki valmistelu on tehty.</li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="mt-3">
            <p class="m-0 fw-bold text-info">Debuggaus</p>
            <ul>
                <li>Aseta <code class="text-warning">ini_set('display_errors', 0);</code> kun sivut on julkisia.
                Ilman tätä virheilmoitukset voivat paljastaa arkaluonteista dataa.
                </li>
                <li>Tässä projektissa toteutettu virheilmoitusten ja logien näyttäminen 
                    omassa html-elementissä, jonka voi avata ja sulkea. Tämä ei ehkä ole
                    paras ratkaisu - tarvitsee lisää testausta ja parantelua.
            </ul>
        </li>

        <li class="mt-3">
            <p class="m-0 fw-bold text-info">Tietokantojen käsittely</p>
            Lisäämällä <a href="https://github.com/avahak/web-ohjelmointi-harjoitukset/blob/main/project1/src/tba_db.sql">SQL-skriptin</a>
            voi välttää phpMyAdmin käytön melkein kokonaan. Tämän voi tehdä luomalla tietokanta-riippumaton 
            yhteys SQL palvelimelle: <code>new mysqli($server, $username, $pw, null, $port)</code> ja 
            suorittaa kaikki skriptin kyselyt <code>mysqli::multi_query</code> avulla.
        </li>

        <li class="mt-3">
            <p class="m-0 fw-bold text-info">three.js</p>
            <p style="margin:0;">Three.js vaikuttaa erinomaiselta kirjastolta 3d-grafiikan 
            lisäämiseksi sivuille, mutta en ole 
            vielä ehtinyt perehtyä siihen paljon.</p>
            <ul>
                <li>Mahdollistaa uskomattomia efektejä: <a href="https://camera-webgi.vercel.app/">esimerkki</a>.</li>
            </ul>
        </li>
    </ol>

</div>

<?php shared_script_end(); ?>
