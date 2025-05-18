<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Fetch user data
$users = readData(USERS_FILE);
$user = null;
foreach ($users as $u) {
    if ($u['id'] === $user_id) {
        $user = $u;
        break;
    }
}

if (!$user) {
    die('User not found.');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fitness_level = $_POST['fitness_level'] ?? 'beginner';
    $preferences = trim($_POST['preferences'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$username || !$email) {
        $errors[] = 'Username dan email harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    } else {
        // Check if username or email is taken by another user
        foreach ($users as $u) {
            if (($u['username'] === $username || $u['email'] === $email) && $u['id'] !== $user_id) {
                $errors[] = 'Username atau email sudah digunakan oleh pengguna lain.';
                break;
            }
        }
    }

    // Handle password change if requested
    if ($new_password || $confirm_password) {
        if (!$current_password) {
            $errors[] = 'Masukkan password saat ini untuk mengganti password.';
        } else {
            if (!password_verify($current_password, $user['password_hash'])) {
                $errors[] = 'Password saat ini salah.';
            } elseif ($new_password !== $confirm_password) {
                $errors[] = 'Password baru dan konfirmasi tidak cocok.';
            }
        }
    }

    if (empty($errors)) {
        // Update user data
        $user['username'] = $username;
        $user['email'] = $email;
        $user['fitness_level'] = $fitness_level;
        $user['preferences'] = $preferences;
        if ($new_password) {
            $user['password_hash'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        $user['updated_at'] = date('c');

        // Update users array
        foreach ($users as &$u) {
            if ($u['id'] === $user_id) {
                $u = $user;
                break;
            }
        }
        writeData(USERS_FILE, $users);

        $_SESSION['username'] = $username;
        $success = 'Profil berhasil diperbarui.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil Pengguna - Fitpulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800 min-h-screen flex flex-col">
    <header class="bg-blue-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Fitpulse</h1>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="index.php" class="hover:underline">Beranda</a></li>
                    <li><a href="schedule.php" class="hover:underline">Jadwal</a></li>
                    <li><a href="recommendations.php" class="hover:underline">Rekomendasi</a></li>
                    <li><a href="progress.php" class="hover:underline">Progres</a></li>
                    <li><a href="profile.php" class="hover:underline font-semibold underline">Profil</a></li>
                    <li><a href="logout.php" class="hover:underline">Keluar</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container mx-auto flex-grow p-6 max-w-4xl">
        <h2 class="text-3xl font-semibold mb-6">Profil Pengguna</h2>

        <?php if ($errors): ?>
            <div class="mb-4 text-red-600">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><i class="fas fa-exclamation-circle mr-2"></i><?=htmlspecialchars($error)?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <div class="mb-4 text-green-600">
                <i class="fas fa-check-circle mr-2"></i><?=htmlspecialchars($success)?>
            </div>
        <?php endif; ?>

        <form method="POST" action="profile.php" class="bg-white p-6 rounded shadow space-y-6">
            <div>
                <label for="username" class="block mb-1 font-semibold">Username</label>
                <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?=htmlspecialchars($user['username'])?>" />
            </div>
            <div>
                <label for="email" class="block mb-1 font-semibold">Email</label>
                <input type="email" id="email" name="email" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?=htmlspecialchars($user['email'])?>" />
            </div>
            <div>
                <label for="fitness_level" class="block mb-1 font-semibold">Tingkat Kebugaran</label>
                <select id="fitness_level" name="fitness_level" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="beginner" <?=($user['fitness_level'] === 'beginner') ? 'selected' : ''?>>Pemula</option>
                    <option value="intermediate" <?=($user['fitness_level'] === 'intermediate') ? 'selected' : ''?>>Menengah</option>
                    <option value="advanced" <?=($user['fitness_level'] === 'advanced') ? 'selected' : ''?>>Lanjutan</option>
                </select>
            </div>
            <div>
                <label for="preferences" class="block mb-1 font-semibold">Preferensi Olahraga</label>
                <textarea id="preferences" name="preferences" rows="4" class="w-full border border-gray-300 rounded px-3 py-2"><?=htmlspecialchars($user['preferences'])?></textarea>
            </div>
            <fieldset class="border border-gray-300 rounded p-4">
                <legend class="font-semibold mb-2">Ganti Password</legend>
                <div>
                    <label for="current_password" class="block mb-1">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" class="w-full border border-gray-300 rounded px-3 py-2" />
                </div>
                <div class="mt-4">
                    <label for="new_password" class="block mb-1">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" class="w-full border border-gray-300 rounded px-3 py-2" />
                </div>
                <div class="mt-4">
                    <label for="confirm_password" class="block mb-1">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full border border-gray-300 rounded px-3 py-2" />
                </div>
            </fieldset>
            <button type="submit" class="bg-gray-700 text-white py-2 px-6 rounded hover:bg-gray-800 transition">Perbarui Profil</button>
        </form>
    </main>
    <footer class="bg-gray-200 text-center p-4 text-sm text-gray-600">
        &copy; 2024 Fitpulse. All rights reserved.
    </footer>
</body>
</html>
