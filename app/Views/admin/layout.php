<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($pageTitle ?? 'Admin') ?> - East Regional FAT</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #f3f6fa; color: #172b4d; font: 15px Arial, sans-serif; }
        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar { display: flex; flex: 0 0 250px; flex-direction: column; padding: 24px 16px; background: #153e64; color: #fff; transition: flex-basis .2s ease, padding .2s ease; }
        .admin-shell.sidebar-hidden .admin-sidebar { flex-basis: 0; width: 0; overflow: hidden; padding-right: 0; padding-left: 0; }
        .admin-brand { display: flex; align-items: center; gap: 12px; min-height: 54px; margin: 0 8px 32px; font-size: 14px; font-weight: 700; }
        .admin-brand img { width: 90px; height: auto; }
        .admin-nav { display: grid; gap: 6px; }
        .admin-nav a { padding: 12px 14px; border-radius: 8px; color: #d9e6f3; text-decoration: none; }
        .admin-nav a:hover, .admin-nav a:focus-visible { background: #ffffff1a; color: #fff; }
        .admin-nav a[aria-current="page"] { background: #fff; color: #153e64; font-weight: 700; }
        .admin-sidebar-footer { margin-top: auto; padding: 20px 8px 0; color: #c7d6e5; font-size: 13px; }
        .admin-main { flex: 1; min-width: 0; }
        .admin-topbar { display: flex; min-height: 72px; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 32px; border-bottom: 1px solid #e2e8f0; background: #fff; }
        .admin-topbar-start { display: flex; align-items: center; gap: 14px; }
        .sidebar-toggle { display: inline-grid; width: 38px; height: 38px; place-items: center; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: #153e64; font-size: 20px; line-height: 1; cursor: pointer; }
        .sidebar-toggle:hover, .sidebar-toggle:focus-visible { background: #f1f6fb; }
        .admin-topbar strong { font-size: 14px; }
        .admin-topbar a { color: #1769aa; text-decoration: none; }
        .admin-topbar form { margin: 0; }
        .admin-topbar button { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #334155; font: inherit; cursor: pointer; }
        .admin-content { width: min(1180px, 100%); margin: 0 auto; padding: 32px; }
        .admin-content h1 { margin: 0 0 8px; font-size: 27px; }
        .page-description { margin: 0 0 24px; color: #64748b; line-height: 1.5; }
        .admin-card { padding: 24px; border: 1px solid #e7edf4; border-radius: 12px; background: #fff; box-shadow: 0 4px 16px #172b4d0c; }
        .admin-card h2 { margin: 0 0 12px; font-size: 18px; }
        .admin-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .admin-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
        .admin-stat { padding: 20px; border-radius: 10px; background: #fff; box-shadow: 0 4px 16px #172b4d0c; }
        .admin-stat span { display: block; color: #64748b; font-size: 13px; }
        .admin-stat strong { display: block; margin-top: 8px; font-size: 27px; }
        .admin-links { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-top: 18px; }
        .admin-link-card { display: block; padding: 18px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; color: #1769aa; font-weight: 700; text-decoration: none; }
        .admin-link-card:hover { border-color: #1769aa; background: #f8fbff; }
        label { display: block; margin: 16px 0 6px; font-weight: 700; }
        input, select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; font: inherit; }
        button.primary-button { margin-top: 18px; padding: 11px 16px; border: 0; border-radius: 7px; background: #1769aa; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        button:disabled { cursor: wait; opacity: .65; }
        .notice, .errors { margin: 0 0 16px; padding: 12px; border-radius: 8px; background: #e8f7ed; color: #176536; }
        .errors { background: #fff0f0; color: #a52626; }
        .errors p { margin: 3px 0; }
        .muted { color: #64748b; font-size: 13px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #e5eaf0; white-space: nowrap; }
        th { color: #52627a; font-size: 13px; }
        .user-actions { display: flex; align-items: center; gap: 6px; }
        .user-actions a, .user-actions button { display: inline-block; padding: 6px 9px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #1769aa; font: inherit; font-size: 12px; text-decoration: none; cursor: pointer; }
        .user-actions form { margin: 0; }
        .user-actions button { color: #b42318; }
        .user-details { display: grid; grid-template-columns: minmax(120px, 180px) 1fr; gap: 0; margin: 0; }
        .user-details dt, .user-details dd { margin: 0; padding: 12px 10px; border-bottom: 1px solid #e5eaf0; }
        .user-details dt { color: #52627a; font-weight: 700; }
        .chart-wrap { position: relative; min-height: 390px; }
        .admin-map-frame { display: block; width: 100%; height: calc(100vh - 145px); min-height: 600px; border: 0; border-radius: 10px; background: #eaf0f5; }
        @media (max-width: 800px) {
            .admin-shell { display: block; }
            .admin-sidebar { position: absolute; z-index: 20; top: 58px; left: 0; width: min(280px, 85vw); min-height: calc(100vh - 58px); padding: 18px 16px; box-shadow: 8px 12px 24px #172b4d26; transition: transform .2s ease, visibility .2s ease; }
            .admin-shell.sidebar-hidden .admin-sidebar { width: min(280px, 85vw); padding: 18px 16px; transform: translateX(-105%); visibility: hidden; }
            .admin-brand { min-height: 40px; margin: 0 4px 22px; }
            .admin-nav { display: grid; gap: 6px; overflow: visible; }
            .admin-nav a { flex: initial; padding: 12px 14px; font-size: 14px; white-space: normal; }
            .admin-sidebar-footer { display: block; }
            .admin-topbar { min-height: 58px; padding: 12px 16px; }
            .admin-content { padding: 22px 16px; }
            .admin-grid, .admin-links { grid-template-columns: 1fr; }
        }
        @media (max-width: 520px) { .admin-stats { grid-template-columns: 1fr; } .chart-wrap { min-height: 300px; } }
    </style>
</head>
<body>
<div class="admin-shell" id="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="admin-brand">
            <img src="<?= site_url('resources/logoamt.png') ?>" alt="Logo AMT">
            <span>ADMIN</span>
        </div>
        <nav class="admin-nav" aria-label="Menu admin">
            <a href="<?= site_url('admin') ?>" <?= $currentPage === 'dashboard' ? 'aria-current="page"' : '' ?>>Dashboard</a>
            <a href="<?= site_url('admin/diagram') ?>" <?= $currentPage === 'diagram' ? 'aria-current="page"' : '' ?>>Diagram FAT</a>
            <a href="<?= site_url('admin/data') ?>" <?= $currentPage === 'data' ? 'aria-current="page"' : '' ?>>Full Data</a>
            <a href="<?= site_url('admin/upload') ?>" <?= $currentPage === 'upload' ? 'aria-current="page"' : '' ?>>Upload Excel</a>
            <a href="<?= site_url('admin/users') ?>" <?= $currentPage === 'users' ? 'aria-current="page"' : '' ?>>Tambah Pengguna</a>
        </nav>
        <div class="admin-sidebar-footer">East Regional · FAT</div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-start">
                <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="true" aria-label="Sembunyikan sidebar">☰</button>
                <strong><?= esc(session()->get('name')) ?></strong>
            </div>
            <div>
                <a href="<?= site_url('/') ?>">Buka peta</a>
                &nbsp; · &nbsp;
                <form method="post" action="<?= site_url('logout') ?>" style="display:inline">
                    <?= csrf_field() ?><button type="submit">Keluar</button>
                </form>
            </div>
        </header>
        <main class="admin-content">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>
<script>
    (() => {
        const shell = document.getElementById('admin-shell');
        const toggle = document.getElementById('sidebar-toggle');
        const storageKey = 'webgis-admin-sidebar-hidden';

        const setSidebarHidden = hidden => {
            shell.classList.toggle('sidebar-hidden', hidden);
            toggle.setAttribute('aria-expanded', String(!hidden));
            toggle.setAttribute('aria-label', hidden ? 'Tampilkan sidebar' : 'Sembunyikan sidebar');
        };

        setSidebarHidden(localStorage.getItem(storageKey) === 'true');
        toggle.addEventListener('click', () => {
            const hidden = !shell.classList.contains('sidebar-hidden');
            setSidebarHidden(hidden);
            localStorage.setItem(storageKey, String(hidden));
        });
    })();
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
