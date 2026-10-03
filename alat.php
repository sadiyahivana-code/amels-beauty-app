<?php
require 'config.php';
require 'includes/auth.php';
requireLogin();
$pageTitle = 'Manajemen Alat';
$user = currentUser();

$rows = $pdo->query("SELECT * FROM alat ORDER BY jenis DESC, nama_alat")->fetchAll(PDO::FETCH_ASSOC);

require 'includes/header.php';

function kondisiBadge($k) {
    $map = ['baik' => 'bg-success', 'perlu servis' => 'bg-warning text-dark', 'rusak' => 'bg-danger'];
    return '<span class="badge ' . ($map[$k] ?? 'bg-secondary') . '">' . htmlspecialchars($k) . '</span>';
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-semibold mb-0">Manajemen Alat</h5>
  <?php if ($user['role'] === 'owner'): ?>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlat" onclick="resetForm()">
    <i class="bi bi-plus-lg"></i> Tambah Alat
  </button>
  <?php endif; ?>
</div>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white align-middle">
  <thead>
    <tr><th>Nama Alat</th><th>Jenis</th><th>Kondisi</th><th>Servis Terakhir</th><th>Servis Berikutnya</th><th></th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $a): $warn = $a['kondisi'] !== 'baik'; ?>
    <tr class="<?= $warn ? 'needs-service' : '' ?>">
      <td><?= htmlspecialchars($a['nama_alat']) ?></td>
      <td class="small text-muted text-capitalize"><?= htmlspecialchars($a['jenis']) ?></td>
      <td><?= kondisiBadge($a['kondisi']) ?></td>
      <td class="small"><?= htmlspecialchars($a['tanggal_servis_terakhir'] ?? '-') ?></td>
      <td class="small"><?= htmlspecialchars($a['tanggal_servis_berikutnya'] ?? '-') ?></td>
      <td>
        <?php if ($user['role'] === 'owner'): ?>
        <button class="btn btn-sm btn-outline-secondary" onclick='editAlat(<?= json_encode($a) ?>)'><i class="bi bi-pencil"></i></button>
        <form method="post" action="alat_action.php" class="d-inline" onsubmit="return confirm('Hapus alat ini?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $a['id'] ?>">
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
<div class="modal fade" id="modalAlat" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="alat_action.php" class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="modalAlatTitle">Tambah Alat</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="id" id="formId">
        <div class="mb-2">
          <label class="form-label small">Nama Alat</label>
          <input type="text" name="nama_alat" id="formNama" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Jenis</label>
          <select name="jenis" id="formJenis" class="form-select">
            <option value="kecil">Alat Kecil</option>
            <option value="elektronik">Alat Elektronik</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Kondisi</label>
          <select name="kondisi" id="formKondisi" class="form-select">
            <option value="baik">Baik</option>
            <option value="perlu servis">Perlu Servis</option>
            <option value="rusak">Rusak</option>
          </select>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label small">Servis Terakhir</label>
            <input type="date" name="tanggal_servis_terakhir" id="formTglTerakhir" class="form-control">
          </div>
          <div class="col-6">
            <label class="form-label small">Servis Berikutnya</label>
            <input type="date" name="tanggal_servis_berikutnya" id="formTglBerikutnya" class="form-control">
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
  document.getElementById('modalAlatTitle').innerText = 'Tambah Alat';
  document.getElementById('formAction').value = 'add';
  document.getElementById('formId').value = '';
  document.getElementById('formNama').value = '';
  document.getElementById('formJenis').value = 'kecil';
  document.getElementById('formKondisi').value = 'baik';
  document.getElementById('formTglTerakhir').value = '';
  document.getElementById('formTglBerikutnya').value = '';
}
function editAlat(a) {
  document.getElementById('modalAlatTitle').innerText = 'Edit Alat';
  document.getElementById('formAction').value = 'edit';
  document.getElementById('formId').value = a.id;
  document.getElementById('formNama').value = a.nama_alat;
  document.getElementById('formJenis').value = a.jenis;
  document.getElementById('formKondisi').value = a.kondisi;
  document.getElementById('formTglTerakhir').value = a.tanggal_servis_terakhir || '';
  document.getElementById('formTglBerikutnya').value = a.tanggal_servis_berikutnya || '';
  new bootstrap.Modal(document.getElementById('modalAlat')).show();
}
</script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
