// Export tabel laporan ke Excel (.xlsx) di browser dengan SheetJS. Satu tabel = satu sheet.
document.querySelectorAll('[data-export-excel]').forEach(tombol => {
    tombol.addEventListener('click', () => {
        const buku = XLSX.utils.book_new();
        document.querySelectorAll('table.tabel-laporan').forEach((tabel, i) => {
            const judulKartu = tabel.closest('.card')?.querySelector('.card-header')?.textContent.trim();
            const namaSheet = (judulKartu || `Laporan ${i + 1}`).replace(/[\\/?*[\]:]/g, '').slice(0, 31);
            XLSX.utils.book_append_sheet(buku, XLSX.utils.table_to_sheet(tabel), namaSheet);
        });
        const tgl = new Date().toISOString().slice(0, 10).replaceAll('-', '');
        XLSX.writeFile(buku, `${tombol.dataset.exportExcel}-${tgl}.xlsx`);
    });
});
