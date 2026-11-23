<?php
// Proteksi sesi & hak akses (3 role: sa, admin, siswa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/helpers.php';

function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . base_url() . '/auth/login.php');
        exit;
    }
}

// Contoh: cek_role(['sa', 'admin'])
function cek_role(array $roles) {
    cek_login();
    if (!in_array($_SESSION['role'], $roles, true)) {
        flash('Akses ditolak: kamu tidak punya hak akses ke halaman itu.', 'error');
        header('Location: ' . base_url() . '/index.php');
        exit;
    }
}

// SA & Admin boleh kelola semua project, siswa hanya miliknya sendiri
function boleh_kelola(array $project): bool {
    if (!isset($_SESSION['user_id'])) return false;
    if (in_array($_SESSION['role'], ['sa', 'admin'], true)) return true;
    return (int)$project['user_id'] === (int)$_SESSION['user_id'];
}
