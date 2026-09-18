<?php
require 'config.php';
require 'includes/auth.php';
header('Location: ' . (currentUser() ? 'dashboard.php' : 'login.php'));
exit;
