<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FreeNetly Control Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-16">
      <header class="text-center mb-12">
        <p class="uppercase tracking-[0.35em] text-cyan-400 text-sm">GitHub ready deployment</p>
        <h1 class="text-4xl md:text-6xl font-semibold mt-4">Simple web control panel with one-click setup</h1>
        <p class="mt-6 text-lg text-slate-300 max-w-3xl mx-auto">
          Install Apache, PHP, MySQL, phpMyAdmin, the admin area, and the user area from one script.
        </p>
      </header>

      <div class="grid md:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-cyan-500/30 bg-slate-900/70 p-6 shadow-2xl shadow-cyan-500/10">
          <h2 class="text-xl font-semibold">One-click installer</h2>
          <p class="mt-3 text-slate-400">Set up the database, create the default admin account, and prepare the app in a few steps.</p>
          <a href="installer.php" class="mt-6 inline-flex rounded-full bg-cyan-500 px-4 py-2 text-sm font-medium text-slate-950">Run installer</a>
        </div>
        <div class="rounded-2xl border border-emerald-500/30 bg-slate-900/70 p-6 shadow-2xl shadow-emerald-500/10">
          <h2 class="text-xl font-semibold">Admin panel</h2>
          <p class="mt-3 text-slate-400">Manage users, overview services, and monitor control panel actions from a clean dashboard.</p>
          <a href="auth/login.php" class="mt-6 inline-flex rounded-full bg-emerald-500 px-4 py-2 text-sm font-medium text-slate-950">Open admin access</a>
        </div>
        <div class="rounded-2xl border border-violet-500/30 bg-slate-900/70 p-6 shadow-2xl shadow-violet-500/10">
          <h2 class="text-xl font-semibold">User panel</h2>
          <p class="mt-3 text-slate-400">Provide a simple area for regular users to view account information and service status.</p>
          <a href="auth/register.php" class="mt-6 inline-flex rounded-full bg-violet-500 px-4 py-2 text-sm font-medium text-slate-950">Create account</a>
        </div>
      </div>

      <div class="mt-10 rounded-2xl border border-slate-800 bg-slate-900/60 p-8">
        <h3 class="text-2xl font-semibold">Included components</h3>
        <ul class="mt-4 grid md:grid-cols-2 gap-4 text-slate-300">
          <li>• Apache + PHP + MySQL + phpMyAdmin setup script</li>
          <li>• Tailwind CSS based UI for a polished experience</li>
          <li>• Standard admin and user dashboards</li>
          <li>• Database-backed registration and login flow</li>
        </ul>
      </div>
    </div>
  </body>
</html>
