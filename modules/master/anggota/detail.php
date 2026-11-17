<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

$id = (int) ($_GET['id'] ?? 0);
$a = db_row(
    'SELECT a.*, u.email, u.username, u.is_active, v.nama AS verifikator
     FROM anggota a JOIN users u ON u.id = a.user_id LEFT JOIN users v ON v.id = a.verified_by WHERE a.id = ?',
    [$id]
);
if (!$a) {
    flash('gagal', 'Anggota tidak ditemukan.');
    redirect('modules/master/anggota/index.php');
}
$pinjaman = db_all(
    "SELECT p.no_transaksi, p.tgl_pinjam, b.judul, b.foto_thumb, b.foto_cover, e.kode_eksemplar, d.tgl_jatuh_tempo, d.status, k.tgl_kembali
     FROM peminjaman p
     JOIN peminjaman_detail d ON d.peminjaman_id = p.id
     JOIN eksemplar e ON e.id = d.eksemplar_id
     JOIN buku b ON b.id = e.buku_id
     LEFT JOIN pengembalian k ON k.peminjaman_detail_id = d.id
     WHERE p.anggota_id = ? ORDER BY p.tgl_pinjam DESC, d.id DESC",
    [$id]
);
$denda_belum = (int) db_val("SELECT COALESCE(SUM(jumlah), 0) FROM denda WHERE anggota_id = ? AND status = 'belum_lunas'", [$id]);
$warna = ['menunggu' => 'warning', 'aktif' => 'success', 'ditolak' => 'danger', 'nonaktif' => 'secondary'];

$judul = $a['nama'];
require ROOT_DIR . '/includes/layout/header.php';
?>
<a class="btn btn-sm btn-link px-0 mb-2" href="<?= url('modules/master/anggota/index.php') ?>"><i class="bi bi-arrow-left"></i> Daftar anggota</a>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm"><div class="card-body">
            <div class="d-flex gap-3">
                <?php if ($a['foto']): ?>
                    <img src="<?= url('uploads/anggota/' . $a['foto']) ?>" alt="Foto <?= e($a['nama']) ?>" class="rounded" style="width:110px;height:140px;object-fit:cover">
                <?php else: ?>
                    <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width:110px;height:140px"><i class="bi bi-person fs-1"></i></div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <h1 class="h4 mb-1"><?= e($a['nama']) ?></h1>
                    <div class="mb-2"><span class="badge text-bg-<?= $warna[$a['status']] ?>"><?= e($a['status']) ?></span>
                        <span class="font-monospace ms-2"><?= e($a['kode_anggota'] ?? '') ?></span></div>
                    <dl class="row small mb-0">
                        <dt class="col-sm-4">No. identitas</dt><dd class="col-sm-8"><?= e($a['no_identitas']) ?></dd>
                        <dt class="col-sm-4">Jenis kelamin / lahir</dt><dd class="col-sm-8"><?= $a['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>, <?= tgl($a['tgl_lahir']) ?></dd>
                        <dt class="col-sm-4">Alamat</dt><dd class="col-sm-8"><?= e($a['alamat']) ?></dd>
                        <dt class="col-sm-4">No. HP / email</dt><dd class="col-sm-8"><?= e($a['no_hp']) ?> · <?= e($a['email']) ?></dd>
                        <dt class="col-sm-4">Username</dt><dd class="col-sm-8"><?= e($a['username']) ?></dd>
                        <dt class="col-sm-4">Daftar</dt><dd class="col-sm-8"><?= tgl($a['tgl_daftar']) ?></dd>
                        <dt class="col-sm-4">Berlaku s.d.</dt><dd class="col-sm-8 <?= $a['tgl_berlaku'] && $a['tgl_berlaku'] < hari_ini() ? 'text-danger fw-semibold' : '' ?>"><?= tgl($a['tgl_berlaku']) ?></dd>
                        <?php if ($a['verifikator']): ?><dt class="col-sm-4">Diverifikasi oleh</dt><dd class="col-sm-8"><?= e($a['verifikator']) ?></dd><?php endif; ?>
                        <?php if ($a['alasan_tolak']): ?><dt class="col-sm-4">Alasan ditolak</dt><dd class="col-sm-8"><?= e($a['alasan_tolak']) ?></dd><?php endif; ?>
                        <dt class="col-sm-4">Denda belum lunas</dt><dd class="col-sm-8 <?= $denda_belum ? 'text-danger fw-semibold' : '' ?>"><?= rupiah($denda_belum) ?></dd>
                    </dl>
                </div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-body d-grid gap-2">
            <?php if ($a['status'] === 'menunggu'): ?>
                <form method="post" action="<?= url('modules/master/anggota/verifikasi.php') ?>" class="d-grid konfirmasi" data-judul="Setujui anggota?" data-pesan="Kode anggota akan dibuat dan akun langsung aktif.">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
                    <button class="btn btn-success" name="aksi" value="setujui"><i class="bi bi-person-check"></i> Setujui</button>
                </form>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-tolak"><i class="bi bi-person-x"></i> Tolak</button>
            <?php else: ?>
                <?php if ($a['kode_anggota']): ?>
                    <a class="btn btn-outline-primary" href="<?= url('modules/master/anggota/kartu.php?id=' . $id) ?>" target="_blank"><i class="bi bi-person-vcard"></i> Cetak kartu anggota</a>
                    <form method="post" action="<?= url('modules/master/anggota/verifikasi.php') ?>" class="d-grid konfirmasi" data-judul="Perpanjang keanggotaan?" data-pesan="Masa berlaku ditambah <?= (int) setting('masa_berlaku_anggota') ?> hari.">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-outline-success" name="aksi" value="perpanjang"><i class="bi bi-calendar-plus"></i> Perpanjang masa berlaku</button>
                    </form>
                <?php endif; ?>
                <a class="btn btn-outline-secondary" href="<?= url('modules/master/anggota/form.php?id=' . $id) ?>"><i class="bi bi-pencil"></i> Ubah data</a>
            <?php endif; ?>
            <form method="post" action="<?= url('modules/master/anggota/hapus.php') ?>" class="d-grid <?= $a['status'] === 'nonaktif' ? '' : 'konfirmasi' ?>"
                  data-pesan="Anggota yang pernah bertransaksi akan dinonaktifkan, bukan dihapus.">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
                <?php if ($a['status'] === 'nonaktif'): ?>
                    <button class="btn btn-outline-success" name="aksi" value="aktifkan"><i class="bi bi-arrow-counterclockwise"></i> Aktifkan kembali</button>
                <?php else: ?>
                    <button class="btn btn-outline-danger" name="aksi" value="hapus"><i class="bi bi-trash"></i> Hapus / nonaktifkan</button>
                <?php endif; ?>
            </form>
        </div></div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white fw-semibold">Riwayat peminjaman</div>
    <div class="card-body">
        <table class="table table-sm align-middle datatable">
            <thead><tr><th>Buku</th><th>No. transaksi</th><th>Pinjam</th><th>Jatuh tempo</th><th>Kembali</th></tr></thead>
            <tbody>
            <?php foreach ($pinjaman as $p): ?>
                <?php $telat = $p['status'] === 'dipinjam' && $p['tgl_jatuh_tempo'] < hari_ini(); ?>
                <tr>
                    <td><div class="d-flex gap-2 align-items-center"><div class="cover-kecil" style="width:32px"><?= cover_html($p) ?></div>
                        <div><?= e($p['judul']) ?><div class="small text-muted font-monospace"><?= e($p['kode_eksemplar']) ?></div></div></div></td>
                    <td><?= e($p['no_transaksi']) ?></td>
                    <td data-order="<?= e($p['tgl_pinjam']) ?>"><?= tgl($p['tgl_pinjam']) ?></td>
                    <td class="<?= $telat ? 'text-danger fw-semibold' : '' ?>"><?= tgl($p['tgl_jatuh_tempo']) ?></td>
                    <td><?= $p['tgl_kembali'] ? tgl($p['tgl_kembali']) : '<span class="badge text-bg-' . ($telat ? 'danger">Terlambat' : 'warning">Dipinjam') . '</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-tolak" tabindex="-1" aria-labelledby="judul-tolak" aria-hidden="true">
    <div class="modal-dialog"><form class="modal-content" method="post" action="<?= url('modules/master/anggota/verifikasi.php') ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
        <div class="modal-header"><h2 class="modal-title h5" id="judul-tolak">Tolak pendaftaran</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <label class="form-label" for="alasan">Alasan penolakan (wajib)</label>
            <textarea class="form-control" id="alasan" name="alasan" rows="3" required maxlength="255"></textarea>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" name="aksi" value="tolak">Tolak</button></div>
    </form></div>
</div>
<?php require ROOT_DIR . '/includes/layout/footer.php'; ?>
