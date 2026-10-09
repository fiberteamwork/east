<?php
$loginError = isset($error) && is_string($error) ? $error : null;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>East Regional - FAT</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #eef3f8; color: #172b4d; font: 16px Arial, sans-serif; }
        main { width: min(100%, 420px); padding: 32px; border-radius: 16px; background: #fff; box-shadow: 0 12px 36px #172b4d1a; }
        h1 { margin: 0 0 8px; font-size: 25px; }
        p { color: #52627a; line-height: 1.5; }
        label { display: block; margin: 18px 0 6px; font-weight: 700; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
        button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 8px; background: #1769aa; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        .error { padding: 12px; border-radius: 8px; background: #fff0f0; color: #a52626; }
    </style>
</head>
<body>
<main>
    <center><h1>East Regional</h1></center>
    <p>Masuk untuk membuka aplikasi peta.</p>
    <?php if ($loginError !== null): ?><p class="error" role="alert"><?= esc($loginError) ?></p><?php endif ?>
    <form method="post" action="<?= site_url('login') ?>">
        <?= csrf_field() ?>
        <label for="username">Username</label>
        <input id="username" name="username" autocomplete="username" required autofocus>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit">Masuk</button>
    </form>
</main>
</body>
</html>
