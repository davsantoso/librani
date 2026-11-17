<?php
require __DIR__ . '/../../includes/init.php';
require_role('anggota');

$a = anggota_saya();
$data = db_all(
    "SELECT d.id, d.tgl_jatuh_tempo, d.status, d.jumlah_perpanjangan, p.no_transaksi, p.tgl_pinjam,
            e.kode_eksemplar, b.judul, b.penulis, b.foto_thumb, b.foto_cover, k.tgl_kembali, k.hari_terlambat
     FROM peminjaman_detail d
     JOIN peminjaman p ON p.id = d.peminjaman_id
     JOIN eksemplar e ON e.id = d.eksemplar_id
     JOIN buku b ON b.id = e.buku_id
     LEFT JOIN pengembalian k ON k.peminjaman_detail_id = d.id
     WHERE p.anggota_id = ? ORDER BY d.status = 'dipinjam' DESC, p.tgl_pinjam DESC, d.id DESC",
    [$a['id']]
);
$bisa_perpanjang = function_exists('alasan_tidak_bisa_perpanjang');

$judul = 'Pinjaman Saya';
require ROOT_DIR . '/includes/layout/header.php';
?>
<h1 class="h4 mb-3">Pinjaman Saya</h1>
<div class="card shadow-sm"><div class="card-body">
<table class="table align-middle datatable">
    <thead><tr><th>Buku</th><th>Tgl pinjam</th><th>Jatuh tempo</th><th>Status</th><?php if ($bisa_perpanjang): ?><th></th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($data as $x): ?>
        <?php $telat = $x['status'] === 'dipinjam' && $x['tgl_jatuh_tempo'] < hari_ini(); ?>
        <tr>
            <td><div class="d-flex gap-2 align-items-center"><div class="cover-kecil"><?= cover_html($x) ?></div>
                <div><div class="fw-semibold"><?= e($x['judul']) ?></div><div class="small text-muted"><?= e($x['penulis']) ?> · <span class="font-monospace"><?= e($x['kode_eksemplar']) ?></span></div></div></div></td>
            <td data-order="<?= e($x['tgl_pinjam']) ?>"><?= tgl($x['tgl_pinjam']) ?></td>
            <td data-order="<?= e($x['tgl_jatuh_tempo']) ?>" class="<?= $telat ? 'text-danger fw-bold' : '' ?>"><?= tgl($x['tgl_jatuh_tempo']) ?>
                <?= $x['jumlah_perpanjangan'] ? '<div class="small text-muted">diperpanjang ' . (int) $x['jumlah_perpanjangan'] . 'x</div>' : '' ?></td>
            <td>
                <?php if ($x['tgl_kembali']): ?>
                    <span class="badge text-bg-secondary">Kembali <?= tgl($x['tgl_kembali']) ?></span>
                    <?= $x['hari_terlambat'] ? '<div class="small text-danger">terlambat ' . (int) $x['hari_terlambat'] . ' hari</div>' : '' ?>
                <?php elseif ($telat): ?><span class="badge text-bg-danger">Terlambat</span>
                <?php else: ?><span class="badge text-bg-warning">Dipinjam</span><?php endif; ?>
            </td>
            <?php if ($bisa_perpanjang): ?>
            <td class="text-end">
                <?php if ($x['status'] === 'dipinjam'): ?>
                    <?php $alasan = alasan_tidak_bisa_perpanjang($x['id'], user()['id']); ?>
                    <form method="post" action="<?= url('modules/transaksi/perpanjangan/proses.php') ?>" class="konfirmasi"
                          data-judul="Perpanjang pinjaman?" data-pesan="Jatuh tempo bertambah <?= (int) setting('lama_perpanjangan') ?> hari.">
                        <?= csrf_field() ?><input type="hidden" name="detail_id" value="<?= $x['id'] ?>">
                        <button class="btn btn-sm btn-outline-primary" <?= $alasan ? 'disabled title="' . e($alasan) . '"' : '' ?>><i class="bi bi-calendar-plus"></i> Perpanjang</button>
                    </form>
                    <?php if ($alasan): ?><div class="small text-muted"><?= e($alasan) ?></div><?php endif; ?>
                <?php endif; ?>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
