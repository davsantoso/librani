<?php
// Tombol reservasi di detail katalog (butuh $b dan $tersedia). Hanya untuk anggota aktif saat semua eksemplar dipinjam.
if (!$b || !user() || user()['role'] !== 'anggota') {
    return;
}
$saya = anggota_saya();
if (!$saya || $saya['status'] !== 'aktif') {
    return;
}
$punya = db_row("SELECT status, tgl_batas_ambil FROM reservasi WHERE anggota_id = ? AND buku_id = ? AND status IN ('menunggu','siap')", [$saya['id'], $b['id']]);
?>
<?php if ($punya): ?>
    <div class="alert alert-info py-2">
        <?= $punya['status'] === 'siap'
            ? 'Buku siap diambil di meja layanan paling lambat <strong>' . tgl($punya['tgl_batas_ambil']) . '</strong>.'
            : 'Anda sudah mereservasi buku ini. <a href="' . url('modules/anggota/reservasi.php') . '">Lihat antrean</a>.' ?>
    </div>
<?php elseif ($tersedia === 0): ?>
    <form method="post" action="<?= url('modules/transaksi/reservasi/buat.php') ?>" class="mb-3 konfirmasi"
          data-judul="Reservasi buku ini?" data-pesan="Anda akan masuk antrean. Saat buku kembali, Anda punya <?= (int) setting('batas_ambil_reservasi') ?> hari untuk mengambilnya.">
        <?= csrf_field() ?><input type="hidden" name="buku_id" value="<?= (int) $b['id'] ?>">
        <button class="btn btn-primary"><i class="bi bi-bookmark-plus"></i> Reservasi buku ini</button>
    </form>
<?php endif; ?>
