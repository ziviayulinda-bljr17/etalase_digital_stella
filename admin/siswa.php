<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';
cek_role(['sa', 'admin']); // SA & Admin: kelola data siswa

$error = '';
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $error = 'Sesi tidak valid, coba lagi.';
    } else {
        $aksi = $_POST['aksi'] ?? '';
        $id   = (int)($_POST['id'] ?? 0);

        if ($aksi === 'tambah' || $aksi === 'ubah') {
            $nama     = trim($_POST['nama'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($nama === '' || $username === '') {
                $error = 'Nama dan email wajib diisi.';
            } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL) || strlen($username) > 50) {
                $error = 'Format email tidak valid (maksimal 50 karakter).';
            } elseif ($aksi === 'tambah' && strlen($password) < 6) {
                $error = 'Password minimal 6 karakter.';
            } else {
                $cek = $pdo->prepare('SELECT id FROM users WHERE username = :u AND id <> :id');
                $cek->execute([':u' => $username, ':id' => $id]);
                if ($cek->fetch()) {
                    $error = 'Email sudah terdaftar!';
                } elseif ($aksi === 'tambah') {
                    $pdo->prepare("INSERT INTO users (username, nama_lengkap, password, role) VALUES (:u, :n, :p, 'siswa')")
                        ->execute([':u' => $username, ':n' => $nama, ':p' => password_hash($password, PASSWORD_DEFAULT)]);
                    flash('Siswa berhasil ditambahkan.');
                    header('Location: siswa.php');
                    exit;
                } else {
                    $pdo->prepare("UPDATE users SET username = :u, nama_lengkap = :n WHERE id = :id AND role = 'siswa'")
                        ->execute([':u' => $username, ':n' => $nama, ':id' => $id]);
                    flash('Data siswa diperbarui.');
                    header('Location: siswa.php');
                    exit;
                }
            }
        } elseif ($aksi === 'reset') {
            $pdo->prepare("UPDATE users SET password = :p WHERE id = :id AND role = 'siswa'")
                ->execute([':p' => password_hash('12345', PASSWORD_DEFAULT), ':id' => $id]);
            flash('Password siswa direset menjadi 12345.');
            header('Location: siswa.php');
            exit;
        } elseif ($aksi === 'hapus') {
            // hapus file gambar milik siswa dulu (baris project ikut terhapus lewat ON DELETE CASCADE)
            $g = $pdo->prepare("SELECT p.gambar FROM projects p JOIN users u ON p.user_id = u.id WHERE u.id = :id AND u.role = 'siswa'");
            $g->execute([':id' => $id]);
            foreach ($g->fetchAll(PDO::FETCH_COLUMN) as $file) hapus_gambar($file);
            $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'siswa'")->execute([':id' => $id]);
            flash('Siswa beserta project-nya dihapus.');
            header('Location: siswa.php');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $q = $pdo->prepare("SELECT * FROM users WHERE id = :id AND role = 'siswa'");
    $q->execute([':id' => (int)$_GET['edit']]);
    $edit = $q->fetch() ?: null;
}

$siswa = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM projects WHERE user_id = u.id) AS jml
                      FROM users u WHERE u.role = 'siswa' ORDER BY u.id")->fetchAll();

$judul_halaman = 'Kelola Siswa';
require '../includes/header.php';
?>
<h2>Kelola Data Siswa</h2>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="form-box wide">
    <h3><?= $edit ? 'Edit Siswa' : 'Tambah Siswa' ?></h3>
    <form method="POST" class="inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <input type="text" name="nama" placeholder="Nama lengkap" value="<?= e($edit['nama_lengkap'] ?? '') ?>" required>
        <input type="email" name="username" placeholder="Email" value="<?= e($edit['username'] ?? '') ?>" required>
        <?php if (!$edit): ?><input type="password" name="password" placeholder="Password (min 6)" required><?php endif; ?>
        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
        <?php if ($edit): ?><a href="siswa.php" class="btn btn-secondary">Batal</a><?php endif; ?>
    </form>
</div>

<div class="table-wrap">
<table>
    <tr><th>ID</th><th>Nama</th><th>Email</th><th>Project</th><th>Terdaftar</th><th>Aksi</th></tr>
    <?php foreach ($siswa as $s): ?>
    <tr>
        <td><?= $s['id'] ?></td>
        <td><?= e($s['nama_lengkap']) ?></td>
        <td><?= e($s['username']) ?></td>
        <td><?= $s['jml'] ?></td>
        <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
        <td class="aksi">
            <a href="siswa.php?edit=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <form method="POST" data-confirm="Reset password siswa ini menjadi 12345?">
                <?= csrf_field() ?><input type="hidden" name="aksi" value="reset"><input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button class="btn btn-sm btn-secondary">Reset</button>
            </form>
            <form method="POST" data-confirm="Hapus siswa ini beserta semua project-nya?">
                <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$siswa): ?><tr><td colspan="6" class="empty-row">Belum ada siswa.</td></tr><?php endif; ?>
</table>
</div>
<?php require '../includes/footer.php'; ?>