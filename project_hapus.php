<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
cek_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    flash('Permintaan tidak valid.', 'error');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
$stmt->execute([':id' => (int)($_POST['id'] ?? 0)]);
$p = $stmt->fetch();

if (!$p) {
    flash('Project tidak ditemukan.', 'error');
} elseif (!boleh_kelola($p)) {
    flash('Kamu tidak boleh menghapus project milik orang lain.', 'error');
} else {
    hapus_gambar($p['gambar']);
    $pdo->prepare('DELETE FROM projects WHERE id = :id')->execute([':id' => $p['id']]);
    flash('Project berhasil dihapus.');
}
header('Location: index.php');
exit;
