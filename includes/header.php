<?php
$user = currentUser();
$page = basename($_SERVER['PHP_SELF']);
function navActive($p, $page) { return $p === $page ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Amels Beauty</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">

<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#E8779E">
<link rel="icon" href="assets/icons/icon-192.png">
<link rel="apple-touch-icon" href="assets/icons/icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Amels Beauty">
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('service-worker.js').catch(() => {});
  });
}
</script>
</head>
<body>
<?php if ($user): ?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold" href="dashboard.php"><img src="assets/logo-round.png" alt="Amels Beauty" style="height:42px;width:42px;object-fit:cover;border-radius:50%;"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= navActive('dashboard.php',$page) ?>" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('kasir.php',$page) ?>" href="kasir.php"><i class="bi bi-cash-coin"></i> Kasir</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('transaksi_riwayat.php',$page) ?>" href="transaksi_riwayat.php"><i class="bi bi-receipt"></i> Riwayat</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('bahan.php',$page) ?>" href="bahan.php"><i class="bi bi-box-seam"></i> Bahan</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('alat.php',$page) ?>" href="alat.php"><i class="bi bi-tools"></i> Alat</a></li>
        <?php if ($user['role'] === 'owner'): ?>
        <li class="nav-item"><a class="nav-link <?= navActive('pengeluaran.php',$page) ?>" href="pengeluaran.php"><i class="bi bi-wallet2"></i> Pengeluaran</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('laporan.php',$page) ?>" href="laporan.php"><i class="bi bi-bar-chart"></i> Laporan</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('users.php',$page) ?>" href="users.php"><i class="bi bi-people"></i> User</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('pengaturan.php',$page) ?>" href="pengaturan.php"><i class="bi bi-gear"></i> Pengaturan</a></li>
        <?php endif; ?>
      </ul>
      <span class="navbar-text text-light me-3 small">
        <?= htmlspecialchars($user['nama']) ?> · <span class="badge bg-light text-dark"><?= htmlspecialchars($user['role']) ?></span>
      </span>
      <a href="logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </div>
</nav>
<?php endif; ?>
<main class="container-fluid py-3 py-md-4 px-3 px-md-4">
