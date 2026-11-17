// Perilaku umum: DataTables, konfirmasi aksi, pesan flash.
const bahasaTabel = {
    search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ baris', info: '_START_–_END_ dari _TOTAL_ data',
    infoEmpty: 'Tidak ada data', infoFiltered: '(disaring dari _MAX_)', zeroRecords: 'Data tidak ditemukan',
    emptyTable: 'Belum ada data', paginate: { first: '«', last: '»', next: '›', previous: '‹' }
};

document.querySelectorAll('table.datatable').forEach(t => {
    new DataTable(t, { pageLength: 10, lengthMenu: [10, 25, 50], language: bahasaTabel, order: [] });
});

// <form class="konfirmasi" data-judul="..." data-pesan="..."> meminta konfirmasi sebelum dikirim.
document.addEventListener('submit', ev => {
    const f = ev.target;
    if (!f.matches('form.konfirmasi') || f.dataset.yakin) return;
    ev.preventDefault();
    Swal.fire({
        title: f.dataset.judul || 'Yakin?', text: f.dataset.pesan || 'Data akan dihapus.', icon: 'warning',
        showCancelButton: true, confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545'
    }).then(r => {
        if (r.isConfirmed) { f.dataset.yakin = '1'; f.submit(); }
    });
});

const flashEl = document.getElementById('flash');
if (flashEl && flashEl.dataset.pesan) {
    const ok = flashEl.dataset.tipe === 'sukses';
    Swal.fire({
        toast: true, position: 'top-end', timer: ok ? 3500 : 7000, showConfirmButton: false, timerProgressBar: true,
        icon: ok ? 'success' : 'error', title: flashEl.dataset.pesan
    });
}
