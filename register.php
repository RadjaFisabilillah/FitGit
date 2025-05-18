<?php
session_start();
require_once 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'Semua field harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Password dan konfirmasi password tidak cocok.';
    } else {
        $users = readData(USERS_FILE);

        // Check if username or email already exists
        foreach ($users as $user) {
            if ($user['username'] === $username || $user['email'] === $email) {
                $errors[] = 'Username atau email sudah digunakan.';
                break;
            }
        }

        if (empty($errors)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $new_user = [
                'id' => uniqid(),
                'username' => $username,
                'email' => $email,
                'password_hash' => $password_hash,
                'fitness_level' => 'beginner',
                'preferences' => '',
                'created_at' => date('c'),
                'updated_at' => date('c')
            ];
            $users[] = $new_user;
            writeData(USERS_FILE, $users);

            $_SESSION['user_id'] = $new_user['id'];
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Daftar - Fitpulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-600 to-purple-700 font-sans min-h-screen flex flex-col justify-center items-center">
    <div class="bg-white p-12 rounded-3xl shadow-2xl w-full max-w-md">
        <h1 class="text-4xl font-extrabold mb-10 text-center text-gray-900">Daftar Fitpulse</h1>
        <?php if ($errors): ?>
            <div class="mb-8 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg" role="alert">
                <ul class="list-disc list-inside text-sm">
                    <?php foreach ($errors as $error): ?>
                        <li><?=htmlspecialchars($error)?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="register.php" class="space-y-8">
            <div>
                <label for="username" class="block mb-3 font-semibold text-gray-800 text-lg">Username</label>
                <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-purple-500" value="<?=htmlspecialchars($_POST['username'] ?? '')?>" />
            </div>
            <div>
                <label for="email" class="block mb-3 font-semibold text-gray-800 text-lg">Email</label>
                <input type="email" id="email" name="email" required class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-purple-500" value="<?=htmlspecialchars($_POST['email'] ?? '')?>" />
            </div>
            <div>
                <label for="password" class="block mb-3 font-semibold text-gray-800 text-lg">Password</label>
                <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-purple-500" />
            </div>
            <div>
                <label for="confirm_password" class="block mb-3 font-semibold text-gray-800 text-lg">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:outline-none focus:ring-4 focus:ring-purple-500" />
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white py-4 rounded-2xl hover:bg-purple-700 transition text-lg font-semibold">Daftar</button>
        </form>
        <p class="mt-8 text-center text-gray-700 text-sm">
            Sudah punya akun? <a href="login.php" class="text-purple-600 hover:underline font-semibold">Masuk di sini</a>
        </p>
    </div>
</body>
</html>
