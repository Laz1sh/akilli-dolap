<?php
/**
 * db.php — Veritabani baglantisi (PDO) + oturum (session) baslatma
 * --------------------------------------------------
 * Asagidaki 4 degeri kendi ortamina gore duzenle.
 * XAMPP (yerel): host=localhost, kullanici=root, sifre="" (bos)
 * Ucretsiz hostingde (InfinityFree vb.) panelin verdigi degerleri yaz.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = 'localhost';
$DB_NAME = 'akilli_dolap';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Veritabanina baglanilamadi: ' . $e->getMessage());
}
