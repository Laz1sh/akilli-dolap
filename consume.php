<?php
/**
 * consume.php — "Bu Tarifi Yaptim!" -> malzemeleri dolaptan dus (kullaniciya ozel)
 * --------------------------------------------------
 * JS'ten JSON govdesiyle gelir:
 *   { "ingredients": [ {"name":"Yumurta","quantity":3}, ... ] }
 * Sadece giris yapan kullanicinin malzemeleri guncellenir.
 */

require __DIR__ . '/auth.php';
require_login_api();
header('Content-Type: application/json; charset=utf-8');

$uid   = current_user_id();
$input = json_decode(file_get_contents('php://input'), true);
$ingredients = $input['ingredients'] ?? [];

if (!is_array($ingredients) || count($ingredients) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dusulecek malzeme listesi bos.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $update = $pdo->prepare(
        'UPDATE inventory SET quantity = quantity - :used WHERE name = :name AND user_id = :uid'
    );
    $cleanup = $pdo->prepare('DELETE FROM inventory WHERE quantity <= 0 AND user_id = :uid');

    foreach ($ingredients as $ing) {
        $name = trim($ing['name'] ?? '');
        $used = $ing['quantity'] ?? 0;
        if ($name === '' || !is_numeric($used)) {
            continue;
        }
        $update->execute([':used' => (float) $used, ':name' => $name, ':uid' => $uid]);
    }

    $cleanup->execute([':uid' => $uid]);
    $pdo->commit();

    // Guncel listeyi geri dondur ki arayuz yenilensin
    $stmt = $pdo->prepare('SELECT id, name, quantity, unit FROM inventory WHERE user_id = :uid ORDER BY name');
    $stmt->execute([':uid' => $uid]);
    echo json_encode(['success' => true, 'items' => $stmt->fetchAll()]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
