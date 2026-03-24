<?php
session_start();

require_once "/../model/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/create_organisateur.php");
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header("Location: ../views/create_organisateur.php?error=csrf");
    exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

if (
    empty($name) ||
    empty($email) ||
    empty($password) ||
    empty($confirm)
) {
    header("Location: ../views/create_organisateur.php?error=champs");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../views/create_organisateur.php?error=email");
    exit;
}

if ($password !== $confirm) {
    header("Location: ../views/create_organisateur.php?error=password");
    exit;
}

try {

    $check = $pdo->prepare("
        SELECT id_user 
        FROM users 
        WHERE user_email = ?
    ");
    $check->execute([$email]);

    if ($check->fetch()) {
        header("Location: ../views/create_organisateur.php?error=exists");
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users
        (user_name, user_email, user_password, user_role, user_is_active)
        VALUES (?, ?, ?, 'organisateur', 1)
    ");

    $stmt->execute([
        $name,
        $email,
        $hashedPassword
    ]);

    header("Location: ../views/login_form.php?status=success");
    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());
    header("Location: ../views/create_organisateur.php?error=sql");
    exit;
}