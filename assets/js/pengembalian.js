// Hitung total denda di layar saat petugas mencentang eksemplar dan memilih kondisi (server menghitung ulang saat simpan).
(() => {
    const form = document.getElementById('form-kembali');
    if (!form) return;
    const rupiah = n => 'Rp' + n.toLocaleString('id-ID');

    function hitung() {
        let total = 0, dipilih = 0;
        form.querySelectorAll('.baris-kembali').forEach(baris => {
            const centang = baris.querySelector('input[type=checkbox]').checked;
            const kondisi = baris.querySelector('select').value;
            baris.classList.toggle('opacity-50', !centang);
            if (!centang) return;
            dipilih++;
            total += Number(baris.dataset.terlambat);
            if (kondisi === 'rusak') total += Number(baris.dataset.rusak);
            if (kondisi === 'hilang') total += Number(baris.dataset.hilang);
        });
        document.getElementById('total-denda').textContent = rupiah(total);
        document.getElementById('btn-simpan-kembali').disabled = dipilih === 0;
    }

    form.addEventListener('change', hitung);
    hitung();
})();
