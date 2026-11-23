# Etalase Digital Stella

Cara pasang (XAMPP):
1. Salin folder `etalase-digital-stella` ke `htdocs/`.
2. Buka phpMyAdmin, import `schema.sql` (otomatis membuat database `etalase_digital_stella`).
3. Buka http://localhost/etalase-digital-stella/

Akun default:
| Role  | Email                    | Password  |
|-------|--------------------------|-----------|
| SA    | superadmin@stella.sch.id | sa12345   |
| Admin | admin@stella.sch.id      | admin123  |
| Siswa | siswa@stella.sch.id      | siswa123  |

Hak akses:
- Siswa: tambah project, lihat project lain, edit/hapus project sendiri.
- Admin: kelola project (lihat, tambah, edit, hapus semua) + kelola data siswa.
- SA: semua hak Admin + kelola user (tambah/hapus admin, reset password).

Input project: judul, deskripsi, gambar, link publishing, link demo (opsional).
