<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';
cek_role(['sa']); // Hanya Super Admin

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $error = 'Sesi tidak valid, coba lagi.';
    } else {
        $aksi = $_POST['aksi'] ?? '';
        $id   = (int)($_POST['id'] ?? 0);

        if ($aksi === 'tambah') {
            $nama     = trim($_POST['nama'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            if ($nama === '' || $username === '' || strlen($password) < 6) {
                $error = 'Nama & email wajib diisi, password minimal 6 karakter.';
            } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL) || strlen($username) > 50) {
                $error = 'Format email tidak valid (maksimal 50 karakter).';
            } else {
                $cek = $pdo->prepare('SELECT id FROM users WHERE username = :u');
                $cek->execute([':u' => $username]);
                if ($cek->fetch()) {
                    $error = 'Email sudah terdaftar!';
                } else {
                    $pdo->prepare("INSERT INTO users (username, nama_lengkap, password, role) VALUES (:u, :n, :p, 'admin')")
                        ->execute([':u' => $username, ':n' => $nama, ':p' => password_hash($password, PASSWORD_DEFAULT)]);
                    flash('Admin baru berhasil ditambahkan.');
                    header('Location: users.php');
                    exit;
                }
            }
        } elseif ($aksi === 'reset') {
            $pdo->prepare("UPDATE users SET password = :p WHERE id = :id AND role <> 'sa'")
                ->execute([':p' => password_hash('12345', PASSWORD_DEFAULT), ':id' => $id]);
            flash('Password direset menjadi 12345.');
            header('Location: users.php');
            exit;
        } elseif ($aksi === 'hapus') {
            $g = $pdo->prepare("SELECT p.gambar FROM projects p JOIN users u ON p.user_id = u.id WHERE u.id = :id AND u.role <> 'sa'");
            $g->execute([':id' => $id]);
            foreach ($g->fetchAll(PDO::FETCH_COLUMN) as $file) hapus_gambar($file);
            $pdo->prepare("DELETE FROM users WHERE id = :id AND role <> 'sa'")->execute([':id' => $id]);
            flash('User dihapus.');
            header('Location: users.php');
            exit;
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY FIELD(role, 'sa', 'admin', 'siswa'), id")->fetchAll();

$judul_halaman = 'Kelola User';
require '../includes/header.php';
?>
<h2>Kelola User <span class="muted">(Super Admin)</span></h2>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="form-box wide">
    <h3>Tambah Admin Baru</h3>
    <form method="POST" class="inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="tambah">
        <input type="text" name="nama" placeholder="Nama admin" required>
        <input type="email" name="username" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password (min 6)" required>
        <button type="submit" class="btn btn-primary">Tambah Admin</button>
    </form>
</div>

<div class="table-wrap">
<table>
    <tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= e($u['nama_lengkap']) ?></td>
        <td><?= e($u['username']) ?></td>
        <td><span class="badge badge-<?= e($u['role']) ?>"><?= e(strtoupper($u['role'])) ?></span></td>
        <td class="aksi">
        <?php if ($u['role'] !== 'sa'): ?>
            <form method="POST" data-confirm="Reset password user ini menjadi 12345?">
                <?= csrf_field() ?><input type="hidden" name="aksi" value="reset"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button class="btn btn-sm btn-secondary">Reset</button>
            </form>
            <form method="POST" data-confirm="Hapus user ini beserta project-nya?">
                <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button class="btn btn-sm btn-danger">Hapus</button>
            </form>
        <?php else: ?>-<?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php require '../includes/footer.php'; ?>