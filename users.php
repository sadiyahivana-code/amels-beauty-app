<?php
require 'config.php';
require 'includes/auth.php';
requireRole('owner');
$pageTitle = 'Manajemen User';
$me = currentUser();

$rows = $pdo->query("SELECT * FROM users ORDER BY role, nama")->fetchAll(PDO::FETCH_ASSOC);

require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-semibold mb-0">Manajemen User</h5>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUser" onclick="resetUserForm()">
    <i class="bi bi-plus-lg"></i> Tambah User
  </button>
</div>

<div class="table-responsive">
<table class="table table-sm table-hover bg-white align-middle">
  <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $u): ?>
    <tr>
      <td><?= htmlspecialchars($u['nama']) ?></td>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><span class="badge <?= $u['role']==='owner' ? 'bg-dark' : 'bg-secondary' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
      <td>
        <?php if ($u['id'] != $me['id']): ?>
        <button class="btn btn-sm btn-outline-secondary" onclick='editUser(<?= json_encode($u) ?>)'><i class="bi bi-pencil"></i></button>
        <form method="post" action="users_action.php" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $u['id'] ?>">
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
        <?php else: ?>
          <button class="btn btn-sm btn-outline-secondary" onclick='editUser(<?= json_encode($u) ?>)'><i class="bi bi-pencil"></i></button>
          <span class="small text-muted">(kamu)</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="modal fade" id="modalUser" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="users_action.php" class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="modalUserTitle">Tambah User</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="id" id="formId">
        <div class="mb-2">
          <label class="form-label small">Nama</label>
          <input type="text" name="nama" id="formNama" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Username</label>
          <input type="text" name="username" id="formUsername" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Password <span id="passwordHint" class="text-muted fw-normal"></span></label>
          <input type="password" name="password" id="formPassword" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label small">Role</label>
          <select name="role" id="formRole" class="form-select">
            <option value="kasir">Kasir</option>
            <option value="owner">Owner</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function resetUserForm() {
  document.getElementById('modalUserTitle').innerText = 'Tambah User';
  document.getElementById('formAction').value = 'add';
  document.getElementById('formId').value = '';
  document.getElementById('formNama').value = '';
  document.getElementById('formUsername').value = '';
  document.getElementById('formPassword').value = '';
  document.getElementById('formPassword').required = true;
  document.getElementById('passwordHint').innerText = '';
  document.getElementById('formRole').value = 'kasir';
}
function editUser(u) {
  document.getElementById('modalUserTitle').innerText = 'Edit User';
  document.getElementById('formAction').value = 'edit';
  document.getElementById('formId').value = u.id;
  document.getElementById('formNama').value = u.nama;
  document.getElementById('formUsername').value = u.username;
  document.getElementById('formPassword').value = '';
  document.getElementById('formPassword').required = false;
  document.getElementById('passwordHint').innerText = '(kosongkan kalau tidak ingin ganti password)';
  document.getElementById('formRole').value = u.role;
  new bootstrap.Modal(document.getElementById('modalUser')).show();
}
</script>

<?php require 'includes/footer.php'; ?>
