<?php
/**
 * auth.php — Giris kontrol yardimcilari
 * --------------------------------------------------
 * require_login()      -> Sayfa icin: giris yoksa login.php'ye yonlendirir.
 * require_login_api()  -> API icin: giris yoksa 401 + JSON dondurur.
 * current_user_id()    -> Giris yapan kullanicinin id'sini verir.
 * current_username()   -> Giris yapan kullanicinin adini verir.
 */

require_once __DIR__ . '/db.php';

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_username() {
    return $_SESSION['username'] ?? '';
}

function require_login() {
    if (!current_user_id()) {
        header('Location: login.php');
        exit;
    }
}

function require_login_api() {
    if (!current_user_id()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Once giris yapmalisiniz.']);
        exit;
    }
}
