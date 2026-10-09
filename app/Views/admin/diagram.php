<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h1>Diagram FAT</h1>
<p class="page-description">Perbandingan total port terpakai dan tersedia untuk setiap kota/kabupaten dari seluruh workbook data.</p>
<section class="admin-card">
    <div id="fat-chart-status" class="muted" role="status" aria-live="polite">Memuat data diagram...</div>
    <div class="chart-wrap"><canvas id="fat-chart"></canvas></div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= site_url('resources/xlsx-lite.js') ?>"></script>
<script>
    async function loadFatDiagram() {
        const status = document.getElementById('fat-chart-status');
        try {
            const uploadedResponse = await fetch(<?= json_encode(site_url('map-data')) ?>, { cache: 'no-store' });
            if (!uploadedResponse.ok) throw new Error('Daftar Excel tambahan gagal dimuat.');
            const uploaded = await uploadedResponse.json();
            const workbookUrls = [
                <?= json_encode(site_url('data/Data FAT Full.xlsx')) ?>,
                ...uploaded.files.map(file => file.url)
            ];
            const groups = new Map();
            const normalizeHeader = value => String(value || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            const cityHeaders = new Set(['kotakabupaten', 'kotakab', 'kota', 'kabupaten', 'kabkota', 'city']);

            for (const url of workbookUrls) {
                const response = await fetch(url, { cache: 'no-cache' });
                if (!response.ok) throw new Error('Workbook gagal dimuat. HTTP ' + response.status);
                const workbook = await XLSXLite.read(await response.arrayBuffer());
                const sheet = workbook.sheets[workbook.sheetNames[0]];
                if (!sheet) throw new Error('Sheet pertama tidak ditemukan.');
                const rows = XLSXLite.sheetToObjects(sheet);
                for (const row of rows) {
                    const cityHeader = Object.keys(row).find(header => cityHeaders.has(normalizeHeader(header)));
                    const city = String(cityHeader ? row[cityHeader] : '').replace(/\s+/g, ' ').trim();
                    if (!city) continue;
                    if (!groups.has(city)) groups.set(city, { used: 0, idle: 0 });
                    const values = groups.get(city);
                    const sum = value => {
                        const matches = String(value ?? '').match(/-?\d+(?:[.,]\d+)?/g) || [];
                        return matches.reduce((total, part) => total + (Number(part.replace(',', '.')) || 0), 0);
                    };
                    values.used += sum(row['Total Used (Visual)']);
                    values.idle += sum(row['Total Idle (Visual)']);
                }
            }

            if (groups.size === 0) throw new Error('Kolom Kota/Kabupaten belum memiliki data untuk ditampilkan.');
            const labels = [...groups.keys()];
            new Chart(document.getElementById('fat-chart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Total Used (Visual)', data: labels.map(label => groups.get(label).used), backgroundColor: '#ef4444' },
                        { label: 'Total Idle (Visual)', data: labels.map(label => groups.get(label).idle), backgroundColor: '#22c55e' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { maxRotation: 45, minRotation: 0 } },
                        y: { beginAtZero: true, title: { display: true, text: 'Jumlah Port' } }
                    }
                }
            });
            status.textContent = 'Diagram FAT memuat ' + labels.length + ' kota/kabupaten.';
        } catch (error) {
            status.textContent = error.message || 'Diagram FAT gagal dimuat.';
            status.setAttribute('aria-live', 'assertive');
        }
    }

    loadFatDiagram();
</script>
<?= $this->endSection() ?>
