<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h1>Detail Pengguna</h1>
<p class="page-description">Informasi akun tanpa menampilkan kredensial rahasia.</p>
<section class="admin-card" style="max-width:720px">
    <h2><?= esc($user['name']) ?></h2>
    <dl class="user-details">
        <dt>Nama</dt><dd><?= esc($user['name']) ?></dd>
        <dt>Username</dt><dd><?= esc($user['username']) ?></dd>
        <dt>Peran</dt><dd><?= esc(ucfirst($user['role'])) ?></dd>
        <dt>Dibuat</dt><dd><?= esc($user['created_at'] ?? '-') ?></dd>
        <dt>Terakhir diperbarui</dt><dd><?= esc($user['updated_at'] ?? '-') ?></dd>
    </dl>
    <p>
        <a class="admin-link-card" style="display:inline-block;margin-top:16px" href="<?= site_url('admin/users/' . $user['id'] . '/edit') ?>">Edit pengguna</a>
        <a class="admin-link-card" style="display:inline-block;margin-top:16px" href="<?= site_url('admin/users') ?>">Kembali ke daftar</a>
    </p>
</section>
<?= $this->endSection() ?>
