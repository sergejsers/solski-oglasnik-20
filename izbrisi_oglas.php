<?php
// izbrisi_oglas.php - brisanje oglasov.

// 1. Uvoz datoteke za povezavo z bazo podatkov
require_once 'db.php';

// Zajememo ID oglasa iz URL parametra (?id=X)
$oglas_id = $_GET['id'] ?? null;

// Če ID ni podan, uporabnika takoj vrnemo na osnovno stran
if (!$oglas_id) {
    header("Location: index.php");
    exit;
}

try {
    // 2. SQL poizvedba DELETE z uporabo Prepared Statement (zaščita pred SQLi)
    $sql = "DELETE FROM oglasi WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$oglas_id]);

    // 3. Po uspešnem izbrisu uporabnika preusmerimo nazaj na index.php
    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    die("Napaka pri brisanju oglasa iz baze: " . $e->getMessage());
}
?>