<?php
// Daftar reservasi aktif anggota (dashboard). Butuh $reservasi dari reservasi_anggota().
$aktif_r = array_filter($reservasi, fn($r) => in_array($r['status'], ['menunggu', 'siap'], true));
?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between">
        <span>Reservasi</span><a class="small" href="<?= url('modules/anggota/reservasi.php') ?>">Semua reservasi</a>
    </div>
    <div class="card-body">
        <?php if (!$aktif_r): ?><p class="text-muted mb-0">Tidak ada reservasi aktif.</p><?php endif; ?>
        <div class="d-grid gap-2">
        <?php foreach ($aktif_r as $r): ?>
            <div class="d-flex gap-3 align-items-center border rounded p-2 <?= $r['status'] === 'siap' ? 'border-success' : '' ?>">
                <div class="cover-kecil"><?= cover_html($r) ?></div>
                <div class="flex-grow-1"><div class="fw-semibold"><?= e($r['judul']) ?></div><div class="small text-muted"><?= e($r['penulis']) ?></div></div>
                <div class="text-end small">
                    <?php if ($r['status'] === 'siap'): ?>
                        <span class="badge text-bg-success">Siap diambil</span><div>s.d. <strong><?= tgl($r['tgl_batas_ambil']) ?></strong></div>
                    <?php else: ?>
                        <span class="badge text-bg-info">Menunggu</span><div>Antrean ke-<?= (int) $r['posisi'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
