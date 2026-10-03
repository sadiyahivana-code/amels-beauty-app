<?php
require 'config.php';
require 'includes/auth.php';
requireLogin();
$pageTitle = 'Riwayat Transaksi';

$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT t.*, u.nama as nama_kasir FROM transaksi t
    JOIN users u ON u.id = t.kasir_id
    WHERE t.tanggal BETWEEN ? AND ?
    ORDER BY t.id DESC");
$stmt->execute([$dari, $sampai]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalOmzet = array_sum(array_column($rows, 'total'));

require 'includes/header.php';
?>

<h5 class="fw-semibold mb-3">Riwayat Transaksi</h5>

<form method="get" class="row g-2 mb-3">
  <div class="col-6 col-md-3">
    <label class="form-label small">Dari</label>
    <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>" class="form-control">
  </div>
  <div class="col-6 col-md-3">
    <label class="form-label small">Sampai</label>
    <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="form-control">
  </div>
  <div class="col-12 col-md-3 d-flex align-items-end">
    <button class="btn btn-primary w-100">Filter</button>
  </div>
</form>

<div class="card p-3 mb-3">
  <div class="d-flex justify-content-between">
    <span class="text-muted">Total omzet periode ini</span>
    <span class="fw-bold" style="color:var(--pink-deep)"><?= rupiah($totalOmzet) ?></span>
  </div>
</div>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white">
  <thead><tr><th>#</th><th>Tanggal</th><th>Kasir</th><th>Metode</th><th class="text-end">Total</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?= htmlspecialchars($r['tanggal']) ?></td>
      <td><?= htmlspecialchars($r['nama_kasir']) ?></td>
      <td class="text-uppercase"><?= htmlspecialchars($r['metode_bayar']) ?></td>
      <td class="text-end"><?= rupiah($r['total']) ?></td>
      <td><a href="struk.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat</a></td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($rows)): ?>
    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada transaksi pada periode ini.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php require 'includes/footer.php'; ?>
