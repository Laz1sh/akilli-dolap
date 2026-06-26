<?php
/**
 * manage_inventory.php — Dolap (envanter) API'si — kullaniciya ozel
 * --------------------------------------------------
 *   GET  ?action=list   -> giris yapan kullanicinin malzemeleri
 *   POST  action=add    -> malzeme ekle  (name, quantity, unit)
 *   POST  action=delete -> malzeme sil   (id)  [sadece kendi kaydini silebilir]
 *
 * Tum sorgular user_id ile filtreli + PDO prepared statement.
 */

require __DIR__ . '/auth.php';
require_login_api();
header('Content-Type: application/json; charset=utf-8');

$uid    = current_user_id();
$action = $_REQUEST['action'] ?? 'list';

try {
    switch ($action) {

        case 'list':
            $stmt = $pdo->prepare(
                'SELECT id, name, quantity, unit FROM inventory WHERE user_id = :uid ORDER BY name ASC'
            );
            $stmt->execute([':uid' => $uid]);
            echo json_encode(['success' => true, 'items' => $stmt->fetchAll()]);
            break;

        case 'add':
            $name     = trim($_POST['name'] ?? '');
            $quantity = $_POST['quantity'] ?? '';
            $unit     = trim($_POST['unit'] ?? 'adet');

            if ($name === '' || !is_numeric($quantity)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Gecersiz malzeme adi veya miktar.']);
                exit;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO inventory (user_id, name, quantity, unit) VALUES (:uid, :name, :quantity, :unit)'
            );
            $stmt->execute([
                ':uid'      => $uid,
                ':name'     => $name,
                ':quantity' => (float) $quantity,
                ':unit'     => $unit !== '' ? $unit : 'adet',
            ]);
            echo json_encode(['success' => true, 'id' => (int) $pdo->lastInsertId()]);
            break;

        case 'delete':
            $id = $_POST['id'] ?? '';
            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Gecersiz id.']);
                exit;
            }
            // user_id sarti: baskasinin kaydini silemez
            $stmt = $pdo->prepare('DELETE FROM inventory WHERE id = :id AND user_id = :uid');
            $stmt->execute([':id' => (int) $id, ':uid' => $uid]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Bilinmeyen eylem.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
