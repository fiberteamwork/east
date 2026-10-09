<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h1>Dashboard Admin</h1>
<p class="page-description">Ringkasan dan akses cepat untuk pengelolaan WebGIS East Regional.</p>
<div class="admin-stats">
    <div class="admin-stat"><span>Total pengguna</span><strong><?= number_format($userCount, 0, ',', '.') ?></strong></div>
    <div class="admin-stat"><span>File Excel tambahan</span><strong><?= number_format($uploadCount, 0, ',', '.') ?></strong></div>
</div>
<section class="admin-card">
    <h2>Menu pengelolaan</h2>
    <div class="admin-links">
        <a class="admin-link-card" href="<?= site_url('admin/diagram') ?>">Diagram FAT</a>
        <a class="admin-link-card" href="<?= site_url('admin/data') ?>">Tampilkan full data</a>
        <a class="admin-link-card" href="<?= site_url('admin/upload') ?>">Upload Excel</a>
        <a class="admin-link-card" href="<?= site_url('admin/users') ?>">Tambah pengguna</a>
        <a class="admin-link-card" href="<?= site_url('/') ?>">Buka peta</a>
    </div>
</section>
<?= $this->endSection() ?>
