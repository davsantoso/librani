<?php
require __DIR__ . '/../../../includes/init.php';
require_role('admin', 'petugas', 'anggota');

// Petugas/admin: kartu anggota mana pun (?id=). Anggota: hanya kartunya sendiri.
if (user()['role'] === 'anggota') {
    $a = anggota_saya();
} else {
    $a = db_row('SELECT * FROM anggota WHERE id = ?', [(int) ($_GET['id'] ?? 0)]);
}
if (!$a || !$a['kode_anggota']) {
    http_response_code(404);
    exit('Kartu anggota belum tersedia (anggota belum diverifikasi).');
}
$qr = is_file(ROOT_DIR . '/assets/vendor/qrcode.min.js');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Anggota <?= e($a['kode_anggota']) ?></title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <style>
        body { background: #eef1f5; }
        .kartu { width: 85.6mm; height: 54mm; border-radius: 3mm; overflow: hidden; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.15); display: flex; flex-direction: column; }
        .kartu-kepala { background: linear-gradient(135deg, #1d4e89, #3d5a80); color: #fff; padding: 2.2mm 3.5mm; font-size: 8pt; }
        .kartu-isi { display: flex; gap: 3mm; padding: 3mm 3.5mm; flex: 1; font-size: 7.5pt; }
        .foto { width: 18mm; height: 23mm; object-fit: cover; border-radius: 1mm; background: #e9ecef; }
        .kode { font-family: monospace; font-size: 10pt; font-weight: 700; letter-spacing: .5px; }
        #qr { margin-left: auto; align-self: flex-end; }
        #qr img, #qr canvas { width: 18mm !important; height: 18mm !important; }
        @media print { body { background: #fff; } .no-print { display: none; } .kartu { box-shadow: none; border: 1px solid #ccc; } @page { margin: 10mm; } }
    </style>
</head>
<body class="p-4">
<div class="no-print mb-3"><button class="btn btn-primary btn-sm" onclick="window.print()">Cetak</button></div>
<div class="kartu">
    <div class="kartu-kepala"><strong>KARTU ANGGOTA</strong> · <?= e(setting('nama_perpustakaan')) ?></div>
    <div class="kartu-isi">
        <?php if ($a['foto']): ?>
            <img class="foto" src="<?= url('uploads/anggota/' . $a['foto']) ?>" alt="">
        <?php else: ?>
            <div class="foto"></div>
        <?php endif; ?>
        <div>
            <div class="fw-bold" style="font-size: 9pt"><?= e($a['nama']) ?></div>
            <div class="kode"><?= e($a['kode_anggota']) ?></div>
            <div class="mt-1">Berlaku s.d. <strong><?= tgl($a['tgl_berlaku']) ?></strong></div>
            <div class="text-muted"><?= e(setting('alamat_perpustakaan')) ?></div>
        </div>
        <?php if ($qr): ?><div id="qr" data-kode="<?= e($a['kode_anggota']) ?>"></div><?php endif; ?>
    </div>
</div>
<?php if ($qr): ?>
<script src="<?= url('assets/vendor/qrcode.min.js') ?>"></script>
<script>
    const el = document.getElementById('qr');
    new QRCode(el, { text: el.dataset.kode, width: 128, height: 128, correctLevel: QRCode.CorrectLevel.M });
</script>
<?php endif; ?>
</body>
</html>
