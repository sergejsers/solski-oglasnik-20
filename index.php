<?php
// index.php iz 1. koraka Sklopa 3.2.
// Dodana funkcionalnost iskanja in filtriranja na index.php.



// 1. Uvoz datoteke za povezavo z bazo podatkov
require_once 'db.php';

// Zajememo parametre za iskanje in kategorijo iz URL-ja (metoda GET)
$iskanje = trim($_GET['iskanje'] ?? '');
$kat_id = $_GET['kategorija_id'] ?? '';

try {
    // 2. Osnovna SQL poizvedba (povežemo oglase in kategorije z LEFT JOIN)
    $sql = "SELECT oglasi.*, COALESCE(kategorije.naziv, 'Brez kategorije') AS kategorija_naziv 
            FROM oglasi 
            LEFT JOIN kategorije ON oglasi.kategorija_id = kategorije.id 
            WHERE 1=1";
    
    $params = [];

    // Če je uporabnik vnesel iskalni niz, dodamo pogoja za naslov ali opis (LIKE)
    if (!empty($iskanje)) {
        $sql .= " AND (oglasi.naslov LIKE ? OR oglasi.opis LIKE ?)";
        $params[] = "%$iskanje%";
        $params[] = "%$iskanje%";
    }

    // Če je uporabnik izbral določeno kategorijo, dodamo pogoj za kategorijo
    if (!empty($kat_id)) {
        $sql .= " AND oglasi.kategorija_id = ?";
        $params[] = $kat_id;
    }

    // Razvrstimo oglase od najnovejšega do najstarejšega
    $sql .= " ORDER BY datum_vnosa DESC";
    
    // Izvedemo varno poizvedbo s pripravljenim stavkom (Prepared Statement)
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $oglasi = $stmt->fetchAll();

    // Zajememo vse kategorije iz baze za prikaz v padajočem meniju iskalnika
    $kategorije = $pdo->query("SELECT * FROM kategorije")->fetchAll();

} catch (PDOException $e) {
    die("Napaka pri branju podatkov iz baze: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Šolski Oglasnik - Pregled in iskanje oglasov</title>
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
            <p>Iščite med objavljenimi oglasi ali jih filtrirajte po kategoriji.</p>
        </section>

        <!-- ISKALNIK IN FILTRIRANJE (Obrazec z GET metodo) -->
        <form method="GET" action="index.php" class="grid">
            <input type="search" name="iskanje" placeholder="Išči po naslovu ali opisu..." value="<?= htmlspecialchars($iskanje) ?>">
            
            <select name="kategorija_id">
                <option value="">Vse kategorije</option>
                <?php foreach ($kategorije as $kat): ?>
                    <option value="<?= $kat['id'] ?>" <?= $kat_id == $kat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['naziv']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit">Išči</button>
        </form>

        <!-- TABELARIČNI IZPIS OGLASOV Z AKCIJAMI -->
        <?php if (count($oglasi) > 0): ?>
            <figure>
                <table role="grid">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 25%;">Naslov oglasa</th>
                            <th scope="col" style="width: 15%;">Kategorija</th>
                            <th scope="col" style="width: 10%;">Cena</th>
                            <th scope="col" style="width: 15%;">Datum</th>
                            <th scope="col" style="width: 35%;">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($oglasi as $oglas): ?>
                            <tr>
                                <td>
                                    <a href="poglej_oglas.php?id=<?= $oglas['id'] ?>">
                                        <strong><?= htmlspecialchars($oglas['naslov']) ?></strong>
                                    </a>
                                </td>
                                <td><small><?= htmlspecialchars($oglas['kategorija_naziv']) ?></small></td>
                                <td><strong><?= number_format($oglas['cena'], 2, ',', '.') ?> €</strong></td>
                                <td><small><?= date('d. m. Y', strtotime($oglas['datum_vnosa'])) ?></small></td>
                                <td>
                                    <a href="poglej_oglas.php?id=<?= $oglas['id'] ?>" role="button" class="secondary outline" style="padding: 2px 8px; font-size: 0.8rem;">👁️ Poglej</a>
                                    <a href="uredi_oglas.php?id=<?= $oglas['id'] ?>" role="button" class="secondary outline" style="padding: 2px 8px; font-size: 0.8rem;">✏️ Uredi</a>
                                    <a href="izbrisi_oglas.php?id=<?= $oglas['id'] ?>" role="button" class="contrast outline" style="padding: 2px 8px; font-size: 0.8rem; color: #d9534f; border-color: #d9534f;" onclick="return confirm('Res želiš izbrisati ta oglas?');">🗑️ Izbriši</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </figure>
        <?php else: ?>
            <article>
                <p>Ni najdenih oglasov po vaših iskalnih kriterijih. <a href="index.php">Prikaži vse oglase</a>.</p>
            </article>
        <?php endif; ?>
    </main>
</body>
</html>