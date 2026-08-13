<?php
// uredi_oglas.php - posodabljanje oglasov

// 1. Uvoz datoteke za povezavo z bazo podatkov
require_once 'db.php';

$sporočilo = '';
$oglas_id = $_GET['id'] ?? null;

// Če ID oglasa ni podan v URL-ju, preusmerimo nazaj na osnovno stran
if (!$oglas_id) {
    header("Location: index.php");
    exit;
}

// 2. OBDBDELAVA OBRAZCA (POST METODA) - POSODOBITEV (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naslov = trim($_POST['naslov'] ?? '');
    $opis = trim($_POST['opis'] ?? '');
    $cena = trim($_POST['cena'] ?? '');
    $kategorija_id = $_POST['kategorija_id'] ?? 1;

    if (!empty($naslov) && !empty($opis) && is_numeric($cena)) {
        try {
            // SQL poizvedba UPDATE z uporabo Prepared Statement
            $sql = "UPDATE oglasi 
                    SET naslov = ?, opis = ?, cena = ?, kategorija_id = ? 
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$naslov, $opis, $cena, $kategorija_id, $oglas_id]);

            // Po uspešni posodobitvi preusmerimo na domov
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $sporočilo = "Napaka pri posodabljanju oglasa: " . $e->getMessage();
        }
    } else {
        $sporočilo = "Prosimo, pravilno izpolnite vsa polja!";
    }
}

// 3. ZAJEVM OBSTOJEČIH PODATKOV IZ BAZE ZA PREDIZPOLNITEV OBRAZCA (GET)
try {
    $stmt = $pdo->prepare("SELECT * FROM oglasi WHERE id = ?");
    $stmt->execute([$oglas_id]);
    $oglas = $stmt->fetch();

    // Če oglas z podanim ID-jem ne obstaja
    if (!$oglas) {
        die("Oglas s tem ID-jem ne obstaja!");
    }

    // Zajem vseh kategorij za padajoči meni
    $kategorije = $pdo->query("SELECT * FROM kategorije")->fetchAll();
} catch (PDOException $e) {
    die("Napaka pri pridobivanju podatkov: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uredi oglas - Šolski Oglasnik</title>
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
                <li><a href="index.php">Nazaj na oglase</a></li>
            </ul>
        </nav>

        <h2>Uredi oglas #<?= htmlspecialchars($oglas['id']) ?></h2>

        <?php if ($sporočilo): ?>
            <article style="background-color: #f8d7da; color: #721c24; border: none;">
                <?= htmlspecialchars($sporočilo) ?>
            </article>
        <?php endif; ?>

        <!-- OBRAZEC Z PREDIZPOLNJENIMI PODATKI (value="...") -->
        <form action="uredi_oglas.php?id=<?= $oglas_id ?>" method="POST">
            <label for="naslov">Naslov oglasa:
                <input type="text" id="naslov" name="naslov" value="<?= htmlspecialchars($oglas['naslov']) ?>" required>
            </label>

            <label for="kategorija_id">Kategorija:
                <select id="kategorija_id" name="kategorija_id" required>
                    <?php foreach ($kategorije as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= $kat['id'] == $oglas['kategorija_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['naziv']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label for="opis">Opis predmeta:
                <textarea id="opis" name="opis" required><?= htmlspecialchars($oglas['opis']) ?></textarea>
            </label>

            <label for="cena">Cena (€):
                <input type="number" step="0.01" id="cena" name="cena" value="<?= htmlspecialchars($oglas['cena']) ?>" required>
            </label>

            <button type="submit">Shrani spremembe</button>
        </form>
    </main>
</body>
</html>