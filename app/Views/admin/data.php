<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h1>Full Data FAT</h1>
<p class="page-description">Seluruh baris dari spreadsheet utama dan file Excel tambahan, tanpa tampilan peta.</p>
<section class="admin-card">
    <label for="full-data-search" style="margin-top:0">Cari seluruh data</label>
    <input id="full-data-search" type="search" placeholder="Cari label, kota, site, koordinat, atau data lainnya...">
    <p id="full-data-status" class="muted" role="status" aria-live="polite">Memuat data FAT...</p>
    <div class="table-wrap" style="max-height:calc(100vh - 300px);margin-top:16px">
        <table>
            <thead id="full-data-head" style="position:sticky;top:0;background:#f7f9fc"></thead>
            <tbody id="full-data-body"></tbody>
        </table>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px">
        <button class="primary-button" id="full-data-previous" type="button" disabled style="margin:0">Sebelumnya</button>
        <span class="muted" id="full-data-page"></span>
        <button class="primary-button" id="full-data-next" type="button" disabled style="margin:0">Berikutnya</button>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= site_url('resources/xlsx-lite.js') ?>"></script>
<script>
    const DATA_PAGE_SIZE = 100;
    const fullDataStatus = document.getElementById('full-data-status');
    const fullDataSearch = document.getElementById('full-data-search');
    const fullDataHead = document.getElementById('full-data-head');
    const fullDataBody = document.getElementById('full-data-body');
    const fullDataPage = document.getElementById('full-data-page');
    const fullDataPrevious = document.getElementById('full-data-previous');
    const fullDataNext = document.getElementById('full-data-next');
    let fullDataRows = [];
    let fullDataHeaders = [];
    let fullDataCurrentPage = 1;

    async function readWorkbookRows(url) {
        const response = await fetch(url, { cache: 'no-cache' });
        if (!response.ok) throw new Error('Workbook gagal dimuat. HTTP ' + response.status);
        const workbook = await XLSXLite.read(await response.arrayBuffer());
        const sheetName = workbook.sheetNames[0];
        const sheet = sheetName ? workbook.sheets[sheetName] : null;
        if (!sheet) throw new Error('Sheet pertama tidak ditemukan di workbook.');
        return XLSXLite.sheetToObjects(sheet);
    }

    function renderFullData() {
        const query = fullDataSearch.value.trim().toLocaleLowerCase('id');
        const filteredRows = query
            ? fullDataRows.filter(row => fullDataHeaders.some(header =>
                String(row[header] ?? '').toLocaleLowerCase('id').includes(query)
            ))
            : fullDataRows;
        const totalPages = Math.max(1, Math.ceil(filteredRows.length / DATA_PAGE_SIZE));
        fullDataCurrentPage = Math.min(fullDataCurrentPage, totalPages);
        const start = (fullDataCurrentPage - 1) * DATA_PAGE_SIZE;
        const visibleRows = filteredRows.slice(start, start + DATA_PAGE_SIZE);

        fullDataHead.replaceChildren();
        const headerRow = document.createElement('tr');
        for (const header of fullDataHeaders) {
            const cell = document.createElement('th');
            cell.textContent = header;
            headerRow.appendChild(cell);
        }
        fullDataHead.appendChild(headerRow);

        fullDataBody.replaceChildren();
        for (const row of visibleRows) {
            const tableRow = document.createElement('tr');
            for (const header of fullDataHeaders) {
                const cell = document.createElement('td');
                cell.textContent = String(row[header] ?? '');
                tableRow.appendChild(cell);
            }
            fullDataBody.appendChild(tableRow);
        }

        fullDataStatus.textContent = `Menampilkan ${visibleRows.length} dari ${filteredRows.length} baris cocok (${fullDataRows.length} baris total).`;
        fullDataPage.textContent = `Halaman ${fullDataCurrentPage} dari ${totalPages}`;
        fullDataPrevious.disabled = fullDataCurrentPage <= 1;
        fullDataNext.disabled = fullDataCurrentPage >= totalPages;
    }

    async function loadFullData() {
        try {
            const uploadedResponse = await fetch(<?= json_encode(site_url('map-data')) ?>, { cache: 'no-store' });
            if (!uploadedResponse.ok) throw new Error('Daftar Excel tambahan gagal dimuat. HTTP ' + uploadedResponse.status);
            const uploaded = await uploadedResponse.json();
            if (!Array.isArray(uploaded.files)) throw new Error('Daftar Excel tambahan tidak valid.');

            const workbookUrls = [
                <?= json_encode(site_url('data/Data FAT Full.xlsx')) ?>,
                ...uploaded.files.map(file => file.url)
            ];
            const workbooks = await Promise.all(workbookUrls.map(readWorkbookRows));
            fullDataHeaders = [...new Set(workbooks.flatMap(rows => rows.flatMap(row => Object.keys(row))))];
            fullDataRows = workbooks.flat();
            if (fullDataHeaders.length === 0) throw new Error('Workbook tidak memiliki judul kolom atau baris data.');
            renderFullData();
        } catch (error) {
            fullDataStatus.textContent = error.message || 'Data FAT gagal dimuat.';
            fullDataStatus.setAttribute('aria-live', 'assertive');
        }
    }

    fullDataSearch.addEventListener('input', () => {
        fullDataCurrentPage = 1;
        renderFullData();
    });
    fullDataPrevious.addEventListener('click', () => {
        if (fullDataCurrentPage > 1) {
            fullDataCurrentPage--;
            renderFullData();
        }
    });
    fullDataNext.addEventListener('click', () => {
        fullDataCurrentPage++;
        renderFullData();
    });

    loadFullData();
</script>
<?= $this->endSection() ?>
