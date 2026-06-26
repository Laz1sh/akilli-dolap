<?php
/**
 * logout.php — Cikis yap
 * Oturumu temizler ve giris sayfasina yonlendirir.
 */
require __DIR__ . '/db.php';   // session_start() burada cagrilir

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
