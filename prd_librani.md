# PRD Librani — Sistem Informasi Perpustakaan

Nov 17, 2026 · @David Santoso

## 1. Ringkasan Produk

Librani adalah aplikasi web perpustakaan umum/daerah untuk mengelola koleksi buku per eksemplar (lengkap dengan foto sampul), keanggotaan, peminjaman, pengembalian, denda, dan laporan. Aplikasi dibangun dengan PHP native + MySQL, kodenya disimpan di GitHub, dan di-hosting gratis di InfinityFree. Proyek dikerjakan 1 orang dalam 1 minggu, dengan target semua fitur P0 online paling lambat hari ke-6, sebelum presentasi (±1 minggu dari sekarang).

| Atribut | Keterangan |
| --- | --- |
| Nama produk | Librani — Sistem Informasi Perpustakaan |
| Konteks | Perpustakaan umum/daerah (anggota = masyarakat umum) |
| Pengguna | Admin, Petugas, Anggota |
| Tim | 1 orang |
| Versi dokumen | 1.0 (draft) |
| Jenis proyek | Tugas kelompok — studi kasus CRUD |
| Repositori kode | GitHub, repo librani (lihat bagian 12) |

### Latar belakang

Banyak perpustakaan daerah kecil masih mencatat peminjaman di buku tulis atau Excel. Akibatnya petugas sulit mengetahui siapa meminjam eksemplar mana, kapan jatuh temponya, dan berapa denda yang belum dibayar. Laporan bulanan juga harus direkap manual.

### Masalah yang diselesaikan

- Status tiap eksemplar (tersedia, dipinjam, rusak, hilang) tidak terlacak.
- Keterlambatan dan denda dihitung manual, rawan salah dan tidak konsisten.
- Pendaftaran anggota tidak terverifikasi dan data anggota tersebar.
- Anggota tidak bisa mengecek ketersediaan buku atau riwayat pinjamnya sendiri.
- Laporan peminjaman, pengembalian, dan denda butuh waktu lama untuk direkap.

## 2. Tujuan dan Metrik Keberhasilan

Proyek dinilai berhasil jika keenam ketentuan tugas terpenuhi dan bisa didemokan langsung dari URL hosting tanpa error.

### Pemetaan ke ketentuan tugas

| No | Ketentuan tugas | Implementasi di Librani |
| --- | --- | --- |
| 1 | Menggunakan basis data | MySQL/MariaDB, 14 tabel berelasi (lihat bagian 10) |
| 2 | Login / register / dashboard | Login 3 role, register anggota online + verifikasi petugas, dashboard berbeda per role |
| 3 | File master | Kategori, Penerbit, Rak, Buku, Eksemplar, Anggota, User petugas |
| 4 | File transaksi | Peminjaman (header–detail), Pengembalian, Pembayaran Denda, Perpanjangan, Reservasi |
| 5 | File report | Laporan peminjaman, pengembalian, denda, keterlambatan, koleksi, anggota, rekap buku terpopuler |
| 6 | Hosting gratis | InfinityFree (subdomain gratis + SSL gratis) |

### Metrik keberhasilan

- 100% fitur P0 berjalan di URL hosting paling lambat hari ke-6.
- Satu transaksi peminjaman 3 buku selesai dalam waktu kurang dari 1 menit oleh petugas.
- Status eksemplar selalu konsisten dengan transaksi (tidak ada eksemplar "dipinjam" tanpa transaksi aktif).
- Denda keterlambatan dihitung otomatis dan cocok dengan hitungan manual pada data uji.
- Skenario demo (bagian 15) berjalan tanpa error dalam waktu kurang dari 10 menit.

## 3. Ruang Lingkup

P0 adalah semua yang dibutuhkan untuk memenuhi ketentuan tugas, dan wajib online paling lambat hari ke-6. P1 hanya dikerjakan di hari ke-7 jika P0 sudah stabil, berurutan sesuai nomor. Dengan 1 orang dan 1 minggu, yang realistis hanya P1 nomor 1 dan 2.

### P0 — Wajib

- Login, logout, dan hak akses untuk 3 role (Admin, Petugas, Anggota).
- Register anggota online dan verifikasi oleh petugas.
- Dashboard per role.
- CRUD master: Kategori, Penerbit, Rak, Buku, Eksemplar, Anggota, User petugas.
- Upload foto sampul untuk setiap buku, ditampilkan di katalog, detail buku, dan form peminjaman (bagian 6).
- Katalog buku berbentuk kartu bergambar dengan pencarian (halaman publik dan halaman anggota).
- Transaksi peminjaman (header–detail, banyak eksemplar per transaksi).
- Transaksi pengembalian, termasuk hitung denda keterlambatan otomatis.
- Pembayaran denda.
- Pengaturan aturan peminjaman oleh admin (bagian 9).
- Laporan dengan filter periode dan tampilan siap cetak (print browser).
- Kode sumber di repository GitHub dengan README dan file SQL (bagian 12).
- Deploy ke InfinityFree dengan SSL.

### P1 — Fitur plus (urutan pengerjaan)

1. Perpanjangan pinjaman.
2. Export laporan ke PDF dan Excel.
3. Barcode/QR untuk eksemplar dan kartu anggota (generate, cetak, scan).
4. Reservasi/booking buku dengan antrean.

### Di luar lingkup

- Pembayaran denda online (QRIS, transfer otomatis).
- Notifikasi email/WhatsApp jatuh tempo.
- Multi-cabang perpustakaan.
- E-book atau koleksi digital.
- Aplikasi mobile native.

## 4. Role Pengguna dan Hak Akses

Ada tiga role. Admin mengatur sistem, Petugas melayani transaksi harian, dan Anggota melayani dirinya sendiri. Akun admin pertama dibuat lewat data awal (seeder); akun petugas dibuat oleh admin.

| Role | Siapa | Tujuan utama |
| --- | --- | --- |
| Admin | Kepala/pengelola perpustakaan | Mengelola master referensi, user petugas, pengaturan, dan memantau semua laporan |
| Petugas | Staf layanan sirkulasi | Verifikasi anggota, mengelola buku/eksemplar, melayani pinjam, kembali, dan pembayaran denda |
| Anggota | Masyarakat umum terdaftar | Mencari buku, melihat pinjaman dan denda sendiri, perpanjang, reservasi |

### Matriks hak akses

| Modul | Admin | Petugas | Anggota |
| --- | --- | --- | --- |
| Dashboard | Ringkasan penuh | Ringkasan sirkulasi | Ringkasan pribadi |
| User petugas | CRUD | — | — |
| Kategori, Penerbit, Rak | CRUD | Lihat | — |
| Buku dan Eksemplar | CRUD | CRUD | Lihat katalog |
| Anggota | CRUD | CRUD + verifikasi | Edit profil sendiri |
| Peminjaman, Pengembalian | Lihat | Buat dan proses | Lihat milik sendiri |
| Pembayaran denda | Lihat | Proses | Lihat milik sendiri |
| Perpanjangan (P1) | Lihat | Proses | Ajukan milik sendiri |
| Reservasi (P1) | Lihat | Proses pengambilan | Buat dan batalkan milik sendiri |
| Laporan | Semua | Semua | — |
| Pengaturan aturan | Ubah | — | — |

Setiap halaman mengecek role di sisi server. Mengetik URL langsung ke halaman yang bukan haknya akan diarahkan ke halaman "403 — Akses ditolak".

## 5. Kebutuhan Fungsional — Autentikasi, Register, Dashboard

Anggota mendaftar sendiri, tetapi baru bisa meminjam setelah petugas memverifikasi datanya.

| ID | Kebutuhan | Kriteria penerimaan | Prioritas |
| --- | --- | --- | --- |
| FR-AUTH-01 | Login dengan username/email + password | Password salah menampilkan pesan umum; login berhasil diarahkan ke dashboard sesuai role | P0 |
| FR-AUTH-02 | Logout | Session dihapus; tombol Back tidak membuka halaman terproteksi | P0 |
| FR-AUTH-03 | Proteksi halaman per role | Akses tanpa login diarahkan ke login; role salah ke halaman 403 | P0 |
| FR-AUTH-04 | Ganti password dan edit profil | Wajib isi password lama; password baru minimal 8 karakter | P0 |
| FR-REG-01 | Form register anggota | Field: nama, no. identitas (KTP/kartu pelajar), jenis kelamin, tanggal lahir, alamat, no. HP, email, username, password, foto (opsional, jpg/png maks. 1 MB) | P0 |
| FR-REG-02 | Validasi register | Email, username, dan no. identitas unik; semua field wajib terisi kecuali foto | P0 |
| FR-REG-03 | Status awal "menunggu" | Anggota bisa login, tetapi hanya melihat pesan "Akun menunggu verifikasi" dan katalog | P0 |
| FR-REG-04 | Verifikasi oleh petugas | Petugas bisa Setujui atau Tolak (alasan wajib). Jika disetujui: status aktif, kode anggota dibuat otomatis (format AGT-2026-0001), masa berlaku 1 tahun | P0 |
| FR-REG-05 | Kartu anggota | Halaman kartu anggota siap cetak (nama, kode, masa berlaku); versi QR di P1 | P0 |

### Isi dashboard

**Admin dan Petugas**

- Kartu angka: total judul, total eksemplar, eksemplar sedang dipinjam, peminjaman hari ini, pinjaman terlambat, total denda belum lunas, anggota menunggu verifikasi.
- Grafik garis jumlah peminjaman 30 hari terakhir (Chart.js).
- Tabel "Jatuh tempo hari ini" dan "Terlambat" dengan tautan ke proses pengembalian.
- Admin juga melihat 5 buku terpopuler bulan ini beserta foto sampulnya.

**Anggota**

- Pinjaman aktif beserta tanggal jatuh tempo (merah jika terlambat).
- Sisa kuota pinjam (misal 1 dari 3).
- Total denda belum lunas.
- Status reservasi (P1).

## 6. Kebutuhan Fungsional — File Master

Ada tujuh file master. Buku menyimpan data judul, sedangkan Eksemplar menyimpan setiap fisik buku dengan kode dan statusnya sendiri.

| Master | Field utama | Aturan khusus | Prioritas |
| --- | --- | --- | --- |
| Kategori | kode, nama, deskripsi | Kode unik | P0 |
| Penerbit | nama, kota, telepon | — | P0 |
| Rak | kode rak, nama, lantai | Kode unik | P0 |
| Buku (judul) | ISBN, judul, penulis, penerbit, kategori, tahun terbit, jumlah halaman, sinopsis, foto sampul, harga | ISBN unik (boleh kosong untuk buku lama); harga dipakai untuk denda rusak/hilang | P0 |
| Eksemplar | kode eksemplar, buku, rak, kondisi, status, tanggal pengadaan, sumber (beli/hibah) | Kode dibuat otomatis (BK0001-01, BK0001-02, …); status hanya diubah oleh transaksi, kecuali ditandai rusak/hilang manual oleh petugas | P0 |
| Anggota | kode anggota, data diri, status, tanggal daftar, masa berlaku | Status: menunggu, aktif, ditolak, nonaktif | P0 |
| User petugas | nama, username, email, password, role, status aktif | Hanya admin; admin tidak bisa menghapus akunnya sendiri | P0 |

### Kebutuhan umum semua master

- Daftar data dengan pencarian, pengurutan kolom, dan paginasi 10/25/50 baris.
- Tambah dan ubah lewat form dengan validasi di server; pesan sukses/gagal muncul setelah simpan.
- Hapus dengan konfirmasi. Data yang sudah dipakai transaksi tidak bisa dihapus permanen; sebagai gantinya dinonaktifkan (soft delete).
- Saat menambah Buku, petugas mengisi "jumlah eksemplar" dan sistem membuat eksemplar sebanyak itu secara otomatis.
- Halaman detail Buku menampilkan semua eksemplarnya beserta status dan riwayat peminjaman.
- Katalog (publik dan anggota): cari berdasarkan judul, penulis, ISBN, atau kategori; menampilkan jumlah eksemplar tersedia.

### Foto buku

Setiap judul buku punya satu foto sampul yang muncul di semua halaman yang menampilkan buku. Tujuannya agar tampilan lebih menarik dan petugas bisa mencocokkan buku fisik dengan data saat peminjaman.

| ID | Kebutuhan | Kriteria penerimaan | Prioritas |
| --- | --- | --- | --- |
| FR-FOTO-01 | Upload foto sampul saat tambah/ubah buku | Format jpg, png, atau webp, maksimal 2 MB; pratinjau tampil sebelum disimpan | P0 |
| FR-FOTO-02 | Kompres dan thumbnail otomatis | Server menyimpan versi utama (lebar maks. 600 px) dan thumbnail (lebar 200 px) dengan library GD; nama file diacak. Jika GD tidak aktif di hosting, simpan file asli dengan batas 1 MB | P0 |
| FR-FOTO-03 | Gambar pengganti | Buku tanpa foto menampilkan placeholder berisi judul buku | P0 |
| FR-FOTO-04 | Ganti dan hapus foto | File lama dihapus dari server saat foto diganti | P0 |
| FR-FOTO-05 | Katalog bergambar | Grid kartu: foto sampul rasio 2:3, judul, penulis, jumlah eksemplar tersedia; gambar memakai lazy loading | P0 |
| FR-FOTO-06 | Foto saat peminjaman | Setiap eksemplar yang dipilih atau dipindai muncul sebagai kartu berisi thumbnail, judul, dan kode eksemplar | P0 |
| FR-FOTO-07 | Foto di halaman lain | Detail buku, pengembalian, riwayat pinjam anggota, dan dashboard buku terpopuler memakai thumbnail | P0 |

## 7. Kebutuhan Fungsional — File Transaksi

Ada lima transaksi. Setiap transaksi mengubah data master, terutama status eksemplar. Karena itu setiap proses simpan dijalankan dalam satu transaksi database (BEGIN … COMMIT/ROLLBACK).

| ID | Transaksi | Aktor | Efek ke data | Prioritas |
| --- | --- | --- | --- | --- |
| TR-01 | Peminjaman | Petugas | Eksemplar: tersedia → dipinjam | P0 |
| TR-02 | Pengembalian | Petugas | Eksemplar: dipinjam → tersedia / rusak / hilang / direservasi; denda dibuat jika terlambat | P0 |
| TR-03 | Pembayaran denda | Petugas | Denda: belum lunas → lunas | P0 |
| TR-04 | Perpanjangan | Anggota atau Petugas | Jatuh tempo bertambah | P1 |
| TR-05 | Reservasi | Anggota (buat), Petugas (serahkan) | Eksemplar yang kembali dikunci untuk pemesan | P1 |

### TR-01 Peminjaman (header–detail)

1. Petugas memilih anggota lewat kode, nama, atau scan kartu.
2. Sistem menolak jika anggota tidak aktif, masa berlaku habis, punya denda belum lunas, atau punya pinjaman yang sudah terlambat.
3. Petugas menambahkan eksemplar satu per satu (ketik atau scan kode). Hanya eksemplar berstatus "tersedia" yang bisa dipilih, atau "direservasi" jika reservasinya milik anggota ini.
4. Jumlah pinjaman aktif + eksemplar baru tidak boleh melebihi batas maksimal pinjam.
5. Saat disimpan: nomor transaksi dibuat otomatis (PJM-20261117-0001), tanggal pinjam = hari ini, jatuh tempo = hari ini + lama pinjam, status eksemplar menjadi "dipinjam".
6. Bukti peminjaman bisa dicetak.

### TR-02 Pengembalian

1. Petugas mencari pinjaman lewat nomor transaksi, kode anggota, atau scan kode eksemplar.
2. Petugas memilih eksemplar yang dikembalikan (boleh sebagian) dan kondisinya: baik, rusak, atau hilang.
3. Sistem menghitung hari terlambat dan denda secara otomatis (rumus di bagian 9) dan menampilkannya sebelum disimpan.
4. Saat disimpan: nomor pengembalian dibuat (KMB-20261117-0001), status eksemplar diperbarui, dan baris denda dibuat jika ada.
5. Jika judul tersebut punya reservasi menunggu (P1), eksemplar yang kembali dalam kondisi baik langsung berstatus "direservasi".
6. Header peminjaman berubah menjadi "selesai" saat semua eksemplarnya sudah kembali.

### TR-03 Pembayaran denda

- Petugas membuka daftar denda belum lunas per anggota, memilih satu atau beberapa denda, lalu menandainya lunas.
- Tercatat tanggal bayar, nominal, dan petugas penerima; kuitansi bisa dicetak.
- Denda tidak bisa dihapus, hanya bisa dilunasi atau dibebaskan oleh admin (dengan alasan).

### TR-04 Perpanjangan (P1)

- Hanya untuk eksemplar yang belum terlambat dan belum mencapai batas perpanjangan.
- Ditolak jika judul tersebut sedang direservasi anggota lain.
- Jatuh tempo baru = jatuh tempo lama + lama perpanjangan; riwayat perpanjangan tersimpan.

### TR-05 Reservasi (P1)

- Anggota hanya bisa mereservasi judul yang semua eksemplarnya sedang dipinjam.
- Antrean berdasarkan urutan waktu (siapa cepat dia dapat), maksimal reservasi aktif per anggota sesuai pengaturan.
- Saat eksemplar kembali, reservasi berubah menjadi "siap diambil" dengan batas waktu ambil.
- Reservasi yang lewat batas ambil otomatis kadaluarsa. Pengecekan dilakukan setiap kali halaman terkait dibuka, karena hosting gratis tidak menyediakan cron job.
- Anggota bisa membatalkan reservasinya sendiri selama belum diambil.

## 8. Kebutuhan Fungsional — File Report

Ada tujuh laporan: lima bersumber dari transaksi, dua dari master. Semuanya bisa difilter, menampilkan total di baris akhir, dan punya tampilan siap cetak (P0). Export PDF/Excel masuk P1.

| ID | Laporan | Isi | Filter | Sumber |
| --- | --- | --- | --- | --- |
| RPT-01 | Laporan Peminjaman | No. transaksi, tanggal, anggota, judul/eksemplar, jatuh tempo, status | Periode, anggota, kategori, status | Transaksi |
| RPT-02 | Laporan Pengembalian | No. pengembalian, tanggal kembali, jatuh tempo, hari terlambat, kondisi, denda | Periode, tepat waktu/terlambat | Transaksi |
| RPT-03 | Laporan Denda | Anggota, jenis denda, nominal, status, tanggal bayar; total lunas dan belum lunas | Periode, status, anggota | Transaksi |
| RPT-04 | Laporan Keterlambatan Aktif | Pinjaman yang lewat jatuh tempo per hari ini, lama terlambat, estimasi denda berjalan | Kategori, lama terlambat | Transaksi |
| RPT-05 | Rekap Buku Terpopuler dan Anggota Teraktif | 10 judul paling sering dipinjam dan 10 anggota paling aktif | Periode | Rekap transaksi |
| RPT-06 | Laporan Koleksi | Judul, jumlah eksemplar per status (tersedia, dipinjam, rusak, hilang), per kategori dan rak | Kategori, rak, status | Master |
| RPT-07 | Laporan Anggota | Kode, nama, tanggal daftar, masa berlaku, status, jumlah pinjaman | Status, periode daftar | Master |

### Format output

- Tampilan layar: tabel dengan kop laporan (nama perpustakaan, judul laporan, periode, tanggal cetak, petugas pencetak).
- Cetak (P0): halaman khusus cetak dengan CSS print; bisa disimpan sebagai PDF lewat menu print browser.
- Export PDF (P1): dibuat di server dengan Dompdf.
- Export Excel (P1): dibuat di browser dengan SheetJS (.xlsx), sehingga tidak membebani hosting.

## 9. Aturan Bisnis dan Pengaturan

Semua angka aturan disimpan di tabel pengaturan dan bisa diubah admin tanpa mengubah kode. Nilai yang berlaku disalin ke transaksi saat transaksi dibuat, jadi perubahan aturan tidak mengubah hitungan transaksi lama.

| Parameter | Default | Keterangan | Prioritas |
| --- | --- | --- | --- |
| maks\_pinjam | 3 eksemplar | Total pinjaman aktif per anggota | P0 |
| lama\_pinjam | 7 hari | Jatuh tempo dihitung dari tanggal pinjam | P0 |
| denda\_per\_hari | Rp1.000 | Per eksemplar per hari terlambat | P0 |
| masa\_berlaku\_anggota | 365 hari | Sejak verifikasi; bisa diperpanjang petugas | P0 |
| maks\_perpanjangan | 1 kali | Per eksemplar per peminjaman | P1 |
| lama\_perpanjangan | 7 hari | Ditambahkan ke jatuh tempo lama | P1 |
| maks\_reservasi | 2 judul | Reservasi aktif per anggota | P1 |
| batas\_ambil\_reservasi | 2 hari | Sejak status "siap diambil" | P1 |
| denda\_rusak | 50% harga buku | Opsional, aktif jika harga buku terisi | P1 |
| denda\_hilang | 100% harga buku | Opsional, aktif jika harga buku terisi | P1 |

### Rumus denda keterlambatan

```latex
\text{denda} = \max(0,\ \text{tgl\_kembali} - \text{tgl\_jatuh\_tempo}) \times \text{denda\_per\_hari}
```

Selisih dihitung dalam hari kalender (hari libur tetap dihitung) supaya logikanya sederhana. Contoh: jatuh tempo 10 November, kembali 13 November, terlambat 3 hari, denda Rp3.000 per eksemplar.

### Aturan tambahan

- Anggota dengan denda belum lunas atau pinjaman terlambat tidak bisa meminjam, memperpanjang, atau mereservasi.
- Anggota nonaktif atau masa berlakunya habis tidak bisa bertransaksi sampai diaktifkan kembali oleh petugas.
- Eksemplar berstatus rusak atau hilang tidak muncul sebagai "tersedia" di katalog.
- Nomor transaksi berurutan per hari dan tidak bisa diubah.

## 10. Rancangan Database

Database terdiri dari 14 tabel: 7 master, 6 transaksi, dan 1 sistem. Semua tabel memakai InnoDB (agar mendukung foreign key dan transaksi), kolom `id` INT AUTO\_INCREMENT sebagai primary key, serta `created_at` dan `updated_at`.

| Tabel | Jenis | Kolom utama | Relasi |
| --- | --- | --- | --- |
| users | Master | nama, username (unik), email (unik), password\_hash, role ENUM(admin, petugas, anggota), is\_active | 1–1 ke anggota |
| anggota | Master | user\_id, kode\_anggota (unik), no\_identitas (unik), nama, jenis\_kelamin, tgl\_lahir, alamat, no\_hp, foto, status ENUM(menunggu, aktif, ditolak, nonaktif), alasan\_tolak, tgl\_daftar, tgl\_berlaku, verified\_by | user\_id → users; verified\_by → users |
| kategori | Master | kode (unik), nama, deskripsi | 1–N ke buku |
| penerbit | Master | nama, kota, telepon | 1–N ke buku |
| rak | Master | kode (unik), nama, lantai | 1–N ke eksemplar |
| buku | Master | isbn, judul, penulis, penerbit\_id, kategori\_id, tahun\_terbit, halaman, sinopsis, foto\_cover, foto\_thumb, harga, is\_active | → penerbit, kategori; 1–N ke eksemplar |
| eksemplar | Master | buku\_id, rak\_id, kode\_eksemplar (unik), kondisi, status ENUM(tersedia, dipinjam, direservasi, rusak, hilang), tgl\_pengadaan, sumber | → buku, rak |
| peminjaman | Transaksi (header) | no\_transaksi (unik), anggota\_id, petugas\_id, tgl\_pinjam, status ENUM(aktif, selesai) | → anggota, users; 1–N ke peminjaman\_detail |
| peminjaman\_detail | Transaksi (detail) | peminjaman\_id, eksemplar\_id, tgl\_jatuh\_tempo, jumlah\_perpanjangan, denda\_per\_hari (salinan aturan), status ENUM(dipinjam, kembali) | → peminjaman, eksemplar |
| pengembalian | Transaksi | no\_pengembalian, peminjaman\_detail\_id (unik), tgl\_kembali, kondisi ENUM(baik, rusak, hilang), hari\_terlambat, petugas\_id | → peminjaman\_detail, users |
| denda | Transaksi | pengembalian\_id, anggota\_id, jenis ENUM(terlambat, rusak, hilang), jumlah, status ENUM(belum\_lunas, lunas, dibebaskan), tgl\_bayar, petugas\_id, keterangan | → pengembalian, anggota, users |
| perpanjangan | Transaksi (P1) | peminjaman\_detail\_id, jatuh\_tempo\_lama, jatuh\_tempo\_baru, diajukan\_oleh | → peminjaman\_detail, users |
| reservasi | Transaksi (P1) | anggota\_id, buku\_id, eksemplar\_id (nullable), tgl\_reservasi, tgl\_batas\_ambil, status ENUM(menunggu, siap, diambil, batal, kadaluarsa) | → anggota, buku, eksemplar |
| pengaturan | Sistem | kunci (unik), nilai, keterangan | — |

### Catatan desain

- Pengembalian dipisah dari detail peminjaman agar transaksi "pengembalian" terlihat jelas sebagai file transaksi tersendiri saat presentasi.
- Kolom `denda_per_hari` disalin ke detail saat peminjaman, sehingga perubahan pengaturan tidak memengaruhi pinjaman yang sudah berjalan.
- Tambahkan index pada kolom yang sering dicari: `eksemplar.status`, `peminjaman.tgl_pinjam`, `peminjaman_detail.tgl_jatuh_tempo`, `denda.status`.
- ERD digambar di draw.io atau dbdiagram.io dari tabel ini untuk lampiran presentasi.

## 11. Alur Proses Utama

Alur dimulai dari pendaftaran dan berakhir saat eksemplar kembali tersedia. Ada tiga titik keputusan: verifikasi, syarat pinjam, dan keterlambatan.

&#91;embedded content: alur sirkulasi · register sampai eksemplar kembali tersedia\]

Pengembalian terlambat selalu menghasilkan denda, dan anggota baru bisa meminjam lagi setelah dendanya lunas. Jika ada reservasi (P1), eksemplar yang kembali berstatus "direservasi", bukan "tersedia".

## 12. Kebutuhan Non-Fungsional, Arsitektur, dan Hosting

Aplikasi memakai PHP native tanpa framework, dengan struktur folder per modul supaya mudah dikerjakan bertahap per hari dan mudah dibaca dosen di GitHub.

### Tech stack

| Lapisan | Pilihan | Catatan |
| --- | --- | --- |
| Backend | PHP 8.x native + PDO | Prepared statement untuk semua query |
| Database | MySQL/MariaDB (InnoDB) | Dikelola lewat phpMyAdmin |
| UI | Bootstrap 5 + Bootstrap Icons | Template admin gratis (misal AdminLTE/Mazer) boleh dipakai |
| Tabel data | DataTables | Pencarian, sort, dan paginasi di sisi klien |
| Grafik dashboard | Chart.js | — |
| Notifikasi UI | SweetAlert2 | Konfirmasi hapus dan pesan sukses |
| Export PDF (P1) | Dompdf | Folder vendor di-build lokal lalu di-upload |
| Export Excel (P1) | SheetJS | Dijalankan di browser |
| Barcode/QR (P1) | JsBarcode, qrcode.js, html5-qrcode | Scanner USB bekerja seperti keyboard; scan kamera butuh HTTPS |
| Versi kode | Git + GitHub (repo librani) | Commit per fitur, push minimal setiap akhir hari |

### Struktur folder

```
librani/
├── config/
│   ├── config.example.php  # contoh, di-commit
│   └── config.php          # kredensial asli, masuk .gitignore
├── includes/        # auth.php, helpers.php, upload.php, header, sidebar, footer
├── modules/
│   ├── auth/        # login, register, logout
│   ├── dashboard/
│   ├── master/      # kategori, penerbit, rak, buku, eksemplar, anggota, users
│   ├── transaksi/   # peminjaman, pengembalian, denda, perpanjangan, reservasi
│   ├── laporan/
│   └── pengaturan/
├── assets/          # css, js, img (placeholder sampul)
├── uploads/
│   ├── cover/       # foto sampul + thumb/
│   └── anggota/     # foto anggota
├── database/
│   ├── librani.sql  # schema + data contoh
│   └── seed-covers/ # foto sampul untuk data contoh
├── docs/            # screenshot dan ERD untuk README
├── .gitignore
├── README.md
└── index.php        # landing + katalog publik
```

### Keamanan

- Password disimpan dengan `password_hash()` (bcrypt), dicek dengan `password_verify()`.
- Semua query memakai PDO prepared statement untuk mencegah SQL injection.
- Semua output dari database di-escape dengan `htmlspecialchars()` untuk mencegah XSS.
- Token CSRF di setiap form POST.
- `session_regenerate_id()` setelah login berhasil.
- Upload foto sampul (jpg/png/webp, maks. 2 MB) dan foto anggota (jpg/png, maks. 1 MB) dicek dari tipe MIME aslinya, nama file diacak, dan eksekusi PHP di folder uploads dimatikan lewat .htaccess.
- File konfigurasi database dilindungi dari akses langsung lewat .htaccess.

### Performa dan kompatibilitas

- Halaman utama terbuka dalam waktu kurang dari 3 detik di hosting gratis.
- Desktop-first, tetap rapi di layar tablet dan HP (responsif Bootstrap).
- Diuji di Chrome dan Edge versi terbaru.

### Hosting (InfinityFree)

- Subdomain gratis (misal librani.infinityfreeapp.com) dengan SSL gratis diaktifkan.
- Tidak ada SSH: upload file lewat FTP (FileZilla) atau File Manager; impor database lewat phpMyAdmin.
- Host database bukan `localhost`, jadi gunakan host MySQL yang tertera di panel. Konfigurasi lokal dan hosting dipisah dalam dua file config.
- Tidak ada cron job, sehingga pengecekan otomatis (reservasi kadaluarsa, status terlambat) dilakukan saat halaman dibuka.
- Ada batas jumlah proses dan hit harian. Untuk demo kelas ini cukup, tetapi hindari query berat di setiap halaman.
- Backup database (export .sql) setiap kali selesai satu fitur besar.

### GitHub

Seluruh kode disimpan di repository GitHub bernama librani. File SQL dan foto contoh ikut di-commit, jadi aplikasi bisa dipasang ulang dari nol hanya dari repo.

- `.gitignore` mengecualikan `config/config.php` (berisi password database), isi `uploads/` kecuali `.gitkeep`, serta file sistem seperti `.DS_Store` dan `Thumbs.db`.
- `config/config.example.php` di-commit sebagai contoh tanpa kredensial asli.
- `database/librani.sql` diperbarui setiap kali struktur tabel berubah.
- Foto sampul data contoh disimpan di `database/seed-covers/` dan disalin ke `uploads/cover/` saat instalasi, supaya demo tetap bergambar.
- README.md berisi deskripsi, daftar fitur, screenshot, cara instal di XAMPP, akun demo untuk ketiga role, dan link aplikasi online.
- Commit kecil dengan pesan jelas, misal `feat: upload foto sampul buku` atau `fix: hitung denda terlambat`; push minimal setiap akhir hari.
- Opsional: GitHub Actions dengan FTP-Deploy-Action untuk upload otomatis ke InfinityFree setiap push ke `main`. Folder `uploads/` wajib dikecualikan agar foto di server tidak terhapus.

## 13. Timeline 1 Minggu

Satu orang mengerjakan semuanya dalam 7 hari: hari 1–5 membangun P0 per modul, hari ke-6 deploy dan uji di hosting, hari ke-7 untuk P1, README, dan persiapan presentasi. Tanggal masih perkiraan, dihitung dari presentasi ±1 minggu lagi.

&#91;embedded content: timeline 7 hari · gate P0 online di hari ke-6\]

Setiap hari ditutup dengan push ke GitHub. Aturan kerja: jika di akhir hari ke-3 modul buku + foto belum selesai, semua P1 dicoret dan hari ke-7 dipakai untuk menyelesaikan P0.

## 14. Risiko dan Mitigasi

Risiko terbesar adalah waktu: satu orang dalam satu minggu. Karena itu batas P0 online di hari ke-6 tidak boleh mundur.

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Waktu tidak cukup untuk semua fitur | Fitur P1 tidak selesai | P0 dikunci di hari ke-6; P1 hanya di hari ke-7 dan dipotong dari bawah (reservasi lebih dulu) |
| Hosting gratis lambat atau down saat presentasi | Demo gagal | Uji deploy halaman login sejak hari ke-1; siapkan versi localhost (XAMPP) dan video rekaman demo sebagai cadangan |
| Perbedaan konfigurasi lokal dan hosting (versi PHP, host DB) | Error setelah upload | Pakai PHP 8.x di lokal, file config terpisah, dan uji deploy lebih awal |
| Status eksemplar tidak konsisten | Data laporan salah | Proses pinjam dan kembali memakai transaksi database; status hanya diubah oleh transaksi |
| Kode hilang atau laptop bermasalah | Pekerjaan hilang atau rusak | Push ke GitHub setiap selesai satu fitur dan minimal setiap akhir hari |
| Celah keamanan dasar (SQL injection, XSS) | Nilai berkurang jika dosen menguji | PDO prepared statement, escape output, CSRF token (bagian 12) |
| Scan kamera tidak jalan | Fitur barcode gagal didemokan | Aktifkan SSL; sediakan input manual kode sebagai cadangan |
| Foto sampul berukuran besar | Katalog lambat dan kuota hosting cepat habis | Kompres dan thumbnail otomatis (FR-FOTO-02), lazy loading di katalog |
| Password database ikut ter-push ke GitHub | Database bisa diakses orang lain | config.php masuk .gitignore; cek file sebelum commit; ganti password jika terlanjur ter-push |

## 15. Skenario Demo dan Checklist Penerimaan

Demo berdurasi sekitar 10 menit, dijalankan langsung dari URL hosting dengan data contoh yang sudah disiapkan.

### Data contoh (seed)

- 50 judul buku dari 6 kategori, total sekitar 120 eksemplar, semuanya dengan foto sampul.
- 20 anggota aktif, 2 anggota berstatus menunggu verifikasi.
- Transaksi 2 bulan terakhir, termasuk beberapa pinjaman yang sudah terlambat dan denda belum lunas, supaya dashboard dan laporan langsung terisi.

### Urutan demo

1. Buka landing page dan cari buku di katalog publik.
2. Daftar sebagai anggota baru, lalu coba login dan tunjukkan pesan "menunggu verifikasi".
3. Login sebagai petugas, verifikasi anggota baru tersebut, dan tunjukkan kode anggota yang dibuat otomatis.
4. Tunjukkan dashboard petugas: kartu angka, grafik, dan daftar terlambat.
5. Tambah satu buku dengan foto sampul dan 3 eksemplar, lalu tunjukkan kartunya langsung muncul di katalog.
6. Peminjaman: pinjamkan 3 buku ke anggota baru (setiap buku tampil dengan foto sampulnya), lalu coba buku ke-4 untuk menunjukkan validasi batas pinjam.
7. Pengembalian: kembalikan pinjaman contoh yang sudah terlambat dan tunjukkan denda yang dihitung otomatis.
8. Bayar denda tersebut dan cetak kuitansinya.
9. Login sebagai anggota: lihat pinjaman, jatuh tempo, perpanjangan, dan reservasi (P1).
10. Login sebagai admin: ubah denda per hari di pengaturan, lalu buka laporan dengan filter periode, cetak, dan export (P1). Tutup dengan membuka repository GitHub: README dan riwayat commit.

### Checklist penerimaan

- [ ] Database MySQL terpasang di hosting dan semua tabel memiliki relasi.
- [ ] Login, register, dan dashboard berjalan untuk ketiga role.
- [ ] CRUD semua file master berjalan, termasuk validasi dan soft delete.
- [ ] Peminjaman dan pengembalian mengubah status eksemplar dengan benar.
- [ ] Denda keterlambatan sesuai hitungan manual pada data uji.
- [ ] Ketujuh laporan bisa difilter dan dicetak.
- [ ] Aplikasi bisa diakses lewat URL hosting gratis dengan HTTPS.
- [ ] Skenario demo berjalan dari awal sampai akhir tanpa error.
- [ ] Cadangan demo siap: localhost dan video rekaman.
- [ ] Semua buku contoh punya foto sampul, dan buku tanpa foto menampilkan placeholder.
- [ ] Repository GitHub berisi README, file SQL, dan tidak memuat password database.
