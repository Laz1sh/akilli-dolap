<?php
/**
 * recipes.php — Gecmis tarifler API'si (kullaniciya ozel)
 * --------------------------------------------------
 *   GET ?action=list -> giris yapan kullanicinin kaydedilmis tarifleri
 * Tarifler "Bu Tarifi Yaptim" deyince consume.php tarafindan kaydedilir.
 */

require __DIR__ . '/auth.php';
require_login_api();
header('Content-Type: application/json; charset=utf-8');

$uid = current_user_id();

try {
    $stmt = $pdo->prepare(
        'SELECT id, title, data, created_at FROM recipes WHERE user_id = :uid ORDER BY id DESC'
    );
    $stmt->execute([':uid' => $uid]);
    echo json_encode(['success' => true, 'recipes' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
