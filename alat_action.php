<?php
require 'config.php';
require 'includes/auth.php';
requireRole('owner');

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO alat (nama_alat, jenis, kondisi, tanggal_servis_terakhir, tanggal_servis_berikutnya) VALUES (?,?,?,?,?)");
    $stmt->execute([
        trim($_POST['nama_alat']),
        $_POST['jenis'],
        $_POST['kondisi'],
        $_POST['tanggal_servis_terakhir'] ?: null,
        $_POST['tanggal_servis_berikutnya'] ?: null,
    ]);
} elseif ($action === 'edit') {
    $stmt = $pdo->prepare("UPDATE alat SET nama_alat=?, jenis=?, kondisi=?, tanggal_servis_terakhir=?, tanggal_servis_berikutnya=? WHERE id=?");
    $stmt->execute([
        trim($_POST['nama_alat']),
        $_POST['jenis'],
        $_POST['kondisi'],
        $_POST['tanggal_servis_terakhir'] ?: null,
        $_POST['tanggal_servis_berikutnya'] ?: null,
        (int)$_POST['id'],
    ]);
} elseif ($action === 'delete') {
    $pdo->prepare("DELETE FROM alat WHERE id = ?")->execute([(int)$_POST['id']]);
}

header('Location: alat.php');
exit;
