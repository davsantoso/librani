// Pratinjau gambar sebelum upload: <input type="file" data-pratinjau="#wadah">
document.querySelectorAll('input[type=file][data-pratinjau]').forEach(input => {
    input.addEventListener('change', () => {
        const wadah = document.querySelector(input.dataset.pratinjau);
        const file = input.files[0];
        if (!wadah || !file) return;
        if (file.size > 2 * 1000 * 1000) {
            Swal.fire({ icon: 'warning', title: 'File terlalu besar', text: 'Maksimal 2 MB.' });
            input.value = '';
            return;
        }
        const img = document.createElement('img');
        img.className = 'cover';
        img.alt = 'Pratinjau sampul';
        img.src = URL.createObjectURL(file);
        wadah.replaceChildren(img);
    });
});
