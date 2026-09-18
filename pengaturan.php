<?php
require 'config.php';
require 'includes/auth.php';
requireRole('owner');
$pageTitle = 'Pengaturan';

$pesan = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qris_image'])) {
    $file = $_FILES['qris_image'];
    $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'Gagal mengunggah gambar. Coba lagi.';
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $errorMsg = 'Format gambar harus PNG, JPG, atau WEBP.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errorMsg = 'Ukuran gambar maksimal 5MB.';
    } else {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $target = __DIR__ . '/assets/qris-dummy.png';

        // Konversi ke PNG kalau bukan PNG, biar konsisten nama filenya
        if (strtolower($ext) === 'png') {
            move_uploaded_file($file['tmp_name'], $target);
        } elseif (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring(file_get_contents($file['tmp_name']));
            if ($img) {
                imagepng($img, $target);
                imagedestroy($img);
            } else {
                move_uploaded_file($file['tmp_name'], $target);
            }
        } else {
            move_uploaded_file($file['tmp_name'], $target);
        }
        $pesan = 'QR pembayaran berhasil diganti.';
    }
}

require 'includes/header.php';
?>

<h5 class="fw-semibold mb-3">Pengaturan</h5>

<?php if ($pesan): ?>
  <div class="alert alert-success py-2"><?= htmlspecialchars($pesan) ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
  <div class="alert alert-danger py-2"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<div class="card p-4" style="max-width:480px">
  <h6 class="fw-semibold mb-1">QR Pembayaran (QRIS)</h6>
  <p class="small text-muted mb-3">Ganti gambar QR di bawah dengan QRIS asli Amels Beauty. Setelah diganti, otomatis muncul di halaman Kasir tiap kali pelanggan bayar pakai QRIS.</p>

  <div class="text-center mb-3">
    <img src="assets/qris-dummy.png?t=<?= time() ?>" alt="QR saat ini" style="max-width:220px;width:100%;border-radius:12px;border:1px solid var(--gray-soft)">
  </div>

  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label small">Upload Gambar QR Baru</label>
      <input type="file" name="qris_image" accept="image/png,image/jpeg,image/webp" class="form-control" required>
      <div class="form-text">Format PNG/JPG/WEBP, maksimal 5MB. Bisa screenshot langsung dari aplikasi QRIS bank/e-wallet Amels Beauty.</div>
    </div>
    <button type="submit" class="btn btn-primary w-100">Simpan QR Baru</button>
  </form>
</div>

<?php require 'includes/footer.php'; ?>
