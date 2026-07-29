<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_installed();
require_role('user');

$conn = db();
$user = current_user();
$services = $conn->query('SELECT name, status FROM services ORDER BY id ASC');
$database = ensure_user_database_credentials((int) ($user['id'] ?? 0));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Panel | FreeNetly</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-10">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-violet-400 uppercase tracking-[0.3em] text-sm">User area</p>
          <h1 class="text-3xl font-semibold mt-2">Welcome, <?= html($user['full_name'] ?? 'User') ?></h1>
        </div>
        <a href="/auth/logout.php" class="rounded-full border border-slate-700 px-4 py-2">Logout</a>
      </div>

      <div class="mt-8 grid md:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6">
          <h2 class="text-xl font-semibold">Your profile</h2>
          <dl class="mt-4 space-y-3 text-slate-300">
            <div class="flex justify-between"><dt class="text-slate-400">Name</dt><dd><?= html($user['full_name'] ?? '') ?></dd></div>
            <div class="flex justify-between"><dt class="text-slate-400">Email</dt><dd><?= html($user['email'] ?? '') ?></dd></div>
            <div class="flex justify-between"><dt class="text-slate-400">Role</dt><dd><?= html($user['role'] ?? 'user') ?></dd></div>
          </dl>
          <div class="mt-5 rounded-xl border border-violet-500/30 bg-violet-500/10 p-4">
            <p class="text-sm text-violet-200">Personal database workspace</p>
            <p class="mt-2 text-sm text-slate-300">Database: <?= html($database['db_name'] ?? '') ?></p>
            <p class="mt-2 text-sm text-slate-300">Username: <?= html($database['db_user'] ?? '') ?></p>
            <a href="/user/phpmyadmin.php" class="mt-4 inline-flex rounded-full bg-violet-500 px-4 py-2 text-sm font-medium text-slate-950">Open my phpMyAdmin</a>
          </div>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6">
          <h2 class="text-xl font-semibold">Service status</h2>
          <ul class="mt-4 space-y-3">
            <?php while ($service = $services->fetch_assoc()): ?>
              <li class="flex items-center justify-between rounded-xl border border-slate-800 px-4 py-3">
                <span><?= html($service['name']) ?></span>
                <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-sm text-emerald-300"><?= html($service['status']) ?></span>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
    </div>
  </body>
</html>
