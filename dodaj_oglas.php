<?php
// Uvoz datoteke za povezavo z bazo
require_once 'db.php';

$sporočilo = '';

// Preverimo, ali je bil obrazec poslan s POST metodo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naslov = trim($_POST['naslov'] ?? '');
    $opis = trim($_POST['opis'] ?? '');
    $cena = trim($_POST['cena'] ?? '');
    $kategorija_id = $_POST['kategorija_id'] ?? 1;
    $uporabnik_id = 1; // Začasno fiksen testni uporabnik

    // Preprosta validacija vnosnih podatkov
    if (!empty($naslov) && !empty($opis) && is_numeric($cena)) {
        try {
            // Varen vnos podatkov z uporabo Prepared Statements za preprečitev SQLi
            $sql = "INSERT INTO oglasi (uporabnik_id, kategorija_id, naslov, opis, cena) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$uporabnik_id, $kategorija_id, $naslov, $opis, $cena]);

            // Po uspešnem vnosu preusmerimo na domačo stran z oglasi
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $sporočilo = "Napaka pri shranjevanju: " . $e->getMessage();
        }
    } else {
        $sporočilo = "Prosimo, pravilno izpolnite vsa polja! (Cena mora biti število)";
    }
}

// Zajememo vse kategorije iz baze za prikaz v padajočem meniju
try {
    $kategorije = $pdo->query("SELECT * FROM kategorije")->fetchAll();
} catch (PDOException $e) {
    $kategorije = [];
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj nov oglas - Šolski Oglasnik</title>
    <!-- Pico.css mikro ogrodje -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body>
    <main class="container">
        <nav>
            <ul>
                <li><strong>📣 Šolski Oglasnik</strong></li>
            </ul>
            <ul>
                <li><a href="index.php">Nazaj na oglase</a></li>
            </ul>
        </nav>

        <h2>Objavi nov oglas</h2>

        <?php if ($sporočilo): ?>
            <article style="background-color: #f8d7da; color: #721c24; border: none;">
                <?= htmlspecialchars($sporočilo) ?>
            </article>
        <?php endif; ?>

        <form action="dodaj_oglas.php" method="POST">
            <label for="naslov">Naslov oglasa:
                <input type="text" id="naslov" name="naslov" required placeholder="npr. Učbenik za Matematiko 3">
            </label>

            <label for="kategorija_id">Kategorija:
                <select id="kategorija_id" name="kategorija_id" required>
                    <?php foreach ($kategorije as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['naziv']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="opis">Opis predmeta:
                <textarea id="opis" name="opis" required placeholder="Opišite ohranjenost predmeta..."></textarea>
            </label>

            <label for="cena">Cena (€):
                <input type="number" step="0.01" id="cena" name="cena" required placeholder="10.50">
            </label>

            <button type="submit">Objavi oglas</button>
        </form>
    </main>
</body>
</html>