<?php
require 'config.php';
require 'includes/auth.php';
requireRole('owner');

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO pengeluaran (tanggal, keterangan, kategori, jumlah) VALUES (?,?,?,?)");
    $stmt->execute([
        $_POST['tanggal'],
        trim($_POST['keterangan']),
        $_POST['kategori'],
        (int)$_POST['jumlah'],
    ]);
} elseif ($action === 'delete') {
    $pdo->prepare("DELETE FROM pengeluaran WHERE id = ?")->execute([(int)$_POST['id']]);
}

header('Location: pengeluaran.php');
exit;
