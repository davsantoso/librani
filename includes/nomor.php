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
