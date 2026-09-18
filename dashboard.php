<?php
require 'config.php';
require 'includes/auth.php';
requireLogin();
$user = currentUser();
$pageTitle = 'Dashboard';

$today = date('Y-m-d');

$trxToday = $pdo->prepare("SELECT COUNT(*) as jml, COALESCE(SUM(total),0) as total FROM transaksi WHERE tanggal = ?");
$trxToday->execute([$today]);
$trxToday = $trxToday->fetch(PDO::FETCH_ASSOC);

$bahanMenipis = $pdo->query("SELECT * FROM bahan WHERE stok <= stok_minimum ORDER BY stok ASC")->fetchAll(PDO::FETCH_ASSOC);
$alatServis = $pdo->query("SELECT * FROM alat WHERE kondisi != 'baik' ORDER BY kondisi DESC")->fetchAll(PDO::FETCH_ASSOC);

$pengeluaranBulanIni = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) as total FROM pengeluaran WHERE strftime('%Y-%m', tanggal) = strftime('%Y-%m', 'now')");
$pengeluaranBulanIni->execute();
$pengeluaranBulanIni = $pengeluaranBulanIni->fetchColumn();

require 'includes/header.php';
?>

<h4 class="fw-semibold mb-3">Halo, <?= htmlspecialchars($user['nama']) ?></h4>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label">Transaksi Hari Ini</div>
      <div class="stat-num"><?= $trxToday['jml'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label">Omzet Hari Ini</div>
      <div class="stat-num"><?= rupiah($trxToday['total']) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label">Bahan Hampir Habis</div>
      <div class="stat-num text-danger"><?= count($bahanMenipis) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label">Alat Perlu Perhatian</div>
      <div class="stat-num text-warning"><?= count($alatServis) ?></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-semibold mb-3"><i class="bi bi-exclamation-triangle text-danger"></i> Bahan Hampir Habis</h6>
      <?php if (empty($bahanMenipis)): ?>
        <p class="text-muted small mb-0">Semua stok bahan aman.</p>
      <?php else: ?>
        <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>Bahan</th><th>Stok</th><th>Minimum</th></tr></thead>
          <tbody>
          <?php foreach ($bahanMenipis as $b): ?>
            <tr class="low-stock">
              <td><?= htmlspecialchars($b['nama_bahan']) ?></td>
              <td><?= $b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?></td>
              <td><?= $b['stok_minimum'] ?> <?= htmlspecialchars($b['satuan']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-semibold mb-3"><i class="bi bi-tools text-warning"></i> Alat Perlu Servis / Rusak</h6>
      <?php if (empty($alatServis)): ?>
        <p class="text-muted small mb-0">Semua alat dalam kondisi baik.</p>
      <?php else: ?>
        <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead><tr><th>Alat</th><th>Kondisi</th></tr></thead>
          <tbody>
          <?php foreach ($alatServis as $a): ?>
            <tr class="needs-service">
              <td><?= htmlspecialchars($a['nama_alat']) ?></td>
              <td><span class="badge <?= $a['kondisi']==='rusak'?'bg-danger':'bg-warning text-dark' ?>"><?= htmlspecialchars($a['kondisi']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php if ($user['role'] === 'owner'): ?>
  <div class="col-12">
    <div class="card p-3">
      <h6 class="fw-semibold mb-1"><i class="bi bi-wallet2"></i> Pengeluaran Bulan Ini</h6>
      <div class="h4" style="color:var(--pink-deep)"><?= rupiah($pengeluaranBulanIni) ?></div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
