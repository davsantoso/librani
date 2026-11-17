// Scan barcode/QR dengan kamera (html5-qrcode). Tombol: <button data-scan="#input-target" [data-scan-submit="1"]>.
// Hasil scan diisikan ke input lalu diproses sama seperti mengetik + Enter (atau form dikirim jika data-scan-submit).
// Kamera hanya jalan di HTTPS atau localhost; input manual / scanner USB tetap bisa dipakai.
(() => {
    const tombol = document.querySelectorAll('[data-scan]');
    if (!tombol.length || typeof Html5Qrcode === 'undefined') return;

    const modalEl = document.createElement('div');
    modalEl.className = 'modal fade';
    modalEl.tabIndex = -1;
    modalEl.innerHTML = `
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title h5">Arahkan kamera ke barcode / QR</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body"><div id="area-scan"></div><p class="small text-muted mt-2 mb-0" id="pesan-scan"></p></div>
        </div></div>`;
    document.body.append(modalEl);
    const modal = new bootstrap.Modal(modalEl);
    let pemindai = null;
    let target = null;

    async function berhenti() {
        if (pemindai && pemindai.isScanning) await pemindai.stop().catch(() => {});
    }

    tombol.forEach(btn => btn.addEventListener('click', () => {
        target = { input: document.querySelector(btn.dataset.scan), kirim: btn.dataset.scanSubmit === '1' };
        modal.show();
    }));

    modalEl.addEventListener('shown.bs.modal', () => {
        document.getElementById('pesan-scan').textContent = '';
        pemindai ??= new Html5Qrcode('area-scan');
        pemindai.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 260, height: 160 } }, async teks => {
            await berhenti();
            modal.hide();
            const input = target.input;
            input.value = teks.trim();
            if (target.kirim) {
                input.form.submit();
            } else {
                input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
            }
        }).catch(err => {
            document.getElementById('pesan-scan').textContent =
                'Kamera tidak bisa dibuka (' + err + '). Pastikan memakai HTTPS dan izin kamera diberikan, atau ketik kodenya manual.';
        });
    });
    modalEl.addEventListener('hidden.bs.modal', berhenti);
})();
