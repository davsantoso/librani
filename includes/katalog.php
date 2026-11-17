<?php
/**
 * Cari buku aktif untuk katalog (publik & anggota).
 * $q dicocokkan ke judul, penulis, ISBN, dan nama kategori. `tersedia` = eksemplar berstatus tersedia saja.
 * @return array{data: array, total: int}
 */
function cari_katalog(string $q = '', ?int $kategori_id = null, int $hal = 1, int $per_hal = 24): array
{
    $where = ['b.is_active = 1'];
    $p = [];
    if (trim($q) !== '') {
        $like = '%' . addcslashes(trim($q), '%_\\') . '%';
        $where[] = '(b.judul LIKE ? OR b.penulis LIKE ? OR b.isbn LIKE ? OR k.nama LIKE ?)';
        array_push($p, $like, $like, $like, $like);
    }
    if ($kategori_id) {
        $where[] = 'b.kategori_id = ?';
        $p[] = $kategori_id;
    }
    $w = implode(' AND ', $where);
    $total = (int) db_val("SELECT COUNT(*) FROM buku b JOIN kategori k ON k.id = b.kategori_id WHERE $w", $p);
    $offset = max(0, ($hal - 1) * $per_hal);
    $data = db_all(
        "SELECT b.id, b.judul, b.penulis, b.tahun_terbit, b.foto_cover, b.foto_thumb, k.nama AS kategori,
                (SELECT COUNT(*) FROM eksemplar e WHERE e.buku_id = b.id AND e.status = 'tersedia') AS tersedia,
                (SELECT COUNT(*) FROM eksemplar e WHERE e.buku_id = b.id AND e.status NOT IN ('rusak','hilang')) AS total
         FROM buku b JOIN kategori k ON k.id = b.kategori_id
         WHERE $w ORDER BY b.judul LIMIT " . (int) $per_hal . ' OFFSET ' . (int) $offset,
        $p
    );
    return ['data' => $data, 'total' => $total];
}
