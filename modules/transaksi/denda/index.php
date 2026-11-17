<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$role = user()['role'];
$kode = strtoupper(trim((string) ($_GET['anggota'] ?? '')));
$status = in_array($_GET['status'] ?? 'belum_lunas', ['belum_lunas', 'lunas', 'dibebaskan', 'semua'], true) ? ($_GET['status'] ?? 'belum_lunas') : 'belum_lunas';

$anggota = $kode !== '' ? db_row('SELECT id, nama, kode_anggota FROM anggota WHERE kode_anggota = ?', [$kode]) : null;
$sql = "SELECT dn.*, a.nama AS anggota, a.kode_anggota, k.no_pengembalian, k.tgl_kembali, e.kode_eksemplar, b.judul, u.nama AS penerima
        FROM denda dn
        JOIN anggota a ON a.id = dn.anggota_id
        JOIN pengembalian k ON k.id = dn.pengembalian_id
        JOIN peminjaman_detail d ON d.id = k.peminjaman_detail_id
        JOIN eksemplar e ON e.id = d.eksemplar_id
        JOIN buku b ON b.id = e.buku_id
        LEFT JOIN users u ON u.id = dn.petugas_id";
if ($anggota) {
    $data = db_all("$sql WHERE dn.anggota_id = ? AND dn.status = 'belum_lunas' ORDER BY dn.id", [$anggota['id']]);
} else {
    $data = db_all("$sql WHERE (? = 'semua' OR dn.status = ?) ORDER BY dn.status = 'belum_lunas' DESC, k.tgl_kembali DESC, dn.id DESC", [$status, $status]);
}
$total_belum = (int) db_val("SELECT COALESCE(SUM(jumlah), 0) FROM denda WHERE status = 'belum_lunas'");
$warna = ['belum_lunas' => 'danger', 'lunas' => 'success', 'dibebaskan' => 'secondary'];

$judul = 'Denda';
require ROOT_DIR . '/includes/layout/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div><h1 class="h4 mb-0">Denda</h1><div class="small text-muted">Total belum lunas: <strong class="text-danger"><?= rupiah($total_belum) ?></strong></div></div>
    <form class="d-flex gap-2" method="get">
        <input class="form-control font-monospace" name="anggota" value="<?= e($kode) ?>" placeholder="Kode anggota" aria-label="Kode anggota">
        <button class="btn btn-primary text-nowrap"><i class="bi bi-cash-coin"></i> Bayar denda</button>
    </form>
</div>

<?php if ($kode !== '' && !$anggota): ?>
    <div class="alert alert-warning">Anggota dengan kode <?= e($kode) ?> tidak ditemukan.</div>
<?php endif; ?>

<?php if ($anggota): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong><?= e($anggota['nama']) ?></strong> <span class="font-monospace small"><?= e($anggota['kode_anggota']) ?></span>
            · <a href="<?= url('modules/transaksi/denda/index.php') ?>" class="small">tutup</a></div>
        <div class="card-body">
            <?php if (!$data): ?>
                <p class="text-success mb-0"><i class="bi bi-check-circle"></i> Tidak ada denda yang belum lunas.</p>
            <?php else: ?>
            <form method="post" action="<?= url('modules/transaksi/denda/bayar.php') ?>" id="form-bayar">
                <?= csrf_field() ?>
                <input type="hidden" name="anggota" value="<?= e($anggota['kode_anggota']) ?>">
                <table class="table align-middle">
                    <thead><tr><th><input class="form-check-input" type="checkbox" id="pilih-semua" checked aria-label="Pilih semua"></th><th>Jenis</th><th>Buku</th><th>Keterangan</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                    <?php foreach ($data as $d): ?>
                        <tr>
                            <td><input class="form-check-input pilih-denda" type="checkbox" name="denda_ids[]" value="<?= $d['id'] ?>" data-jumlah="<?= (int) $d['jumlah'] ?>" checked aria-label="Pilih"></td>
                            <td><span class="badge text-bg-warning"><?= e($d['jenis']) ?></span></td>
                            <td><?= e($d['judul']) ?> <div class="small text-muted font-monospace"><?= e($d['kode_eksemplar']) ?> · kembali <?= tgl($d['tgl_kembali']) ?></div></td>
                            <td class="small"><?= e($d['keterangan']) ?></td>
                            <td class="text-end"><?= rupiah((int) $d['jumlah']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <div>Total dibayar: <strong class="fs-5" id="total-bayar"></strong></div>
                    <button class="btn btn-success"><i class="bi bi-check2-circle"></i> Tandai lunas &amp; cetak kuitansi</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <ul class="nav nav-pills mb-3">
        <?php foreach (['belum_lunas' => 'Belum lunas', 'lunas' => 'Lunas', 'dibebaskan' => 'Dibebaskan', 'semua' => 'Semua'] as $k => $l): ?>
            <li class="nav-item"><a class="nav-link <?= $status === $k ? 'active' : '' ?>" href="?status=<?= $k ?>"><?= $l ?></a></li>
        <?php endforeach; ?>
    </ul>
    <div class="card shadow-sm"><div class="card-body">
    <table class="table table-hover align-middle datatable">
        <thead><tr><th>Anggota</th><th>Jenis</th><th>Buku</th><th>Kembali</th><th class="text-end">Jumlah</th><th>Status</th><th>Kuitansi</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($data as $d): ?>
            <tr>
                <td><?= e($d['anggota']) ?> <div class="small text-muted font-monospace"><?= e($d['kode_anggota']) ?></div></td>
                <td><?= e($d['jenis']) ?></td>
                <td class="small"><?= e($d['judul']) ?></td>
                <td data-order="<?= e($d['tgl_kembali']) ?>"><?= tgl($d['tgl_kembali']) ?></td>
                <td class="text-end" data-order="<?= (int) $d['jumlah'] ?>"><?= rupiah((int) $d['jumlah']) ?></td>
                <td><span class="badge text-bg-<?= $warna[$d['status']] ?>"><?= e(str_replace('_', ' ', $d['status'])) ?></span>
                    <?php if ($d['tgl_bayar']): ?><div class="small text-muted"><?= tgl($d['tgl_bayar']) ?> · <?= e($d['penerima']) ?></div><?php endif; ?></td>
                <td><?php if ($d['no_kuitansi']): ?><a href="<?= url('modules/transaksi/denda/kuitansi.php?no=' . urlencode($d['no_kuitansi'])) ?>" target="_blank"><?= e($d['no_kuitansi']) ?></a><?php endif; ?></td>
                <td class="text-end text-nowrap">
                    <?php if ($d['status'] === 'belum_lunas'): ?>
                        <?php if ($role === 'petugas'): ?>
                            <a class="btn btn-sm btn-success" href="?anggota=<?= urlencode($d['kode_anggota']) ?>">Bayar</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-bebas" data-id="<?= $d['id'] ?>"
                                    data-info="<?= e($d['anggota'] . ' — ' . rupiah((int) $d['jumlah'])) ?>">Bebaskan</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div></div>
<?php endif; ?>

<?php if ($role === 'admin'): ?>
<div class="modal fade" id="modal-bebas" tabindex="-1" aria-labelledby="judul-bebas" aria-hidden="true">
    <div class="modal-dialog"><form class="modal-content" method="post" action="<?= url('modules/transaksi/denda/bebaskan.php') ?>">
        <?= csrf_field() ?><input type="hidden" name="id" id="bebas-id">
        <div class="modal-header"><h2 class="modal-title h5" id="judul-bebas">Bebaskan denda</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <p id="bebas-info" class="fw-semibold"></p>
            <label class="form-label" for="alasan">Alasan pembebasan (wajib)</label>
            <textarea class="form-control" id="alasan" name="alasan" rows="3" required maxlength="200"></textarea>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Bebaskan</button></div>
    </form></div>
</div>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rupiah = n => 'Rp' + n.toLocaleString('id-ID');
    const kotak = [...document.querySelectorAll('.pilih-denda')];
    const total = document.getElementById('total-bayar');
    const hitung = () => { if (total) total.textContent = rupiah(kotak.filter(k => k.checked).reduce((s, k) => s + Number(k.dataset.jumlah), 0)); };
    kotak.forEach(k => k.addEventListener('change', hitung));
    document.getElementById('pilih-semua')?.addEventListener('change', ev => { kotak.forEach(k => k.checked = ev.target.checked); hitung(); });
    hitung();
    document.getElementById('modal-bebas')?.addEventListener('show.bs.modal', ev => {
        document.getElementById('bebas-id').value = ev.relatedTarget.dataset.id;
        document.getElementById('bebas-info').textContent = ev.relatedTarget.dataset.info;
    });
});
</script>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
