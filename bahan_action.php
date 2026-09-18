<?php
require 'config.php';
require 'includes/auth.php';
requireRole('owner');

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO bahan (nama_bahan, kategori_layanan, satuan, stok, stok_minimum) VALUES (?,?,?,?,?)");
    $stmt->execute([
        trim($_POST['nama_bahan']),
        trim($_POST['kategori_layanan']),
        trim($_POST['satuan']),
        (float)$_POST['stok'],
        (float)$_POST['stok_minimum'],
    ]);
} elseif ($action === 'edit') {
    $stmt = $pdo->prepare("UPDATE bahan SET nama_bahan=?, kategori_layanan=?, satuan=?, stok=?, stok_minimum=? WHERE id=?");
    $stmt->execute([
        trim($_POST['nama_bahan']),
        trim($_POST['kategori_layanan']),
        trim($_POST['satuan']),
        (float)$_POST['stok'],
        (float)$_POST['stok_minimum'],
        (int)$_POST['id'],
    ]);
} elseif ($action === 'delete') {
    $pdo->prepare("DELETE FROM bahan WHERE id = ?")->execute([(int)$_POST['id']]);
}

header('Location: bahan.php');
exit;
