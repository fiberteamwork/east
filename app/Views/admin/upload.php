<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h1>Upload Excel</h1>
<p class="page-description">Tambahkan data baru ke peta dengan mengunggah workbook Excel.</p>
<?php if ($mapDataNotice): ?><p class="notice" role="status"><?= esc($mapDataNotice) ?></p><?php endif ?>
<?php if ($mapDataErrors !== []): ?>
    <div class="errors" role="alert">
        <?php foreach ($mapDataErrors as $error): ?><p><?= esc($error) ?></p><?php endforeach ?>
    </div>
<?php endif ?>
<section class="admin-card" style="max-width:680px">
    <h2>Tambahkan workbook</h2>
    <p class="muted">Format .xlsx, sheet pertama, minimal satu baris data, dan wajib memiliki kolom Latitude serta Longitude. Ukuran maksimal 25 MB.</p>
    <form id="map-data-upload" method="post" action="<?= site_url('admin/map-data') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label for="excel_file">File Excel (.xlsx)</label>
        <input id="excel_file" name="excel_file" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
        <button class="primary-button" type="submit">Unggah dan tambahkan data</button>
        <p id="map-data-upload-status" class="muted" role="status" aria-live="polite"></p>
    </form>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= site_url('resources/xlsx-lite.js') ?>"></script>
<script>
    const mapDataUploadForm = document.getElementById('map-data-upload');
    mapDataUploadForm.addEventListener('submit', async event => {
        event.preventDefault();
        const input = document.getElementById('excel_file');
        const status = document.getElementById('map-data-upload-status');
        const file = input.files[0];
        if (!file) return;

        const button = mapDataUploadForm.querySelector('button[type="submit"]');
        button.disabled = true;
        status.textContent = 'Memeriksa workbook Excel...';

        try {
            const workbook = await XLSXLite.read(await file.arrayBuffer());
            const sheetName = workbook.sheetNames[0];
            const rows = sheetName ? workbook.sheets[sheetName] : null;
            if (!rows || rows.length < 2) {
                throw new Error('Sheet pertama harus berisi judul kolom dan minimal satu baris data.');
            }

            const headers = rows[0].map(value => String(value || '').trim().toLowerCase().replace(/[^a-z0-9]/g, ''));
            const hasLatitude = headers.some(header => ['latitude', 'lat', 'lintang', 'y'].includes(header));
            const hasLongitude = headers.some(header => ['longitude', 'long', 'lon', 'lng', 'bujur', 'x'].includes(header));
            if (!hasLatitude || !hasLongitude) {
                throw new Error('Kolom koordinat tidak ditemukan. Sertakan kolom Latitude dan Longitude.');
            }

            HTMLFormElement.prototype.submit.call(mapDataUploadForm);
        } catch (error) {
            status.textContent = error.message || 'Workbook Excel tidak dapat dibaca.';
            button.disabled = false;
        }
    });
</script>
<?= $this->endSection() ?>
