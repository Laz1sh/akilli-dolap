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

$uid    = current_user_id();
$action = $_REQUEST['action'] ?? 'list';

try {
    // Tarifi gecmisten sil (sadece kendi tarifini silebilir)
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Gecersiz id.']);
            exit;
        }
        $stmt = $pdo->prepare('DELETE FROM recipes WHERE id = :id AND user_id = :uid');
        $stmt->execute([':id' => (int) $id, ':uid' => $uid]);
        echo json_encode(['success' => true]);
        exit;
    }

    // Varsayilan: listele
    $stmt = $pdo->prepare(
        'SELECT id, title, data, created_at FROM recipes WHERE user_id = :uid ORDER BY id DESC'
    );
    $stmt->execute([':uid' => $uid]);
    echo json_encode(['success' => true, 'recipes' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
