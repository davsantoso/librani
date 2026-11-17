<?php
// Pembuat nomor/kode otomatis.

/** BK + id buku 4 digit + '-' + urutan 2 digit, mis. BK0001-02. */
function kode_eksemplar(int $buku_id, int $urut): string
{
    return sprintf('BK%04d-%02d', $buku_id, $urut);
}

/** AGT-<tahun>-<urut 4 digit>, urut per tahun. Panggil di dalam transaksi (baris dikunci FOR UPDATE). */
function kode_anggota_berikutnya(int $tahun): string
{
    $terakhir = db_val('SELECT MAX(kode_anggota) FROM anggota WHERE kode_anggota LIKE ? FOR UPDATE', ["AGT-$tahun-%"]);
    $urut = $terakhir ? (int) substr($terakhir, -4) + 1 : 1;
    return sprintf('AGT-%d-%04d', $tahun, $urut);
}

/**
 * Nomor transaksi berurutan per hari: <PREFIX>-YYYYMMDD-NNNN (mis. PJM-20261117-0001).
 * Wajib dipanggil di dalam transaksi DB; baris terakhir dikunci (FOR UPDATE) dan kolom unik jadi pengaman terakhir.
 */
function nomor_berikutnya(string $prefix, string $tabel, string $kolom, ?string $tanggal = null): string
{
    $boleh = ['peminjaman.no_transaksi', 'pengembalian.no_pengembalian', 'denda.no_kuitansi'];
    if (!in_array("$tabel.$kolom", $boleh, true)) {
        throw new InvalidArgumentException("Nomor untuk $tabel.$kolom tidak dikenal.");
    }
    $awal = $prefix . '-' . str_replace('-', '', $tanggal ?? hari_ini()) . '-';
    $terakhir = db_val("SELECT MAX($kolom) FROM $tabel WHERE $kolom LIKE ? FOR UPDATE", [$awal . '%']);
    return $awal . sprintf('%04d', $terakhir ? (int) substr($terakhir, -4) + 1 : 1);
}
