<?php
require 'config.php';
require 'includes/auth.php';
requireRole('owner');
$pageTitle = 'Pengeluaran';

$rows = $pdo->query("SELECT * FROM pengeluaran ORDER BY tanggal DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$totalBulanIni = 0;
foreach ($rows as $r) if (substr($r['tanggal'],0,7) === date('Y-m')) $totalBulanIni += $r['jumlah'];

require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-semibold mb-0">Pengeluaran</h5>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPengeluaran">
    <i class="bi bi-plus-lg"></i> Catat Pengeluaran
  </button>
</div>

<div class="card p-3 mb-3">
  <div class="d-flex justify-content-between">
    <span class="text-muted">Total pengeluaran bulan ini</span>
    <span class="fw-bold text-danger"><?= rupiah($totalBulanIni) ?></span>
  </div>
</div>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white align-middle">
  <thead><tr><th>Tanggal</th><th>Keterangan</th><th>Kategori</th><th class="text-end">Jumlah</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td class="small"><?= htmlspecialchars($r['tanggal']) ?></td>
      <td><?= htmlspecialchars($r['keterangan']) ?></td>
      <td class="small text-muted"><?= htmlspecialchars($r['kategori']) ?></td>
      <td class="text-end"><?= rupiah($r['jumlah']) ?></td>
      <td>
        <form method="post" action="pengeluaran_action.php" onsubmit="return confirm('Hapus catatan ini?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($rows)): ?>
    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada pengeluaran tercatat.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<div class="modal fade" id="modalPengeluaran" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="pengeluaran_action.php" class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Catat Pengeluaran</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="add">
        <div class="mb-2">
          <label class="form-label small">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Keterangan</label>
          <input type="text" name="keterangan" class="form-control" placeholder="Belanja lem eyelash, servis hair dryer, dll" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Kategori</label>
          <select name="kategori" class="form-select">
            <option value="belanja bahan">Belanja Bahan</option>
            <option value="servis alat">Servis Alat</option>
            <option value="operasional lain">Operasional Lain</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Jumlah (Rp)</label>
          <input type="number" name="jumlah" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
