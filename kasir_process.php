<?php
require 'config.php';
require 'includes/auth.php';
requireLogin();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kasir.php');
    exit;
}

$cart = json_decode($_POST['cart_json'] ?? '[]', true);
$metode = $_POST['metode_bayar'] ?? 'tunai';
$uangDiterima = isset($_POST['uang_diterima']) && $_POST['uang_diterima'] !== '' ? (int)$_POST['uang_diterima'] : null;
$nomorWa = trim($_POST['nomor_wa'] ?? '');
$namaPelanggan = trim($_POST['nama_pelanggan'] ?? '');

if (empty($cart)) {
    header('Location: kasir.php?error=keranjang_kosong');
    exit;
}
if (!in_array($metode, ['tunai','qris','edc'])) {
    header('Location: kasir.php?error=metode_invalid');
    exit;
}

$total = 0;
foreach ($cart as $item) $total += (int)$item['harga'] * (int)$item['qty'];

if ($metode === 'tunai' && ($uangDiterima === null || $uangDiterima < $total)) {
    header('Location: kasir.php?error=uang_kurang');
    exit;
}

// Hitung kebutuhan bahan total dari semua layanan di keranjang, lalu cek stok cukup
$kebutuhanBahan = []; // bahan_id => jumlah dibutuhkan
$stmtLB = $pdo->prepare("SELECT bahan_id, jumlah_terpakai FROM layanan_bahan WHERE layanan_id = ?");
foreach ($cart as $item) {
    $stmtLB->execute([(int)$item['id']]);
    foreach ($stmtLB->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $need = $row['jumlah_terpakai'] * (int)$item['qty'];
        $kebutuhanBahan[$row['bahan_id']] = ($kebutuhanBahan[$row['bahan_id']] ?? 0) + $need;
    }
}

$kurang = [];
if (!empty($kebutuhanBahan)) {
    $stmtCekStok = $pdo->prepare("SELECT nama_bahan, stok, satuan FROM bahan WHERE id = ?");
    foreach ($kebutuhanBahan as $bahanId => $need) {
        $stmtCekStok->execute([$bahanId]);
        $b = $stmtCekStok->fetch(PDO::FETCH_ASSOC);
        if ($b && $b['stok'] < $need) {
            $kurang[] = $b['nama_bahan'] . ' (tersisa ' . $b['stok'] . ' ' . $b['satuan'] . ', butuh ' . $need . ')';
        }
    }
}
if (!empty($kurang)) {
    $_SESSION['kasir_error'] = 'Stok bahan tidak cukup: ' . implode(', ', $kurang);
    header('Location: kasir.php?error=stok_kurang');
    exit;
}

try {
    $pdo->beginTransaction();

    $kembalian = $metode === 'tunai' ? $uangDiterima - $total : null;

    $stmtTrx = $pdo->prepare("INSERT INTO transaksi (tanggal, kasir_id, total, metode_bayar, status_pembayaran, uang_diterima, kembalian, nomor_wa, nama_pelanggan)
        VALUES (?,?,?,?,?,?,?,?,?)");
    $stmtTrx->execute([date('Y-m-d'), $user['id'], $total, $metode, 'lunas', $uangDiterima, $kembalian, $nomorWa ?: null, $namaPelanggan ?: null]);
    $trxId = $pdo->lastInsertId();

    $stmtDetail = $pdo->prepare("INSERT INTO transaksi_detail (transaksi_id, layanan_id, nama_layanan, harga_satuan, jumlah, subtotal)
        VALUES (?,?,?,?,?,?)");
    foreach ($cart as $item) {
        $subtotal = (int)$item['harga'] * (int)$item['qty'];
        $stmtDetail->execute([$trxId, (int)$item['id'], $item['nama'], (int)$item['harga'], (int)$item['qty'], $subtotal]);
    }

    // Kurangi stok bahan
    $stmtUpdateStok = $pdo->prepare("UPDATE bahan SET stok = stok - ? WHERE id = ?");
    foreach ($kebutuhanBahan as $bahanId => $need) {
        $stmtUpdateStok->execute([$need, $bahanId]);
    }

    $pdo->commit();
    header('Location: struk.php?id=' . $trxId);
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['kasir_error'] = 'Gagal menyimpan transaksi: ' . $e->getMessage();
    header('Location: kasir.php?error=gagal_simpan');
    exit;
}
