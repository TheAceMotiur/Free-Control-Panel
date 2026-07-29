<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_installed();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim($_POST['username_or_email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$loginValue || !$password) {
        $error = 'Please enter both your username or email and your password.';
    } else {
        try {
            $conn = db();
            $stmt = $conn->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? OR full_name = ? LIMIT 1');
            $stmt->bind_param('ss', $loginValue, $loginValue);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify_value($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];
                if (($user['role'] ?? 'user') === 'admin') {
                    redirect('/admin/index.php');
                }
                redirect('/user/index.php');
            }

            $error = 'Invalid credentials.';
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
    <title>Login | FreeNetly</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-xl rounded-3xl border border-slate-800 bg-slate-900/80 p-8 shadow-2xl shadow-cyan-500/10">
      <h1 class="text-3xl font-semibold">Sign in</h1>
      <p class="mt-3 text-slate-400">Use your email and password to reach your panel.</p>

      <?php if ($error): ?>
        <div class="mt-6 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-rose-300"><?= html($error) ?></div>
      <?php endif; ?>

      <form method="post" class="mt-8 space-y-4">
        <label class="block">
          <span class="mb-2 block text-sm text-slate-400">Username or email</span>
          <input type="text" name="username_or_email" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3" />
        </label>
        <label class="block">
          <span class="mb-2 block text-sm text-slate-400">Password</span>
          <input type="password" name="password" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3" />
        </label>
        <button type="submit" class="w-full rounded-full bg-cyan-500 px-5 py-3 font-medium text-slate-950">Login</button>
      </form>

      <p class="mt-6 text-sm text-slate-400">New here? <a href="register.php" class="text-cyan-400">Create an account</a></p>
    </div>
  </body>
</html>
