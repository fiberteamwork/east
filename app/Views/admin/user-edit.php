<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h1>Edit Pengguna</h1>
<p class="page-description">Ubah profil dan peran akun. Kosongkan password jika tidak ingin menggantinya.</p>
<?php if ($errors !== []): ?>
    <div class="errors" role="alert">
        <?php foreach ($errors as $error): ?><p><?= esc($error) ?></p><?php endforeach ?>
    </div>
<?php endif ?>
<section class="admin-card" style="max-width:680px">
    <form method="post" action="<?= site_url('admin/users/' . $user['id'] . '/edit') ?>">
        <?= csrf_field() ?>
        <label for="name">Nama</label>
        <input id="name" name="name" value="<?= esc(old('name', $user['name'])) ?>" maxlength="100" required>
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= esc(old('username', $user['username'])) ?>" minlength="3" maxlength="50" pattern="[A-Za-z0-9_.\-]+" autocomplete="username" required>
        <label for="role">Peran</label>
        <select id="role" name="role" required>
            <option value="user" <?= old('role', $user['role']) === 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= old('role', $user['role']) === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <label for="password">Password baru (opsional)</label>
        <input id="password" name="password" type="password" minlength="12" maxlength="255" autocomplete="new-password">
        <button class="primary-button" type="submit">Simpan perubahan</button>
        <a href="<?= site_url('admin/users') ?>" style="margin-left:12px;color:#1769aa">Batal</a>
    </form>
</section>
<?= $this->endSection() ?>
