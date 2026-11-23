<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
cek_login();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
$stmt->execute([':id' => $id]);
$p = $stmt->fetch();

if (!$p) {
    flash('Project tidak ditemukan.', 'error');
    header('Location: index.php');
    exit;
}
// Siswa hanya boleh edit miliknya sendiri
if (!boleh_kelola($p)) {
    flash('Kamu tidak boleh mengedit project milik orang lain.', 'error');
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link_pub  = trim($_POST['link_publishing'] ?? '');
    $link_demo = trim($_POST['link_demo'] ?? '');
    $p = array_merge($p, ['judul' => $judul, 'deskripsi' => $deskripsi, 'link_publishing' => $link_pub, 'link_demo' => $link_demo]);

    if (!csrf_valid()) {
        $error = 'Sesi tidak valid, coba lagi.';
    } elseif ($judul === '' || $deskripsi === '' || $link_pub === '') {
        $error = 'Judul, deskripsi, dan link publishing wajib diisi.';
    } elseif (!link_valid($link_pub) || ($link_demo !== '' && !link_valid($link_demo))) {
        $error = 'Link harus diawali http:// atau https://';
    } else {
        $gambar_lama = $p['gambar'];
        $gambar = $gambar_lama;

        if (!empty($_FILES['gambar']['name'])) {
            $gambar = simpan_gambar($_FILES['gambar'], $error);
        }
        if (!$error) {
            $up = $pdo->prepare('UPDATE projects SET judul=:j, deskripsi=:d, gambar=:g, link_publishing=:lp, link_demo=:ld WHERE id=:id');
            $up->execute([':j' => $judul, ':d' => $deskripsi, ':g' => $gambar, ':lp' => $link_pub, ':ld' => $link_demo ?: null, ':id' => $id]);
            if ($gambar !== $gambar_lama) hapus_gambar($gambar_lama);
            flash('Project berhasil diperbarui!');
            header('Location: index.php');
            exit;
        }
    }
}

$judul_halaman = 'Edit Project';
require 'includes/header.php';
?>
<div class="form-box">
    <h2>Edit Project</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $p['id'] ?>">
        <div class="form-group">
            <label for="judul">Judul Project</label>
            <input type="text" id="judul" name="judul" value="<?= e($p['judul']) ?>" required>
        </div>
        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="5" required><?= e($p['deskripsi']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="gambar">Ganti Gambar <span class="muted">(kosongkan jika tidak diganti)</span></label>
            <input type="file" id="gambar" name="gambar" accept="image/png,image/jpeg,image/webp" data-preview="#preview">
            <img id="preview" class="preview" src="uploads/<?= e($p['gambar']) ?>" alt="Gambar saat ini">
        </div>
        <div class="form-group">
            <label for="link_publishing">Link Publishing</label>
            <input type="url" id="link_publishing" name="link_publishing" value="<?= e($p['link_publishing']) ?>" required>
        </div>
        <div class="form-group">
            <label for="link_demo">Link Demo <span class="muted">(opsional)</span></label>
            <input type="url" id="link_demo" name="link_demo" value="<?= e($p['link_demo']) ?>">
        </div>
        <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?php require 'includes/footer.php'; ?>
