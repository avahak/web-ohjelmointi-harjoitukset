-- Tämän voisi hajoittaa loogisesti kahdeksi: autokanta-schema.sql, autokanta-data.sql

-- Henkilo ensin tai saadaan herjaus 
-- Error in foreign key constraint of table `autokanta`.`ajoneuvo`:
-- Create  table `autokanta`.`ajoneuvo` with foreign key constraint failed. Referenced table `autokanta`.`henkilo` not found in the data dictionary
DROP TABLE IF EXISTS Henkilo;
CREATE TABLE Henkilo (
    hetu CHAR(11) PRIMARY KEY, 
    nimi VARCHAR(50) NOT NULL, 
    osoite VARCHAR(100),
    puhelinnumero VARCHAR(20),
    INDEX (hetu)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS Ajoneuvo;
CREATE TABLE Ajoneuvo (
    rekisterinro CHAR(7) PRIMARY KEY, 
    vari VARCHAR(30),
    vuosimalli INT,
    omistaja CHAR(11),
    FOREIGN KEY (omistaja) REFERENCES Henkilo(hetu) ON DELETE RESTRICT, 
    INDEX (rekisterinro)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS Sakko;
CREATE TABLE Sakko (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ajoneuvo CHAR(7),
    FOREIGN KEY (ajoneuvo) REFERENCES Ajoneuvo(rekisterinro) ON DELETE RESTRICT,
    henkilo CHAR(11),
    FOREIGN KEY (henkilo) REFERENCES Henkilo(hetu) ON DELETE RESTRICT,
    pvm DATE, 
    summa DECIMAL(12,2),
    syy VARCHAR(200), 
    INDEX (id)
) ENGINE=InnoDB;

INSERT INTO Henkilo (hetu, nimi, osoite, puhelinnumero) VALUES ("281182-070W", "Anne Autoilija", "Kanervapolku 2", "050-1640837");
INSERT INTO Henkilo VALUES ("080173-169T", "Matti Miettinen", "Koivukuja 25", "040-1842950");
INSERT INTO Henkilo VALUES ("120760-093B", "Tapio Tamminen", "Tammistontie 18", "0400-576397");
INSERT INTO Henkilo VALUES ("200292-195H", "Teemu Tamminen", "Tammistontie 18", "040-9740768");

INSERT INTO Ajoneuvo VALUES ("CES-528", "sininen", 2010, "281182-070W");
INSERT INTO Ajoneuvo VALUES ("HUT-444", "kulta", 2006, "120760-093B");
INSERT INTO Ajoneuvo VALUES ("ROA-630", "harmaa", 2011, "080173-169T");