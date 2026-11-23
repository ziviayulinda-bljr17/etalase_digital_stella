<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
cek_login(); // Siswa, Admin, SA boleh menambah project

$error = '';
$judul = $deskripsi = $link_pub = $link_demo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link_pub  = trim($_POST['link_publishing'] ?? '');
    $link_demo = trim($_POST['link_demo'] ?? '');

    if (!csrf_valid()) {
        $error = 'Sesi tidak valid, coba lagi.';
    } elseif ($judul === '' || $deskripsi === '' || $link_pub === '') {
        $error = 'Judul, deskripsi, dan link publishing wajib diisi.';
    } elseif (!link_valid($link_pub) || ($link_demo !== '' && !link_valid($link_demo))) {
        $error = 'Link harus diawali http:// atau https://';
    } elseif (empty($_FILES['gambar']['name'])) {
        $error = 'Gambar project wajib diunggah.';
    } else {
        $gambar = simpan_gambar($_FILES['gambar'], $error);
        if ($gambar) {
            $stmt = $pdo->prepare('INSERT INTO projects (user_id, judul, deskripsi, gambar, link_publishing, link_demo)
                                   VALUES (:uid, :j, :d, :g, :lp, :ld)');
            $stmt->execute([
                ':uid' => $_SESSION['user_id'], ':j' => $judul, ':d' => $deskripsi,
                ':g' => $gambar, ':lp' => $link_pub, ':ld' => $link_demo ?: null,
            ]);
            flash('Project berhasil ditambahkan!');
            header('Location: index.php');
            exit;
        }
    }
}

$judul_halaman = 'Tambah Project';
require 'includes/header.php';
?>
<div class="form-box">
    <h2>Tambah Project</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="judul">Judul Project</label>
            <input type="text" id="judul" name="judul" value="<?= e($judul) ?>" required>
        </div>
        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="5" required><?= e($deskripsi) ?></textarea>
        </div>
        <div class="form-group">
            <label for="gambar">Gambar (JPG/PNG/WEBP, maks 2MB)</label>
            <input type="file" id="gambar" name="gambar" accept="image/png,image/jpeg,image/webp" data-preview="#preview" required>
            <img id="preview" class="preview" alt="Pratinjau gambar" hidden>
        </div>
        <div class="form-group">
            <label for="link_publishing">Link Publishing</label>
            <input type="url" id="link_publishing" name="link_publishing" value="<?= e($link_pub) ?>" placeholder="https://..." required>
        </div>
        <div class="form-group">
            <label for="link_demo">Link Demo <span class="muted">(opsional)</span></label>
            <input type="url" id="link_demo" name="link_demo" value="<?= e($link_demo) ?>" placeholder="https://...">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?php require 'includes/footer.php'; ?>
