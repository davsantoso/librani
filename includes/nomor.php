<?php
// Pembuat nomor/kode otomatis.

/** BK + id buku 4 digit + '-' + urutan 2 digit, mis. BK0001-02. */
function kode_eksemplar(int $buku_id, int $urut): string
{
    return sprintf('BK%04d-%02d', $buku_id, $urut);
}
