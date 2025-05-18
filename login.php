<?php
session_start();
require_once 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = 'Username dan password harus diisi.';
    } else {
        $users = readData(USERS_FILE);
        $user = null;
        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $user = $u;
                break;
            }
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Username atau password salah.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Masuk - Fitpulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-600 to-purple-700 font-sans min-h-screen flex flex-col justify-center items-center">
    <div class="bg-white p-12 rounded-3xl shadow-2xl w-full max-w-md">
        <h1 class="text-4xl font-extrabold mb-10 text-center text-gray-900">Masuk ke Fitpulse</h1>
        <?php if ($errors): ?>
            <div class="mb-8 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg" role="alert">
                <ul class="list-disc list-inside text-sm">
                    <?php foreach ($errors as $error): ?>
                        <li><?=htmlspecialchars($error)?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="login.php" class="space-y-8">
            <div>
                <label for="username" class="block mb-3 font-semibold text-gray-800 text-lg">Username</label>
                <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-purple-500" value="<?=htmlspecialchars($_POST['username'] ?? '')?>" />
            </div>
            <div>
                <label for="password" class="block mb-3 font-semibold text-gray-800 text-lg">Password</label>
                <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-purple-500" />
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white py-4 rounded-2xl hover:bg-purple-700 transition text-lg font-semibold">Masuk</button>
        </form>
        <p class="mt-8 text-center text-gray-700 text-sm">
            Belum punya akun? <a href="register.php" class="text-purple-600 hover:underline font-semibold">Daftar di sini</a>
        </p>
    </div>
</body>
</html>
