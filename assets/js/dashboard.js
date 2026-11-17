// Grafik garis jumlah peminjaman 30 hari (satu seri: tanpa legenda, judul kartu sudah menamai datanya).
(() => {
    const kanvas = document.getElementById('grafik-pinjam');
    if (!kanvas || typeof Chart === 'undefined') return;
    const data = JSON.parse(kanvas.dataset.grafik);
    const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const label = t => { const [, m, d] = t.split('-'); return `${Number(d)} ${bulan[Number(m) - 1]}`; };
    const warna = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#0d6efd';

    new Chart(kanvas, {
        type: 'line',
        data: {
            labels: data.map(d => label(d.tgl)),
            datasets: [{
                label: 'Peminjaman', data: data.map(d => d.jumlah),
                borderColor: warna, backgroundColor: warna + '1a', fill: true, borderWidth: 2, cubicInterpolationMode: 'monotone',  // monotone: kurva tidak turun di bawah 0
                pointRadius: 0, pointHoverRadius: 5, pointHitRadius: 12,
            }],
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => `${c.parsed.y} transaksi` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 8, color: '#6a5f54' } },
                y: { beginAtZero: true, ticks: { precision: 0, color: '#6a5f54' }, grid: { color: 'rgba(43,36,30,.07)' }, border: { display: false } },
            },
        },
    });
})();
