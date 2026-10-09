<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<h1>Tambah Pengguna</h1>
<p class="page-description">Buat akun pengguna dan kelola akun yang dapat mengakses peta.</p>
<?php if ($notice): ?><p class="notice" role="status"><?= esc($notice) ?></p><?php endif ?>
<?php if ($errors !== []): ?>
    <div class="errors" role="alert">
        <?php foreach ($errors as $error): ?><p><?= esc($error) ?></p><?php endforeach ?>
    </div>
<?php endif ?>
<div class="admin-grid">
    <section class="admin-card">
        <h2>Buat akun baru</h2>
        <form method="post" action="<?= site_url('admin/users') ?>">
            <?= csrf_field() ?>
            <label for="name">Nama</label>
            <input id="name" name="name" value="<?= esc(old('name') ?? '') ?>" maxlength="100" required>
            <label for="username">Username</label>
            <input id="username" name="username" value="<?= esc(old('username') ?? '') ?>" minlength="3" maxlength="50" pattern="[A-Za-z0-9_.\-]+" autocomplete="username" required>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" minlength="12" maxlength="255" autocomplete="new-password" required>
            <label for="role">Peran</label>
            <select id="role" name="role" required>
                <option value="user" <?= old('role', 'user') === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <button class="primary-button" type="submit">Tambahkan pengguna</button>
        </form>
    </section>
    <section class="admin-card">
        <h2>Daftar pengguna (<?= count($users) ?>)</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Username</th><th>Peran</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= esc($user['name']) ?></td>
                        <td><?= esc($user['username']) ?></td>
                        <td><?= esc($user['role']) ?></td>
                        <td><?= esc($user['created_at'] ?? '-') ?></td>
                        <td>
                            <div class="user-actions">
                                <a href="<?= site_url('admin/users/' . $user['id']) ?>">View</a>
                                <a href="<?= site_url('admin/users/' . $user['id'] . '/edit') ?>">Edit</a>
                                <?php if ((int) session()->get('user_id') !== (int) $user['id']): ?>
                                    <form method="post" action="<?= site_url('admin/users/' . $user['id'] . '/delete') ?>" onsubmit="return confirm('Hapus akun ini? Tindakan ini tidak dapat dibatalkan.');">
                                        <?= csrf_field() ?>
                                        <button type="submit">Delete</button>
                                    </form>
                                <?php endif ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
