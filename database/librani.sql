-- Librani: struktur + data contoh. Impor ke database kosong.
-- Dibuat 2026-11-17 15:58 dengan database/export.php. Akun demo: admin/admin123, petugas1/petugas123, anggota1/anggota123


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `kode_anggota` varchar(20) DEFAULT NULL,
  `no_identitas` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tgl_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `foto` varchar(100) DEFAULT NULL,
  `status` enum('menunggu','aktif','ditolak','nonaktif') NOT NULL DEFAULT 'menunggu',
  `alasan_tolak` varchar(255) DEFAULT NULL,
  `tgl_daftar` date NOT NULL,
  `tgl_berlaku` date DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `no_identitas` (`no_identitas`),
  UNIQUE KEY `kode_anggota` (`kode_anggota`),
  KEY `idx_anggota_status` (`status`),
  KEY `fk_anggota_verifikator` (`verified_by`),
  CONSTRAINT `fk_anggota_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_anggota_verifikator` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `anggota` WRITE;
/*!40000 ALTER TABLE `anggota` DISABLE KEYS */;
INSERT INTO `anggota` VALUES
(1,4,'AGT-2026-0001','3578231196758060','Budi Santoso','L','1973-02-18','Jl. Anggrek No. 73','084931647958',NULL,'aktif',NULL,'2026-03-20','2027-03-22',2,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(2,5,'AGT-2026-0002','3578005597130527','Sari Wulandari','P','1996-08-20','Jl. Cempaka No. 82','081118400289',NULL,'aktif',NULL,'2026-03-26','2027-03-27',2,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(3,6,'AGT-2026-0003','3578966649877080','Rina Kartika','P','2004-03-09','Jl. Anggrek No. 52','085889831326',NULL,'aktif',NULL,'2026-03-30','2027-04-01',2,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(4,7,'AGT-2026-0004','3578756104210722','Dimas Saputra','L','1980-04-21','Jl. Anggrek No. 11','088011340608',NULL,'aktif',NULL,'2026-04-04','2027-04-06',3,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(5,8,'AGT-2026-0005','3578710273792476','Ayu Lestari','P','1977-10-27','Jl. Anggrek No. 17','088187919919',NULL,'aktif',NULL,'2026-04-09','2027-04-11',2,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(6,9,'AGT-2026-0006','3578438518970014','Fajar Nugroho','L','2002-11-13','Jl. Melati No. 84','083010295797',NULL,'aktif',NULL,'2026-04-14','2027-04-16',3,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(7,10,'AGT-2026-0007','3578562769013954','Putri Maharani','P','2008-06-10','Jl. Anggrek No. 22','088991226218',NULL,'aktif',NULL,'2026-04-19','2027-04-21',2,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(8,11,'AGT-2026-0008','3578802704831151','Reza Pratama','L','1981-09-20','Jl. Anggrek No. 90','082566548478',NULL,'aktif',NULL,'2026-04-24','2027-04-26',3,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(9,12,'AGT-2026-0009','3578365872173380','Nadia Safitri','P','2006-10-27','Jl. Anggrek No. 110','084836890966',NULL,'aktif',NULL,'2026-04-29','2027-05-01',2,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(10,13,'AGT-2026-0010','3578070552722816','Galih Permana','L','1996-03-20','Jl. Kenanga No. 103','087293682201',NULL,'aktif',NULL,'2026-05-04','2027-05-06',3,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(11,14,'AGT-2026-0011','3578801612190918','Dewi Anggraini','P','1998-07-07','Jl. Kenanga No. 73','083658965889',NULL,'aktif',NULL,'2026-05-09','2027-05-11',2,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(12,15,'AGT-2026-0012','3578157560328489','Yoga Aditya','L','2000-07-16','Jl. Cempaka No. 82','086227320899',NULL,'aktif',NULL,'2026-05-14','2027-05-16',3,'2026-11-17 15:58:40','2026-11-17 15:58:40'),
(13,16,'AGT-2026-0013','3578117219870359','Intan Permatasari','P','1993-01-27','Jl. Anggrek No. 7','088804679103',NULL,'aktif',NULL,'2026-05-19','2027-05-21',2,'2026-11-17 15:58:41','2026-11-17 15:58:41'),
(14,17,'AGT-2026-0014','3578233953871129','Bayu Setiawan','L','1984-07-22','Jl. Cempaka No. 33','086662110660',NULL,'aktif',NULL,'2026-05-24','2027-05-26',3,'2026-11-17 15:58:41','2026-11-17 15:58:41'),
(15,18,'AGT-2026-0015','3578586859919435','Laras Wulandari','P','1977-04-16','Jl. Cempaka No. 100','088665793633',NULL,'aktif',NULL,'2026-05-29','2027-05-31',2,'2026-11-17 15:58:42','2026-11-17 15:58:42'),
(16,19,'AGT-2026-0016','3578128850115438','Hendra Wijaya','L','1990-11-09','Jl. Anggrek No. 13','089630949571',NULL,'aktif',NULL,'2026-06-03','2027-06-05',3,'2026-11-17 15:58:42','2026-11-17 15:58:42'),
(17,20,'AGT-2026-0017','3578063936826886','Citra Kirana','P','1976-12-26','Jl. Cempaka No. 22','086470783606',NULL,'aktif',NULL,'2026-06-08','2027-06-10',2,'2026-11-17 15:58:43','2026-11-17 15:58:43'),
(18,21,'AGT-2026-0018','3578259443359161','Arif Rahman','L','2001-06-22','Jl. Kenanga No. 44','084740987477',NULL,'aktif',NULL,'2026-06-13','2027-06-15',3,'2026-11-17 15:58:43','2026-11-17 15:58:43'),
(19,22,'AGT-2026-0019','3578229967632914','Melati Puspita','P','1973-02-13','Jl. Melati No. 89','083948994793',NULL,'aktif',NULL,'2026-06-18','2027-06-20',2,'2026-11-17 15:58:44','2026-11-17 15:58:44'),
(20,23,'AGT-2026-0020','3578950674111554','Taufik Hidayat','L','1978-04-15','Jl. Kenanga No. 50','083240819442',NULL,'aktif',NULL,'2026-06-23','2026-11-14',3,'2026-11-17 15:58:45','2026-11-17 15:58:46'),
(21,24,NULL,'3578276115172057','Wulan Sari','P','2002-10-15','Jl. Melati No. 23','087558233712',NULL,'menunggu',NULL,'2026-11-16',NULL,NULL,'2026-11-17 15:58:45','2026-11-17 15:58:45'),
(22,25,NULL,'3578559082962780','Irfan Maulana','L','1983-09-06','Jl. Kenanga No. 69','085809693649',NULL,'menunggu',NULL,'2026-11-17',NULL,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46');
/*!40000 ALTER TABLE `anggota` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `buku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `buku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) DEFAULT NULL,
  `judul` varchar(200) NOT NULL,
  `penulis` varchar(150) NOT NULL,
  `penerbit_id` int(11) DEFAULT NULL,
  `kategori_id` int(11) NOT NULL,
  `tahun_terbit` smallint(6) DEFAULT NULL,
  `halaman` smallint(6) DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `foto_cover` varchar(100) DEFAULT NULL,
  `foto_thumb` varchar(100) DEFAULT NULL,
  `harga` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `fk_buku_penerbit` (`penerbit_id`),
  KEY `fk_buku_kategori` (`kategori_id`),
  CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`),
  CONSTRAINT `fk_buku_penerbit` FOREIGN KEY (`penerbit_id`) REFERENCES `penerbit` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `buku` WRITE;
/*!40000 ALTER TABLE `buku` DISABLE KEYS */;
INSERT INTO `buku` VALUES
(1,NULL,'Laskar Pelangi','Andrea Hirata',1,1,2005,NULL,'Sepuluh anak Belitung berjuang bersekolah di SD Muhammadiyah yang nyaris ditutup, dibimbing guru yang tak pernah menyerah.',NULL,NULL,89000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(2,NULL,'Bumi Manusia','Pramoedya Ananta Toer',5,2,2005,NULL,'Minke, pemuda pribumi terpelajar di akhir abad ke-19, jatuh cinta pada Annelies dan berhadapan dengan ketidakadilan hukum kolonial.',NULL,NULL,125000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(3,NULL,'Ronggeng Dukuh Paruk','Ahmad Tohari',2,2,2003,NULL,'Srintil menjadi ronggeng kebanggaan dukuh kecil yang miskin, hingga gejolak politik 1965 mengubah hidup seluruh warganya.',NULL,NULL,99000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(4,NULL,'Sang Pemimpi','Andrea Hirata',1,1,2006,NULL,'Ikal dan Arai, dua remaja Belitung, bekerja keras demi mimpi melanjutkan sekolah hingga ke Eropa.',NULL,NULL,79000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(5,NULL,'Edensor','Andrea Hirata',1,1,2007,NULL,'Petualangan Ikal dan Arai sebagai mahasiswa di Eropa sambil mencari cinta pertama yang hilang.',NULL,NULL,79000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(6,NULL,'Orang-Orang Biasa','Andrea Hirata',1,1,2019,NULL,'Sekelompok sahabat yang hidup pas-pasan merencanakan aksi nekat demi biaya kuliah seorang anak.',NULL,NULL,89000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(7,NULL,'Bumi','Tere Liye',2,1,2014,NULL,'Raib, gadis lima belas tahun yang bisa menghilang, menemukan dunia paralel bersama dua sahabatnya.',NULL,NULL,98000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(8,NULL,'Hujan','Tere Liye',2,1,2016,NULL,'Lail mengenang persahabatan dan cintanya di masa depan setelah bencana besar mengubah dunia.',NULL,NULL,92000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(9,NULL,'Rindu','Tere Liye',3,1,2014,NULL,'Perjalanan kapal haji tahun 1938 mempertemukan para penumpang dengan pertanyaan-pertanyaan hidup mereka.',NULL,NULL,85000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(10,NULL,'Pulang','Leila S. Chudori',4,1,2012,NULL,'Para eksil politik di Paris merindukan tanah air yang tak bisa mereka datangi setelah 1965.',NULL,NULL,110000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(11,NULL,'Laut Bercerita','Leila S. Chudori',4,1,2017,NULL,'Kisah Biru Laut dan kawan-kawan aktivis mahasiswa yang dihilangkan menjelang 1998, dan keluarga yang menunggu mereka.',NULL,NULL,115000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(12,NULL,'Cantik Itu Luka','Eka Kurniawan',2,1,2004,NULL,'Saga keluarga Dewi Ayu yang melintasi masa kolonial hingga pasca-kemerdekaan, penuh realisme magis.',NULL,NULL,125000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(13,NULL,'Lelaki Harimau','Eka Kurniawan',2,1,2004,NULL,'Margio, pemuda pendiam, melakukan pembunuhan yang mengejutkan seisi kampung; kisahnya ditelusuri mundur.',NULL,NULL,85000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(14,NULL,'Supernova: Ksatria, Puteri, dan Bintang Jatuh','Dee Lestari',1,1,2012,NULL,'Dua ilmuwan menulis kisah cinta yang ternyata bersinggungan dengan kehidupan nyata tokoh-tokohnya.',NULL,NULL,95000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(15,NULL,'Perahu Kertas','Dee Lestari',1,1,2009,NULL,'Kugy si pengkhayal dan Keenan si pelukis saling menemukan di tengah jalan hidup yang berbeda arah.',NULL,NULL,99000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(16,NULL,'Ayat-Ayat Cinta','Habiburrahman El Shirazy',3,1,2004,NULL,'Fahri, mahasiswa Indonesia di Kairo, menghadapi ujian iman dan cinta.',NULL,NULL,89000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(17,NULL,'Negeri 5 Menara','Ahmad Fuadi',2,1,2009,NULL,'Alif dan lima sahabatnya di pondok pesantren bermimpi menaklukkan dunia dengan mantra \"man jadda wajada\".',NULL,NULL,89000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(18,NULL,'Saman','Ayu Utami',4,1,1998,NULL,'Empat sahabat perempuan dan seorang mantan pastor bersinggungan dengan isu kebebasan menjelang Reformasi.',NULL,NULL,80000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(19,NULL,'Gadis Kretek','Ratih Kumala',2,1,2012,NULL,'Pencarian sosok Jeng Yah membuka sejarah keluarga dan industri kretek di Jawa.',NULL,NULL,99000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(20,NULL,'Anak Semua Bangsa','Pramoedya Ananta Toer',5,2,2006,NULL,'Minke belajar memahami rakyatnya sendiri dan mulai menulis dalam bahasa Melayu.',NULL,NULL,120000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(21,NULL,'Jejak Langkah','Pramoedya Ananta Toer',5,2,2006,NULL,'Minke mendirikan surat kabar dan organisasi sebagai alat perlawanan terhadap penjajahan.',NULL,NULL,135000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(22,NULL,'Rumah Kaca','Pramoedya Ananta Toer',5,2,2006,NULL,'Kisah gerakan Minke dilihat dari mata Pangemanann, pejabat kolonial yang ditugasi mengawasinya.',NULL,NULL,135000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(23,NULL,'Siti Nurbaya','Marah Rusli',6,2,1922,NULL,'Kisah cinta Siti Nurbaya dan Samsulbahri yang kandas oleh utang keluarga dan adat di Padang.',NULL,NULL,65000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(24,NULL,'Layar Terkembang','Sutan Takdir Alisjahbana',6,2,1936,NULL,'Dua kakak beradik, Tuti dan Maria, mewakili dua cara pandang perempuan terpelajar di masa pergerakan.',NULL,NULL,60000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(25,NULL,'Atheis','Achdiat K. Mihardja',6,2,1949,NULL,'Hasan, pemuda saleh, goyah keyakinannya setelah bergaul dengan kawan-kawan berpaham modern.',NULL,NULL,65000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(26,NULL,'Di Bawah Lindungan Ka\'bah','Hamka',6,2,1938,NULL,'Hamid dan Zainab saling mencintai, tetapi perbedaan derajat memisahkan mereka hingga ke Mekah.',NULL,NULL,55000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(27,NULL,'Robohnya Surau Kami','A.A. Navis',2,2,1956,NULL,'Kumpulan cerpen yang menyindir cara beragama yang hanya mementingkan ibadah tanpa kerja.',NULL,NULL,60000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(28,NULL,'Burung-Burung Manyar','Y.B. Mangunwijaya',7,2,1981,NULL,'Teto, anak tentara KNIL, berada di pihak yang berlawanan dengan perempuan yang dicintainya pada masa revolusi.',NULL,NULL,95000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(29,NULL,'Sejarah Indonesia Modern 1200–2008','M.C. Ricklefs',8,3,2008,NULL,'Rangkuman sejarah Indonesia dari masuknya Islam hingga era Reformasi.',NULL,NULL,175000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(30,NULL,'Habibie & Ainun','B.J. Habibie',9,3,2010,NULL,'Kenangan B.J. Habibie tentang perjalanan hidup bersama istrinya, Hasri Ainun Besari.',NULL,NULL,99000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(31,NULL,'Bung Karno: Penyambung Lidah Rakyat Indonesia','Cindy Adams',10,3,2007,NULL,'Autobiografi Soekarno seperti dituturkan kepada jurnalis Cindy Adams.',NULL,NULL,150000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(32,NULL,'Catatan Seorang Demonstran','Soe Hok Gie',11,3,1983,NULL,'Buku harian Soe Hok Gie tentang kegelisahan mahasiswa dan politik Indonesia tahun 1960-an.',NULL,NULL,95000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(33,NULL,'Sapiens: Riwayat Singkat Umat Manusia','Yuval Noah Harari',4,3,2017,NULL,'Perjalanan Homo sapiens dari pemburu-peramu hingga masyarakat modern.',NULL,NULL,145000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(34,NULL,'Merry Riana: Mimpi Sejuta Dolar','Alberthiene Endah',2,3,2011,NULL,'Kisah Merry Riana yang merantau ke Singapura dan bekerja keras hingga sukses di usia muda.',NULL,NULL,85000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(35,NULL,'Chairul Tanjung Si Anak Singkong','Tjahja Gunawan Diredja',7,3,2012,NULL,'Perjalanan Chairul Tanjung dari keluarga sederhana hingga menjadi pengusaha besar.',NULL,NULL,90000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(36,NULL,'Madilog','Tan Malaka',12,4,1943,NULL,'Pemikiran Tan Malaka tentang materialisme, dialektika, dan logika sebagai cara berpikir rasional.',NULL,NULL,85000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(37,NULL,'Filosofi Teras','Henry Manampiring',7,4,2018,NULL,'Pengantar filsafat Stoa untuk menghadapi emosi negatif dan kecemasan sehari-hari.',NULL,NULL,98000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(38,NULL,'Lapis-Lapis Keberkahan','Salim A. Fillah',13,4,2014,NULL,'Renungan tentang makna berkah dalam rezeki, keluarga, dan kehidupan sehari-hari.',NULL,NULL,95000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(39,NULL,'Tafsir Al-Misbah Jilid 1','M. Quraish Shihab',14,4,2000,NULL,'Tafsir Al-Qur\'an berbahasa Indonesia yang menekankan keserasian makna ayat-ayatnya.',NULL,NULL,165000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(40,NULL,'Membumikan Al-Qur\'an','M. Quraish Shihab',15,4,1992,NULL,'Kumpulan tulisan tentang fungsi dan peran wahyu dalam kehidupan masyarakat.',NULL,NULL,99000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(41,NULL,'Tasawuf Modern','Hamka',3,4,1939,NULL,'Pandangan Hamka tentang kebahagiaan dan cara mencapainya melalui akhlak dan iman.',NULL,NULL,79000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(42,NULL,'Dunia Sophie','Jostein Gaarder',15,4,1996,NULL,'Sophie menerima surat misterius yang mengajaknya menjelajahi sejarah filsafat Barat.',NULL,NULL,115000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(43,NULL,'Atomic Habits','James Clear',2,5,2019,NULL,'Cara membangun kebiasaan baik dan menghentikan kebiasaan buruk lewat perubahan kecil yang konsisten.',NULL,NULL,108000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(44,NULL,'Sebuah Seni untuk Bersikap Bodo Amat','Mark Manson',16,5,2018,NULL,'Ajakan untuk memilih hal yang benar-benar penting dan menerima keterbatasan diri.',NULL,NULL,85000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(45,NULL,'Nanti Kita Cerita tentang Hari Ini','Marchella FP',4,5,2018,NULL,'Kumpulan tulisan pendek dan ilustrasi tentang tumbuh dewasa, gagal, dan bangkit lagi.',NULL,NULL,98000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(46,NULL,'The Psychology of Money','Morgan Housel',1,5,2021,NULL,'Kisah-kisah pendek tentang cara orang berpikir dan bersikap terhadap uang.',NULL,NULL,99000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(47,NULL,'Kambing Jantan','Raditya Dika',17,6,2005,NULL,'Catatan harian kocak seorang mahasiswa Indonesia yang kuliah di Australia.',NULL,NULL,59000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(48,NULL,'Dilan: Dia adalah Dilanku Tahun 1990','Pidi Baiq',18,6,2014,NULL,'Milea mengenang masa SMA di Bandung tahun 1990 bersama Dilan, panglima tempur geng motor yang romantis.',NULL,NULL,79000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(49,NULL,'5 cm','Donny Dhirgantoro',16,6,2005,NULL,'Lima sahabat menantang diri mendaki Mahameru demi membuktikan arti persahabatan dan mimpi.',NULL,NULL,75000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(50,NULL,'Lupus: Tangkaplah Daku Kau Kujitak','Hilman Hariwijaya',2,6,1986,NULL,'Petualangan jenaka Lupus, remaja Jakarta yang gemar mengunyah permen karet, di sekolah dan di rumah.',NULL,NULL,55000,1,'2026-11-17 15:58:37','2026-11-17 15:58:37');
/*!40000 ALTER TABLE `buku` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `denda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `denda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengembalian_id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `jenis` enum('terlambat','rusak','hilang') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `status` enum('belum_lunas','lunas','dibebaskan') NOT NULL DEFAULT 'belum_lunas',
  `tgl_bayar` date DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `no_kuitansi` varchar(20) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_denda_status` (`status`),
  KEY `idx_denda_kuitansi` (`no_kuitansi`),
  KEY `fk_denda_pengembalian` (`pengembalian_id`),
  KEY `fk_denda_anggota` (`anggota_id`),
  KEY `fk_denda_petugas` (`petugas_id`),
  CONSTRAINT `fk_denda_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `fk_denda_pengembalian` FOREIGN KEY (`pengembalian_id`) REFERENCES `pengembalian` (`id`),
  CONSTRAINT `fk_denda_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `denda` WRITE;
/*!40000 ALTER TABLE `denda` DISABLE KEYS */;
INSERT INTO `denda` VALUES
(1,1,2,'terlambat',3000,'belum_lunas',NULL,NULL,NULL,'Terlambat 3 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(2,2,2,'terlambat',3000,'belum_lunas',NULL,NULL,NULL,'Terlambat 3 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(3,9,12,'terlambat',1000,'belum_lunas',NULL,NULL,NULL,'Terlambat 1 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(4,28,9,'terlambat',2000,'belum_lunas',NULL,NULL,NULL,'Terlambat 2 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(5,30,9,'terlambat',1000,'belum_lunas',NULL,NULL,NULL,'Terlambat 1 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(6,37,20,'terlambat',2000,'lunas','2026-10-12',2,'BYR-20261012-0001','Terlambat 2 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(7,39,20,'rusak',39500,'lunas','2026-10-14',2,'BYR-20261014-0001','Buku rusak (50% dari Rp79.000)','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(8,41,14,'terlambat',2000,'lunas','2026-10-13',2,'BYR-20261013-0001','Terlambat 2 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(9,42,17,'terlambat',1000,'lunas','2026-10-13',2,'BYR-20261013-0002','Terlambat 1 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(10,44,4,'terlambat',19000,'belum_lunas',NULL,NULL,NULL,'Terlambat 19 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(11,47,5,'terlambat',2000,'lunas','2026-10-18',2,'BYR-20261018-0001','Terlambat 2 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(12,50,15,'terlambat',7000,'lunas','2026-10-21',3,'BYR-20261021-0001','Terlambat 7 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(13,51,19,'terlambat',5000,'belum_lunas',NULL,NULL,NULL,'Terlambat 5 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(14,53,17,'terlambat',4000,'lunas','2026-10-20',3,'BYR-20261020-0001','Terlambat 4 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(15,54,5,'terlambat',2000,'lunas','2026-10-19',3,'BYR-20261019-0001','Terlambat 2 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(16,56,13,'terlambat',20000,'belum_lunas',NULL,NULL,NULL,'Terlambat 20 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(17,57,18,'terlambat',1000,'belum_lunas',NULL,NULL,NULL,'Terlambat 1 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(18,58,10,'terlambat',19000,'belum_lunas',NULL,NULL,NULL,'Terlambat 19 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(19,59,3,'terlambat',1000,'lunas','2026-10-22',3,'BYR-20261022-0001','Terlambat 1 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(20,62,17,'terlambat',3000,'lunas','2026-10-24',3,'BYR-20261024-0001','Terlambat 3 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(21,63,20,'terlambat',17000,'lunas','2026-10-26',2,'BYR-20261026-0001','Terlambat 17 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(22,64,7,'terlambat',15000,'belum_lunas',NULL,NULL,NULL,'Terlambat 15 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(23,71,3,'terlambat',3000,'belum_lunas',NULL,NULL,NULL,'Terlambat 3 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(24,72,16,'terlambat',2000,'lunas','2026-10-31',2,'BYR-20261031-0001','Terlambat 2 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(25,73,19,'terlambat',11000,'lunas','2026-11-01',2,'BYR-20261101-0001','Terlambat 11 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(26,75,5,'terlambat',1000,'lunas','2026-11-06',3,'BYR-20261106-0001','Terlambat 1 hari x Rp1.000','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(27,83,17,'terlambat',2000,'belum_lunas',NULL,NULL,NULL,'Terlambat 2 hari x Rp1.000','2026-11-17 15:58:47','2026-11-17 15:58:47');
/*!40000 ALTER TABLE `denda` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `eksemplar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `eksemplar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buku_id` int(11) NOT NULL,
  `rak_id` int(11) DEFAULT NULL,
  `kode_eksemplar` varchar(20) NOT NULL,
  `kondisi` enum('baik','cukup','buruk') NOT NULL DEFAULT 'baik',
  `status` enum('tersedia','dipinjam','direservasi','rusak','hilang') NOT NULL DEFAULT 'tersedia',
  `tgl_pengadaan` date DEFAULT NULL,
  `sumber` enum('beli','hibah') NOT NULL DEFAULT 'beli',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_eksemplar` (`kode_eksemplar`),
  KEY `idx_eksemplar_status` (`status`),
  KEY `fk_eksemplar_buku` (`buku_id`),
  KEY `fk_eksemplar_rak` (`rak_id`),
  CONSTRAINT `fk_eksemplar_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`),
  CONSTRAINT `fk_eksemplar_rak` FOREIGN KEY (`rak_id`) REFERENCES `rak` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `eksemplar` WRITE;
/*!40000 ALTER TABLE `eksemplar` DISABLE KEYS */;
INSERT INTO `eksemplar` VALUES
(1,1,1,'BK0001-01','baik','tersedia','2024-06-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(2,1,1,'BK0001-02','baik','tersedia','2024-06-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(3,1,1,'BK0001-03','baik','tersedia','2024-06-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(4,1,1,'BK0001-04','baik','tersedia','2024-06-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(5,2,3,'BK0002-01','baik','tersedia','2025-09-17','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(6,2,3,'BK0002-02','baik','tersedia','2025-09-17','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(7,3,3,'BK0003-01','baik','dipinjam','2024-12-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(8,4,1,'BK0004-01','baik','tersedia','2025-09-27','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(9,4,1,'BK0004-02','baik','rusak','2025-09-27','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(10,4,1,'BK0004-03','baik','tersedia','2025-09-27','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(11,4,1,'BK0004-04','baik','tersedia','2025-09-27','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(12,5,1,'BK0005-01','baik','tersedia','2025-08-15','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(13,5,1,'BK0005-02','baik','tersedia','2025-08-15','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(14,6,1,'BK0006-01','baik','tersedia','2024-09-08','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(15,6,1,'BK0006-02','baik','tersedia','2024-09-08','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(16,7,1,'BK0007-01','baik','tersedia','2024-11-26','hibah','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(17,7,1,'BK0007-02','baik','tersedia','2024-11-26','hibah','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(18,7,1,'BK0007-03','baik','tersedia','2024-11-26','hibah','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(19,7,1,'BK0007-04','baik','tersedia','2024-11-26','hibah','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(20,8,1,'BK0008-01','baik','tersedia','2025-03-08','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(21,8,1,'BK0008-02','baik','tersedia','2025-03-08','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(22,8,1,'BK0008-03','baik','tersedia','2025-03-08','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(23,8,1,'BK0008-04','baik','tersedia','2025-03-08','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(24,9,1,'BK0009-01','baik','tersedia','2025-12-20','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(25,9,1,'BK0009-02','baik','tersedia','2025-12-20','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(26,10,1,'BK0010-01','baik','tersedia','2025-03-26','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(27,10,1,'BK0010-02','baik','tersedia','2025-03-26','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(28,10,1,'BK0010-03','baik','tersedia','2025-03-26','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(29,11,1,'BK0011-01','baik','tersedia','2024-10-28','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(30,11,1,'BK0011-02','baik','tersedia','2024-10-28','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(31,11,1,'BK0011-03','baik','tersedia','2024-10-28','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(32,11,1,'BK0011-04','baik','tersedia','2024-10-28','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(33,12,1,'BK0012-01','baik','tersedia','2024-10-10','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(34,12,1,'BK0012-02','baik','tersedia','2024-10-10','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(35,13,1,'BK0013-01','baik','tersedia','2025-04-05','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(36,13,1,'BK0013-02','baik','tersedia','2025-04-05','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(37,14,1,'BK0014-01','baik','tersedia','2026-04-18','hibah','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(38,14,1,'BK0014-02','baik','tersedia','2026-04-18','hibah','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(39,15,1,'BK0015-01','baik','dipinjam','2025-11-01','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(40,15,1,'BK0015-02','baik','tersedia','2025-11-01','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(41,15,1,'BK0015-03','baik','tersedia','2025-11-01','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(42,15,1,'BK0015-04','baik','tersedia','2025-11-01','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(43,16,1,'BK0016-01','baik','tersedia','2025-11-15','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(44,16,1,'BK0016-02','baik','dipinjam','2025-11-15','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(45,17,1,'BK0017-01','baik','dipinjam','2024-07-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(46,17,1,'BK0017-02','baik','tersedia','2024-07-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(47,17,1,'BK0017-03','baik','tersedia','2024-07-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(48,17,1,'BK0017-04','baik','tersedia','2024-07-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(49,18,1,'BK0018-01','baik','direservasi','2025-08-27','beli','2026-11-17 15:58:37','2026-11-17 15:58:47'),
(50,19,1,'BK0019-01','baik','tersedia','2025-12-04','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(51,19,1,'BK0019-02','baik','tersedia','2025-12-04','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(52,20,3,'BK0020-01','baik','tersedia','2024-08-16','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(53,20,3,'BK0020-02','baik','tersedia','2024-08-16','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(54,21,3,'BK0021-01','baik','tersedia','2025-10-14','hibah','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(55,21,3,'BK0021-02','baik','tersedia','2025-10-14','hibah','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(56,22,3,'BK0022-01','baik','tersedia','2025-09-13','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(57,23,3,'BK0023-01','baik','tersedia','2025-09-13','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(58,23,3,'BK0023-02','baik','tersedia','2025-09-13','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(59,24,3,'BK0024-01','baik','tersedia','2025-12-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(60,24,3,'BK0024-02','baik','tersedia','2025-12-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(61,25,3,'BK0025-01','baik','tersedia','2025-04-22','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(62,26,3,'BK0026-01','baik','tersedia','2024-10-17','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(63,26,3,'BK0026-02','baik','tersedia','2024-10-17','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(64,27,3,'BK0027-01','baik','tersedia','2024-09-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(65,27,3,'BK0027-02','baik','tersedia','2024-09-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(66,28,3,'BK0028-01','baik','tersedia','2025-12-01','hibah','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(67,29,4,'BK0029-01','baik','tersedia','2024-11-22','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(68,29,4,'BK0029-02','baik','tersedia','2024-11-22','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(69,30,4,'BK0030-01','baik','tersedia','2024-12-20','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(70,30,4,'BK0030-02','baik','tersedia','2024-12-20','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(71,30,4,'BK0030-03','baik','tersedia','2024-12-20','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(72,30,4,'BK0030-04','baik','tersedia','2024-12-20','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(73,31,4,'BK0031-01','baik','tersedia','2025-04-24','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(74,31,4,'BK0031-02','baik','tersedia','2025-04-24','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(75,32,4,'BK0032-01','baik','tersedia','2025-05-28','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(76,32,4,'BK0032-02','baik','dipinjam','2025-05-28','beli','2026-11-17 15:58:37','2026-11-17 15:58:47'),
(77,33,4,'BK0033-01','baik','tersedia','2026-04-07','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(78,33,4,'BK0033-02','baik','tersedia','2026-04-07','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(79,33,4,'BK0033-03','baik','tersedia','2026-04-07','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(80,34,4,'BK0034-01','baik','tersedia','2025-11-13','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(81,34,4,'BK0034-02','baik','tersedia','2025-11-13','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(82,35,4,'BK0035-01','baik','tersedia','2025-03-17','hibah','2026-11-17 15:58:37','2026-11-17 15:58:47'),
(83,35,4,'BK0035-02','baik','tersedia','2025-03-17','hibah','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(84,36,5,'BK0036-01','baik','tersedia','2024-10-09','beli','2026-11-17 15:58:37','2026-11-17 15:58:47'),
(85,36,5,'BK0036-02','baik','tersedia','2024-10-09','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(86,37,5,'BK0037-01','baik','tersedia','2024-06-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(87,37,5,'BK0037-02','baik','tersedia','2024-06-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(88,37,5,'BK0037-03','baik','tersedia','2024-06-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(89,37,5,'BK0037-04','baik','tersedia','2024-06-23','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(90,38,5,'BK0038-01','baik','tersedia','2025-10-22','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(91,38,5,'BK0038-02','baik','dipinjam','2025-10-22','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(92,39,5,'BK0039-01','baik','dipinjam','2025-02-25','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(93,40,5,'BK0040-01','baik','dipinjam','2025-02-10','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(94,40,5,'BK0040-02','baik','tersedia','2025-02-10','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(95,41,5,'BK0041-01','baik','tersedia','2026-01-18','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(96,41,5,'BK0041-02','baik','tersedia','2026-01-18','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(97,42,5,'BK0042-01','baik','tersedia','2024-11-08','hibah','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(98,42,5,'BK0042-02','baik','tersedia','2024-11-08','hibah','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(99,43,6,'BK0043-01','baik','dipinjam','2024-10-03','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(100,43,6,'BK0043-02','baik','tersedia','2024-10-03','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(101,43,6,'BK0043-03','baik','tersedia','2024-10-03','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(102,43,6,'BK0043-04','baik','tersedia','2024-10-03','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(103,44,6,'BK0044-01','baik','tersedia','2024-06-21','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(104,44,6,'BK0044-02','baik','tersedia','2024-06-21','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(105,44,6,'BK0044-03','baik','tersedia','2024-06-21','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(106,44,6,'BK0044-04','baik','tersedia','2024-06-21','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(107,45,6,'BK0045-01','baik','tersedia','2025-03-19','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(108,45,6,'BK0045-02','baik','tersedia','2025-03-19','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(109,46,6,'BK0046-01','baik','tersedia','2025-12-04','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(110,46,6,'BK0046-02','baik','tersedia','2025-12-04','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(111,46,6,'BK0046-03','baik','tersedia','2025-12-04','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(112,47,2,'BK0047-01','baik','tersedia','2024-09-13','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(113,47,2,'BK0047-02','baik','tersedia','2024-09-13','beli','2026-11-17 15:58:37','2026-11-17 15:58:47'),
(114,48,2,'BK0048-01','baik','tersedia','2024-06-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(115,48,2,'BK0048-02','baik','tersedia','2024-06-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(116,48,2,'BK0048-03','baik','tersedia','2024-06-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(117,48,2,'BK0048-04','baik','tersedia','2024-06-06','beli','2026-11-17 15:58:37','2026-11-17 15:58:47'),
(118,49,2,'BK0049-01','baik','tersedia','2026-03-14','hibah','2026-11-17 15:58:37','2026-11-17 15:58:46'),
(119,49,2,'BK0049-02','baik','tersedia','2026-03-14','hibah','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(120,50,2,'BK0050-01','baik','tersedia','2026-02-12','beli','2026-11-17 15:58:37','2026-11-17 15:58:37');
/*!40000 ALTER TABLE `eksemplar` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES
(1,'NOV','Novel & Fiksi','Novel dan cerita fiksi karya penulis Indonesia masa kini.',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(2,'SKL','Sastra Klasik Indonesia','Karya sastra Indonesia yang menjadi bacaan wajib lintas generasi.',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(3,'SEJ','Sejarah & Biografi','Sejarah Indonesia dan dunia serta kisah hidup tokoh.',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(4,'AGM','Agama & Filsafat','Buku keagamaan, pemikiran, dan filsafat.',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(5,'PDB','Pengembangan Diri & Bisnis','Kebiasaan, keuangan pribadi, dan pengembangan diri.',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(6,'REM','Remaja & Humor','Bacaan ringan untuk remaja dan cerita jenaka.',1,'2026-11-17 15:58:37','2026-11-17 15:58:37');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `peminjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_transaksi` varchar(20) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `status` enum('aktif','selesai') NOT NULL DEFAULT 'aktif',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_transaksi` (`no_transaksi`),
  KEY `idx_peminjaman_tgl` (`tgl_pinjam`),
  KEY `fk_peminjaman_anggota` (`anggota_id`),
  KEY `fk_peminjaman_petugas` (`petugas_id`),
  CONSTRAINT `fk_peminjaman_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `fk_peminjaman_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `peminjaman` WRITE;
/*!40000 ALTER TABLE `peminjaman` DISABLE KEYS */;
INSERT INTO `peminjaman` VALUES
(1,'PJM-20261028-0001',2,2,'2026-10-28','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(2,'PJM-20261103-0001',2,3,'2026-11-03','aktif','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(3,'PJM-20260918-0001',12,3,'2026-09-18','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(4,'PJM-20260918-0002',12,3,'2026-09-18','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(5,'PJM-20260918-0003',17,3,'2026-09-18','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(6,'PJM-20260918-0004',4,3,'2026-09-18','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(7,'PJM-20260919-0001',9,3,'2026-09-19','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(8,'PJM-20260921-0001',10,2,'2026-09-21','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(9,'PJM-20260922-0001',14,2,'2026-09-22','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(10,'PJM-20260922-0002',10,2,'2026-09-22','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(11,'PJM-20260922-0003',9,2,'2026-09-22','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(12,'PJM-20260922-0004',6,2,'2026-09-22','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(13,'PJM-20260923-0001',15,2,'2026-09-23','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(14,'PJM-20260923-0002',13,2,'2026-09-23','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(15,'PJM-20260923-0003',15,2,'2026-09-23','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(16,'PJM-20260924-0001',16,3,'2026-09-24','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(17,'PJM-20260924-0002',6,3,'2026-09-24','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(18,'PJM-20260925-0001',14,2,'2026-09-25','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(19,'PJM-20260925-0002',10,2,'2026-09-25','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(20,'PJM-20260925-0003',9,2,'2026-09-25','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(21,'PJM-20260927-0001',10,3,'2026-09-27','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(22,'PJM-20260927-0002',15,3,'2026-09-27','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(23,'PJM-20260928-0001',10,3,'2026-09-28','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(24,'PJM-20260928-0002',9,3,'2026-09-28','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(25,'PJM-20260929-0001',17,2,'2026-09-29','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(26,'PJM-20260930-0001',5,3,'2026-09-30','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(27,'PJM-20260930-0002',16,3,'2026-09-30','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(28,'PJM-20260930-0003',19,3,'2026-09-30','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(29,'PJM-20260930-0004',11,3,'2026-09-30','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(30,'PJM-20261001-0001',20,2,'2026-10-01','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(31,'PJM-20261001-0002',18,2,'2026-10-01','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(32,'PJM-20261002-0001',20,3,'2026-10-02','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(33,'PJM-20261003-0001',7,2,'2026-10-03','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(34,'PJM-20261004-0001',15,2,'2026-10-04','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(35,'PJM-20261004-0002',14,2,'2026-10-04','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(36,'PJM-20261005-0001',17,2,'2026-10-05','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(37,'PJM-20261006-0001',19,2,'2026-10-06','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(38,'PJM-20261007-0001',19,2,'2026-10-07','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(39,'PJM-20261007-0002',20,2,'2026-10-07','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(40,'PJM-20261007-0003',5,2,'2026-10-07','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(41,'PJM-20261008-0001',15,3,'2026-10-08','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(42,'PJM-20261008-0002',17,3,'2026-10-08','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(43,'PJM-20261008-0003',18,3,'2026-10-08','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(44,'PJM-20261008-0004',19,3,'2026-10-08','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(45,'PJM-20261010-0001',5,3,'2026-10-10','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(46,'PJM-20261012-0001',3,2,'2026-10-12','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(47,'PJM-20261012-0002',11,2,'2026-10-12','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(48,'PJM-20261012-0003',18,2,'2026-10-12','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(49,'PJM-20261013-0001',3,2,'2026-10-13','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(50,'PJM-20261014-0001',17,2,'2026-10-14','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(51,'PJM-20261014-0002',17,2,'2026-10-14','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(52,'PJM-20261015-0001',16,3,'2026-10-15','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(53,'PJM-20261019-0001',3,3,'2026-10-19','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(54,'PJM-20261021-0001',14,3,'2026-10-21','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(55,'PJM-20261021-0002',5,3,'2026-10-21','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(56,'PJM-20261021-0003',15,3,'2026-10-21','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(57,'PJM-20261021-0004',16,3,'2026-10-21','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(58,'PJM-20261023-0001',3,3,'2026-10-23','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(59,'PJM-20261025-0001',11,2,'2026-10-25','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(60,'PJM-20261026-0001',5,2,'2026-10-26','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(61,'PJM-20261028-0002',20,2,'2026-10-28','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(62,'PJM-20261029-0001',5,3,'2026-10-29','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(63,'PJM-20261029-0002',6,3,'2026-10-29','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(64,'PJM-20261102-0001',8,2,'2026-11-02','aktif','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(65,'PJM-20261103-0002',8,3,'2026-11-03','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(66,'PJM-20261106-0001',17,3,'2026-11-06','aktif','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(67,'PJM-20261108-0001',11,3,'2026-11-08','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(68,'PJM-20261109-0001',16,3,'2026-11-09','aktif','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(69,'PJM-20261109-0002',11,3,'2026-11-09','aktif','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(70,'PJM-20261110-0001',20,2,'2026-11-10','aktif','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(71,'PJM-20261111-0001',17,3,'2026-11-11','selesai','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(72,'PJM-20261112-0001',20,2,'2026-11-12','aktif','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(73,'PJM-20261113-0001',16,3,'2026-11-13','selesai','2026-11-17 15:58:47','2026-11-17 15:58:47'),
(74,'PJM-20261115-0001',5,3,'2026-11-15','aktif','2026-11-17 15:58:47','2026-11-17 15:58:47'),
(75,'PJM-20261111-0002',5,2,'2026-11-11','selesai','2026-11-17 15:58:47','2026-11-17 15:58:47');
/*!40000 ALTER TABLE `peminjaman` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `peminjaman_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `peminjaman_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peminjaman_id` int(11) NOT NULL,
  `eksemplar_id` int(11) NOT NULL,
  `tgl_jatuh_tempo` date NOT NULL,
  `jumlah_perpanjangan` tinyint(4) NOT NULL DEFAULT 0,
  `denda_per_hari` int(11) NOT NULL,
  `status` enum('dipinjam','kembali') NOT NULL DEFAULT 'dipinjam',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_detail_jatuh_tempo` (`tgl_jatuh_tempo`),
  KEY `idx_detail_status` (`status`),
  KEY `fk_detail_peminjaman` (`peminjaman_id`),
  KEY `fk_detail_eksemplar` (`eksemplar_id`),
  CONSTRAINT `fk_detail_eksemplar` FOREIGN KEY (`eksemplar_id`) REFERENCES `eksemplar` (`id`),
  CONSTRAINT `fk_detail_peminjaman` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `peminjaman_detail` WRITE;
/*!40000 ALTER TABLE `peminjaman_detail` DISABLE KEYS */;
INSERT INTO `peminjaman_detail` VALUES
(1,1,5,'2026-11-04',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(2,1,6,'2026-11-04',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(3,2,7,'2026-11-10',0,1000,'dipinjam','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(4,3,24,'2026-09-25',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(5,4,79,'2026-09-25',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(6,5,89,'2026-09-25',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(7,6,70,'2026-09-25',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(8,6,107,'2026-10-02',1,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(9,7,52,'2026-09-26',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(10,7,94,'2026-09-26',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(11,8,71,'2026-09-28',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(12,8,103,'2026-09-28',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(13,9,75,'2026-09-29',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(14,9,96,'2026-09-29',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(15,10,92,'2026-09-29',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(16,11,87,'2026-10-06',1,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(17,12,43,'2026-09-29',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(18,13,27,'2026-09-30',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(19,14,81,'2026-09-30',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(20,15,112,'2026-09-30',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(21,16,73,'2026-10-01',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(22,17,116,'2026-10-01',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(23,18,86,'2026-10-02',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(24,19,64,'2026-10-02',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(25,20,20,'2026-10-02',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(26,21,66,'2026-10-04',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(27,22,65,'2026-10-04',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(28,23,52,'2026-10-05',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(29,24,118,'2026-10-05',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(30,25,44,'2026-10-06',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(31,26,110,'2026-10-07',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(32,27,45,'2026-10-07',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(33,28,42,'2026-10-07',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(34,29,10,'2026-10-07',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(35,30,73,'2026-10-08',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(36,30,112,'2026-10-08',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(37,31,11,'2026-10-08',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(38,32,56,'2026-10-09',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(39,33,53,'2026-10-10',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(40,33,80,'2026-10-10',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(41,34,8,'2026-10-11',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(42,34,28,'2026-10-18',1,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(43,35,25,'2026-10-11',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(44,35,82,'2026-10-11',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(45,36,44,'2026-10-12',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(46,37,83,'2026-10-13',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(47,38,114,'2026-10-21',1,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(48,39,9,'2026-10-21',1,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(49,40,71,'2026-10-14',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(50,40,108,'2026-10-14',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(51,41,54,'2026-10-15',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(52,42,46,'2026-10-15',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(53,43,86,'2026-10-15',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(54,43,105,'2026-10-15',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(55,44,58,'2026-10-15',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(56,45,21,'2026-10-17',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(57,46,91,'2026-10-19',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(58,47,53,'2026-10-19',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(59,48,37,'2026-10-26',1,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(60,48,96,'2026-10-19',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(61,49,55,'2026-10-20',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(62,50,70,'2026-10-21',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(63,51,59,'2026-10-21',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(64,52,88,'2026-10-22',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(65,53,19,'2026-11-02',1,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(66,53,50,'2026-10-26',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(67,54,104,'2026-10-28',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(68,55,82,'2026-10-28',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(69,56,75,'2026-10-28',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(70,57,32,'2026-10-28',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(71,57,36,'2026-10-28',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(72,58,22,'2026-10-30',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(73,59,15,'2026-11-01',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(74,59,75,'2026-11-01',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(75,60,59,'2026-11-02',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(76,61,26,'2026-11-04',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(77,62,48,'2026-11-12',1,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(78,63,63,'2026-11-05',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(79,64,44,'2026-11-09',0,1000,'dipinjam','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(80,65,30,'2026-11-10',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(81,65,68,'2026-11-10',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(82,66,92,'2026-11-13',0,1000,'dipinjam','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(83,66,117,'2026-11-13',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:47'),
(84,67,62,'2026-11-15',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(85,68,82,'2026-11-16',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:47'),
(86,68,99,'2026-11-16',0,1000,'dipinjam','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(87,69,84,'2026-11-16',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:47'),
(88,69,93,'2026-11-16',0,1000,'dipinjam','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(89,70,39,'2026-11-17',0,1000,'dipinjam','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(90,71,34,'2026-11-18',0,1000,'kembali','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(91,72,45,'2026-11-19',0,1000,'dipinjam','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(92,72,91,'2026-11-19',0,1000,'dipinjam','2026-11-17 15:58:46','2026-11-17 15:58:46'),
(93,73,113,'2026-11-20',0,1000,'kembali','2026-11-17 15:58:47','2026-11-17 15:58:47'),
(94,74,76,'2026-11-22',0,1000,'dipinjam','2026-11-17 15:58:47','2026-11-17 15:58:47'),
(95,75,49,'2026-11-18',0,1000,'kembali','2026-11-17 15:58:47','2026-11-17 15:58:47');
/*!40000 ALTER TABLE `peminjaman_detail` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `penerbit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `penerbit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `penerbit` WRITE;
/*!40000 ALTER TABLE `penerbit` DISABLE KEYS */;
INSERT INTO `penerbit` VALUES
(1,'Bentang Pustaka','Yogyakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(2,'Gramedia Pustaka Utama','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(3,'Republika Penerbit','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(4,'Kepustakaan Populer Gramedia','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(5,'Lentera Dipantara','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(6,'Balai Pustaka','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(7,'Penerbit Buku Kompas','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(8,'Serambi Ilmu Semesta','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(9,'THC Mandiri','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(10,'Yayasan Bung Karno','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(11,'LP3ES','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(12,'Narasi','Yogyakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(13,'Pro-U Media','Yogyakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(14,'Lentera Hati','Tangerang',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(15,'Mizan','Bandung',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(16,'Grasindo','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(17,'GagasMedia','Jakarta',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(18,'Pastel Books','Bandung',NULL,1,'2026-11-17 15:58:37','2026-11-17 15:58:37');
/*!40000 ALTER TABLE `penerbit` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kunci` varchar(50) NOT NULL,
  `nilai` varchar(255) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kunci` (`kunci`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pengaturan` WRITE;
/*!40000 ALTER TABLE `pengaturan` DISABLE KEYS */;
INSERT INTO `pengaturan` VALUES
(1,'maks_pinjam','3','Maksimal eksemplar yang dipinjam bersamaan per anggota','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(2,'lama_pinjam','7','Lama pinjam (hari) sejak tanggal pinjam','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(3,'denda_per_hari','1000','Denda keterlambatan per eksemplar per hari (Rp)','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(4,'masa_berlaku_anggota','365','Masa berlaku keanggotaan sejak verifikasi (hari)','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(5,'maks_perpanjangan','1','Maksimal perpanjangan per eksemplar per peminjaman','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(6,'lama_perpanjangan','7','Tambahan hari setiap perpanjangan','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(7,'maks_reservasi','2','Maksimal reservasi aktif per anggota','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(8,'batas_ambil_reservasi','2','Batas hari mengambil buku reservasi sejak siap','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(9,'denda_rusak_persen','50','Denda buku rusak (% dari harga buku)','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(10,'denda_hilang_persen','100','Denda buku hilang (% dari harga buku)','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(11,'nama_perpustakaan','Perpustakaan Umum Librani','Nama perpustakaan pada kop laporan','2026-11-17 15:58:37','2026-11-17 15:58:37'),
(12,'alamat_perpustakaan','Jl. Merdeka No. 1','Alamat perpustakaan pada kop laporan','2026-11-17 15:58:37','2026-11-17 15:58:37');
/*!40000 ALTER TABLE `pengaturan` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pengembalian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengembalian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_pengembalian` varchar(20) NOT NULL,
  `peminjaman_detail_id` int(11) NOT NULL,
  `tgl_kembali` date NOT NULL,
  `kondisi` enum('baik','rusak','hilang') NOT NULL DEFAULT 'baik',
  `hari_terlambat` int(11) NOT NULL DEFAULT 0,
  `petugas_id` int(11) NOT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `peminjaman_detail_id` (`peminjaman_detail_id`),
  KEY `idx_pengembalian_no` (`no_pengembalian`),
  KEY `idx_pengembalian_tgl` (`tgl_kembali`),
  KEY `fk_pengembalian_petugas` (`petugas_id`),
  CONSTRAINT `fk_pengembalian_detail` FOREIGN KEY (`peminjaman_detail_id`) REFERENCES `peminjaman_detail` (`id`),
  CONSTRAINT `fk_pengembalian_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pengembalian` WRITE;
/*!40000 ALTER TABLE `pengembalian` DISABLE KEYS */;
INSERT INTO `pengembalian` VALUES
(1,'KMB-20261107-0001',1,'2026-11-07','baik',3,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(2,'KMB-20261107-0001',2,'2026-11-07','baik',3,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(3,'KMB-20260923-0001',11,'2026-09-23','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(4,'KMB-20260924-0001',5,'2026-09-24','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(5,'KMB-20260924-0002',6,'2026-09-24','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(6,'KMB-20260924-0003',9,'2026-09-24','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(7,'KMB-20260925-0001',10,'2026-09-25','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(8,'KMB-20260925-0002',20,'2026-09-25','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(9,'KMB-20260926-0001',4,'2026-09-26','baik',1,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(10,'KMB-20260926-0002',8,'2026-09-26','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(11,'KMB-20260926-0003',15,'2026-09-26','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(12,'KMB-20260926-0004',17,'2026-09-26','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(13,'KMB-20260926-0005',21,'2026-09-26','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(14,'KMB-20260927-0001',12,'2026-09-27','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(15,'KMB-20260927-0002',13,'2026-09-27','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(16,'KMB-20260927-0003',14,'2026-09-27','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(17,'KMB-20260927-0004',22,'2026-09-27','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(18,'KMB-20260929-0001',18,'2026-09-29','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(19,'KMB-20260930-0001',23,'2026-09-30','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(20,'KMB-20260930-0002',26,'2026-09-30','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(21,'KMB-20260930-0003',28,'2026-09-30','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(22,'KMB-20261001-0001',16,'2026-10-01','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(23,'KMB-20261002-0001',31,'2026-10-02','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(24,'KMB-20261002-0002',33,'2026-10-02','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(25,'KMB-20261003-0001',27,'2026-10-03','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(26,'KMB-20261003-0002',30,'2026-10-03','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(27,'KMB-20261003-0003',36,'2026-10-03','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(28,'KMB-20261004-0001',25,'2026-10-04','baik',2,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(29,'KMB-20261005-0001',32,'2026-10-05','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(30,'KMB-20261006-0001',29,'2026-10-06','baik',1,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(31,'KMB-20261006-0002',34,'2026-10-06','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(32,'KMB-20261008-0001',37,'2026-10-08','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(33,'KMB-20261010-0001',39,'2026-10-10','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(34,'KMB-20261010-0002',43,'2026-10-10','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(35,'KMB-20261010-0003',49,'2026-10-10','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(36,'KMB-20261010-0004',51,'2026-10-10','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(37,'KMB-20261011-0001',38,'2026-10-11','baik',2,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(38,'KMB-20261011-0002',42,'2026-10-11','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(39,'KMB-20261011-0003',48,'2026-10-11','rusak',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(40,'KMB-20261012-0001',53,'2026-10-12','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(41,'KMB-20261013-0001',44,'2026-10-13','baik',2,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(42,'KMB-20261013-0002',45,'2026-10-13','baik',1,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(43,'KMB-20261013-0003',54,'2026-10-13','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(44,'KMB-20261014-0001',7,'2026-10-14','baik',19,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(45,'KMB-20261014-0002',55,'2026-10-14','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(46,'KMB-20261014-0003',58,'2026-10-14','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(47,'KMB-20261016-0001',50,'2026-10-16','baik',2,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(48,'KMB-20261017-0001',57,'2026-10-17','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(49,'KMB-20261017-0002',62,'2026-10-17','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(50,'KMB-20261018-0001',41,'2026-10-18','baik',7,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(51,'KMB-20261018-0002',46,'2026-10-18','baik',5,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(52,'KMB-20261018-0003',64,'2026-10-18','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(53,'KMB-20261019-0001',52,'2026-10-19','baik',4,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(54,'KMB-20261019-0002',56,'2026-10-19','baik',2,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(55,'KMB-20261019-0003',59,'2026-10-19','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(56,'KMB-20261020-0001',19,'2026-10-20','baik',20,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(57,'KMB-20261020-0002',60,'2026-10-20','baik',1,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(58,'KMB-20261021-0001',24,'2026-10-21','baik',19,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(59,'KMB-20261021-0002',61,'2026-10-21','baik',1,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(60,'KMB-20261023-0001',69,'2026-10-23','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(61,'KMB-20261023-0002',70,'2026-10-23','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(62,'KMB-20261024-0001',63,'2026-10-24','baik',3,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(63,'KMB-20261025-0001',35,'2026-10-25','baik',17,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(64,'KMB-20261025-0002',40,'2026-10-25','baik',15,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(65,'KMB-20261025-0003',67,'2026-10-25','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(66,'KMB-20261026-0001',65,'2026-10-26','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(67,'KMB-20261026-0002',68,'2026-10-26','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(68,'KMB-20261027-0001',72,'2026-10-27','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(69,'KMB-20261028-0001',73,'2026-10-28','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(70,'KMB-20261028-0002',74,'2026-10-28','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(71,'KMB-20261029-0001',66,'2026-10-29','baik',3,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(72,'KMB-20261030-0001',71,'2026-10-30','baik',2,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(73,'KMB-20261101-0001',47,'2026-11-01','baik',11,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(74,'KMB-20261101-0002',76,'2026-11-01','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(75,'KMB-20261103-0001',75,'2026-11-03','baik',1,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(76,'KMB-20261103-0002',78,'2026-11-03','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(77,'KMB-20261105-0001',80,'2026-11-05','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(78,'KMB-20261107-0002',81,'2026-11-07','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(79,'KMB-20261108-0001',77,'2026-11-08','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(80,'KMB-20261110-0001',84,'2026-11-10','baik',0,2,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(81,'KMB-20261113-0001',90,'2026-11-13','baik',0,3,NULL,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(82,'KMB-20261114-0001',85,'2026-11-14','baik',0,3,NULL,'2026-11-17 15:58:47','2026-11-17 15:58:47'),
(83,'KMB-20261115-0001',83,'2026-11-15','baik',2,3,NULL,'2026-11-17 15:58:47','2026-11-17 15:58:47'),
(84,'KMB-20261116-0001',87,'2026-11-16','baik',0,2,NULL,'2026-11-17 15:58:47','2026-11-17 15:58:47'),
(85,'KMB-20261117-0001',93,'2026-11-17','baik',0,3,NULL,'2026-11-17 15:58:47','2026-11-17 15:58:47'),
(86,'KMB-20261116-0002',95,'2026-11-16','baik',0,3,NULL,'2026-11-17 15:58:47','2026-11-17 15:58:47');
/*!40000 ALTER TABLE `pengembalian` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `perpanjangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `perpanjangan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peminjaman_detail_id` int(11) NOT NULL,
  `jatuh_tempo_lama` date NOT NULL,
  `jatuh_tempo_baru` date NOT NULL,
  `tgl_perpanjangan` date NOT NULL,
  `diajukan_oleh` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_perpanjangan_detail` (`peminjaman_detail_id`),
  KEY `fk_perpanjangan_user` (`diajukan_oleh`),
  CONSTRAINT `fk_perpanjangan_detail` FOREIGN KEY (`peminjaman_detail_id`) REFERENCES `peminjaman_detail` (`id`),
  CONSTRAINT `fk_perpanjangan_user` FOREIGN KEY (`diajukan_oleh`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `perpanjangan` WRITE;
/*!40000 ALTER TABLE `perpanjangan` DISABLE KEYS */;
INSERT INTO `perpanjangan` VALUES
(1,8,'2026-09-25','2026-10-02','2026-09-23',7,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(2,16,'2026-09-29','2026-10-06','2026-09-28',12,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(3,42,'2026-10-11','2026-10-18','2026-10-08',18,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(4,48,'2026-10-14','2026-10-21','2026-10-08',23,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(5,47,'2026-10-14','2026-10-21','2026-10-11',22,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(6,59,'2026-10-19','2026-10-26','2026-10-14',21,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(7,65,'2026-10-26','2026-11-02','2026-10-23',6,'2026-11-17 15:58:46','2026-11-17 15:58:46'),
(8,77,'2026-11-05','2026-11-12','2026-10-31',8,'2026-11-17 15:58:46','2026-11-17 15:58:46');
/*!40000 ALTER TABLE `perpanjangan` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `rak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rak` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `lantai` tinyint(4) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `rak` WRITE;
/*!40000 ALTER TABLE `rak` DISABLE KEYS */;
INSERT INTO `rak` VALUES
(1,'A1','Rak Novel & Fiksi',1,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(2,'A2','Rak Remaja & Humor',1,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(3,'B1','Rak Sastra Klasik',1,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(4,'B2','Rak Sejarah & Biografi',2,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(5,'C1','Rak Agama & Filsafat',2,1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(6,'C2','Rak Pengembangan Diri',2,1,'2026-11-17 15:58:37','2026-11-17 15:58:37');
/*!40000 ALTER TABLE `rak` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `reservasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `eksemplar_id` int(11) DEFAULT NULL,
  `tgl_reservasi` datetime NOT NULL,
  `tgl_siap` date DEFAULT NULL,
  `tgl_batas_ambil` date DEFAULT NULL,
  `status` enum('menunggu','siap','diambil','batal','kadaluarsa') NOT NULL DEFAULT 'menunggu',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reservasi_buku_status` (`buku_id`,`status`),
  KEY `fk_reservasi_anggota` (`anggota_id`),
  KEY `fk_reservasi_eksemplar` (`eksemplar_id`),
  CONSTRAINT `fk_reservasi_anggota` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `fk_reservasi_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`),
  CONSTRAINT `fk_reservasi_eksemplar` FOREIGN KEY (`eksemplar_id`) REFERENCES `eksemplar` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `reservasi` WRITE;
/*!40000 ALTER TABLE `reservasi` DISABLE KEYS */;
INSERT INTO `reservasi` VALUES
(1,5,3,NULL,'2026-11-15 15:58:47',NULL,NULL,'menunggu','2026-11-17 15:58:47','2026-11-17 15:58:47'),
(2,6,18,49,'2026-11-12 15:58:47','2026-11-16','2026-11-18','siap','2026-11-17 15:58:47','2026-11-17 15:58:47');
/*!40000 ALTER TABLE `reservasi` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','petugas','anggota') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Administrator','admin','admin@librani.test','$2y$10$UP9EbSRJAm2/ickCM.UNmuHMkgVR7iEJM1QDts2zsLK.IWVxXqVKO','admin',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(2,'Siti Rahmawati','petugas1','petugas1@librani.test','$2y$12$yGqTSkl3kFyaRqPjB16ynuH3XaA1MMwKmNBZhqnsKCrscbo7jlhXG','petugas',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(3,'Andi Pratama','petugas2','petugas2@librani.test','$2y$12$MdcLxvd/ZEQ7wbY9nbXKcuANR3Xn3OPZH/uGzqaVST8zAy1ZV3Jci','petugas',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(4,'Budi Santoso','anggota1','anggota1@contoh.id','$2y$12$bwlfvxeO0/ZiTcuBya90XuOGMTSfijt3hRuVWMd3s8tdENPbVAapG','anggota',1,'2026-11-17 15:58:37','2026-11-17 15:58:37'),
(5,'Sari Wulandari','anggota2','anggota2@contoh.id','$2y$12$UeW8c39S2mqDcZ0HEeTdcO7PE/Y1ZRNoSnkQxKIlMZJ63QXXgjx7u','anggota',1,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(6,'Rina Kartika','rina','rina@contoh.id','$2y$12$CVfeEcUtdDAMYcusSm6Q.ed.61/uQYh3yC17hXKl8ppyQrg.y3rMq','anggota',1,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(7,'Dimas Saputra','dimas','dimas@contoh.id','$2y$12$t3WfSKRK8cDjznoMXy3.Ae/oKCDbpWVHPjIJ3j0dzUfHFcN/6D9M2','anggota',1,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(8,'Ayu Lestari','ayu','ayu@contoh.id','$2y$12$uTWXtzb13nQfpKAPPpBeWe6.iDDwiuu2bOjTcdAiXp1VUdvI3Bmk6','anggota',1,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(9,'Fajar Nugroho','fajar','fajar@contoh.id','$2y$12$lFFmXg5F6jwrFFWfKWruz.pmaQkhUpB1oOQ6pqdE5hDIN8UfFsB5C','anggota',1,'2026-11-17 15:58:38','2026-11-17 15:58:38'),
(10,'Putri Maharani','putri','putri@contoh.id','$2y$12$JTOUmarNycedmvS72Adk0.TjnssdAXIvn3fjAGQqGjZDnJPPyMsgK','anggota',1,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(11,'Reza Pratama','reza','reza@contoh.id','$2y$12$sQG4pMi4xmHWuT5BCLKAlO3YVpGGVbKnSQJA7uVfDw3MSYCwPSmLG','anggota',1,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(12,'Nadia Safitri','nadia','nadia@contoh.id','$2y$12$3r2jsvA9ZxxEguTt.n7AWubsME0EWRVwQiDdV/N4o.NBCdae1mexa','anggota',1,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(13,'Galih Permana','galih','galih@contoh.id','$2y$12$.KJA9KuyJKjPcBvWmzsfA.bpPk3E1i0OIdAJvqgiDPgqVEQBtPRPW','anggota',1,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(14,'Dewi Anggraini','dewi','dewi@contoh.id','$2y$12$4x60kWGjvQe.gWykyybme.SGZYF1cBfcvSTKDX.bCGsI08twTzfni','anggota',1,'2026-11-17 15:58:39','2026-11-17 15:58:39'),
(15,'Yoga Aditya','yoga','yoga@contoh.id','$2y$12$0OPnH88zipu0fxUqVYyfJup/aeoPQGHL8qiUL5xAOH8Aa8osiwH52','anggota',1,'2026-11-17 15:58:40','2026-11-17 15:58:40'),
(16,'Intan Permatasari','intan','intan@contoh.id','$2y$12$499roQyrBHQgXkU4q7rgdOyYlM/0.olesU2uDBkD4qHcLlNczBFIm','anggota',1,'2026-11-17 15:58:41','2026-11-17 15:58:41'),
(17,'Bayu Setiawan','bayu','bayu@contoh.id','$2y$12$eTjKFtJNjcSa98C79G8n0.5hdipB1GUKgU/kRuQy2gY2Iv4tShT5i','anggota',1,'2026-11-17 15:58:41','2026-11-17 15:58:41'),
(18,'Laras Wulandari','laras','laras@contoh.id','$2y$12$eR14PuAA7swT9eLx8CdLReTpDLcyPY.Sfx0tFRhxGRGwU678xmHJy','anggota',1,'2026-11-17 15:58:42','2026-11-17 15:58:42'),
(19,'Hendra Wijaya','hendra','hendra@contoh.id','$2y$12$N35cRoMev3RTu5ZDj.nStOIsThougOOG55tYqL9usNEdDF0b.DPUS','anggota',1,'2026-11-17 15:58:42','2026-11-17 15:58:42'),
(20,'Citra Kirana','citra','citra@contoh.id','$2y$12$ypzUDpBLBMPzpXhPbu5K7uFIPC3k4dR6Uv1vRrhfMgqK5XOk2AFoy','anggota',1,'2026-11-17 15:58:43','2026-11-17 15:58:43'),
(21,'Arif Rahman','arif','arif@contoh.id','$2y$12$/gav8GDrKCNsro8IWCwxA.ClG3KSFki5ao32orROXHc6j/u1Xgzeq','anggota',1,'2026-11-17 15:58:43','2026-11-17 15:58:43'),
(22,'Melati Puspita','melati','melati@contoh.id','$2y$12$q9n21hLbIPDZMmKr6n/qteyEPOOib1zKD2qtIDhT9mMTMxz47yAau','anggota',1,'2026-11-17 15:58:44','2026-11-17 15:58:44'),
(23,'Taufik Hidayat','taufik','taufik@contoh.id','$2y$12$lSq94P6M.kan7DAL7Iafx.YWx9rzhhEi5QDhwg.DqryDVnk7MhkVq','anggota',1,'2026-11-17 15:58:45','2026-11-17 15:58:45'),
(24,'Wulan Sari','wulan','wulan@contoh.id','$2y$12$ogiljT5LY9K6gd.ZjlK.z.18WbLb00g1AQUB4UCE9PtelkeYyWQku','anggota',1,'2026-11-17 15:58:45','2026-11-17 15:58:45'),
(25,'Irfan Maulana','irfan','irfan@contoh.id','$2y$12$mGC1PlSg3UB3dO/k/oo0buhDdwcM9Tbvilw4HZI4Tna6HJqffbYjG','anggota',1,'2026-11-17 15:58:46','2026-11-17 15:58:46');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

