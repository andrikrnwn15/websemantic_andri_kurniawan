-- =========================================================
-- SKEMA BASIS DATA: SISTEM DATA MAHASISWA PER PROGRAM STUDI
-- Universitas Muhammadiyah Bengkulu
-- Sudah dinormalisasi hingga 3NF
-- =========================================================

-- Hapus tabel jika sudah ada (urutan disesuaikan agar FK tidak error)
DROP TABLE IF EXISTS pengguna;
DROP TABLE IF EXISTS mahasiswa;
DROP TABLE IF EXISTS program_studi;
DROP TABLE IF EXISTS role;

-- =========================================================
-- 1. TABEL ROLE
-- =========================================================
CREATE TABLE role (
    kode_role   INT AUTO_INCREMENT PRIMARY KEY,
    nama_role   VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO role (kode_role, nama_role) VALUES
(1, 'Admin'),
(2, 'Prodi');

-- =========================================================
-- 2. TABEL PROGRAM_STUDI
-- =========================================================
CREATE TABLE program_studi (
    kode_prodi   VARCHAR(10) PRIMARY KEY,
    nama_prodi   VARCHAR(100) NOT NULL,
    fakultas     VARCHAR(100) NOT NULL
);

INSERT INTO program_studi (kode_prodi, nama_prodi, fakultas) VALUES
('TIF', 'Teknik Informatika', 'Teknik');

-- =========================================================
-- 3. TABEL MAHASISWA
-- =========================================================
CREATE TABLE mahasiswa (
    npm             VARCHAR(15) PRIMARY KEY,
    nama_mahasiswa  VARCHAR(100) NOT NULL,
    jenis_kelamin   ENUM('Laki-laki', 'Perempuan') NOT NULL,
    tempat_lahir    VARCHAR(50),
    tanggal_lahir   DATE,
    tanggal_masuk   DATE,
    alamat          TEXT,
    password        VARCHAR(255) NOT NULL,
    kode_prodi      VARCHAR(10) NOT NULL,

    CONSTRAINT fk_mahasiswa_prodi
        FOREIGN KEY (kode_prodi) REFERENCES program_studi(kode_prodi)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- =========================================================
-- 4. TABEL PENGGUNA
-- =========================================================
CREATE TABLE pengguna (
    id_pengguna   INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    kode_role     INT NOT NULL,
    kode_prodi    VARCHAR(10) NULL,   -- diisi hanya jika role = Prodi, NULL jika Admin

    CONSTRAINT fk_pengguna_role
        FOREIGN KEY (kode_role) REFERENCES role(kode_role)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pengguna_prodi
        FOREIGN KEY (kode_prodi) REFERENCES program_studi(kode_prodi)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- Contoh data pengguna
-- Admin: tidak terikat prodi tertentu (kode_prodi = NULL)
INSERT INTO pengguna (username, password, kode_role, kode_prodi) VALUES
('admin01', 'hash_password_admin', 1, NULL);

-- Prodi: terikat pada satu program studi tertentu
INSERT INTO pengguna (username, password, kode_role, kode_prodi) VALUES
('prodi_tif', 'hash_password_prodi', 2, 'TIF');

-- =========================================================
-- CONTOH QUERY BERGUNA
-- =========================================================

-- Menampilkan mahasiswa beserta nama prodi dan fakultasnya
-- SELECT m.npm, m.nama_mahasiswa, p.nama_prodi, p.fakultas
-- FROM mahasiswa m
-- JOIN program_studi p ON m.kode_prodi = p.kode_prodi;

-- Menampilkan pengguna beserta role dan prodi (jika ada)
-- SELECT u.username, r.nama_role, p.nama_prodi
-- FROM pengguna u
-- JOIN role r ON u.kode_role = r.kode_role
-- LEFT JOIN program_studi p ON u.kode_prodi = p.kode_prodi;
