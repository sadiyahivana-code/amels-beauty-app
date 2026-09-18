<?php
// includes/auth.php — helper login & proteksi halaman

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (currentUser()['role'] !== $role) {
        header('Location: dashboard.php?error=akses_ditolak');
        exit;
    }
}
