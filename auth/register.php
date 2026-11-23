
<?php
session_start();
require_once '../config/kuis_db.php';
require_once '../includes/helpers.php';

$error = '';
$nama = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nama === '' || $username === '' || $password === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL) || strlen($username) > 50) {
        $error = 'Format email tidak valid (maksimal 50 karakter).';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $cek = $pdo->prepare('SELECT id FROM users WHERE username = :u');
        $cek->execute([':u' => $username]);

        if ($cek->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, nama_lengkap, password, role)
                 VALUES (:u, :n, :p, 'siswa')"
            );

            $stmt->execute([
                ':u' => $username,
                ':n' => $nama,
                ':p' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            flash('Registrasi berhasil! Silakan login.');
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi - Etalase Digital Stella</title>

    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body class="auth-page">

    <div class="auth-card register-card">

        <div class="auth-header">
            <img
                src="../assets/img/logo-telkom.png"
                alt="Logo Stella"
                class="auth-logo"
            >

            <h1>Registrasi Siswa</h1>
            <p>Buat akun siswa untuk memamerkan karya Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>

                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M7 10h10M7 14h6"/>
                        </svg>
                    </span>

                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        placeholder="Masukkan nama lengkap"
                        value="<?= e($nama) ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="username">Email</label>

                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M3 7l9 6 9-6"/>
                        </svg>
                    </span>

                    <input
                        type="email"
                        id="username"
                        name="username"
                        placeholder="Masukkan email aktif"
                        value="<?= e($username) ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                        </svg>
                    </span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Buat password (min. 6 karakter)"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="konfirmasi">Konfirmasi Password</label>

                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </span>

                    <input
                        type="password"
                        id="konfirmasi"
                        name="konfirmasi"
                        placeholder="Ulangi password"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="auth-btn">
                Daftar Sekarang
                <span>&rarr;</span>
            </button>

        </form>

        <div class="auth-footer">
            Sudah punya akun?
            <a href="login.php">Login di sini</a>
        </div>

    </div>

</body>
</html>
