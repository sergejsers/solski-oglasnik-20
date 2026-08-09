<?php
// db.php - Konfiguracija in povezava z bazo podatkov preko PDO

$host = 'localhost';
// Vnesite točno ime baze iz vašega cPanela (npr. uporabnik_oglasnik_db)
$db   = 'mojepodjetje7_solski_oglasnik20'; 
// Vnesite uporabnika baze, ki ste mu dodelili pravice v cPanelu
$user = 'mojepodjetje7_oglasnik_user'; 
// Vnesite geslo uporabnika baze
$pass = 'rps2026!'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    // Izmet izjem ob SQL napakah (ključno za lažje odpravljanje napak / debugging)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Vračanje rezultatov v obliki asociativnega polja
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Izklop emulacije pripravljenih stavkov za boljšo varnost
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // V produkciji za dijake prikažemo razumljivo sporočilo ob napaki
     die("Povezava z bazo podatkov ni uspela: " . $e->getMessage());
}
?>
