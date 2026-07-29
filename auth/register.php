<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_installed();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$fullName || !$email || !$password) {
        $error = 'Please complete every field.';
    } else {
        try {
            $conn = db();
            $hash = password_hash_value($password);
            $stmt = $conn->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, "user")');
            $stmt->bind_param('sss', $fullName, $email, $hash);
            $stmt->execute();
            $userId = (int) $conn->insert_id;
            ensure_user_database_credentials($userId);
            $message = 'Account created. You can now log in and open your personal database workspace.';
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register | FreeNetly</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-xl rounded-3xl border border-slate-800 bg-slate-900/80 p-8 shadow-2xl shadow-violet-500/10">
      <h1 class="text-3xl font-semibold">Create your account</h1>
      <p class="mt-3 text-slate-400">Register for the user panel and access your dashboard.</p>

      <?php if ($message): ?>
        <div class="mt-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-300"><?= html($message) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="mt-6 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-rose-300"><?= html($error) ?></div>
      <?php endif; ?>

      <form method="post" class="mt-8 space-y-4">
        <label class="block">
          <span class="mb-2 block text-sm text-slate-400">Full name</span>
          <input type="text" name="full_name" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3" />
        </label>
        <label class="block">
          <span class="mb-2 block text-sm text-slate-400">Email</span>
          <input type="email" name="email" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3" />
        </label>
        <label class="block">
          <span class="mb-2 block text-sm text-slate-400">Password</span>
          <input type="password" name="password" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3" />
        </label>
        <button type="submit" class="w-full rounded-full bg-violet-500 px-5 py-3 font-medium text-slate-950">Register</button>
      </form>

      <p class="mt-6 text-sm text-slate-400">Already have an account? <a href="login.php" class="text-cyan-400">Sign in</a></p>
    </div>
  </body>
</html>
