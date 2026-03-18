<?php
session_start();
require_once "../model/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/login_form.php");
    exit;
}

$email    = trim($_POST['mail'] ?? '');
$password = $_POST['psw'] ?? '';

if (!$email || !$password) {
    header("Location: ../views/login_form.php");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        header("Location: ../views/login_form.php");
        exit;
    }

    if ($user['is_active'] != 1) {
        header("Location: ../views/login_form.php");
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];

    if ($user['role'] === 'organizer') {
        header("Location: ../views/organisateur_dashboard.php");
    } else {
        header("Location: ../views/homepage.php");
    }
    exit;

} catch (PDOException $e) {
    echo $e->getMessage(); // temporaire debug
    exit;
}