CREATE DATABASE IF NOT EXISTS etalase_digital_stella;
USE etalase_digital_stella;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('sa', 'admin', 'siswa') NOT NULL DEFAULT 'siswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    link_publishing VARCHAR(255) NOT NULL,
    link_demo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Akun default:
--   superadmin@stella.sch.id / sa12345
--   admin@stella.sch.id / admin123
--   siswa@stella.sch.id / siswa123
INSERT INTO users (username, nama_lengkap, password, role) VALUES
('superadmin@stella.sch.id', 'Super Admin', '$2y$10$JSpa7XYcVXirMnFxykNPleGCGhhFWq.0bpo.nbPppdMg30UFiB2qy', 'sa'),
('admin@stella.sch.id', 'Admin Stella', '$2y$10$lMt438PgXDe/3mSkv.jGa.N1QJJ2w0d107z9BFjnhiqpS4tfR2Aly', 'admin'),
('siswa@stella.sch.id', 'Siswa Contoh', '$2y$10$CRBaLRNLsH9cvU8ka6obM.fPb2fA24mp6p9EiCOvbbQN5IgWee4NG', 'siswa');