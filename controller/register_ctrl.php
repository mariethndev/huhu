<?php
session_start();
require_once "../model/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/register_form.php");
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header("Location: ../views/register_form.php?status=danger");
    exit;
}

$name     = trim($_POST['nom'] ?? '');
$email    = strtolower(trim($_POST['mail'] ?? '')); 
$password = $_POST['psw'] ?? '';
$profil   = $_POST['profil'] ?? '';

if (!$name || !$email || !$password || !$profil) {
    header("Location: ../views/register_form.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../views/register_form.php");
    exit;
}

if ($profil !== "acheteur" && $profil !== "vendeur") {
    header("Location: ../views/register_form.php");
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT id_user
        FROM users
        WHERE user_email = ?
    ");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        header("Location: ../views/register_form.php");
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users
        (user_name, user_email, user_password, user_role)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $name,
        $email,
        $hash,
        $profil
    ]);

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$pdo->lastInsertId();
    $_SESSION['role']    = $profil;

    header("Location: ../views/homepage.php");
    exit;

} catch (PDOException $e) {

    echo $e->getMessage();
    exit;
}