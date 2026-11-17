<?php
// Tujuh laporan. Setiap fungsi laporan_*() mengembalikan:
//   ['kolom' => [[label, format], ...], 'baris' => [[nilai mentah, ...], ...], 'total' => [label => nilai], 'format_total' => [label => format]]
// format: '' (teks), 'tgl', 'rupiah', 'angka'. Laporan populer mengembalikan beberapa 'blok' dengan bentuk yang sama.
// Tampilan (layar, cetak, PDF) dibuat oleh tampilkan_laporan().

/** Baca & rapikan filter dari GET. Periode default = bulan berjalan; dari > sampai ditukar. */
function filter_laporan(array $in): array
{
    $tgl_valid = function ($v): ?string {
        $d = DateTime::createFromFormat('!Y-m-d', (string) $v);
        return $d && $d->format('Y-m-d') === $v ? $v : null;
    };
    $f = [
        'dari' => $tgl_valid($in['dari'] ?? '') ?? date('Y-m-01'),
        'sampai' => $tgl_valid($in['sampai'] ?? '') ?? date('Y-m-t'),
        'anggota_id' => (int) ($in['anggota_id'] ?? 0),
        'kategori_id' => (int) ($in['kategori_id'] ?? 0),
        'rak_id' => (int) ($in['rak_id'] ?? 0),
        'status' => is_string($in['status'] ?? null) ? $in['status'] : '',
        'ketepatan' => in_array($in['ketepatan'] ?? '', ['tepat', 'terlambat'], true) ? $in['ketepatan'] : '',
        'min_terlambat' => max(0, (int) ($in['min_terlambat'] ?? 0)),
        'periode_daftar' => !empty($in['periode_daftar']),
    ];
    if ($f['dari'] > $f['sampai']) {
        [$f['dari'], $f['sampai']] = [$f['sampai'], $f['dari']];
    }
    return $f;
}

function laporan_peminjaman(array $f): array
{
    $hari_ini = $f['tanggal'] ?? hari_ini();
    $where = ['p.tgl_pinjam BETWEEN ? AND ?'];
    $p = [$f['dari'], $f['sampai']];
    if (!empty($f['anggota_id'])) {
        $where[] = 'p.anggota_id = ?';
        $p[] = $f['anggota_id'];
    }
    if (!empty($f['kategori_id'])) {
        $where[] = 'b.kategori_id = ?';
        $p[] = $f['kategori_id'];
    }
    $status = $f['status'] ?? '';
    if ($status === 'dipinjam' || $status === 'kembali') {
        $where[] = 'd.status = ?';
        $p[] = $status;
    } elseif ($status === 'terlambat') {
        $where[] = "d.status = 'dipinjam' AND d.tgl_jatuh_tempo < ?";
        $p[] = $hari_ini;
    }
    $rows = db_all(
        'SELECT p.no_transaksi, p.tgl_pinjam, a.nama, a.kode_anggota, b.judul, e.kode_eksemplar, d.tgl_jatuh_tempo, d.status, k.tgl_kembali
         FROM peminjaman_detail d
         JOIN peminjaman p ON p.id = d.peminjaman_id
         JOIN anggota a ON a.id = p.anggota_id
         JOIN eksemplar e ON e.id = d.eksemplar_id
         JOIN buku b ON b.id = e.buku_id
         LEFT JOIN pengembalian k ON k.peminjaman_detail_id = d.id
         WHERE ' . implode(' AND ', $where) . ' ORDER BY p.tgl_pinjam, p.no_transaksi, d.id',
        $p
    );
    $baris = array_map(fn($r) => [
        $r['no_transaksi'], $r['tgl_pinjam'], $r['nama'] . ' (' . $r['kode_anggota'] . ')', $r['judul'], $r['kode_eksemplar'], $r['tgl_jatuh_tempo'],
        $r['status'] === 'kembali' ? 'Kembali ' . tgl($r['tgl_kembali']) : ($r['tgl_jatuh_tempo'] < $hari_ini ? 'Terlambat' : 'Dipinjam'),
    ], $rows);
    return [
        'kolom' => [['No. transaksi', ''], ['Tgl pinjam', 'tgl'], ['Anggota', ''], ['Judul', ''], ['Eksemplar', ''], ['Jatuh tempo', 'tgl'], ['Status', '']],
        'baris' => $baris,
        'total' => ['Jumlah transaksi' => count(array_unique(array_column($rows, 'no_transaksi'))), 'Jumlah eksemplar' => count($rows)],
        'format_total' => [],
    ];
}

function laporan_pengembalian(array $f): array
{
    $where = ['k.tgl_kembali BETWEEN ? AND ?'];
    $p = [$f['dari'], $f['sampai']];
    if (($f['ketepatan'] ?? '') === 'tepat') {
        $where[] = 'k.hari_terlambat = 0';
    } elseif (($f['ketepatan'] ?? '') === 'terlambat') {
        $where[] = 'k.hari_terlambat > 0';
    }
    $rows = db_all(
        'SELECT k.no_pengembalian, k.tgl_kembali, a.nama, b.judul, e.kode_eksemplar, d.tgl_jatuh_tempo, k.hari_terlambat, k.kondisi,
                (SELECT COALESCE(SUM(jumlah), 0) FROM denda WHERE pengembalian_id = k.id) AS denda
         FROM pengembalian k
         JOIN peminjaman_detail d ON d.id = k.peminjaman_detail_id
         JOIN peminjaman p ON p.id = d.peminjaman_id
         JOIN anggota a ON a.id = p.anggota_id
         JOIN eksemplar e ON e.id = d.eksemplar_id
         JOIN buku b ON b.id = e.buku_id
         WHERE ' . implode(' AND ', $where) . ' ORDER BY k.tgl_kembali, k.no_pengembalian, k.id',
        $p
    );
    $baris = array_map(fn($r) => [
        $r['no_pengembalian'], $r['tgl_kembali'], $r['nama'], $r['judul'] . ' (' . $r['kode_eksemplar'] . ')', $r['tgl_jatuh_tempo'],
        (int) $r['hari_terlambat'], $r['kondisi'], (int) $r['denda'],
    ], $rows);
    return [
        'kolom' => [['No. pengembalian', ''], ['Tgl kembali', 'tgl'], ['Anggota', ''], ['Buku', ''], ['Jatuh tempo', 'tgl'], ['Hari terlambat', 'angka'], ['Kondisi', ''], ['Denda', 'rupiah']],
        'baris' => $baris,
        'total' => [
            'Jumlah eksemplar' => count($rows),
            'Terlambat' => count(array_filter($rows, fn($r) => $r['hari_terlambat'] > 0)),
            'Total denda' => array_sum(array_column($baris, 7)),
        ],
        'format_total' => ['Total denda' => 'rupiah'],
    ];
}

function laporan_denda(array $f): array
{
    $where = ['k.tgl_kembali BETWEEN ? AND ?'];
    $p = [$f['dari'], $f['sampai']];
    if (in_array($f['status'] ?? '', ['belum_lunas', 'lunas', 'dibebaskan'], true)) {
        $where[] = 'dn.status = ?';
        $p[] = $f['status'];
    }
    if (!empty($f['anggota_id'])) {
        $where[] = 'dn.anggota_id = ?';
        $p[] = $f['anggota_id'];
    }
    $rows = db_all(
        'SELECT a.nama, a.kode_anggota, k.tgl_kembali, b.judul, dn.jenis, dn.jumlah, dn.status, dn.tgl_bayar, dn.no_kuitansi
         FROM denda dn
         JOIN anggota a ON a.id = dn.anggota_id
         JOIN pengembalian k ON k.id = dn.pengembalian_id
         JOIN peminjaman_detail d ON d.id = k.peminjaman_detail_id
         JOIN eksemplar e ON e.id = d.eksemplar_id
         JOIN buku b ON b.id = e.buku_id
         WHERE ' . implode(' AND ', $where) . ' ORDER BY k.tgl_kembali, a.nama, dn.id',
        $p
    );
    $label = ['belum_lunas' => 'Belum lunas', 'lunas' => 'Lunas', 'dibebaskan' => 'Dibebaskan'];
    $total = ['Lunas' => 0, 'Belum lunas' => 0, 'Dibebaskan' => 0];
    foreach ($rows as $r) {
        $total[$label[$r['status']]] += (int) $r['jumlah'];
    }
    return [
        'kolom' => [['Anggota', ''], ['Tgl kembali', 'tgl'], ['Buku', ''], ['Jenis', ''], ['Jumlah', 'rupiah'], ['Status', ''], ['Tgl bayar', 'tgl'], ['Kuitansi', '']],
        'baris' => array_map(fn($r) => [
            $r['nama'] . ' (' . $r['kode_anggota'] . ')', $r['tgl_kembali'], $r['judul'], $r['jenis'], (int) $r['jumlah'],
            $label[$r['status']], $r['tgl_bayar'], $r['no_kuitansi'] ?? '',
        ], $rows),
        'total' => $total + ['Total' => array_sum($total)],
        'format_total' => ['Lunas' => 'rupiah', 'Belum lunas' => 'rupiah', 'Dibebaskan' => 'rupiah', 'Total' => 'rupiah'],
    ];
}

/** Pinjaman yang lewat jatuh tempo per $f['tanggal'] (default hari ini). Tidak memakai periode. */
function laporan_keterlambatan(array $f): array
{
    $t = $f['tanggal'] ?? hari_ini();
    $where = ["d.status = 'dipinjam'", 'DATEDIFF(?, d.tgl_jatuh_tempo) > ?'];
    $p = [$t, $t, max(0, (int) ($f['min_terlambat'] ?? 0) - 1)];
    if (!empty($f['kategori_id'])) {
        $where[] = 'b.kategori_id = ?';
        $p[] = $f['kategori_id'];
    }
    $rows = db_all(
        'SELECT a.nama, a.kode_anggota, a.no_hp, b.judul, e.kode_eksemplar, p.no_transaksi, d.tgl_jatuh_tempo, d.denda_per_hari,
                DATEDIFF(?, d.tgl_jatuh_tempo) AS hari
         FROM peminjaman_detail d
         JOIN peminjaman p ON p.id = d.peminjaman_id
         JOIN anggota a ON a.id = p.anggota_id
         JOIN eksemplar e ON e.id = d.eksemplar_id
         JOIN buku b ON b.id = e.buku_id
         WHERE ' . implode(' AND ', $where) . ' ORDER BY hari DESC, a.nama',
        $p
    );
    $baris = array_map(fn($r) => [
        $r['nama'] . ' (' . $r['kode_anggota'] . ')', $r['no_hp'], $r['judul'], $r['kode_eksemplar'], $r['no_transaksi'],
        $r['tgl_jatuh_tempo'], (int) $r['hari'], (int) $r['hari'] * (int) $r['denda_per_hari'],
    ], $rows);
    return [
        'kolom' => [['Anggota', ''], ['No. HP', ''], ['Judul', ''], ['Eksemplar', ''], ['No. transaksi', ''], ['Jatuh tempo', 'tgl'], ['Hari terlambat', 'angka'], ['Estimasi denda', 'rupiah']],
        'baris' => $baris,
        'total' => ['Jumlah eksemplar' => count($baris), 'Estimasi denda' => array_sum(array_column($baris, 7))],
        'format_total' => ['Estimasi denda' => 'rupiah'],
    ];
}

/** 10 judul paling sering dipinjam dan 10 anggota paling aktif dalam periode. */
function laporan_populer(array $f): array
{
    $buku = db_all(
        'SELECT b.judul, b.penulis, k.nama AS kategori, COUNT(*) AS n
         FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id
         JOIN eksemplar e ON e.id = d.eksemplar_id JOIN buku b ON b.id = e.buku_id JOIN kategori k ON k.id = b.kategori_id
         WHERE p.tgl_pinjam BETWEEN ? AND ? GROUP BY b.id ORDER BY n DESC, b.judul LIMIT 10',
        [$f['dari'], $f['sampai']]
    );
    $anggota = db_all(
        'SELECT a.nama, a.kode_anggota, COUNT(DISTINCT p.id) AS transaksi, COUNT(*) AS n
         FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id JOIN anggota a ON a.id = p.anggota_id
         WHERE p.tgl_pinjam BETWEEN ? AND ? GROUP BY a.id ORDER BY n DESC, a.nama LIMIT 10',
        [$f['dari'], $f['sampai']]
    );
    return ['blok' => [
        [
            'judul' => '10 Buku Terpopuler',
            'kolom' => [['No', 'angka'], ['Judul', ''], ['Penulis', ''], ['Kategori', ''], ['Dipinjam (eksemplar)', 'angka']],
            'baris' => array_map(fn($r, $i) => [$i + 1, $r['judul'], $r['penulis'], $r['kategori'], (int) $r['n']], $buku, array_keys($buku)),
            'total' => ['Total peminjaman 10 judul' => array_sum(array_column($buku, 'n'))],
            'format_total' => [],
        ],
        [
            'judul' => '10 Anggota Teraktif',
            'kolom' => [['No', 'angka'], ['Nama', ''], ['Kode', ''], ['Transaksi', 'angka'], ['Eksemplar dipinjam', 'angka']],
            'baris' => array_map(fn($r, $i) => [$i + 1, $r['nama'], $r['kode_anggota'], (int) $r['transaksi'], (int) $r['n']], $anggota, array_keys($anggota)),
            'total' => ['Total eksemplar 10 anggota' => array_sum(array_column($anggota, 'n'))],
            'format_total' => [],
        ],
    ]];
}

/** Jumlah eksemplar per status untuk setiap judul. Filter: kategori, rak, status (judul yang punya eksemplar berstatus itu). */
function laporan_koleksi(array $f): array
{
    $where = ['1 = 1'];
    $p = [];
    if (!empty($f['kategori_id'])) {
        $where[] = 'b.kategori_id = ?';
        $p[] = $f['kategori_id'];
    }
    if (!empty($f['rak_id'])) {
        $where[] = 'e.rak_id = ?';
        $p[] = $f['rak_id'];
    }
    $status = ['tersedia', 'dipinjam', 'direservasi', 'rusak', 'hilang'];
    $rows = db_all(
        "SELECT b.judul, k.nama AS kategori,
                SUM(e.status = 'tersedia') AS tersedia, SUM(e.status = 'dipinjam') AS dipinjam, SUM(e.status = 'direservasi') AS direservasi,
                SUM(e.status = 'rusak') AS rusak, SUM(e.status = 'hilang') AS hilang, COUNT(*) AS total
         FROM eksemplar e JOIN buku b ON b.id = e.buku_id JOIN kategori k ON k.id = b.kategori_id
         WHERE " . implode(' AND ', $where) . ' GROUP BY b.id ORDER BY k.nama, b.judul',
        $p
    );
    if (in_array($f['status'] ?? '', $status, true)) {
        $rows = array_values(array_filter($rows, fn($r) => $r[$f['status']] > 0));
    }
    $baris = array_map(fn($r) => [$r['judul'], $r['kategori'], ...array_map(fn($s) => (int) $r[$s], [...$status, 'total'])], $rows);
    $total = ['Jumlah judul' => count($baris)];
    foreach ($status as $i => $s) {
        $total[ucfirst($s)] = array_sum(array_column($baris, $i + 2));
    }
    $total['Total eksemplar'] = array_sum(array_column($baris, 7));
    return [
        'kolom' => [['Judul', ''], ['Kategori', ''], ['Tersedia', 'angka'], ['Dipinjam', 'angka'], ['Direservasi', 'angka'], ['Rusak', 'angka'], ['Hilang', 'angka'], ['Total', 'angka']],
        'baris' => $baris,
        'total' => $total,
        'format_total' => [],
    ];
}

/** Daftar anggota. Filter: status, periode daftar (hanya jika 'periode_daftar' diisi). */
function laporan_anggota(array $f): array
{
    $where = ['1 = 1'];
    $p = [];
    if (in_array($f['status'] ?? '', ['menunggu', 'aktif', 'ditolak', 'nonaktif'], true)) {
        $where[] = 'a.status = ?';
        $p[] = $f['status'];
    }
    if (!empty($f['periode_daftar'])) {
        $where[] = 'a.tgl_daftar BETWEEN ? AND ?';
        array_push($p, $f['dari'], $f['sampai']);
    }
    $rows = db_all(
        'SELECT a.kode_anggota, a.nama, a.tgl_daftar, a.tgl_berlaku, a.status,
                (SELECT COUNT(*) FROM peminjaman_detail d JOIN peminjaman p ON p.id = d.peminjaman_id WHERE p.anggota_id = a.id) AS jumlah
         FROM anggota a WHERE ' . implode(' AND ', $where) . ' ORDER BY a.kode_anggota IS NULL, a.kode_anggota, a.nama',
        $p
    );
    return [
        'kolom' => [['Kode', ''], ['Nama', ''], ['Tgl daftar', 'tgl'], ['Berlaku s.d.', 'tgl'], ['Status', ''], ['Jumlah pinjaman', 'angka']],
        'baris' => array_map(fn($r) => [$r['kode_anggota'] ?? '-', $r['nama'], $r['tgl_daftar'], $r['tgl_berlaku'], $r['status'], (int) $r['jumlah']], $rows),
        'total' => [
            'Jumlah anggota' => count($rows),
            'Aktif' => count(array_filter($rows, fn($r) => $r['status'] === 'aktif')),
            'Total pinjaman' => array_sum(array_column($rows, 'jumlah')),
        ],
        'format_total' => [],
    ];
}

// ===================== Tampilan =====================

function format_sel(mixed $v, string $format): string
{
    return match ($format) {
        'tgl' => tgl($v ?: null),
        'rupiah' => rupiah((int) $v),
        'angka' => number_format((int) $v, 0, ',', '.'),
        default => e((string) $v),
    };
}

/** Tabel satu blok laporan (dipakai layar, cetak, dan PDF). */
function tabel_laporan(array $blok, string $kelas): string
{
    $kanan = fn($fmt) => in_array($fmt, ['rupiah', 'angka'], true) ? ' class="text-end"' : '';
    $h = '<table class="' . $kelas . '"><thead><tr>';
    foreach ($blok['kolom'] as [$label, $fmt]) {
        $h .= '<th' . $kanan($fmt) . '>' . e($label) . '</th>';
    }
    $h .= '</tr></thead><tbody>';
    foreach ($blok['baris'] as $b) {
        $h .= '<tr>';
        foreach ($blok['kolom'] as $i => [, $fmt]) {
            // data-t/data-v: nilai mentah untuk export Excel (SheetJS), data-order: urutan tanggal di tabel
            $mentah = in_array($fmt, ['rupiah', 'angka'], true) ? ' data-t="n" data-v="' . (int) $b[$i] . '"' : '';
            $h .= '<td' . $kanan($fmt) . $mentah . ($fmt === 'tgl' ? ' data-order="' . e((string) $b[$i]) . '"' : '') . '>' . format_sel($b[$i], $fmt) . '</td>';
        }
        $h .= '</tr>';
    }
    if (!$blok['baris']) {
        $h .= '<tr><td colspan="' . count($blok['kolom']) . '" class="text-center text-muted">Tidak ada data untuk filter ini.</td></tr>';
    }
    $h .= '</tbody><tfoot><tr><td colspan="' . count($blok['kolom']) . '">';
    $bagian = [];
    foreach ($blok['total'] as $label => $nilai) {
        $bagian[] = e($label) . ': <strong>' . format_sel($nilai, $blok['format_total'][$label] ?? 'angka') . '</strong>';
    }
    return $h . implode(' &nbsp;·&nbsp; ', $bagian) . '</td></tr></tfoot></table>';
}

/** Ringkasan filter untuk sub judul kop laporan. */
function keterangan_filter(array $f, array $ui): string
{
    $teks = [];
    if (in_array('periode', $ui, true) || in_array('periode_daftar', $ui, true)) {
        if (!in_array('periode_daftar', $ui, true) || !empty($f['periode_daftar'])) {
            $teks[] = 'Periode ' . tgl($f['dari']) . ' s.d. ' . tgl($f['sampai']);
        }
    }
    if (!empty($f['anggota_id'])) {
        $teks[] = 'Anggota: ' . db_val('SELECT nama FROM anggota WHERE id = ?', [$f['anggota_id']]);
    }
    if (!empty($f['kategori_id'])) {
        $teks[] = 'Kategori: ' . db_val('SELECT nama FROM kategori WHERE id = ?', [$f['kategori_id']]);
    }
    if (!empty($f['rak_id'])) {
        $teks[] = 'Rak: ' . db_val('SELECT kode FROM rak WHERE id = ?', [$f['rak_id']]);
    }
    if ($f['status'] !== '') {
        $teks[] = 'Status: ' . str_replace('_', ' ', $f['status']);
    }
    if ($f['ketepatan'] !== '') {
        $teks[] = $f['ketepatan'] === 'tepat' ? 'Tepat waktu' : 'Terlambat';
    }
    if ($f['min_terlambat'] > 0) {
        $teks[] = 'Terlambat ≥ ' . $f['min_terlambat'] . ' hari';
    }
    return implode(' · ', $teks) ?: 'Semua data';
}

/**
 * Tampilkan laporan sesuai ?mode= : 'layar' (default, dengan filter), 'cetak', atau 'pdf'.
 * $ui = daftar filter: 'periode', 'periode_daftar', 'anggota', 'kategori', 'rak', 'ketepatan', 'min_terlambat',
 *       atau 'status' => [nilai => label].
 */
function tampilkan_laporan(string $judul, array $f, array $hasil, array $ui): void
{
    $mode = in_array($_GET['mode'] ?? '', ['cetak', 'pdf'], true) ? $_GET['mode'] : 'layar';
    $blok = $hasil['blok'] ?? [$hasil + ['judul' => '']];
    $sub_judul = keterangan_filter($f, $ui);
    $lebar = max(array_map(fn($b) => count($b['kolom']), $blok)) > 6;

    if ($mode !== 'layar') {
        $isi = '';
        foreach ($blok as $b) {
            $isi .= ($b['judul'] ? '<h3 style="font-size:12pt;margin:14px 0 6px">' . e($b['judul']) . '</h3>' : '') . tabel_laporan($b, 'cetak');
        }
        if ($mode === 'pdf' && function_exists('laporan_pdf')) {
            laporan_pdf($judul, $sub_judul, $isi, $lebar);
            return;
        }
        $judul_cetak = strtoupper($judul);
        $kertas = $lebar ? 'A4 landscape' : 'A4';
        require ROOT_DIR . '/includes/layout/print_header.php';
        echo $isi;
        echo '<div class="ttd">' . e(tgl(hari_ini())) . '<br>Petugas,<br><br><br><br>' . e(user()['nama']) . '</div>';
        require ROOT_DIR . '/includes/layout/print_footer.php';
        return;
    }

    $query = $_GET;
    unset($query['mode']);
    $url_mode = fn(string $m) => '?' . http_build_query($query + ['mode' => $m]);
    $js_tambahan = is_file(ROOT_DIR . '/assets/js/export.js') ? ['assets/vendor/xlsx.full.min.js', 'assets/js/export.js'] : [];
    require ROOT_DIR . '/includes/layout/header.php';
    ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <a class="small text-decoration-none" href="<?= url('modules/laporan/index.php') ?>"><i class="bi bi-arrow-left"></i> Semua laporan</a>
            <h1 class="h4 mb-0"><?= e($judul) ?></h1>
            <div class="small text-muted"><?= e($sub_judul) ?></div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="<?= e($url_mode('cetak')) ?>" target="_blank"><i class="bi bi-printer"></i> Cetak</a>
            <?php if (function_exists('laporan_pdf')): ?>
                <a class="btn btn-outline-danger" href="<?= e($url_mode('pdf')) ?>"><i class="bi bi-filetype-pdf"></i> PDF</a>
            <?php endif; ?>
            <?php if ($js_tambahan): ?>
                <button class="btn btn-outline-success" type="button" data-export-excel="<?= e(preg_replace('/[^a-z0-9]+/', '-', strtolower($judul))) ?>"><i class="bi bi-file-earmark-excel"></i> Excel</button>
            <?php endif; ?>
        </div>
    </div>
    <?php form_filter_laporan($f, $ui); ?>
    <?php foreach ($blok as $b): ?>
        <div class="card shadow-sm mb-4">
            <?php if ($b['judul']): ?><div class="card-header bg-white fw-semibold"><?= e($b['judul']) ?></div><?php endif; ?>
            <div class="card-body table-responsive"><?= tabel_laporan($b, 'table table-sm table-striped align-middle tabel-laporan') ?></div>
        </div>
    <?php endforeach; ?>
    <?php
    require ROOT_DIR . '/includes/layout/footer.php';
}

function form_filter_laporan(array $f, array $ui): void
{
    $pilih = function (string $nama, string $label, array $opsi, $nilai) {
        echo '<div class="col-md-3"><label class="form-label small" for="' . $nama . '">' . e($label) . '</label><select class="form-select" id="' . $nama . '" name="' . $nama . '"><option value="">Semua</option>';
        foreach ($opsi as $v => $l) {
            echo '<option value="' . e((string) $v) . '"' . ((string) $v === (string) $nilai ? ' selected' : '') . '>' . e($l) . '</option>';
        }
        echo '</select></div>';
    };
    echo '<form class="card card-body shadow-sm mb-4 no-print" method="get"><div class="row g-3 align-items-end">';
    if (in_array('periode', $ui, true) || in_array('periode_daftar', $ui, true)) {
        if (in_array('periode_daftar', $ui, true)) {
            echo '<div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" id="periode_daftar" name="periode_daftar" value="1"'
                . (!empty($f['periode_daftar']) ? ' checked' : '') . '><label class="form-check-label small" for="periode_daftar">Saring tgl daftar</label></div></div>';
        }
        echo '<div class="col-md-2"><label class="form-label small" for="dari">Dari</label><input class="form-control" type="date" id="dari" name="dari" value="' . e($f['dari']) . '"></div>';
        echo '<div class="col-md-2"><label class="form-label small" for="sampai">Sampai</label><input class="form-control" type="date" id="sampai" name="sampai" value="' . e($f['sampai']) . '"></div>';
    }
    if (in_array('anggota', $ui, true)) {
        $pilih('anggota_id', 'Anggota', array_column(db_all("SELECT id, CONCAT(nama, ' (', COALESCE(kode_anggota, '-'), ')') AS l FROM anggota ORDER BY nama"), 'l', 'id'), $f['anggota_id']);
    }
    if (in_array('kategori', $ui, true)) {
        $pilih('kategori_id', 'Kategori', array_column(db_all('SELECT id, nama FROM kategori ORDER BY nama'), 'nama', 'id'), $f['kategori_id']);
    }
    if (in_array('rak', $ui, true)) {
        $pilih('rak_id', 'Rak', array_column(db_all('SELECT id, kode FROM rak ORDER BY kode'), 'kode', 'id'), $f['rak_id']);
    }
    if (isset($ui['status'])) {
        $pilih('status', 'Status', $ui['status'], $f['status']);
    }
    if (in_array('ketepatan', $ui, true)) {
        $pilih('ketepatan', 'Ketepatan', ['tepat' => 'Tepat waktu', 'terlambat' => 'Terlambat'], $f['ketepatan']);
    }
    if (in_array('min_terlambat', $ui, true)) {
        echo '<div class="col-md-2"><label class="form-label small" for="min_terlambat">Terlambat minimal (hari)</label><input class="form-control" type="number" min="0" id="min_terlambat" name="min_terlambat" value="' . (int) $f['min_terlambat'] . '"></div>';
    }
    echo '<div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Tampilkan</button></div></div></form>';
}

// ===================== Export PDF (P1) =====================
// Hanya tersedia jika Dompdf terpasang (composer install). Tanpa vendor/, tombol PDF disembunyikan.
if (is_file(ROOT_DIR . '/vendor/autoload.php')) {
    function laporan_pdf(string $judul, string $sub_judul, string $isi_tabel, bool $landscape): void
    {
        require_once ROOT_DIR . '/vendor/autoload.php';
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #000; }
            .kop { border-bottom: 3px double #000; padding-bottom: 4px; margin-bottom: 10px; }
            .kop h1 { font-size: 13pt; margin: 0; } .kanan { float: right; text-align: right; font-size: 8pt; }
            h2 { font-size: 11pt; text-align: center; margin: 4px 0 0; } .sub { text-align: center; margin: 0 0 10px; }
            table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #555; padding: 3px 4px; vertical-align: top; }
            th { background: #eee; } tfoot td { font-weight: bold; background: #f5f5f5; } .text-end { text-align: right; }
            .text-center { text-align: center; } .text-muted { color: #666; }
        </style></head><body>'
            . '<div class="kop"><div class="kanan">Dicetak: ' . e(tgl(hari_ini()) . ' ' . date('H:i')) . '<br>Oleh: ' . e(user()['nama'] ?? '-') . '</div>'
            . '<h1>' . e(setting('nama_perpustakaan')) . '</h1><div>' . e(setting('alamat_perpustakaan')) . '</div></div>'
            . '<h2>' . e(strtoupper($judul)) . '</h2><p class="sub">' . e($sub_judul) . '</p>'
            . $isi_tabel . '</body></html>';
        $pdf = new Dompdf\Dompdf(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false]);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', $landscape ? 'landscape' : 'portrait');
        $pdf->render();
        $nama = preg_replace('/[^a-z0-9]+/', '-', strtolower($judul)) . '-' . date('Ymd') . '.pdf';
        $pdf->stream($nama, ['Attachment' => true]);
    }
}
