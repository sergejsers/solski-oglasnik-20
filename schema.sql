-- 1. Ustvarjanje tabele uporabniki
CREATE TABLE IF NOT EXISTS uporabniki (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    geslo VARCHAR(255) NOT NULL,
    vloga VARCHAR(20) DEFAULT 'uporabnik',
    datum_registracije DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Ustvarjanje tabele kategorije
CREATE TABLE IF NOT EXISTS kategorije (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(100) NOT NULL,
    opis TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Ustvarjanje glavne tabele oglasi
CREATE TABLE IF NOT EXISTS oglasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uporabnik_id INT NOT NULL,
    kategorija_id INT NOT NULL,
    naslov VARCHAR(150) NOT NULL,
    opis TEXT NOT NULL,
    cena DECIMAL(10, 2) NOT NULL,
    slika_url VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'aktiven',
    datum_vnosa DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_oglasi_uporabnik FOREIGN KEY (uporabnik_id) REFERENCES uporabniki(id) ON DELETE CASCADE,
    CONSTRAINT fk_oglasi_kategorija FOREIGN KEY (kategorija_id) REFERENCES kategorije(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Ustvarjanje povezane tabele povpraševanja
CREATE TABLE IF NOT EXISTS povpraševanja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zapis_id INT NOT NULL,
    uporabnik_id INT NOT NULL,
    vsebina TEXT NOT NULL,
    datum DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_povpraševanja_oglas FOREIGN KEY (zapis_id) REFERENCES oglasi(id) ON DELETE CASCADE,
    CONSTRAINT fk_povpraševanja_uporabnik FOREIGN KEY (uporabnik_id) REFERENCES uporabniki(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Vnos začetnih testnih podatkov
INSERT INTO uporabniki (ime, email, geslo, vloga) VALUES 
('Testni Učitelj', 'ucitelj@solskicenter.si', '$2y$10$eImiTXuWVxfM37uY4JANjOL.81qzG76y5/WkQ0/F8fN/H6mC', 'admin');

INSERT INTO kategorije (naziv, opis) VALUES 
('Učbeniki in literatura', 'Šolski učbeniki, delovni zvezki in leposlovje'),
('Šolska oprema in pribor', 'Kalkulatorji, risalni pribor, peresnice');

INSERT INTO oglasi (uporabnik_id, kategorija_id, naslov, opis, cena) VALUES 
(1, 1, 'Matematika 3 - Delovni zvezek', 'Lepo ohranjen delovni zvezek za 3. letnik PTI.', 12.00),
(1, 2, 'Rišalni pribor za TZN', 'Komplet trikotnikov in šestilo v škatli.', 8.50);