-- Tässä harjoituksessa käytetään edelleen tietokantaa "sakila".
-- Tutustu tarkasti tietokantakaavioon (ja tarvittaessa itse tietokantaan phpMyAdminin kautta) 
-- ja kirjoita SQL-lauseet, jotka vastaavat seuraaviin kysymyksiin.

-- Missä filmeissä on sekä trailereita, poistettuja kohtauksia että "behind the scenes" -lisämateriaalia? 
-- Näytä vain filmien nimet ja lisämateriaalit (special_features). 
-- Huomaa, että lisämateriaalia voi olla muutakin kuin nämä "pakolliset" kolme. 
-- (Vihje: LIKE-operaatio. Lisämateriaalit ovat aina tietyssä järjestyksessä.)
SELECT title, special_features FROM film WHERE special_features LIKE "%deleted%" AND special_features LIKE "%behind%" AND special_features LIKE "%trailer%"

-- NC-17 kauhuleffojen hintaa korotetaan 50 senttiä. Mitä filmejä korotus koskee, mitkä olivat niiden 
-- hinnat ja mitkä ovat uudet hinnat? Vastaustietueita pitäisi olla 7.
SELECT title, rental_rate, (rental_rate+0.5) AS new_rental_rate 
    FROM film f JOIN film_category fc ON f.film_id=fc.film_id JOIN category c on fc.category_id=c.category_id 
    WHERE f.rating="NC-17" AND c.name="Horror"

-- Kuinka monta kopiota on filmistä "BUTTERFLY CHOCOLAT"? Vastaus: 8.
SELECT COUNT(*) FROM film f JOIN inventory i ON f.film_id=i.film_id WHERE f.title="BUTTERFLY CHOCOLAT"

-- Kuinka monta filmiä on vuokrattuna eli niitä ei ole vielä palautettu? 
-- (Vihje: "is null" -vertailuoperaatio where-osassa.) Vastaus: 183.
SELECT COUNT(*) FROM rental WHERE return_date IS NULL

-- Mitkä ovat keskimääräiset (avg) filmien pituudet ikärajoittain (G, PG, PG-13, R ja NC-17)? 
-- Näytä ikäraja sarakkeessa "Ikäraja" ja pituus sarakkeessa "Pituus".
-- Vastaus: G: 111.0565, PG: 112.0052, PG-13: 120.4439, R: 118.0363, NC-17: 113.2286.
SELECT AVG(length) AS Pituus, rating AS Ikäraja FROM film GROUP BY rating 

-- Vapaaehtoisia lisätehtäviä. Näissä tehtävissä tarvitaan alikyselyjä.

-- Kuka oli tietokannan viimeisin filmin palauttaja ja milloin? 
-- (Eli asiakas vuokraustapahtumassa, jonka palautuspäivämäärä on suurin.)
-- Vastaus: Leo Ebert, 2005-09-02 02:35:22
SELECT CONCAT(c.first_name, " ", c.last_name) AS "name", MAX(return_date) as last_return
    FROM rental r JOIN customer c ON r.customer_id=c.customer_id 
    GROUP BY c.first_name, c.last_name ORDER BY last_return DESC LIMIT 1

-- Ketkä ovat näytelleet samoissa elokuvissa kuin Emily Dee?
SELECT CONCAT(a.first_name, " ", a.last_name) as full_name, edf.edf_title as "Film title" FROM 
    (SELECT f.title as edf_title, f.film_id as edf_film_id FROM film f JOIN film_actor fa ON f.film_id=fa.film_id JOIN actor a ON fa.actor_id=a.actor_id
        WHERE a.first_name="Emily" AND a.last_name="Dee") as edf
    JOIN film_actor fa ON edf.edf_film_id=fa.film_id JOIN actor a ON fa.actor_id=a.actor_id
    WHERE NOT CONCAT(a.first_name, " ", a.last_name)="Emily Dee"
-- tai käyttäen CTE (Common Table Expression):
WITH edf AS (
    SELECT f.title as edf_title, f.film_id as edf_film_id FROM film f JOIN film_actor fa ON f.film_id=fa.film_id JOIN actor a ON fa.actor_id=a.actor_id
        WHERE a.first_name="Emily" AND a.last_name="Dee"
)
SELECT CONCAT(a.first_name, " ", a.last_name) as full_name, edf.edf_title as "Film title" FROM edf
    JOIN film_actor fa ON edf.edf_film_id=fa.film_id JOIN actor a ON fa.actor_id=a.actor_id
    WHERE NOT CONCAT(a.first_name, " ", a.last_name)="Emily Dee"