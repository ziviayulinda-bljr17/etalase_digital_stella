<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
cek_login();

$stmt = $pdo->prepare('SELECT p.*, u.nama_lengkap FROM projects p JOIN users u ON p.user_id = u.id WHERE p.id = :id');
$stmt->execute([':id' => (int)($_GET['id'] ?? 0)]);
$p = $stmt->fetch();
if (!$p) {
    flash('Project tidak ditemukan.', 'error');
    header('Location: index.php');
    exit;
}

$judul_halaman = $p['judul'];
require 'includes/header.php';
?>
<a href="index.php" class="btn btn-secondary btn-sm">&larr; Kembali</a>
<article class="detail">
    <img src="uploads/<?= e($p['gambar']) ?>" alt="<?= e($p['judul']) ?>">
    <div>
        <h1><?= e($p['judul']) ?></h1>
        <p class="muted">Oleh <?= e($p['nama_lengkap']) ?> &middot; <?= date('d M Y', strtotime($p['created_at'])) ?></p>
        <p class="deskripsi"><?= nl2br(e($p['deskripsi'])) ?></p>
        <div class="card-actions">
            <a href="<?= e($p['link_publishing']) ?>" target="_blank" rel="noopener" class="btn btn-primary">Buka Link Publishing</a>
            <?php if ($p['link_demo']): ?>
                <a href="<?= e($p['link_demo']) ?>" target="_blank" rel="noopener" class="btn btn-secondary">Lihat Demo</a>
            <?php endif; ?>
            <?php if (boleh_kelola($p)): ?>
                <a href="project_edit.php?id=<?= $p['id'] ?>" class="btn btn-warning">Edit</a>
                <form method="POST" action="project_hapus.php" data-confirm="Yakin hapus project ini?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>
<?php require 'includes/footer.php'; ?>
