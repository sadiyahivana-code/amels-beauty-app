<?php
require 'config.php';
require 'includes/auth.php';
requireRole('owner');
$me = currentUser();

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)");
    try {
        $stmt->execute([
            trim($_POST['nama']),
            trim($_POST['username']),
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['role'],
        ]);
    } catch (PDOException $e) {
        $_SESSION['users_error'] = 'Username sudah dipakai.';
    }
} elseif ($action === 'edit') {
    $id = (int)$_POST['id'];
    $password = $_POST['password'] ?? '';
    try {
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id=?");
            $stmt->execute([
                trim($_POST['nama']),
                trim($_POST['username']),
                password_hash($password, PASSWORD_DEFAULT),
                $_POST['role'],
                $id,
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id=?");
            $stmt->execute([
                trim($_POST['nama']),
                trim($_POST['username']),
                $_POST['role'],
                $id,
            ]);
        }
        // Kalau yang diedit adalah diri sendiri, sinkronkan session biar nama di navbar ikut update
        if ($id === (int)$me['id']) {
            $_SESSION['user']['nama'] = trim($_POST['nama']);
            $_SESSION['user']['username'] = trim($_POST['username']);
            $_SESSION['user']['role'] = $_POST['role'];
        }
    } catch (PDOException $e) {
        $_SESSION['users_error'] = 'Username sudah dipakai.';
    }
} elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    if ($id !== (int)$me['id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
}

header('Location: users.php');
exit;
