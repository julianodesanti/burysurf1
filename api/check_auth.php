<?php

session_start();

if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    $loginUrl = '../login.php';
    header('Location: ' . $loginUrl, true, 302);
    exit;
}