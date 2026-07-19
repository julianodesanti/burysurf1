<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$senha   = trim($_POST['senha'] ?? '');

if ($usuario === '' || $senha === '') {
    header('Location: ../login.php?erro=1');
    exit;
}

$stmt = $conn->prepare("SELECT id, usuario, senha FROM login WHERE usuario = ? AND ativo = 1 LIMIT 1");
if ($stmt === false) {
    header('Location: ../login.php?erro=1');
    exit;
}

$stmt->bind_param("s", $usuario);
if (!$stmt->execute()) {
    $stmt->close();
    header('Location: ../login.php?erro=1');
    exit;
}

$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($user && is_string($user['senha']) && password_verify($senha, $user['senha'])) {
        session_regenerate_id(true);

        $_SESSION['admin_logado'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_user'] = $user['usuario'];

        header("Location: ../admin.php");
        exit;
    }
}

header("Location: ../login.php?erro=1");
exit;