<?php
// poglej_oglas.php pregled oglasa


// 1. Uvoz datoteke za povezavo z bazo podatkov
require_once 'db.php';

$sporočilo = '';
$oglas_id = $_GET['id'] ?? null;

if (!$oglas_id) {
    header("Location: index.php");
    exit;
}

// 2. OBDRDELAVA ODDAJE POVPRAŠEVANJA (INSERT v povezano tabelo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vsebina = trim($_POST['vsebina'] ?? '');
    $uporabnik_id = 1; // Začasno fiksen testni uporabnik (kupca)

    if (!empty($vsebina)) {
        try {
            $sql = "INSERT INTO povpraševanja (zapis_id, uporabnik_id, vsebina) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$oglas_id, $uporabnik_id, $vsebina]);
            
            $sporočilo = "Vaše povpraševanje je bilo uspešno poslano prodajalcu!";
        } catch (PDOException $e) {
            $sporočilo = "Napaka pri pošiljanju: " . $e->getMessage();
        }
    } else {
        $sporočilo = "Vnesite vsebino povpraševanja!";
    }
}

// 3. ZAJEVM PODROBNOSTI OGLASA (GLAVNA ENTITETA + JOIN KATEGORIJE)
try {
    $sql_oglas = "SELECT oglasi.*, COALESCE(kategorije.naziv, 'Brez kategorije') AS kategorija_naziv, uporabniki.ime AS prodajalec_ime, uporabniki.email AS prodajalec_email
                  FROM oglasi 
                  LEFT JOIN kategorije ON oglasi.kategorija_id = kategorije.id 
                  LEFT JOIN uporabniki ON oglasi.uporabnik_id = uporabniki.id
                  WHERE oglasi.id = ?";
    $stmt = $pdo->prepare($sql_oglas);
    $stmt->execute([$oglas_id]);
    $oglas = $stmt->fetch();

    if (!$oglas) {
        die("Oglas s tem ID-jem ne obstaja!");
    }

    // 4. ZAJEVM POVPRAŠEVANJ ZA TA OGLAS (POVEZANA TABELA + JOIN UPORABNIKI)
    $sql_povp = "SELECT povpraševanja.*, uporabniki.ime AS posiljatelj 
                 FROM povpraševanja 
                 JOIN uporabniki ON povpraševanja.uporabnik_id = uporabniki.id 
                 WHERE zapis_id = ? 
                 ORDER BY datum DESC";
    $stmt_p = $pdo->prepare($sql_povp);
    $stmt_p->execute([$oglas_id]);
    $povpraševanja = $stmt_p->fetchAll();

} catch (PDOException $e) {
    die("Napaka pri pridobivanju podatkov: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($oglas['naslov']) ?> - Šolski Oglasnik</title>
    <!-- Pico.css mikro CSS ogrodje -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body>
    <main class="container">
        <!-- Navigacijska vrstica -->
        <nav>
            <ul><li><strong>📣 Šolski Oglasnik</strong></li></ul>
            <ul><li><a href="index.php">Nazaj na vse oglase</a></li></ul>
        </nav>

        <!-- PODROBNOSTI OGLASA -->
        <article>
            <header>
                <hgroup>
                    <h2><?= htmlspecialchars($oglas['naslov']) ?></h2>
                    <h3>Kategorija: <?= htmlspecialchars($oglas['kategorija_naziv']) ?> | Prodajalec: <?= htmlspecialchars($oglas['prodajalec_ime']) ?></h3>
                </hgroup>
            </header>
            
            <p><strong>Opis predmeta:</strong></p>
            <p><?= nl2br(htmlspecialchars($oglas['opis'])) ?></p>
            
            <footer>
                <div class="grid">
                    <div><strong>Cena: <?= number_format($oglas['cena'], 2, ',', '.') ?> €</strong></div>
                    <div><small>Objavljeno: <?= date('d. m. Y ob H:i', strtotime($oglas['datum_vnosa'])) ?></small></div>
                </div>
            </footer>
        </article>

        <!-- OBRAZEC ZA POŠILJANJE POVPRAŠEVANJA -->
        <section>
            <h3>Pošlji povpraševanje prodajalcu</h3>
            
            <?php if ($sporočilo): ?>
                <blockquote style="background-color: #d4edda; color: #155724;">
                    <?= htmlspecialchars($sporočilo) ?>
                </blockquote>
            <?php endif; ?>

            <form action="poglej_oglas.php?id=<?= $oglas_id ?>" method="POST">
                <label for="vsebina">Vaše sporočilo ali vprašanje glede nakupa:
                    <textarea id="vsebina" name="vsebina" required placeholder="Npr.: Zdravo, ali je knjiga še na voljo? Kje se lahko dobiva za prevzem?"></textarea>
                </label>
                <button type="submit">Pošlji sporočilo</button>
            </form>
        </section>

        <!-- SEZNAM ŽE ODDANIH POVPRAŠEVANJ (RELACIJSKI IZPIS) -->
        <section>
            <h3>Zgodovina povpraševanj za ta oglas (<?= count($povpraševanja) ?>)</h3>
            <?php if (count($povpraševanja) > 0): ?>
                <?php foreach ($povpraševanja as $p): ?>
                    <article style="padding: 10px; margin-bottom: 10px; background-color: #f9f9f9;">
                        <strong><?= htmlspecialchars($p['posiljatelj']) ?></strong> 
                        <small>(<?= date('d. m. Y ob H:i', strtotime($p['datum'])) ?>):</small>
                        <p style="margin-top: 5px; margin-bottom: 0;"><?= htmlspecialchars($p['vsebina']) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p><small>Za ta oglas še ni bilo oddanih povpraševanj.</small></p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>