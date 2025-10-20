<?php
require_once __DIR__ . '/../includes/session.php';
unset($_SESSION['admin']);
header('Location: login.php');
exit;
