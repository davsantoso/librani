<?php
require __DIR__ . '/../../includes/init.php';
require_login();

$role = user()['role'];
$anggota = $role === 'anggota' ? anggota_saya() : null;
cek_reservasi_kadaluarsa();

if ($role === 'anggota') {
    $r = $anggota && $anggota['status'] === 'aktif' ? ringkasan_anggota($anggota['id']) : null;
    $reservasi = $r ? reservasi_anggota($anggota['id']) : [];
} else {
    $s = statistik_sirkulasi();
    $grafik = grafik_30_hari();
    $jt = jatuh_tempo_dan_terlambat();
    $populer = $role === 'admin' ? buku_populer(date('Y-m-01'), hari_ini(), 5) : [];
}

$judul = 'Dashboard';
$js_tambahan = $role === 'anggota' ? [] : ['assets/vendor/chart.umd.js', 'assets/js/dashboard.js'];
require ROOT_DIR . '/includes/layout/header.php';
$link_kembali = fn(string $no) => user()['role'] === 'petugas'
    ? '<a class="btn btn-sm btn-outline-primary" href="' . url('modules/transaksi/pengembalian/form.php?q=' . urlencode($no)) . '">Proses</a>' : '';
?>
<h1 class="h4 mb-1">Dashboard</h1>
<p class="text-muted">Selamat datang, <strong><?= e(user()['nama']) ?></strong> · <?= tgl(hari_ini()) ?></p>

<?php if ($role === 'anggota'): ?>
    <?php if (!$anggota || $anggota['status'] !== 'aktif'): ?>
        <?php if (($anggota['status'] ?? '') === 'menunggu'): ?>
            <div class="alert alert-warning">
                <h2 class="h5"><i class="bi bi-hourglass-split"></i> Akun menunggu verifikasi</h2>
                <p class="mb-0">Datang ke meja layanan dengan membawa kartu identitas (no. <?= e($anggota['no_identitas']) ?>).
                    Sambil menunggu, Anda bisa melihat <a href="<?= url('modules/katalog/index.php') ?>">katalog buku</a>.</p>
            </div>
        <?php elseif (($anggota['status'] ?? '') === 'ditolak'): ?>
            <div class="alert alert-danger">
                <h2 class="h5"><i class="bi bi-x-circle"></i> Pendaftaran ditolak</h2>
                <p class="mb-0">Alasan: <?= e($anggota['alasan_tolak']) ?></p>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary">Keanggotaan Anda nonaktif. Hubungi petugas untuk mengaktifkan kembali.</div>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($anggota['tgl_berlaku'] < hari_ini()): ?>
            <div class="alert alert-danger">Masa berlaku keanggotaan habis sejak <?= tgl($anggota['tgl_berlaku']) ?>. Hubungi petugas untuk memperpanjang.</div>
        <?php endif; ?>
        <div class="row g-3 mb-4">
            <div class="col-sm-4"><div class="card shadow-sm kartu-angka"><div class="card-body">
                <div class="text-muted small">Sisa kuota pinjam</div>
                <div class="angka"><?= $r['sisa_kuota'] ?> <span class="fs-6 text-muted">dari <?= $r['maks_pinjam'] ?></span></div>
            </div></div></div>
            <div class="col-sm-4"><div class="card shadow-sm kartu-angka"><div class="card-body">
                <div class="text-muted small">Denda belum lunas</div>
                <div class="angka <?= $r['denda_belum_lunas'] ? 'text-danger' : '' ?>"><?= rupiah($r['denda_belum_lunas']) ?></div>
            </div></div></div>
            <div class="col-sm-4"><div class="card shadow-sm kartu-angka"><div class="card-body">
                <div class="text-muted small">Kode anggota · berlaku s.d.</div>
                <div class="fw-semibold font-monospace"><?= e($anggota['kode_anggota']) ?></div>
                <div class="small"><?= tgl($anggota['tgl_berlaku']) ?> · <a href="<?= url('modules/master/anggota/kartu.php') ?>" target="_blank">kartu</a></div>
            </div></div></div>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Pinjaman aktif</div>
            <div class="card-body">
                <?php if (!$r['aktif']): ?><p class="text-muted mb-0">Tidak ada pinjaman aktif. <a href="<?= url('modules/katalog/index.php') ?>">Cari buku di katalog</a>.</p><?php endif; ?>
                <div class="d-grid gap-2">
                <?php foreach ($r['aktif'] as $x): ?>
                    <div class="d-flex gap-3 align-items-center border rounded p-2">
                        <div class="cover-kecil"><?= cover_html($x) ?></div>
                        <div class="flex-grow-1"><div class="fw-semibold"><?= e($x['judul']) ?></div><div class="small text-muted"><?= e($x['penulis']) ?> · <?= e($x['kode_eksemplar']) ?></div></div>
                        <div class="text-end">
                            <div class="small">Jatuh tempo</div>
                            <div class="<?= $x['terlambat'] ? 'text-danger fw-bold' : 'fw-semibold' ?>"><?= tgl($x['tgl_jatuh_tempo']) ?></div>
                            <?php if ($x['terlambat']): ?><span class="badge text-bg-danger">Terlambat</span><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php require ROOT_DIR . '/includes/layout/reservasi_anggota.php'; ?>
    <?php endif; ?>

<?php else: ?>
    <?php
    $kartu = [
        ['Total judul', $s['total_judul'], 'bi-book', '', 'modules/master/buku/index.php'],
        ['Total eksemplar', $s['total_eksemplar'], 'bi-collection', '', 'modules/master/eksemplar/index.php'],
        ['Sedang dipinjam', $s['dipinjam'], 'bi-box-arrow-up-right', '', 'modules/transaksi/peminjaman/index.php?status=aktif'],
        ['Peminjaman hari ini', $s['pinjam_hari_ini'], 'bi-calendar-check', '', 'modules/transaksi/peminjaman/index.php'],
        ['Eksemplar terlambat', $s['terlambat'], 'bi-exclamation-triangle', $s['terlambat'] ? 'text-danger' : '', 'modules/transaksi/peminjaman/index.php?status=terlambat'],
        ['Denda belum lunas', rupiah($s['denda_belum_lunas']), 'bi-cash-coin', $s['denda_belum_lunas'] ? 'text-danger' : '', 'modules/transaksi/denda/index.php'],
        ['Menunggu verifikasi', $s['menunggu_verifikasi'], 'bi-person-check', $s['menunggu_verifikasi'] ? 'text-warning' : '', 'modules/master/anggota/index.php?status=menunggu'],
    ];
    if ($s['reservasi_siap']) {
        $kartu[] = ['Reservasi siap diambil', $s['reservasi_siap'], 'bi-bookmark-star', 'text-info', 'modules/transaksi/reservasi/index.php?status=siap'];
    }
    ?>
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        <?php foreach ($kartu as [$label, $nilai, $ikon, $kelas, $tautan]): ?>
            <div class="col"><a class="card shadow-sm kartu-angka h-100 text-decoration-none text-reset" href="<?= url($tautan) ?>"><div class="card-body">
                <div class="text-muted small"><i class="bi <?= $ikon ?>"></i> <?= e($label) ?></div>
                <div class="angka <?= $kelas ?>"><?= e((string) $nilai) ?></div>
            </div></a></div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="<?= $populer ? 'col-xl-8' : 'col-12' ?>">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Jumlah transaksi peminjaman, 30 hari terakhir</div>
                <div class="card-body">
                    <div style="height: 260px"><canvas id="grafik-pinjam" role="img" aria-label="Grafik garis jumlah peminjaman per hari selama 30 hari terakhir"
                        data-grafik="<?= e(json_encode($grafik)) ?>"></canvas></div>
                    <details class="mt-2 small"><summary>Lihat sebagai tabel</summary>
                        <table class="table table-sm mt-2 mb-0"><thead><tr><th>Tanggal</th><th class="text-end">Peminjaman</th></tr></thead><tbody>
                        <?php foreach (array_filter($grafik, fn($g) => $g['jumlah'] > 0) as $g): ?>
                            <tr><td><?= tgl($g['tgl']) ?></td><td class="text-end"><?= $g['jumlah'] ?></td></tr>
                        <?php endforeach; ?>
                        </tbody></table>
                    </details>
                </div>
            </div>
        </div>
        <?php if ($populer): ?>
        <div class="col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">5 buku terpopuler bulan ini</div>
                <div class="card-body d-grid gap-2">
                    <?php foreach ($populer as $n => $b): ?>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="text-muted fw-semibold" style="width:1.2rem"><?= $n + 1 ?></span>
                            <div class="cover-kecil" style="width:36px"><?= cover_html($b) ?></div>
                            <div class="flex-grow-1 small"><div class="fw-semibold"><?= e($b['judul']) ?></div><div class="text-muted"><?= e($b['penulis']) ?></div></div>
                            <span class="badge text-bg-light"><?= (int) $b['jumlah'] ?>x</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <?php foreach (['hari_ini' => 'Jatuh tempo hari ini', 'terlambat' => 'Terlambat'] as $k => $label): ?>
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold"><?= $label ?> <span class="badge <?= $k === 'terlambat' ? 'text-bg-danger' : 'text-bg-warning' ?>"><?= count($jt[$k]) ?></span></div>
                <div class="card-body">
                    <?php if (!$jt[$k]): ?><p class="text-muted mb-0">Tidak ada.</p><?php else: ?>
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Anggota</th><th>Buku</th><th><?= $k === 'terlambat' ? 'Terlambat' : 'Transaksi' ?></th><th></th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($jt[$k], 0, 10) as $x): ?>
                            <tr>
                                <td class="small"><?= e($x['anggota']) ?><div class="text-muted font-monospace"><?= e($x['kode_anggota']) ?></div></td>
                                <td class="small"><?= e($x['judul']) ?><div class="text-muted font-monospace"><?= e($x['kode_eksemplar']) ?></div></td>
                                <td class="small"><?= $k === 'terlambat' ? '<span class="text-danger fw-semibold">' . (int) $x['hari_terlambat'] . ' hari</span>' : e($x['no_transaksi']) ?></td>
                                <td class="text-end"><?= $link_kembali($x['no_transaksi']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (count($jt[$k]) > 10): ?><a class="small" href="<?= url('modules/transaksi/peminjaman/index.php?status=terlambat') ?>">Lihat semua (<?= count($jt[$k]) ?>)</a><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
