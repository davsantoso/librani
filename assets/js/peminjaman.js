// Form peminjaman: pilih anggota → tambah eksemplar satu per satu (ketik/scan) → simpan.
// Validasi di sini hanya untuk kenyamanan; server tetap memeriksa ulang semuanya.
(() => {
    const form = document.getElementById('form-pinjam');
    if (!form) return;
    const $ = sel => document.querySelector(sel);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let anggota = null;
    const dipilih = new Map(); // id eksemplar → data

    const peringatan = teks => Swal.fire({ icon: 'warning', title: 'Tidak bisa ditambahkan', text: teks });

    function segarkan() {
        const sisa = anggota ? anggota.sisa_kuota - dipilih.size : 0;
        const bolehTambah = anggota && anggota.masalah.length === 0;
        $('#kode-eksemplar').disabled = !bolehTambah;
        $('#btn-tambah-eksemplar').disabled = !bolehTambah;
        $('#btn-simpan').disabled = !(bolehTambah && dipilih.size > 0);
        $('#kosong-eksemplar').hidden = dipilih.size > 0;
        $('#info-kuota').textContent = anggota ? `Sisa kuota: ${Math.max(0, sisa)} dari ${anggota.maks_pinjam}` : '';
    }

    function pilihAnggota(a) {
        anggota = a;
        dipilih.clear();
        $('#daftar-eksemplar').replaceChildren();
        $('#hasil-anggota').replaceChildren();
        $('#anggota_id').value = a.id;
        const masalah = a.masalah.length
            ? `<div class="alert alert-danger py-2 mt-2 mb-0 small"><strong>Tidak bisa meminjam:</strong><ul class="mb-0">${a.masalah.map(m => `<li>${esc(m)}</li>`).join('')}</ul></div>`
            : '';
        $('#kartu-anggota').innerHTML = `
            <div class="border rounded p-3 ${a.masalah.length ? 'border-danger' : 'border-success'}">
                <div class="fw-semibold">${esc(a.nama)}</div>
                <div class="small font-monospace">${esc(a.kode_anggota ?? '-')}</div>
                <div class="small text-muted">Status: ${esc(a.status)} · berlaku s.d. ${esc(a.tgl_berlaku)}</div>
                <div class="small">Sisa kuota: <strong>${a.sisa_kuota}</strong> dari ${a.maks_pinjam}</div>
                ${masalah}
            </div>`;
        segarkan();
        if (!a.masalah.length) $('#kode-eksemplar').focus();
    }

    async function cariAnggota(q, otomatis = false) {
        q = q.trim();
        if (!q) return;
        const res = await fetch(`${form.dataset.urlAnggota}?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        const wadah = $('#hasil-anggota');
        wadah.replaceChildren();
        if (data.length === 0) {
            wadah.innerHTML = '<div class="list-group-item text-muted small">Anggota tidak ditemukan.</div>';
        } else if (data.length === 1 || otomatis) {
            pilihAnggota(data[0]);
        } else {
            data.forEach(a => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action small';
                btn.innerHTML = `<strong>${esc(a.nama)}</strong> <span class="font-monospace">${esc(a.kode_anggota ?? '')}</span> <span class="badge text-bg-secondary">${esc(a.status)}</span>`;
                btn.addEventListener('click', () => pilihAnggota(a));
                wadah.append(btn);
            });
        }
    }

    async function tambahEksemplar(kode) {
        kode = kode.trim().toUpperCase();
        if (!kode || !anggota) return;
        const res = await fetch(`${form.dataset.urlEksemplar}?kode=${encodeURIComponent(kode)}&anggota_id=${anggota.id}`);
        const x = await res.json();
        $('#kode-eksemplar').value = '';
        if (x.error) return peringatan(x.error);
        if (dipilih.has(x.id)) return peringatan(`${x.kode_eksemplar} sudah ada di daftar.`);
        if (!x.boleh) return peringatan(`${x.kode_eksemplar}: ${x.alasan}`);
        if (dipilih.size >= anggota.sisa_kuota) return peringatan(`Batas pinjam ${anggota.maks_pinjam} eksemplar tercapai.`);
        dipilih.set(x.id, x);
        const sampul = x.thumb_url
            ? `<img class="cover" src="${esc(x.thumb_url)}" alt="">`
            : `<div class="cover-placeholder"><span class="cp-judul">${esc(x.judul)}</span></div>`;
        const kartu = document.createElement('div');
        kartu.className = 'border rounded p-2 d-flex gap-3 align-items-center';
        kartu.innerHTML = `
            <div class="cover-kecil">${sampul}</div>
            <div class="flex-grow-1"><div class="fw-semibold">${esc(x.judul)}</div>
                <div class="small text-muted">${esc(x.penulis)}</div>
                <div class="small font-monospace">${esc(x.kode_eksemplar)}</div></div>
            <input type="hidden" name="eksemplar_ids[]" value="${x.id}">
            <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus dari daftar"><i class="bi bi-x-lg"></i></button>`;
        kartu.querySelector('button').addEventListener('click', () => { dipilih.delete(x.id); kartu.remove(); segarkan(); });
        $('#daftar-eksemplar').append(kartu);
        segarkan();
    }

    const enter = (input, fn) => input.addEventListener('keydown', ev => {
        if (ev.key === 'Enter') { ev.preventDefault(); fn(input.value); }
    });
    enter($('#cari-anggota'), q => cariAnggota(q));
    enter($('#kode-eksemplar'), tambahEksemplar);
    $('#btn-cari-anggota').addEventListener('click', () => cariAnggota($('#cari-anggota').value));
    $('#btn-tambah-eksemplar').addEventListener('click', () => tambahEksemplar($('#kode-eksemplar').value));

    // Pulihkan pilihan setelah simpan gagal, atau anggota dari tautan ?anggota=
    const awal = JSON.parse(form.dataset.awal || '{}');
    if (awal.anggota) {
        $('#cari-anggota').value = awal.anggota;
        cariAnggota(awal.anggota, true).then(async () => {
            for (const k of awal.eksemplar || []) await tambahEksemplar(k);
        });
    }
    segarkan();
})();
