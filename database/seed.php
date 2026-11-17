<?php
// Mengisi ulang database `librani` dengan data contoh. HANYA lewat terminal:
//   php database/seed.php
// PERINGATAN: semua data di database librani dan isi uploads/cover akan dihapus lalu dibuat ulang.
//
// Isi: 1 admin, 2 petugas, 6 kategori, 6 rak, 18 penerbit, 50 buku asli (± 120 eksemplar),
// 22 anggota (20 aktif, 2 menunggu), dan transaksi 60 hari terakhir yang dibuat lewat fungsi aplikasi
// (simpan_peminjaman, proses_kembali, bayar_denda, perpanjang, reservasi) sehingga status selalu konsisten.
// Tanggal relatif terhadap hari ini: jalankan ulang + export SQL sehari sebelum presentasi.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config/config.php';
(new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS))
    ->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
require __DIR__ . '/../includes/init.php';

$db = db();
$db->exec('DROP DATABASE `' . DB_NAME . '`');
$db->exec('CREATE DATABASE `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->exec('USE `' . DB_NAME . '`');
unset($GLOBALS['_setting']);
jalankan_sql_file(__DIR__ . '/schema.sql');
foreach (glob(UPLOAD_DIR . '/{cover,cover/thumb,anggota}/*.{jpg,png,webp}', GLOB_BRACE) as $lama) {
    unlink($lama);   // file upload lama tidak lagi dirujuk database baru
}
echo "Skema dimuat, folder upload dibersihkan.\n";

$data = require __DIR__ . '/buku.php';
mt_srand(2026);                                  // hasil selalu sama setiap dijalankan
$hari = fn(int $n): string => tambah_hari(hari_ini(), $n);

function seed_user(string $nama, string $username, string $password, string $role): int
{
    db()->prepare('INSERT INTO users (nama, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)')
        ->execute([$nama, $username, "$username@librani.test", password_hash($password, PASSWORD_DEFAULT), $role]);
    return (int) db()->lastInsertId();
}

/** Daftarkan anggota (+ verifikasi jika $tgl_verifikasi diisi). */
function seed_anggota(string $nama, string $jk, string $username, ?string $tgl_verifikasi, int $petugas, string $tgl_daftar): int
{
    $id = simpan_register([
        'nama' => $nama, 'no_identitas' => '3578' . sprintf('%012d', mt_rand(1, 999999999999)), 'jenis_kelamin' => $jk,
        'tgl_lahir' => sprintf('%d-%02d-%02d', mt_rand(1970, 2008), mt_rand(1, 12), mt_rand(1, 28)),
        'alamat' => 'Jl. ' . ['Kenanga', 'Melati', 'Mawar', 'Anggrek', 'Flamboyan', 'Cempaka'][mt_rand(0, 5)] . ' No. ' . mt_rand(1, 120),
        'no_hp' => '08' . mt_rand(1, 9) . sprintf('%09d', mt_rand(1, 999999999)),
        'email' => "$username@contoh.id", 'username' => $username, 'password' => 'anggota123',
    ], null);
    db()->prepare('UPDATE anggota SET tgl_daftar = ? WHERE id = ?')->execute([$tgl_daftar, $id]);
    if ($tgl_verifikasi) {
        verifikasi_anggota($id, $petugas, $tgl_verifikasi);
    }
    return $id;
}

// ===================== Akun & master =====================
$petugas = [seed_user('Siti Rahmawati', 'petugas1', 'petugas123', 'petugas'), seed_user('Andi Pratama', 'petugas2', 'petugas123', 'petugas')];

$kat = [];
foreach ($data['kategori'] as [$kode, $nama, $desk]) {
    db()->prepare('INSERT INTO kategori (kode, nama, deskripsi) VALUES (?, ?, ?)')->execute([$kode, $nama, $desk]);
    $kat[$kode] = (int) db()->lastInsertId();
}
$rak_kat = array_column($data['kategori'], 3, 0);
$rak = [];
foreach ($data['rak'] as [$kode, $nama, $lantai]) {
    db()->prepare('INSERT INTO rak (kode, nama, lantai) VALUES (?, ?, ?)')->execute([$kode, $nama, $lantai]);
    $rak[$kode] = (int) db()->lastInsertId();
}
$penerbit = [];
foreach ($data['penerbit'] as [$nama, $kota]) {
    db()->prepare('INSERT INTO penerbit (nama, kota) VALUES (?, ?)')->execute([$nama, $kota]);
    $penerbit[$nama] = (int) db()->lastInsertId();
}

$tanpa_sampul = [];
foreach ($data['buku'] as $i => [$judul, $penulis, $kode_kat, $nama_penerbit, $tahun, $harga, $jumlah, $sinopsis]) {
    $no = sprintf('BK%04d', $i + 1);
    $file = glob(__DIR__ . "/seed-covers/$no.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}", GLOB_BRACE)[0] ?? null;
    $upload = null;
    if ($file) {
        $tmp = tempnam(sys_get_temp_dir(), 'cover');
        copy($file, $tmp);                   // salin dulu: simpan_gambar memindahkan file sumber
        $upload = ['tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => filesize($tmp), 'tes' => true];
    }
    $isi = [
        'judul' => $judul, 'penulis' => $penulis, 'kategori_id' => $kat[$kode_kat], 'penerbit_id' => $penerbit[$nama_penerbit],
        'tahun_terbit' => (string) $tahun, 'harga' => (string) $harga, 'sinopsis' => $sinopsis,
        'jumlah_eksemplar' => $jumlah, 'rak_id' => $rak[$rak_kat[$kode_kat]], 'sumber' => $i % 7 === 6 ? 'hibah' : 'beli',
    ];
    try {
        $buku_id = simpan_buku($isi, $upload);
    } catch (RuntimeException $e) {
        echo "  ! Sampul $no tidak dipakai: {$e->getMessage()}\n";
        $buku_id = simpan_buku($isi, null);
    }
    if (!$file) {
        $tanpa_sampul[] = $no;
    }
    db()->prepare('UPDATE eksemplar SET tgl_pengadaan = ? WHERE buku_id = ?')->execute([$hari(-mt_rand(200, 900)), $buku_id]);
}
echo count($data['buku']) . ' buku, ' . db_val('SELECT COUNT(*) FROM eksemplar') . " eksemplar.\n";
echo $tanpa_sampul ? count($tanpa_sampul) . " buku belum punya sampul (lihat database/seed-covers/DAFTAR.md).\n" : "Semua buku punya sampul.\n";

// ===================== Anggota =====================
// anggota1 (Budi) & anggota2 (Sari) dipakai skenario demo dan tests/smoke.php.
$budi = seed_anggota('Budi Santoso', 'L', 'anggota1', $hari(-240), $petugas[0], $hari(-242));
$sari = seed_anggota('Sari Wulandari', 'P', 'anggota2', $hari(-235), $petugas[0], $hari(-236));
$pool = [];
foreach (array_slice($data['anggota'], 0, -2) as $k => [$nama, $jk]) {
    $username = strtolower(explode(' ', $nama)[0]);
    $pool[] = seed_anggota($nama, $jk, $username, $hari(-230 + $k * 5), $petugas[$k % 2], $hari(-232 + $k * 5));
}
foreach (array_slice($data['anggota'], -2) as $k => [$nama, $jk]) {
    seed_anggota($nama, $jk, strtolower(explode(' ', $nama)[0]), null, $petugas[0], $hari(-1 + $k));   // menunggu verifikasi
}
// Satu anggota masa berlakunya habis 3 hari lalu (contoh validasi saat demo).
db()->prepare('UPDATE anggota SET tgl_berlaku = ? WHERE id = ?')->execute([$hari(-3), end($pool)]);
echo db_val('SELECT COUNT(*) FROM anggota') . " anggota.\n";

// ===================== Skenario tetap untuk demo & smoke test =====================
// Sari: pinjam 2 Bumi Manusia (H-20), pinjam Ronggeng Dukuh Paruk (H-14, kini terlambat),
// kembalikan Bumi Manusia terlambat 3 hari (H-10) → 2 denda belum lunas.
$eks = fn(int $buku_id) => array_map('intval', array_column(db_all('SELECT id FROM eksemplar WHERE buku_id = ? ORDER BY id', [$buku_id]), 'id'));
$pj = simpan_peminjaman($sari, $eks(2), $petugas[0], $hari(-20));
simpan_peminjaman($sari, $eks(3), $petugas[1], $hari(-14));
proses_kembali(array_map(fn($d) => ['detail_id' => (int) $d, 'kondisi' => 'baik'],
    array_column(db_all('SELECT id FROM peminjaman_detail WHERE peminjaman_id = ?', [$pj]), 'id')), $petugas[0], $hari(-10));

// ===================== Simulasi transaksi 60 hari =====================
$rencana_kembali = [];   // detail_id => tanggal kembali
$rencana_bayar = [];     // denda_id => tanggal bayar
$user_anggota = array_column(db_all('SELECT id, user_id FROM anggota'), 'user_id', 'id');
$jumlah = ['pinjam' => 0, 'kembali' => 0, 'perpanjang' => 0];

for ($n = -60; $n <= 0; $n++) {
    $t = $hari($n);
    $ptg = $petugas[mt_rand(0, 1)];

    // 1. Pengembalian yang direncanakan hari ini
    foreach (array_keys($rencana_kembali, $t, true) as $detail_id) {
        unset($rencana_kembali[$detail_id]);
        $kondisi = mt_rand(1, 60) === 1 ? 'rusak' : 'baik';
        proses_kembali([['detail_id' => $detail_id, 'kondisi' => $kondisi]], $ptg, $t);
        $jumlah['kembali']++;
        foreach (db_all('SELECT dn.id FROM denda dn JOIN pengembalian k ON k.id = dn.pengembalian_id WHERE k.peminjaman_detail_id = ?', [$detail_id]) as $d) {
            if (mt_rand(1, 10) <= 7) {
                $rencana_bayar[$d['id']] = tambah_hari($t, mt_rand(0, 3));
            }
        }
    }

    // 2. Pembayaran denda yang direncanakan hari ini (dikelompokkan per anggota)
    $bayar = array_keys($rencana_bayar, $t, true);
    $per_anggota = [];
    foreach ($bayar as $id) {
        unset($rencana_bayar[$id]);
        $per_anggota[db_val('SELECT anggota_id FROM denda WHERE id = ?', [$id])][] = $id;
    }
    foreach ($per_anggota as $ids) {
        bayar_denda($ids, $ptg, $t);
    }

    // 3. Sesekali anggota memperpanjang pinjaman
    foreach (array_keys($rencana_kembali) as $detail_id) {
        if (mt_rand(1, 40) === 1) {
            $anggota_id = (int) db_val('SELECT p.anggota_id FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id WHERE d.id = ?', [$detail_id]);
            if (!alasan_tidak_bisa_perpanjang($detail_id, $user_anggota[$anggota_id], $t)) {
                perpanjang($detail_id, $user_anggota[$anggota_id], $t);
                $rencana_kembali[$detail_id] = max($rencana_kembali[$detail_id], tambah_hari($t, 3));
                $jumlah['perpanjang']++;
            }
        }
    }

    // 4. Peminjaman baru (lebih ramai di hari kerja)
    $banyak = date('N', strtotime($t)) >= 6 ? mt_rand(0, 2) : mt_rand(1, 4);
    for ($k = 0; $k < $banyak; $k++) {
        $a = $pool[mt_rand(0, count($pool) - 1)];
        $sisa = (int) setting('maks_pinjam') - jumlah_pinjaman_aktif($a);
        if (syarat_pinjam($a, $t) || $sisa < 1) {
            continue;
        }
        $tersedia = array_column(db_all("SELECT id, buku_id FROM eksemplar WHERE status = 'tersedia' AND buku_id > 3"), 'buku_id', 'id');
        $pilih = [];
        foreach ((array) array_rand($tersedia, min(count($tersedia), mt_rand(1, min(2, $sisa)))) as $eid) {
            $pilih[$tersedia[$eid]] = $eid;   // satu eksemplar per judul
        }
        $pid = simpan_peminjaman($a, array_values($pilih), $ptg, $t);
        $jumlah['pinjam']++;
        foreach (db_all('SELECT id, tgl_jatuh_tempo FROM peminjaman_detail WHERE peminjaman_id = ?', [$pid]) as $d) {
            $r = mt_rand(1, 100);
            $offset = $r <= 60 ? -mt_rand(0, 5) : ($r <= 85 ? mt_rand(1, 5) : mt_rand(6, 20));
            $rencana_kembali[$d['id']] = max(tambah_hari($t, 1), tambah_hari($d['tgl_jatuh_tempo'], $offset));
        }
    }
}
echo "Simulasi: {$jumlah['pinjam']} transaksi pinjam, {$jumlah['kembali']} pengembalian, {$jumlah['perpanjang']} perpanjangan.\n";

// ===================== Reservasi contoh =====================
// (a) Ronggeng Dukuh Paruk (dipinjam Sari) diantre oleh anggota lain → status menunggu.
foreach ($pool as $a) {
    try {
        buat_reservasi($a, 3, $hari(-2));
        break;
    } catch (RuntimeException) {
    }
}
// (b) Judul dengan satu eksemplar: dipinjam H-6, direservasi H-5, kembali H-1 → siap diambil s.d. besok.
$bisa = fn(int $a, string $t) => !syarat_pinjam($a, $t) && jumlah_pinjaman_aktif($a) < (int) setting('maks_pinjam');
$buku_satu = db_val("SELECT b.id FROM buku b JOIN eksemplar e ON e.buku_id = b.id WHERE b.id > 3
                     GROUP BY b.id HAVING COUNT(*) = 1 AND SUM(e.status = 'tersedia') = 1 ORDER BY b.id LIMIT 1");
$calon = array_values(array_filter($pool, fn($a) => $bisa($a, $hari(-6))));
if ($buku_satu && count($calon) >= 2) {
    // Fungsi-fungsi ini masing-masing sudah memakai transaksi DB sendiri.
    $pid = simpan_peminjaman($calon[0], $eks((int) $buku_satu), $petugas[0], $hari(-6));
    buat_reservasi($calon[1], (int) $buku_satu, $hari(-5));
    proses_kembali([['detail_id' => (int) db_val('SELECT MIN(id) FROM peminjaman_detail WHERE peminjaman_id = ?', [$pid]), 'kondisi' => 'baik']], $petugas[1], $hari(-1));
}
echo 'Reservasi: ' . db_val("SELECT COUNT(*) FROM reservasi WHERE status = 'menunggu'") . ' menunggu, '
    . db_val("SELECT COUNT(*) FROM reservasi WHERE status = 'siap'") . " siap diambil.\n";

// ===================== Ringkasan & cek konsistensi =====================
$s = statistik_sirkulasi();
echo "Dashboard: {$s['dipinjam']} dipinjam, {$s['terlambat']} terlambat, denda belum lunas " . rupiah($s['denda_belum_lunas'])
    . ", {$s['menunggu_verifikasi']} menunggu verifikasi.\n";
$salah = (int) db_val(
    "SELECT COUNT(*) FROM eksemplar e WHERE
        (e.status = 'dipinjam' AND NOT EXISTS (SELECT 1 FROM peminjaman_detail d WHERE d.eksemplar_id = e.id AND d.status = 'dipinjam'))
     OR (e.status <> 'dipinjam' AND EXISTS (SELECT 1 FROM peminjaman_detail d WHERE d.eksemplar_id = e.id AND d.status = 'dipinjam'))
     OR (e.status = 'direservasi' AND NOT EXISTS (SELECT 1 FROM reservasi r WHERE r.eksemplar_id = e.id AND r.status = 'siap'))"
);
if ($salah > 0) {
    fwrite(STDERR, "GAGAL: $salah eksemplar statusnya tidak konsisten dengan transaksi.\n");
    exit(1);
}
echo "Cek konsistensi status eksemplar: OK.\n";
echo "Akun demo: admin/admin123, petugas1/petugas123, anggota1/anggota123 (password anggota lain: anggota123).\n";
