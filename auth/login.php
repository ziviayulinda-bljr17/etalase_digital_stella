
<?php
session_start();
require_once '../config/kuis_db.php';
require_once '../includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];

        header('Location: ../index.php');
        exit;
    }

    $error = 'Email atau password salah!';
}

$flash = ambil_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Etalase Digital Stella</title>

    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body class="auth-page">

    <div class="auth-card">

        <div class="auth-header">
            <img
                src="../assets/img/logo-telkom.png"
                alt="Logo Stella"
                class="auth-logo"
            >

            <h1>Etalase Digital Stella</h1>
            <p>Silakan masuk ke akun Anda</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['tipe']) ?>">
                <?= e($flash['pesan']) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">

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
                        placeholder="Masukkan email Anda"
                        value="<?= e($username) ?>"
                        required
                        autofocus
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
                        placeholder="Masukkan password"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="auth-btn">
                Login
                <span>&rarr;</span>
            </button>

        </form>

        <div class="auth-footer">
            Belum punya akun?
            <a href="register.php">Registrasi Siswa</a>
        </div>

    </div>

</body>
</html>
