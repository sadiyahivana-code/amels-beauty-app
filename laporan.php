<?php
require 'config.php';
require 'includes/auth.php';
requireRole('owner');
$pageTitle = 'Laporan';

$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// Penjualan
$stmtPenjualan = $pdo->prepare("SELECT t.*, u.nama as nama_kasir FROM transaksi t JOIN users u ON u.id=t.kasir_id WHERE t.tanggal BETWEEN ? AND ? ORDER BY t.tanggal DESC, t.id DESC");
$stmtPenjualan->execute([$dari, $sampai]);
$penjualan = $stmtPenjualan->fetchAll(PDO::FETCH_ASSOC);
$totalPenjualan = array_sum(array_column($penjualan, 'total'));

// Layanan terlaris
$stmtLaris = $pdo->prepare("SELECT nama_layanan, SUM(jumlah) as total_terjual, SUM(subtotal) as total_omzet
    FROM transaksi_detail td JOIN transaksi t ON t.id = td.transaksi_id
    WHERE t.tanggal BETWEEN ? AND ?
    GROUP BY nama_layanan ORDER BY total_terjual DESC");
$stmtLaris->execute([$dari, $sampai]);
$layananLaris = $stmtLaris->fetchAll(PDO::FETCH_ASSOC);

// Stok bahan
$bahan = $pdo->query("SELECT * FROM bahan ORDER BY (stok <= stok_minimum) DESC, nama_bahan")->fetchAll(PDO::FETCH_ASSOC);

// Kondisi alat
$alat = $pdo->query("SELECT * FROM alat ORDER BY (kondisi != 'baik') DESC, nama_alat")->fetchAll(PDO::FETCH_ASSOC);

// Laba rugi
$stmtPengeluaran = $pdo->prepare("SELECT * FROM pengeluaran WHERE tanggal BETWEEN ? AND ? ORDER BY tanggal DESC");
$stmtPengeluaran->execute([$dari, $sampai]);
$pengeluaran = $stmtPengeluaran->fetchAll(PDO::FETCH_ASSOC);
$totalPengeluaran = array_sum(array_column($pengeluaran, 'jumlah'));
$labaRugi = $totalPenjualan - $totalPengeluaran;

// Data grafik penjualan harian (ikut filter tanggal yang sama)
$stmtChart = $pdo->prepare("SELECT tanggal, SUM(total) as total FROM transaksi WHERE tanggal BETWEEN ? AND ? GROUP BY tanggal ORDER BY tanggal");
$stmtChart->execute([$dari, $sampai]);
$chartRows = $stmtChart->fetchAll(PDO::FETCH_ASSOC);
$chartLabels = array_column($chartRows, 'tanggal');
$chartData = array_map('intval', array_column($chartRows, 'total'));

require 'includes/header.php';
?>

<h5 class="fw-semibold mb-3">Laporan</h5>

<form method="get" class="row g-2 mb-3 no-print">
  <div class="col-6 col-md-3">
    <label class="form-label small">Dari</label>
    <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>" class="form-control">
  </div>
  <div class="col-6 col-md-3">
    <label class="form-label small">Sampai</label>
    <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="form-control">
  </div>
  <div class="col-6 col-md-2 d-flex align-items-end">
    <button class="btn btn-primary w-100">Filter</button>
  </div>
  <div class="col-6 col-md-2 d-flex align-items-end">
    <button type="button" onclick="window.print()" class="btn btn-outline-secondary w-100"><i class="bi bi-printer"></i> Export PDF</button>
  </div>
</form>

<ul class="nav nav-tabs mb-3 no-print" id="laporanTab">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPenjualan">Penjualan</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabStok">Stok Bahan</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAlat">Kondisi Alat</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLabaRugi">Laba Rugi</button></li>
</ul>

<div class="tab-content">

  <div class="tab-pane fade show active" id="tabPenjualan">
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <div class="stat-card"><div class="stat-label">Total Omzet Periode Ini</div><div class="stat-num"><?= rupiah($totalPenjualan) ?></div></div>
      </div>
      <div class="col-md-6">
        <div class="stat-card"><div class="stat-label">Jumlah Transaksi</div><div class="stat-num"><?= count($penjualan) ?></div></div>
      </div>
    </div>
    <div class="card p-3 mb-3">
      <h6 class="fw-semibold mb-2">Grafik Penjualan Harian</h6>
      <canvas id="chartPenjualan" height="90"></canvas>
    </div>
    <h6 class="fw-semibold">Layanan Terlaris</h6>
    <div class="table-responsive mb-3">
    <table class="table table-sm bg-white">
      <thead><tr><th>Layanan</th><th>Terjual</th><th class="text-end">Omzet</th></tr></thead>
      <tbody>
      <?php foreach ($layananLaris as $l): ?>
        <tr><td><?= htmlspecialchars($l['nama_layanan']) ?></td><td><?= $l['total_terjual'] ?></td><td class="text-end"><?= rupiah($l['total_omzet']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <h6 class="fw-semibold">Detail Transaksi</h6>
    <div class="table-responsive">
    <table class="table table-sm bg-white">
      <thead><tr><th>Tanggal</th><th>Kasir</th><th>Metode</th><th class="text-end">Total</th></tr></thead>
      <tbody>
      <?php foreach ($penjualan as $p): ?>
        <tr><td><?= htmlspecialchars($p['tanggal']) ?></td><td><?= htmlspecialchars($p['nama_kasir']) ?></td><td class="text-uppercase"><?= htmlspecialchars($p['metode_bayar']) ?></td><td class="text-end"><?= rupiah($p['total']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tabStok">
    <div class="table-responsive">
    <table class="table table-sm bg-white">
      <thead><tr><th>Bahan</th><th>Kategori</th><th>Stok</th><th>Min</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($bahan as $b): $low = $b['stok'] <= $b['stok_minimum']; ?>
        <tr class="<?= $low ? 'low-stock' : '' ?>">
          <td><?= htmlspecialchars($b['nama_bahan']) ?></td>
          <td class="small text-muted"><?= htmlspecialchars($b['kategori_layanan']) ?></td>
          <td><?= $b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?></td>
          <td><?= $b['stok_minimum'] ?></td>
          <td><?= $low ? '<span class="badge bg-danger">Menipis</span>' : '<span class="badge bg-success">Aman</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tabAlat">
    <div class="table-responsive">
    <table class="table table-sm bg-white">
      <thead><tr><th>Alat</th><th>Jenis</th><th>Kondisi</th><th>Servis Berikutnya</th></tr></thead>
      <tbody>
      <?php foreach ($alat as $a): ?>
        <tr class="<?= $a['kondisi'] !== 'baik' ? 'needs-service' : '' ?>">
          <td><?= htmlspecialchars($a['nama_alat']) ?></td>
          <td class="small text-muted text-capitalize"><?= htmlspecialchars($a['jenis']) ?></td>
          <td class="text-capitalize"><?= htmlspecialchars($a['kondisi']) ?></td>
          <td class="small"><?= htmlspecialchars($a['tanggal_servis_berikutnya'] ?? '-') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tabLabaRugi">
    <div class="row g-3 mb-3">
      <div class="col-md-4"><div class="stat-card"><div class="stat-label">Pemasukan</div><div class="stat-num text-success"><?= rupiah($totalPenjualan) ?></div></div></div>
      <div class="col-md-4"><div class="stat-card"><div class="stat-label">Pengeluaran</div><div class="stat-num text-danger"><?= rupiah($totalPengeluaran) ?></div></div></div>
      <div class="col-md-4"><div class="stat-card"><div class="stat-label">Laba / Rugi</div><div class="stat-num" style="color:<?= $labaRugi >= 0 ? '#2e7d32' : '#c62828' ?>"><?= rupiah($labaRugi) ?></div></div></div>
    </div>
    <h6 class="fw-semibold">Rincian Pengeluaran</h6>
    <div class="table-responsive">
    <table class="table table-sm bg-white">
      <thead><tr><th>Tanggal</th><th>Keterangan</th><th>Kategori</th><th class="text-end">Jumlah</th></tr></thead>
      <tbody>
      <?php foreach ($pengeluaran as $p): ?>
        <tr><td class="small"><?= htmlspecialchars($p['tanggal']) ?></td><td><?= htmlspecialchars($p['keterangan']) ?></td><td class="small text-muted"><?= htmlspecialchars($p['kategori']) ?></td><td class="text-end"><?= rupiah($p['jumlah']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartPenjualan'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Omzet (Rp)',
      data: <?= json_encode($chartData) ?>,
      backgroundColor: '#E8779E',
      borderRadius: 6,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } }
    }
  }
});
</script>

<?php require 'includes/footer.php'; ?>
