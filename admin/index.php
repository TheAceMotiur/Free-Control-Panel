<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_installed();
require_role('admin');

$conn = db();
$users = $conn->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY id DESC');
$services = $conn->query('SELECT id, name, status FROM services ORDER BY id ASC');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel | FreeNetly</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-6 py-10">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-cyan-400 uppercase tracking-[0.3em] text-sm">Administrator</p>
          <h1 class="text-3xl font-semibold mt-2">Admin dashboard</h1>
        </div>
        <a href="/auth/logout.php" class="rounded-full border border-slate-700 px-4 py-2">Logout</a>
      </div>

      <div class="mt-8 grid md:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6">
          <h2 class="text-xl font-semibold">Overview</h2>
          <p class="mt-3 text-slate-400">Manage the control panel for your deployment and monitor user activity.</p>
          <ul class="mt-5 space-y-2 text-slate-300">
            <li>• Total users: <?= (int) $users->num_rows ?></li>
            <li>• Services tracked: <?= (int) $services->num_rows ?></li>
            <li>• Access: secure admin-only area</li>
          </ul>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6">
          <h2 class="text-xl font-semibold">Tracked services</h2>
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

      <div class="mt-8 rounded-2xl border border-slate-800 bg-slate-900/80 p-6">
        <h2 class="text-xl font-semibold">User list</h2>
        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-slate-400">
                <th class="pb-3">Name</th>
                <th class="pb-3">Email</th>
                <th class="pb-3">Role</th>
                <th class="pb-3">Created</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($user = $users->fetch_assoc()): ?>
                <tr class="border-t border-slate-800">
                  <td class="py-3"><?= html($user['full_name']) ?></td>
                  <td class="py-3"><?= html($user['email']) ?></td>
                  <td class="py-3"><?= html($user['role']) ?></td>
                  <td class="py-3"><?= html($user['created_at']) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>
