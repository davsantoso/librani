<?php
// Lembar label barcode (CODE128) eksemplar untuk ditempel di buku. ?buku_id=1 atau ?ids=1,2,3
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas');

if (isset($_GET['buku_id'])) {
    $data = db_all(
        "SELECT e.kode_eksemplar, b.judul, r.kode AS rak FROM eksemplar e JOIN buku b ON b.id = e.buku_id LEFT JOIN rak r ON r.id = e.rak_id
         WHERE e.buku_id = ? AND e.status <> 'hilang' ORDER BY e.kode_eksemplar",
        [(int) $_GET['buku_id']]
    );
} else {
    $ids = array_values(array_filter(array_map('intval', explode(',', (string) ($_GET['ids'] ?? '')))));
    $data = $ids ? db_all(
        'SELECT e.kode_eksemplar, b.judul, r.kode AS rak FROM eksemplar e JOIN buku b ON b.id = e.buku_id LEFT JOIN rak r ON r.id = e.rak_id
         WHERE e.id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY e.kode_eksemplar',
        $ids
    ) : [];
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Label Eksemplar · Librani</title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <style>
        .lembar { display: grid; grid-template-columns: repeat(3, 62mm); gap: 4mm; }
        .label { border: 1px dashed #999; border-radius: 2mm; padding: 2mm 3mm; height: 32mm; overflow: hidden; text-align: center; font-size: 7pt; }
        .label .judul { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .label svg { width: 100%; height: 17mm; }
        @media print { .no-print { display: none; } @page { size: A4; margin: 10mm; } body { padding: 0 !important; } }
    </style>
</head>
<body class="p-4">
<div class="no-print mb-3 d-flex gap-2 align-items-center">
    <button class="btn btn-primary btn-sm" onclick="window.print()">Cetak label</button>
    <span class="small text-muted"><?= count($data) ?> label · tempel di punggung/sampul belakang buku</span>
</div>
<?php if (!$data): ?><p>Tidak ada eksemplar.</p><?php endif; ?>
<div class="lembar">
    <?php foreach ($data as $x): ?>
        <div class="label">
            <div class="text-muted"><?= e(setting('nama_perpustakaan')) ?><?= $x['rak'] ? ' · Rak ' . e($x['rak']) : '' ?></div>
            <div class="judul"><?= e($x['judul']) ?></div>
            <svg class="barcode" data-kode="<?= e($x['kode_eksemplar']) ?>"></svg>
        </div>
    <?php endforeach; ?>
</div>
<script src="<?= url('assets/vendor/JsBarcode.all.min.js') ?>"></script>
<script>
    document.querySelectorAll('svg.barcode').forEach(svg => JsBarcode(svg, svg.dataset.kode, {
        format: 'CODE128', height: 50, width: 1.6, fontSize: 13, margin: 2, displayValue: true,
    }));
</script>
</body>
</html>
