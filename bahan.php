<?php
require 'config.php';
require 'includes/auth.php';
requireLogin();
$pageTitle = 'Manajemen Bahan';
$user = currentUser();

$rows = $pdo->query("SELECT * FROM bahan ORDER BY kategori_layanan, nama_bahan")->fetchAll(PDO::FETCH_ASSOC);

require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-semibold mb-0">Manajemen Bahan</h5>
  <?php if ($user['role'] === 'owner'): ?>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBahan" onclick="resetForm()">
    <i class="bi bi-plus-lg"></i> Tambah Bahan
  </button>
  <?php endif; ?>
</div>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white align-middle">
  <thead>
    <tr><th>Nama Bahan</th><th>Kategori</th><th>Stok</th><th>Min</th><th></th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $b): $low = $b['stok'] <= $b['stok_minimum']; ?>
    <tr class="<?= $low ? 'low-stock' : '' ?>">
      <td><?= htmlspecialchars($b['nama_bahan']) ?></td>
      <td class="small text-muted"><?= htmlspecialchars($b['kategori_layanan']) ?></td>
      <td><?= $b['stok'] ?> <?= htmlspecialchars($b['satuan']) ?> <?= $low ? '<span class="badge bg-danger">Menipis</span>' : '' ?></td>
      <td><?= $b['stok_minimum'] ?></td>
      <td>
        <?php if ($user['role'] === 'owner'): ?>
        <button class="btn btn-sm btn-outline-secondary" onclick='editBahan(<?= json_encode($b) ?>)'><i class="bi bi-pencil"></i></button>
        <form method="post" action="bahan_action.php" class="d-inline" onsubmit="return confirm('Hapus bahan ini?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $b['id'] ?>">
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if ($user['role'] === 'owner'): ?>
<div class="modal fade" id="modalBahan" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="bahan_action.php" class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="modalBahanTitle">Tambah Bahan</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="id" id="formId">
        <div class="mb-2">
          <label class="form-label small">Nama Bahan</label>
          <input type="text" name="nama_bahan" id="formNama" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Kategori Layanan</label>
          <input type="text" name="kategori_layanan" id="formKategori" class="form-control" placeholder="Eyelash Extensions, Nail Art, dll">
        </div>
        <div class="row g-2">
          <div class="col-4">
            <label class="form-label small">Satuan</label>
            <input type="text" name="satuan" id="formSatuan" class="form-control" required>
          </div>
          <div class="col-4">
            <label class="form-label small">Stok</label>
            <input type="number" step="0.01" name="stok" id="formStok" class="form-control" required>
          </div>
          <div class="col-4">
            <label class="form-label small">Stok Min</label>
            <input type="number" step="0.01" name="stok_minimum" id="formMin" class="form-control" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function resetForm() {
  document.getElementById('modalBahanTitle').innerText = 'Tambah Bahan';
  document.getElementById('formAction').value = 'add';
  document.getElementById('formId').value = '';
  document.getElementById('formNama').value = '';
  document.getElementById('formKategori').value = '';
  document.getElementById('formSatuan').value = '';
  document.getElementById('formStok').value = '';
  document.getElementById('formMin').value = '';
}
function editBahan(b) {
  document.getElementById('modalBahanTitle').innerText = 'Edit Bahan';
  document.getElementById('formAction').value = 'edit';
  document.getElementById('formId').value = b.id;
  document.getElementById('formNama').value = b.nama_bahan;
  document.getElementById('formKategori').value = b.kategori_layanan;
  document.getElementById('formSatuan').value = b.satuan;
  document.getElementById('formStok').value = b.stok;
  document.getElementById('formMin').value = b.stok_minimum;
  new bootstrap.Modal(document.getElementById('modalBahan')).show();
}
</script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
