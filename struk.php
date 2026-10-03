<?php
require 'config.php';
require 'includes/auth.php';
requireLogin();
$pageTitle = 'Struk';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT t.*, u.nama as nama_kasir FROM transaksi t JOIN users u ON u.id = t.kasir_id WHERE t.id = ?");
$stmt->execute([$id]);
$trx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$trx) { die('Transaksi tidak ditemukan.'); }

$stmtD = $pdo->prepare("SELECT * FROM transaksi_detail WHERE transaksi_id = ?");
$stmtD->execute([$id]);
$items = $stmtD->fetchAll(PDO::FETCH_ASSOC);

// Susun teks pesan struk untuk dikirim via WhatsApp
$sapaan = !empty($trx['nama_pelanggan']) ? 'Halo kak ' . $trx['nama_pelanggan'] . ',' : 'Halo kak,';
$pesan = $sapaan . "\n";
$pesan .= "Terima kasih sudah menggunakan layanan Amels Beauty! Berikut invoice transaksinya ya:\n\n";
$pesan .= "No. Transaksi: #" . $trx['id'] . "\n";
$pesan .= "Tanggal: " . $trx['tanggal'] . "\n\n";
foreach ($items as $it) {
    $pesan .= "- " . $it['nama_layanan'] . " x" . $it['jumlah'] . " = " . rupiah($it['subtotal']) . "\n";
}
$pesan .= "\nTotal: " . rupiah($trx['total']) . "\n";
$pesan .= "Metode Bayar: " . strtoupper($trx['metode_bayar']) . "\n\n";
$pesan .= "Sampai jumpa lagi di Amels Beauty ya, kak!";

$nomorTujuan = formatNomorWA($trx['nomor_wa'] ?? '');
if ($nomorTujuan !== '') {
    $waLink = "https://wa.me/" . $nomorTujuan . "?text=" . urlencode($pesan);
} else {
    // Tidak ada nomor pelanggan — buka WhatsApp, kasir pilih kontak manual
    $waLink = "https://api.whatsapp.com/send?text=" . urlencode($pesan);
}

require 'includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-4">
    <div class="card p-4" id="strukCard">
      <div class="text-center mb-3">
        <img src="assets/logo.png" alt="Amels Beauty" style="width:90px;">
        <div class="small text-muted mt-1">Struk Transaksi #<?= $trx['id'] ?></div>
        <div class="small text-muted"><?= htmlspecialchars($trx['tanggal']) ?> · Kasir: <?= htmlspecialchars($trx['nama_kasir']) ?></div>
        <?php if (!empty($trx['nama_pelanggan'])): ?>
        <div class="small text-muted">Pelanggan: <?= htmlspecialchars($trx['nama_pelanggan']) ?></div>
        <?php endif; ?>
      </div>
      <hr>
      <table class="table table-sm mb-2">
        <?php foreach ($items as $it): ?>
        <tr>
          <td>
            <?= htmlspecialchars($it['nama_layanan']) ?><br>
            <span class="small text-muted"><?= $it['jumlah'] ?> x <?= rupiah($it['harga_satuan']) ?></span>
          </td>
          <td class="text-end"><?= rupiah($it['subtotal']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <hr>
      <div class="d-flex justify-content-between fw-semibold">
        <span>Total</span><span><?= rupiah($trx['total']) ?></span>
      </div>
      <div class="d-flex justify-content-between small text-muted">
        <span>Metode Bayar</span><span class="text-uppercase"><?= htmlspecialchars($trx['metode_bayar']) ?></span>
      </div>
      <?php if ($trx['metode_bayar'] === 'tunai'): ?>
      <div class="d-flex justify-content-between small text-muted">
        <span>Uang Diterima</span><span><?= rupiah($trx['uang_diterima']) ?></span>
      </div>
      <div class="d-flex justify-content-between small text-muted">
        <span>Kembalian</span><span><?= rupiah($trx['kembalian']) ?></span>
      </div>
      <?php endif; ?>
      <hr>
      <p class="text-center small text-muted mb-0">Terima kasih sudah berkunjung ke Amels Beauty</p>

      <button type="button" class="btn btn-outline-secondary w-100 mt-2 no-print" onclick="downloadStruk()">
        <i class="bi bi-download"></i> Download Struk sebagai Gambar
      </button>
      <a href="<?= htmlspecialchars($waLink) ?>" target="_blank" class="btn w-100 mt-2 no-print" style="background:#25D366;color:#fff;">
        <i class="bi bi-whatsapp"></i> Kirim ke WhatsApp
      </a>
      <div class="small text-muted mt-2 no-print">
        Urutannya: download struk dulu, klik "Kirim ke WhatsApp" (pesan sapaan otomatis terisi), lalu lampirkan foto struk yang barusan didownload ke chat-nya.
      </div>

      <div class="d-flex gap-2 mt-2 no-print">
        <button onclick="window.print()" class="btn btn-outline-primary w-100"><i class="bi bi-printer"></i> Cetak</button>
        <a href="kasir.php" class="btn btn-primary w-100">Transaksi Baru</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function downloadStruk() {
  const card = document.getElementById('strukCard');
  const hiddenEls = card.querySelectorAll('.no-print');
  hiddenEls.forEach(el => el.style.display = 'none');

  html2canvas(card, {backgroundColor: '#ffffff', scale: 2}).then(canvas => {
    hiddenEls.forEach(el => el.style.display = '');
    const link = document.createElement('a');
    link.download = 'struk-amels-beauty-#<?= $trx['id'] ?>.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
  }).catch(() => {
    hiddenEls.forEach(el => el.style.display = '');
    alert('Gagal membuat gambar struk. Pastikan koneksi internet aktif.');
  });
}
</script>

<?php require 'includes/footer.php'; ?>
