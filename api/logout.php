<?php

session_start();
session_destroy();

$loginUrl = '../login.php';
header('Location: ' . $loginUrl, true, 302);
exit;