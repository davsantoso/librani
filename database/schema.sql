-- Librani: struktur database (14 tabel) + data awal minimal.
-- Kompatibel MySQL 5.7+/8 dan MariaDB 10.3+. Impor ke database kosong.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ===================== MASTER =====================

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','petugas','anggota') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE anggota (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  kode_anggota VARCHAR(20) NULL UNIQUE,
  no_identitas VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  tgl_lahir DATE NOT NULL,
  alamat TEXT NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  foto VARCHAR(100) NULL,
  status ENUM('menunggu','aktif','ditolak','nonaktif') NOT NULL DEFAULT 'menunggu',
  alasan_tolak VARCHAR(255) NULL,
  tgl_daftar DATE NOT NULL,
  tgl_berlaku DATE NULL,
  verified_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_anggota_status (status),
  CONSTRAINT fk_anggota_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_anggota_verifikator FOREIGN KEY (verified_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(10) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  deskripsi TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE penerbit (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  kota VARCHAR(50) NULL,
  telepon VARCHAR(20) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rak (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(10) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  lantai TINYINT NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE buku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  isbn VARCHAR(20) NULL UNIQUE,
  judul VARCHAR(200) NOT NULL,
  penulis VARCHAR(150) NOT NULL,
  penerbit_id INT NULL,
  kategori_id INT NOT NULL,
  tahun_terbit SMALLINT NULL,
  halaman SMALLINT NULL,
  sinopsis TEXT NULL,
  foto_cover VARCHAR(100) NULL,
  foto_thumb VARCHAR(100) NULL,
  harga INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_buku_penerbit FOREIGN KEY (penerbit_id) REFERENCES penerbit(id),
  CONSTRAINT fk_buku_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE eksemplar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buku_id INT NOT NULL,
  rak_id INT NULL,
  kode_eksemplar VARCHAR(20) NOT NULL UNIQUE,
  kondisi ENUM('baik','cukup','buruk') NOT NULL DEFAULT 'baik',
  status ENUM('tersedia','dipinjam','direservasi','rusak','hilang') NOT NULL DEFAULT 'tersedia',
  tgl_pengadaan DATE NULL,
  sumber ENUM('beli','hibah') NOT NULL DEFAULT 'beli',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_eksemplar_status (status),
  CONSTRAINT fk_eksemplar_buku FOREIGN KEY (buku_id) REFERENCES buku(id),
  CONSTRAINT fk_eksemplar_rak FOREIGN KEY (rak_id) REFERENCES rak(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== TRANSAKSI =====================

CREATE TABLE peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  no_transaksi VARCHAR(20) NOT NULL UNIQUE,
  anggota_id INT NOT NULL,
  petugas_id INT NOT NULL,
  tgl_pinjam DATE NOT NULL,
  status ENUM('aktif','selesai') NOT NULL DEFAULT 'aktif',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_peminjaman_tgl (tgl_pinjam),
  CONSTRAINT fk_peminjaman_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id),
  CONSTRAINT fk_peminjaman_petugas FOREIGN KEY (petugas_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE peminjaman_detail (
  id INT AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id INT NOT NULL,
  eksemplar_id INT NOT NULL,
  tgl_jatuh_tempo DATE NOT NULL,
  jumlah_perpanjangan TINYINT NOT NULL DEFAULT 0,
  denda_per_hari INT NOT NULL,
  status ENUM('dipinjam','kembali') NOT NULL DEFAULT 'dipinjam',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_detail_jatuh_tempo (tgl_jatuh_tempo),
  INDEX idx_detail_status (status),
  CONSTRAINT fk_detail_peminjaman FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id),
  CONSTRAINT fk_detail_eksemplar FOREIGN KEY (eksemplar_id) REFERENCES eksemplar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pengembalian (
  id INT AUTO_INCREMENT PRIMARY KEY,
  no_pengembalian VARCHAR(20) NOT NULL,
  peminjaman_detail_id INT NOT NULL UNIQUE,
  tgl_kembali DATE NOT NULL,
  kondisi ENUM('baik','rusak','hilang') NOT NULL DEFAULT 'baik',
  hari_terlambat INT NOT NULL DEFAULT 0,
  petugas_id INT NOT NULL,
  catatan VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pengembalian_no (no_pengembalian),
  INDEX idx_pengembalian_tgl (tgl_kembali),
  CONSTRAINT fk_pengembalian_detail FOREIGN KEY (peminjaman_detail_id) REFERENCES peminjaman_detail(id),
  CONSTRAINT fk_pengembalian_petugas FOREIGN KEY (petugas_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE denda (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pengembalian_id INT NOT NULL,
  anggota_id INT NOT NULL,
  jenis ENUM('terlambat','rusak','hilang') NOT NULL,
  jumlah INT NOT NULL,
  status ENUM('belum_lunas','lunas','dibebaskan') NOT NULL DEFAULT 'belum_lunas',
  tgl_bayar DATE NULL,
  petugas_id INT NULL,
  no_kuitansi VARCHAR(20) NULL,
  keterangan VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_denda_status (status),
  INDEX idx_denda_kuitansi (no_kuitansi),
  CONSTRAINT fk_denda_pengembalian FOREIGN KEY (pengembalian_id) REFERENCES pengembalian(id),
  CONSTRAINT fk_denda_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id),
  CONSTRAINT fk_denda_petugas FOREIGN KEY (petugas_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE perpanjangan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  peminjaman_detail_id INT NOT NULL,
  jatuh_tempo_lama DATE NOT NULL,
  jatuh_tempo_baru DATE NOT NULL,
  tgl_perpanjangan DATE NOT NULL,
  diajukan_oleh INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_perpanjangan_detail FOREIGN KEY (peminjaman_detail_id) REFERENCES peminjaman_detail(id),
  CONSTRAINT fk_perpanjangan_user FOREIGN KEY (diajukan_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  anggota_id INT NOT NULL,
  buku_id INT NOT NULL,
  eksemplar_id INT NULL,
  tgl_reservasi DATETIME NOT NULL,
  tgl_siap DATE NULL,
  tgl_batas_ambil DATE NULL,
  status ENUM('menunggu','siap','diambil','batal','kadaluarsa') NOT NULL DEFAULT 'menunggu',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_reservasi_buku_status (buku_id, status),
  CONSTRAINT fk_reservasi_anggota FOREIGN KEY (anggota_id) REFERENCES anggota(id),
  CONSTRAINT fk_reservasi_buku FOREIGN KEY (buku_id) REFERENCES buku(id),
  CONSTRAINT fk_reservasi_eksemplar FOREIGN KEY (eksemplar_id) REFERENCES eksemplar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== SISTEM =====================

CREATE TABLE pengaturan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kunci VARCHAR(50) NOT NULL UNIQUE,
  nilai VARCHAR(255) NOT NULL,
  keterangan VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ===================== DATA AWAL =====================

INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES
('maks_pinjam', '3', 'Maksimal eksemplar yang dipinjam bersamaan per anggota'),
('lama_pinjam', '7', 'Lama pinjam (hari) sejak tanggal pinjam'),
('denda_per_hari', '1000', 'Denda keterlambatan per eksemplar per hari (Rp)'),
('masa_berlaku_anggota', '365', 'Masa berlaku keanggotaan sejak verifikasi (hari)'),
('maks_perpanjangan', '1', 'Maksimal perpanjangan per eksemplar per peminjaman'),
('lama_perpanjangan', '7', 'Tambahan hari setiap perpanjangan'),
('maks_reservasi', '2', 'Maksimal reservasi aktif per anggota'),
('batas_ambil_reservasi', '2', 'Batas hari mengambil buku reservasi sejak siap'),
('denda_rusak_persen', '50', 'Denda buku rusak (% dari harga buku)'),
('denda_hilang_persen', '100', 'Denda buku hilang (% dari harga buku)'),
('nama_perpustakaan', 'Perpustakaan Umum Librani', 'Nama perpustakaan pada kop laporan'),
('alamat_perpustakaan', 'Jl. Merdeka No. 1', 'Alamat perpustakaan pada kop laporan');

-- Akun admin awal: admin / admin123 (segera ganti password setelah instalasi)
INSERT INTO users (nama, username, email, password_hash, role) VALUES
('Administrator', 'admin', 'admin@librani.test', '$2y$10$UP9EbSRJAm2/ickCM.UNmuHMkgVR7iEJM1QDts2zsLK.IWVxXqVKO', 'admin');
