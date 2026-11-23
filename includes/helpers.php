<?php
// Fungsi bantu umum

function base_url(): string {
    $root = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT']));
    $app  = str_replace('\\', '/', (string)realpath(__DIR__ . '/..'));
    return rtrim(substr($app, strlen($root)), '/');
}

function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function flash(string $pesan, string $tipe = 'success'): void {
    $_SESSION['flash'] = ['pesan' => $pesan, 'tipe' => $tipe];
}

function ambil_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// CSRF untuk semua aksi POST
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_valid(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function link_valid(string $url): bool {
    return (bool)filter_var($url, FILTER_VALIDATE_URL) && preg_match('~^https?://~i', $url);
}

// Upload gambar project: JPG/PNG/WEBP, maks 2MB. Mengembalikan nama file atau null (+ isi $error)
function simpan_gambar(array $file, ?string &$error): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Gagal mengunggah gambar.';
        return null;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        $error = 'Ukuran gambar maksimal 2MB.';
        return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $boleh = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($boleh[$mime])) {
        $error = 'Format gambar harus JPG, PNG, atau WEBP.';
        return null;
    }
    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $nama = 'project_' . bin2hex(random_bytes(8)) . '.' . $boleh[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $nama)) {
        $error = 'Gagal menyimpan gambar ke server.';
        return null;
    }
    return $nama;
}

function hapus_gambar(?string $nama): void {
    if ($nama && ($path = __DIR__ . '/../uploads/' . basename($nama)) && is_file($path)) {
        unlink($path);
    }
}
