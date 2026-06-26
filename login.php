<?php
/**
 * login.php — Kullanici girisi
 * Sifre password_verify() ile kontrol edilir.
 */
require __DIR__ . '/auth.php';

if (current_user_id()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :u');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id']  = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Kullanici adi veya sifre hatali.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giris Yap — Akilli Dolap</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow p-8 w-full max-w-sm">
        <h1 class="text-xl font-bold text-emerald-600 mb-1">Akilli Dolap</h1>
        <h2 class="text-lg font-semibold mb-4">Giris Yap</h2>

        <?php if ($error): ?>
            <p class="bg-red-100 text-red-700 text-sm rounded px-3 py-2 mb-3"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" class="space-y-3">
            <input type="text" name="username" placeholder="Kullanici adi" required
                   class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
            <input type="password" name="password" placeholder="Sifre" required
                   class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg py-2 font-medium transition">
                Giris Yap
            </button>
        </form>

        <p class="text-sm text-slate-500 mt-4 text-center">
            Hesabin yok mu?
            <a href="register.php" class="text-emerald-600 hover:underline">Kayit ol</a>
        </p>
    </div>
</body>
</html>
