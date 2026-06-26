<?php
/**
 * register.php — Yeni kullanici kaydi
 * Sifre password_hash() ile guvenli sekilde saklanir.
 */
require __DIR__ . '/auth.php';

// Zaten girisliyse ana sayfaya gonder
if (current_user_id()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Kullanici adi ve sifre bos olamaz.';
    } elseif (strlen($password) < 4) {
        $error = 'Sifre en az 4 karakter olmali.';
    } else {
        // Kullanici adi zaten var mi?
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u');
        $stmt->execute([':u' => $username]);
        if ($stmt->fetch()) {
            $error = 'Bu kullanici adi zaten alinmis.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:u, :p)');
            $ins->execute([':u' => $username, ':p' => $hash]);

            // Kayittan sonra otomatik giris
            $_SESSION['user_id']  = (int) $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayit Ol — Akilli Dolap</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow p-8 w-full max-w-sm">
        <h1 class="text-xl font-bold text-emerald-600 mb-1">Akilli Dolap</h1>
        <h2 class="text-lg font-semibold mb-4">Kayit Ol</h2>

        <?php if ($error): ?>
            <p class="bg-red-100 text-red-700 text-sm rounded px-3 py-2 mb-3"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" class="space-y-3">
            <input type="text" name="username" placeholder="Kullanici adi" required
                   class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
            <input type="password" name="password" placeholder="Sifre (en az 4 karakter)" required
                   class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg py-2 font-medium transition">
                Kayit Ol
            </button>
        </form>

        <p class="text-sm text-slate-500 mt-4 text-center">
            Zaten hesabin var mi?
            <a href="login.php" class="text-emerald-600 hover:underline">Giris yap</a>
        </p>
    </div>
</body>
</html>
