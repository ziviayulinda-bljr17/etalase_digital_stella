<?php
// Wajib: require auth_check.php sebelum file ini. Set $judul_halaman sebelum include.
$B = base_url();
$flash = ambil_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_halaman ?? 'Etalase') ?> - Etalase Digital Stella</title>
    <link rel="stylesheet" href="<?= $B ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-left">
        <a href="<?= $B ?>/index.php" class="brand">
            <img src="<?= $B ?>/assets/img/logo-telkom.png" alt="Logo">
            <span>Etalase Digital Stella</span>
        </a>
        <div class="nav-menu">
            <a href="<?= $B ?>/index.php">Galeri Karya</a>
            <a href="<?= $B ?>/project_tambah.php">+ Tambah Project</a>
            <?php if (in_array($_SESSION['role'], ['sa', 'admin'], true)): ?>
                <a href="<?= $B ?>/admin/siswa.php">Kelola Siswa</a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] === 'sa'): ?>
                <a href="<?= $B ?>/admin/users.php">Kelola User</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="nav-right">
        <span class="user-badge"><?= e($_SESSION['nama']) ?> <em><?= e(strtoupper($_SESSION['role'])) ?></em></span>
        <a href="<?= $B ?>/auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>
<main class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['tipe']) ?>"><?= e($flash['pesan']) ?></div>
<?php endif; ?>