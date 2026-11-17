# Desain Teknis Librani

Tanggal: 17 November 2026 · Sumber kebutuhan: [prd_librani.md](../../../prd_librani.md)

Dokumen ini tidak mengulang PRD. Isinya keputusan teknis yang dibutuhkan untuk membangun PRD tersebut. Kalau ada yang bertentangan, PRD menang untuk *apa* yang dibangun, dokumen ini menang untuk *bagaimana*.

## 1. Tujuan dan alasan

Librani adalah tugas kuliah studi kasus CRUD yang akan didemokan langsung dari URL hosting (±1 minggu lagi). Ada dua ukuran berhasil: keenam ketentuan tugas terpenuhi, dan pemilik proyek bisa menjelaskan kodenya saat ditanya dosen. Karena itu kode harus mudah dibaca dan polanya seragam. Kode yang pintar tapi sulit dijelaskan justru merugikan.

- **Goals:** semua P0 dan **semua P1** (perpanjangan, export PDF/Excel, barcode/QR, reservasi). Skenario demo bagian 15 PRD berjalan tanpa error di InfinityFree.
- **Non-goals:** semua yang ada di "Di luar lingkup" PRD. Juga tanpa framework, tanpa build step JS, tanpa unit-test framework.
- **Anti-goals:** status eksemplar yang tidak konsisten dengan transaksi; password DB ter-push ke GitHub; halaman yang bisa dibuka role yang salah; demo gagal karena perbedaan lokal dan hosting.

## 2. Batasan

- PHP **8.3** (Laragon lokal; sama dengan kelas versi di InfinityFree), MySQL 8.4 lokal, MySQL/MariaDB di InfinityFree.
- SQL harus jalan di kedua engine: charset `utf8mb4`, collation **`utf8mb4_unicode_ci`** (bukan `utf8mb4_0900_ai_ci` yang ditolak MariaDB), tanpa fitur khusus MySQL 8.
- InfinityFree: tanpa SSH, tanpa cron, host DB bukan localhost, ada batas hit. Upload lewat FTP.
- Lokal: Laragon dengan virtual host `http://librani.test`; hosting di root subdomain. Semua URL dibangun dari `BASE_URL` di config.

## 3. Pendekatan (dipilih: 1)

| # | Pendekatan | Trade-off | Status |
| --- | --- | --- | --- |
| 1 | **Satu file per halaman**, tiap file `require` `includes/init.php` | Paling mudah dibaca dosen, cocok dengan struktur folder PRD, tanpa rewrite `.htaccess`. Ada pola berulang antar master. | **Dipilih pemilik proyek** |
| 2 | Front controller `index.php?page=…` + router | Pengulangan lebih sedikit, satu titik cek akses; ada konsep router yang harus dijelaskan, URL kurang rapi | Ditolak |
| 3 | Mini MVC | Terlalu berat untuk tugas PHP native, bentrok dengan struktur folder PRD | Ditolak |

## 4. Keputusan pemilik proyek

- Semua P1 dikerjakan.
- Environment lokal: Laragon.
- Data contoh: buku dan penulis **asli**, utamakan penulis Indonesia.
- Foto sampul dicari manual oleh pemilik proyek, lalu disimpan sesuai daftar nama file yang disiapkan builder.

## 5. Struktur kode

Mengikuti struktur folder PRD bagian 12, dengan rincian:

```
includes/
  init.php       # load config, buka PDO, session_start, require helpers/auth/csrf
  db.php         # fungsi db(): PDO singleton (ERRMODE_EXCEPTION, FETCH_ASSOC, emulate off)
  auth.php       # require_login(), require_role('admin','petugas'), user(), 403
  csrf.php       # csrf_field(), csrf_check()
  helpers.php    # e() = htmlspecialchars, url(), redirect(), flash(), setting(), rupiah(), tgl()
  upload.php     # upload_cover(), upload_foto_anggota(), hapus_file()
  nomor.php      # nomor_transaksi('PJM'|'KMB'|...), kode_anggota(), kode_eksemplar()
  sirkulasi.php  # aturan bisnis bersama: syarat_pinjam(), hitung_denda(), proses_kembali(), cek_reservasi_kadaluarsa()
  layout/        # header.php, sidebar.php (menu per role), footer.php, print_header.php
modules/<grup>/<modul>/
  index.php      # daftar (DataTables)
  form.php       # tambah + ubah (GET tampil, POST simpan)
  hapus.php      # POST saja; soft delete jika sudah dipakai transaksi
  detail.php     # jika perlu (buku, anggota, peminjaman)
```

Pola satu halaman: `require init.php` → `require_role(...)` → kalau POST: `csrf_check()`, validasi, simpan, `flash()`, `redirect()` (PRG) → kalau GET: query lalu render HTML dengan `e()`.

Logika yang dipakai di lebih dari satu tempat (syarat pinjam, hitung denda, proses pengembalian, kadaluarsa reservasi) **hanya** ada di `includes/sirkulasi.php`. Halaman transaksi memanggilnya; tidak ada salinan logika di halaman.

## 6. Database

- 14 tabel persis seperti PRD bagian 10, InnoDB, FK lengkap, index sesuai catatan desain PRD.
- Tambahan kecil: kunci `nama_perpustakaan`, `alamat_perpustakaan` di tabel `pengaturan` (untuk kop laporan), dan kolom `is_active` pada kategori/penerbit/rak untuk soft delete. *(keputusan builder)*
- Semua penyimpanan transaksi dalam `beginTransaction()` … `commit()`, `rollBack()` di `catch`.
- **Nomor urut harian** (`PJM-20261117-0001`): di dalam transaksi, `SELECT MAX(no_…) … WHERE no_… LIKE 'PJM-20261117-%' FOR UPDATE`, lalu +1. Kolom unik menjadi pengaman terakhir.
- **Kode eksemplar:** `BK` + id buku 4 digit + `-` + urutan 2 digit (`BK0001-01`).
- **Kode anggota:** `AGT-<tahun verifikasi>-<urut 4 digit>`.
- File: `database/schema.sql` (struktur), `database/seed.php` (pengisi data), dan `database/librani.sql` = hasil export lengkap (struktur + data) untuk diimpor ke phpMyAdmin hosting.

## 7. Aturan sirkulasi (ringkas, rinci di PRD bagian 7 & 9)

- Status eksemplar **hanya** diubah oleh fungsi di `sirkulasi.php`, kecuali tandai rusak/hilang manual oleh petugas.
- Pengembalian: `hari_terlambat = max(0, tgl_kembali − jatuh_tempo)` dalam hari kalender × `denda_per_hari` salinan di detail. Kondisi rusak/hilang menambah denda `denda_rusak`/`denda_hilang` × harga (jika harga terisi). Eksemplar → `tersedia`/`rusak`/`hilang`, atau `direservasi` bila kondisi baik dan ada reservasi `menunggu` untuk judul itu (reservasi tertua → `siap`, `eksemplar_id` diisi, `tgl_batas_ambil` = hari ini + `batas_ambil_reservasi`).
- Kadaluarsa reservasi: `cek_reservasi_kadaluarsa()` dipanggil di dashboard, halaman reservasi, peminjaman, dan katalog. Reservasi `siap` yang lewat batas → `kadaluarsa`; eksemplarnya diteruskan ke antrean berikutnya atau kembali `tersedia`.
- "Terlambat" tidak disimpan sebagai status; dihitung dari `tgl_jatuh_tempo < CURDATE()` pada detail berstatus `dipinjam`.
- Pembebasan denda hanya admin, alasan wajib di `keterangan`.

## 8. Front-end

- Bootstrap 5.3, Bootstrap Icons, DataTables 2, Chart.js, SweetAlert2, SheetJS, JsBarcode, qrcode.js, html5-qrcode.
- **Semua library disimpan lokal di `assets/vendor/`**, bukan CDN, supaya cadangan demo localhost tetap jalan tanpa internet. *(keputusan builder)*
- Tanpa template admin (AdminLTE/Mazer); layout sidebar sederhana dengan Bootstrap saja, supaya tidak ada kode template yang harus dijelaskan. *(keputusan builder)*
- Placeholder sampul: elemen HTML/CSS rasio 2:3 berisi judul, bukan file gambar. Satu partial `cover()` di helpers.
- Halaman cetak: file terpisah `cetak.php` per laporan/bukti dengan `print_header.php` dan CSS `@media print`.

## 9. Export, barcode, upload

- PDF: Dompdf lewat Composer (Laragon sudah menyertakan Composer). `vendor/` **tidak** di-commit; README menulis `composer install`; `vendor/` di-upload lewat FTP ke hosting.
- Excel: SheetJS di browser dari tabel yang sedang tampil.
- Barcode Code128 untuk eksemplar (label cetak per buku/rak), QR untuk kartu anggota. Scan: scanner USB = input teks biasa; kamera via html5-qrcode (butuh HTTPS atau localhost). Input manual selalu tersedia.
- Upload: cek MIME dengan `finfo`, ukuran, nama acak `bin2hex(random_bytes(16))`. GD resize ke 600px + thumb 200px; kalau GD tidak ada, simpan asli dengan batas 1 MB. `.htaccess` di `uploads/` mematikan eksekusi PHP; `.htaccess` di `config/` deny all.

## 10. Data contoh

- 6 kategori, 50 judul buku asli (mayoritas penulis Indonesia), ±120 eksemplar, 20 anggota aktif + 2 menunggu, 1 admin, 2 petugas.
- **ISBN hanya diisi bila terverifikasi**, sisanya kosong (PRD membolehkan). Tidak ada ISBN karangan.
- Builder menyiapkan `database/seed-covers/DAFTAR.md`: daftar `BK0001.jpg … BK0050.jpg` + judul + penulis. Pemilik proyek menyimpan foto sesuai nama itu. Seeder memproses foto yang ada (resize + thumb ke `uploads/cover/`); yang belum ada memakai placeholder.
- Transaksi 2 bulan terakhir **dibangkitkan oleh `seed.php`** lewat fungsi yang sama dengan aplikasi (bukan SQL tulis tangan), sehingga status eksemplar dijamin konsisten. Tanggal relatif terhadap hari seeder dijalankan, termasuk pinjaman terlambat, denda belum lunas, dan reservasi.
- Akun demo tiap role ditulis di README.

## 11. Verifikasi

- `tests/cek.php` dijalankan lewat CLI pada database uji: assert untuk hitung denda (contoh PRD: jatuh tempo 10 Nov, kembali 13 Nov → Rp3.000), nomor transaksi berurutan, syarat pinjam (batas 3, denda belum lunas, terlambat), alur pinjam → kembali → status eksemplar, reservasi → siap → kadaluarsa, dan query konsistensi "tidak ada eksemplar `dipinjam` tanpa detail aktif".
- Uji manual skenario demo PRD bagian 15 di lokal, lalu diulang di hosting.

## 12. Hosting dan GitHub

- Repo `librani`; `.gitignore` sesuai PRD + `vendor/`. `config/config.example.php` di-commit.
- Deploy manual via FTP (FileZilla); GitHub Actions FTP-deploy **tidak** dibuat. *(keputusan builder: menghemat waktu, deploy cukup beberapa kali)*
- Uji deploy halaman login sedini mungkin (setelah fondasi selesai), sesuai mitigasi PRD.

## 13. Urutan pengerjaan

1. Fondasi: config, init, auth, CSRF, layout, login/logout/register, skema DB → deploy awal ke InfinityFree.
2. Master: kategori, penerbit, rak, user petugas, buku + foto + eksemplar, anggota + verifikasi + kartu, katalog publik/anggota.
3. Transaksi: pengaturan, peminjaman, pengembalian, denda.
4. Dashboard 3 role.
5. Laporan 7 jenis + cetak.
6. P1 berurutan: perpanjangan → export PDF/Excel → barcode/QR → reservasi (reservasi menyentuh `proses_kembali()`, sudah disiapkan di langkah 3).
7. Seeder + data asli + `librani.sql`, README, deploy final, uji skenario demo di hosting.

## 14. Diserahkan ke builder

Tata letak visual detail, teks pesan validasi, susunan menu sidebar, dan pilihan 50 judul buku (mengikuti batasan bagian 10).
