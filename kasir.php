<?php
require 'config.php';
require 'includes/auth.php';
require 'includes/midtrans_config.php';
requireLogin();
$pageTitle = 'Kasir';

$layananList = $pdo->query("SELECT * FROM layanan ORDER BY kategori, nama_layanan")->fetchAll(PDO::FETCH_ASSOC);
$byKategori = [];
foreach ($layananList as $l) {
    $byKategori[$l['kategori']][] = $l;
}
$midtransReady = midtransConfigured();

require 'includes/header.php';
?>

<?php if ($midtransReady): ?>
<script src="<?= midtransSnapJsUrl() ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
<?php endif; ?>

<div class="row g-3">
  <div class="col-md-7">
    <h6 class="fw-semibold mb-2">Pilih Layanan</h6>
    <div class="accordion" id="kategoriAccordion">
      <?php $first = true; foreach ($byKategori as $kategori => $items): $panelId = 'panel_' . md5($kategori); ?>
      <div class="accordion-item mb-2" style="border-radius:14px;overflow:hidden;border:1px solid var(--gray-soft)">
        <h2 class="accordion-header">
          <button class="accordion-button <?= $first ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $panelId ?>">
            <img src="assets/icons/categories/<?= kategoriIcon($kategori) ?>.png" alt="" style="width:32px;height:32px;margin-right:.6rem">
            <?= htmlspecialchars($kategori) ?>
          </button>
        </h2>
        <div id="<?= $panelId ?>" class="accordion-collapse collapse <?= $first ? 'show' : '' ?>" data-bs-parent="#kategoriAccordion">
          <div class="accordion-body">
            <div class="row g-2">
              <?php foreach ($items as $l): ?>
              <div class="col-6 col-lg-4">
                <button type="button" class="layanan-btn"
                  data-id="<?= $l['id'] ?>"
                  data-nama="<?= htmlspecialchars($l['nama_layanan']) ?>"
                  data-harga="<?= $l['harga'] ?>"
                  onclick="addToCart(this)">
                  <div class="fw-semibold small"><?= htmlspecialchars($l['nama_layanan']) ?></div>
                  <div class="small" style="color:var(--pink-deep)"><?= rupiah($l['harga']) ?></div>
                </button>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php $first = false; endforeach; ?>
    </div>
  </div>

  <div class="col-md-5">
    <div class="card p-3" style="position:sticky; top:80px;">
      <h6 class="fw-semibold mb-2"><i class="bi bi-cart3"></i> Keranjang</h6>
      <div id="cartEmpty" class="text-muted small">Belum ada layanan dipilih.</div>
      <div class="table-responsive">
      <table class="table table-sm align-middle" id="cartTable" style="display:none">
        <thead><tr><th>Layanan</th><th style="width:70px">Qty</th><th class="text-end">Subtotal</th><th></th></tr></thead>
        <tbody id="cartBody"></tbody>
      </table>
      </div>
      <div class="d-flex justify-content-between fw-semibold border-top pt-2 mb-3">
        <span>Total</span>
        <span id="cartTotal">Rp 0</span>
      </div>

      <form method="post" action="kasir_process.php" id="checkoutForm">
        <input type="hidden" name="cart_json" id="cartJson">

        <div class="mb-3">
          <label class="form-label small">Nama Pelanggan (opsional)</label>
          <input type="text" class="form-control" name="nama_pelanggan" placeholder="Nama pelanggan">
        </div>

        <div class="mb-3">
          <label class="form-label small">No. WhatsApp Pelanggan (opsional)</label>
          <input type="text" class="form-control" name="nomor_wa" placeholder="08xxxxxxxxxx">
          <div class="form-text">Diisi kalau mau kirim struk langsung ke WhatsApp pelanggan.</div>
        </div>

        <label class="form-label small fw-semibold">Metode Pembayaran</label>
        <div class="btn-group w-100 mb-3" role="group">
          <input type="radio" class="btn-check" name="metode_bayar" id="mTunai" value="tunai" checked onchange="togglePayment()">
          <label class="btn btn-outline-primary" for="mTunai">Tunai</label>
          <input type="radio" class="btn-check" name="metode_bayar" id="mQris" value="qris" onchange="togglePayment()">
          <label class="btn btn-outline-primary" for="mQris">QRIS</label>
          <input type="radio" class="btn-check" name="metode_bayar" id="mEdc" value="edc" onchange="togglePayment()">
          <label class="btn btn-outline-primary" for="mEdc">Debit/Kredit</label>
        </div>

        <div id="tunaiBox" class="mb-3">
          <label class="form-label small">Uang Diterima</label>
          <input type="number" class="form-control" name="uang_diterima" id="uangDiterima" oninput="hitungKembalian()">
          <div class="small text-muted mt-1">Kembalian: <span id="kembalianText">Rp 0</span></div>
        </div>

        <div id="qrisBox" class="mb-3 text-center" style="display:none">
          <?php if ($midtransReady): ?>
            <p class="small text-muted mb-2">Pembayaran otomatis lewat Midtrans (QRIS, kartu, atau transfer bank).</p>
            <button type="button" class="btn w-100" style="background:#0089D2;color:#fff" onclick="bayarMidtrans()" id="btnMidtrans">
              <i class="bi bi-credit-card"></i> Bayar Sekarang (Midtrans)
            </button>
            <div class="small text-success mt-2" id="midtransStatus" style="display:none">
              <i class="bi bi-check-circle-fill"></i> Pembayaran berhasil, tinggal klik "Proses Pembayaran".
            </div>
          <?php else: ?>
            <img src="assets/qris-dummy.png" alt="QRIS Amels Beauty" style="max-width:220px;width:100%;border-radius:12px;border:1px solid var(--gray-soft)">
            <div class="form-check mt-2 d-inline-block">
              <input class="form-check-input" type="checkbox" id="qrisConfirm">
              <label class="form-check-label small" for="qrisConfirm">Sudah cek, pembayaran QRIS masuk</label>
            </div>
          <?php endif; ?>
        </div>

        <div id="edcBox" class="mb-3" style="display:none">
          <div class="alert alert-secondary small mb-2">Gesek/tap kartu debit/kredit pelanggan di mesin EDC, lalu konfirmasi di bawah.</div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="edcConfirm">
            <label class="form-check-label small" for="edcConfirm">Transaksi kartu berhasil</label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100" id="btnBayar" disabled>Proses Pembayaran</button>
      </form>
    </div>
  </div>
</div>

<script>
const midtransReady = <?= $midtransReady ? 'true' : 'false' ?>;
let cart = [];
let midtransPaid = false;

function bayarMidtrans() {
  const total = cart.reduce((s,c) => s + c.harga * c.qty, 0);
  if (total <= 0) { alert('Keranjang masih kosong.'); return; }

  const btn = document.getElementById('btnMidtrans');
  btn.disabled = true;
  btn.innerText = 'Memproses...';

  fetch('midtrans_create.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({total: total})
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-credit-card"></i> Bayar Sekarang (Midtrans)';
    if (data.error) { alert('Gagal memulai pembayaran: ' + data.error); return; }

    window.snap.pay(data.token, {
      onSuccess: function() {
        midtransPaid = true;
        document.getElementById('midtransStatus').style.display = '';
        updateBtnBayar();
      },
      onPending: function() {
        alert('Pembayaran masih pending, selesaikan dulu sebelum lanjut.');
      },
      onError: function() {
        alert('Pembayaran gagal, coba lagi.');
      },
      onClose: function() {
        if (!midtransPaid) alert('Popup ditutup sebelum pembayaran selesai.');
      }
    });
  })
  .catch(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-credit-card"></i> Bayar Sekarang (Midtrans)';
    alert('Gagal menghubungi server.');
  });
}

function addToCart(btn) {
  const id = btn.dataset.id, nama = btn.dataset.nama, harga = parseInt(btn.dataset.harga);
  const existing = cart.find(c => c.id === id);
  if (existing) { existing.qty++; }
  else { cart.push({id, nama, harga, qty: 1}); }
  renderCart();
}

function changeQty(id, delta) {
  const item = cart.find(c => c.id === id);
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) cart = cart.filter(c => c.id !== id);
  renderCart();
}

function renderCart() {
  const body = document.getElementById('cartBody');
  const table = document.getElementById('cartTable');
  const empty = document.getElementById('cartEmpty');
  body.innerHTML = '';
  let total = 0;
  cart.forEach(item => {
    const subtotal = item.harga * item.qty;
    total += subtotal;
    body.innerHTML += `<tr>
      <td>${item.nama}</td>
      <td>
        <div class="d-flex align-items-center gap-1">
          <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changeQty('${item.id}',-1)">-</button>
          <span>${item.qty}</span>
          <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changeQty('${item.id}',1)">+</button>
        </div>
      </td>
      <td class="text-end">Rp ${subtotal.toLocaleString('id-ID')}</td>
      <td><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="changeQty('${item.id}',-999)"><i class="bi bi-trash"></i></button></td>
    </tr>`;
  });
  document.getElementById('cartTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
  table.style.display = cart.length ? '' : 'none';
  empty.style.display = cart.length ? 'none' : '';
  document.getElementById('cartJson').value = JSON.stringify(cart);
  updateBtnBayar();
  hitungKembalian();
}

function updateBtnBayar() {
  const metode = document.querySelector('input[name=metode_bayar]:checked').value;
  const butuhMidtrans = metode === 'qris' && midtransReady;
  document.getElementById('btnBayar').disabled = cart.length === 0 || (butuhMidtrans && !midtransPaid);
}

function togglePayment() {
  const metode = document.querySelector('input[name=metode_bayar]:checked').value;
  document.getElementById('tunaiBox').style.display = metode === 'tunai' ? '' : 'none';
  document.getElementById('qrisBox').style.display = metode === 'qris' ? '' : 'none';
  document.getElementById('edcBox').style.display = metode === 'edc' ? '' : 'none';
  updateBtnBayar();
}

function hitungKembalian() {
  const total = cart.reduce((s,c) => s + c.harga * c.qty, 0);
  const diterima = parseInt(document.getElementById('uangDiterima').value) || 0;
  const kembali = diterima - total;
  document.getElementById('kembalianText').innerText = 'Rp ' + Math.max(kembali,0).toLocaleString('id-ID');
}

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
  const metode = document.querySelector('input[name=metode_bayar]:checked').value;
  if (metode === 'tunai') {
    const total = cart.reduce((s,c) => s + c.harga * c.qty, 0);
    const diterima = parseInt(document.getElementById('uangDiterima').value) || 0;
    if (diterima < total) {
      e.preventDefault();
      alert('Uang diterima kurang dari total belanja.');
    }
  }
});
</script>

<?php require 'includes/footer.php'; ?>
