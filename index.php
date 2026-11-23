<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';
cek_login();

// Pencarian: judul project atau nama pembuat
$cari = trim($_GET['cari'] ?? '');
$sql = 'SELECT p.*, u.nama_lengkap FROM projects p JOIN users u ON p.user_id = u.id';
$params = [];
if ($cari !== '') {
    $sql .= ' WHERE p.judul LIKE :k OR u.nama_lengkap LIKE :k';
    $params[':k'] = '%' . $cari . '%';
}
$sql .= ' ORDER BY p.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$judul_halaman = 'Etalase';
require 'includes/header.php';
?>
<section class="hero">
    <h1>Etalase Digital Stella</h1>
    <p>Tempat siswa memamerkan project terbaiknya.</p>
</section>

<form method="GET" class="search-form">
    <input type="text" name="cari" placeholder="Cari judul project atau nama pembuat..." value="<?= e($cari) ?>">
    <button type="submit" class="btn btn-primary">Cari</button>
    <?php if ($cari !== ''): ?><a href="index.php" class="btn btn-secondary">Reset</a><?php endif; ?>
</form>

<p class="muted"><?= count($projects) ?> project ditemukan</p>

<?php if (!$projects): ?>
    <div class="empty">Belum ada project. Jadilah yang pertama menambahkan!</div>
<?php endif; ?>

<div class="grid">
<?php foreach ($projects as $p): ?>
    <article class="card"
        data-judul="<?= e($p['judul']) ?>"
        data-oleh="<?= e($p['nama_lengkap']) ?>"
        data-tanggal="<?= date('d M Y', strtotime($p['created_at'])) ?>"
        data-deskripsi="<?= e($p['deskripsi']) ?>"
        data-gambar="uploads/<?= e($p['gambar']) ?>"
        data-link="<?= e($p['link_publishing']) ?>"
        data-demo="<?= e($p['link_demo'] ?? '') ?>">
        <a href="project_detail.php?id=<?= $p['id'] ?>" class="buka-modal">
            <img class="card-img" src="uploads/<?= e($p['gambar']) ?>" alt="<?= e($p['judul']) ?>">
        </a>
        <div class="card-body">
            <h3><a href="project_detail.php?id=<?= $p['id'] ?>" class="buka-modal"><?= e($p['judul']) ?></a></h3>
            <small class="muted">Oleh <?= e($p['nama_lengkap']) ?></small>
            <p><?= e(mb_strimwidth($p['deskripsi'], 0, 110, '...')) ?></p>
            <div class="card-actions">
                <a href="<?= e($p['link_publishing']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-primary">Link</a>
                <?php if ($p['link_demo']): ?>
                    <a href="<?= e($p['link_demo']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-secondary">Demo</a>
                <?php endif; ?>
                <?php if (boleh_kelola($p)): ?>
                    <a href="project_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="project_hapus.php" data-confirm="Yakin hapus project ini?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </article>
<?php endforeach; ?>
</div>

<!-- Pop-up detail project (diisi lewat assets/js/main.js) -->
<div class="modal" id="modal" hidden>
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="m-judul">
        <button type="button" class="modal-tutup" id="m-tutup" aria-label="Tutup">&times;</button>
        <img id="m-gambar" class="modal-gambar" src="" alt="">
        <div class="modal-isi">
            <h2 id="m-judul"></h2>
            <p class="muted" id="m-oleh"></p>
            <p class="deskripsi" id="m-deskripsi"></p>
            <div class="card-actions">
                <a id="m-link" href="#" target="_blank" rel="noopener" class="btn btn-primary">Buka Link Publishing</a>
                <a id="m-demo" href="#" target="_blank" rel="noopener" class="btn btn-secondary">Lihat Demo</a>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>