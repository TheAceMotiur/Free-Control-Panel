<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_installed();
require_role('user');

$user = current_user();
$database = ensure_user_database_credentials((int) ($user['id'] ?? 0));
$host = '127.0.0.1';
$uri = '/phpmyadmin/?db=' . urlencode($database['db_name'] ?? '') . '&server=1';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My phpMyAdmin | FreeNetly</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-10">
      <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-8 shadow-2xl shadow-violet-500/10">
        <h1 class="text-3xl font-semibold">Your personal phpMyAdmin access</h1>
        <p class="mt-3 text-slate-400">Use your personal database credentials to manage your own database workspace.</p>
        <div class="mt-6 rounded-2xl border border-violet-500/30 bg-violet-500/10 p-5">
          <p class="text-sm text-violet-200">Database</p>
          <p class="mt-2 text-lg font-medium text-white"><?= html($database['db_name'] ?? '') ?></p>
          <p class="mt-3 text-sm text-slate-300">Host: <?= html($host) ?></p>
          <p class="mt-2 text-sm text-slate-300">Username: <?= html($database['db_user'] ?? '') ?></p>
          <p class="mt-2 text-sm text-slate-300">Password: <?= html($database['db_password'] ?? '') ?></p>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
          <a href="/phpmyadmin/" class="rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950">Open shared phpMyAdmin</a>
          <a href="/user/index.php" class="rounded-full border border-slate-700 px-4 py-2 text-sm">Back to dashboard</a>
        </div>
        <p class="mt-6 text-sm text-slate-400">For a dedicated per-user phpMyAdmin experience, the server should be configured to proxy your personal database route. The credentials above are ready for your database workspace.</p>
      </div>
    </div>
  </body>
</html>
