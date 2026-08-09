
<?php
// 1. Uvozimo povezavo z bazo podatkov
require_once 'db.php';
// Če se stran naloži brez napake, je PDO povezava uspešna!

try {
    // 2. SQL poizvedba: Preberemo oglase in s LEFT JOIN pridružimo naziv kategorije
    // Uporabimo LEFT JOIN, da se oglas prikaže tudi, če kategorija slučajno ne obstaja!
    $sql = "SELECT oglasi.*, COALESCE(kategorije.naziv, 'Brez kategorije') AS kategorija_naziv 
            FROM oglasi 
            LEFT JOIN kategorije ON oglasi.kategorija_id = kategorije.id 
            ORDER BY datum_vnosa DESC";
    
    $stmt = $pdo->query($sql);
    // Pridobimo vse vrstice iz baze kot asociativno polje
    $oglasi = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Napaka pri branju podatkov iz baze: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Šolski Oglasnik - Dinamični izpis</title>
    <!-- Pico.css mikro CSS ogrodje za avtomatsko odzivnost -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body>
    <main class="container">
        <!-- Navigacijska vrstica -->
        <nav>
            <ul>
                <li><strong>📣 Šolski Oglasnik</strong></li>
            </ul>
            <ul>
                <li><a href="index.php">Oglasi</a></li>
                <li><a href="dodaj_oglas.php" role="button">Dodaj oglas</a></li>
            </ul>
        </nav>

        <section>
            <h2>Aktualni šolski oglasi</h2>
            <p>Pregled oglasov, ki so dinamično prebrani iz baze podatkov MySQL.</p>
        </section>

        <!-- DINAMIČNI IZPIS OGLASOV IZ BAZE -->
        <div class="grid">
            <?php if (count($oglasi) > 0): ?>
                <?php foreach ($oglasi as $oglas): ?>
                    <article>
                        <header>
                            <strong><?= htmlspecialchars($oglas['naslov']) ?></strong>
                            <br><small>Kategorija: <?= htmlspecialchars($oglas['kategorija_naziv']) ?></small>
                        </header>
                        <p><?= htmlspecialchars($oglas['opis']) ?></p>
                        <footer>
                            <strong>Cena: <?= number_format($oglas['cena'], 2, ',', '.') ?> €</strong>
                            <br><small>Objavljeno: <?= $oglas['datum_vnosa'] ?></small>
                        </footer>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>V bazi podatkov trenutno ni objavljenih oglasov.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>