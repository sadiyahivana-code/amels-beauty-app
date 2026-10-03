<?php
require 'config.php';
require 'includes/auth.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($u && password_verify($password, $u['password'])) {
        $_SESSION['user'] = ['id' => $u['id'], 'nama' => $u['nama'], 'username' => $u['username'], 'role' => $u['role']];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk — Amels Beauty</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#E8779E">
<link rel="icon" href="assets/icons/icon-192.png">
<link rel="apple-touch-icon" href="assets/icons/icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => navigator.serviceWorker.register('service-worker.js').catch(() => {}));
}
</script>
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-11 col-sm-8 col-md-5 col-lg-4">
      <div class="text-center mb-4">
        <img src="assets/logo.png" alt="Amels Beauty" style="width:150px;">
        <p class="text-muted mb-0 mt-2">Aplikasi Kasir & Manajemen Stok</p>
      </div>
      <div class="card shadow-sm p-4">
        <?php if ($error): ?>
          <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>
      </div>
  </div>
</div>
</body>
</html>
