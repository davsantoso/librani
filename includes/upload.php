<?php
/**
 * Simpan gambar upload ke uploads/<folder>/ dengan nama acak.
 * - MIME dicek dari isi file (finfo), bukan dari nama/ekstensi.
 * - Dengan GD: gambar di-encode ulang (membuang isi tersembunyi), dikecilkan ke $lebar,
 *   dan dibuatkan thumbnail $lebar_thumb di uploads/<folder>/thumb/.
 * - Tanpa GD: file asli disimpan apa adanya dengan batas 1 MB.
 *
 * @return array{file:string, thumb:?string}
 */
function simpan_gambar(array $file, string $folder, int $maks_byte, array $mime_boleh, ?int $lebar = null, ?int $lebar_thumb = null): array
{
    if (!in_array($folder, ['cover', 'anggota'], true)) {
        throw new InvalidArgumentException('Folder upload tidak dikenal.');
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('Upload file gagal. Coba pilih file lagi.');
    }
    $pakai_gd = extension_loaded('gd');
    if (!$pakai_gd) {
        $maks_byte = min($maks_byte, 1_000_000);
    }
    if ($file['size'] > $maks_byte) {
        throw new RuntimeException('Ukuran file maksimal ' . round($maks_byte / 1_000_000) . ' MB.');
    }

    $ekstensi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (string) finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']);
    if (!in_array($mime, $mime_boleh, true) || !isset($ekstensi[$mime])) {
        $daftar = implode(', ', array_map(fn($m) => strtoupper($ekstensi[$m] ?? $m), $mime_boleh));
        throw new RuntimeException("Format file harus $daftar.");
    }

    $nama = bin2hex(random_bytes(16)) . '.' . $ekstensi[$mime];
    $dir = UPLOAD_DIR . '/' . $folder;

    if (!$pakai_gd) {
        $pindah = empty($file['tes']) ? move_uploaded_file($file['tmp_name'], "$dir/$nama") : rename($file['tmp_name'], "$dir/$nama");
        if (!$pindah) {
            throw new RuntimeException('File tidak bisa disimpan di server.');
        }
        return ['file' => $nama, 'thumb' => null];
    }

    $img = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));
    if (!$img) {
        throw new RuntimeException('File gambar rusak atau tidak bisa dibaca.');
    }
    tulis_gambar($img, $mime, "$dir/$nama", $lebar);
    $thumb = null;
    if ($lebar_thumb) {
        tulis_gambar($img, $mime, "$dir/thumb/$nama", $lebar_thumb);
        $thumb = $nama;
    }
    if (!empty($file['tes'])) {
        @unlink($file['tmp_name']);
    }
    return ['file' => $nama, 'thumb' => $thumb];
}

/** Tulis $img ke $path dalam format $mime, dikecilkan ke $lebar (tidak pernah diperbesar). */
function tulis_gambar(GdImage $img, string $mime, string $path, ?int $lebar): void
{
    $out = ($lebar && imagesx($img) > $lebar) ? imagescale($img, $lebar, -1, IMG_BICUBIC) : $img;
    if ($mime !== 'image/jpeg') {
        imagealphablending($out, false);
        imagesavealpha($out, true);
    }
    $ok = match ($mime) {
        'image/jpeg' => imagejpeg($out, $path, 82),
        'image/png' => imagepng($out, $path, 6),
        'image/webp' => imagewebp($out, $path, 80),
    };
    if (!$ok) {
        throw new RuntimeException('File tidak bisa disimpan di server.');
    }
}

/** Hapus file gambar beserta thumbnail-nya (jika ada). */
function hapus_gambar(string $folder, ?string $nama): void
{
    if (!$nama || !in_array($folder, ['cover', 'anggota'], true)) {
        return;
    }
    $nama = basename($nama);
    foreach (["$folder/$nama", "$folder/thumb/$nama"] as $rel) {
        if (is_file(UPLOAD_DIR . '/' . $rel)) {
            unlink(UPLOAD_DIR . '/' . $rel);
        }
    }
}
