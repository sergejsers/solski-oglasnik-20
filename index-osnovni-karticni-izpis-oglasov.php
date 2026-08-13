<?php
// 1. Uvoz datoteke za povezavo z bazo
require_once 'db.php';

try {
    // 2. SQL poizvedba: Zajememo oglase in pridružimo naziv kategorije (LEFT JOIN)
    $sql = "SELECT oglasi.*, COALESCE(kategorije.naziv, 'Brez kategorije') AS kategorija_naziv 
            FROM oglasi 
            LEFT JOIN kategorije ON oglasi.kategorija_id = kategorije.id 
            ORDER BY datum_vnosa DESC";
    
    $stmt = $pdo->query($sql);
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
    <title>Šolski Oglasnik - Pregled oglasov</title>
    <!-- Pico.css mikro CSS ogrodje -->
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
            <p>Pregled vseh objavljenih oglasov v pregledni vrstični obliki.</p>
        </section>

        <!-- PREGLEDEN VRSTIČNI IZPIS (TABELA) -->
        <?php if (count($oglasi) > 0): ?>
            <figure>
                <table role="grid">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 25%;">Naslov oglasa</th>
                            <th scope="col" style="width: 20%;">Kategorija</th>
                            <th scope="col" style="width: 35%;">Opis</th>
                            <th scope="col" style="width: 10%;">Cena</th>
                            <th scope="col" style="width: 10%;">Datum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($oglasi as $oglas): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($oglas['naslov']) ?></strong></td>
                                <td><small><?= htmlspecialchars($oglas['kategorija_naziv']) ?></small></td>
                                <td><?= htmlspecialchars($oglas['opis']) ?></td>
                                <td><strong><?= number_format($oglas['cena'], 2, ',', '.') ?> €</strong></td>
                                <td><small><?= date('d. m. Y', strtotime($oglas['datum_vnosa'])) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </figure>
        <?php else: ?>
            <article>
                <p>V bazi podatkov trenutno ni objavljenih oglasov.</p>
            </article>
        <?php endif; ?>
    </main>
</body>
</html>