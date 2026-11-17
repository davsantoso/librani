<?php
/**
 * Kop halaman cetak (bukti transaksi, kuitansi, laporan).
 * Variabel: $judul_cetak (wajib), $sub_judul (opsional, mis. periode laporan), $kertas ('A4' | 'A4 landscape' | 'A5').
 */
$kertas ??= 'A4';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_cetak) ?> · Librani</title>
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/bootstrap-icons.min.css') ?>">
    <style>
        body { background: #fff; color: #000; font-size: 11pt; }
        .lembar { max-width: 1000px; margin: 0 auto; padding: 24px; }
        .kop { border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 16px; }
        .kop h1 { font-size: 16pt; margin: 0; }
        table.cetak { width: 100%; border-collapse: collapse; font-size: 10pt; }
        table.cetak th, table.cetak td { border: 1px solid #555; padding: 4px 6px; vertical-align: top; }
        table.cetak th { background: #eee; }
        table.cetak tfoot td { font-weight: 700; background: #f5f5f5; }
        .ttd { margin-top: 40px; width: 220px; margin-left: auto; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            .lembar { padding: 0; max-width: none; }
            @page { size: <?= e($kertas) ?>; margin: 12mm; }
        }
    </style>
</head>
<body>
<div class="lembar">
    <div class="no-print mb-3 d-flex gap-2">
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Cetak / Simpan PDF</button>
        <button class="btn btn-outline-secondary btn-sm" onclick="history.length > 1 ? history.back() : window.close()">Kembali</button>
        <?php foreach ($tombol_cetak ?? [] as $t): ?><?= $t ?><?php endforeach; ?>
    </div>
    <div class="kop d-flex justify-content-between align-items-end">
        <div>
            <h1><?= e(setting('nama_perpustakaan')) ?></h1>
            <div class="small"><?= e(setting('alamat_perpustakaan')) ?></div>
        </div>
        <div class="text-end small">
            Dicetak: <?= tgl(hari_ini()) ?> <?= date('H:i') ?><br>
            Oleh: <?= e(user()['nama'] ?? '-') ?>
        </div>
    </div>
    <h2 class="h5 text-center mb-0"><?= e($judul_cetak) ?></h2>
    <?php if (!empty($sub_judul)): ?><p class="text-center small mb-3"><?= e($sub_judul) ?></p><?php else: ?><div class="mb-3"></div><?php endif; ?>
