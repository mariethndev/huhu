<?php
session_start();
require_once "../model/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/create_organisateur.php");
    exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

if (
    empty($name) ||
    empty($email) ||
    empty($password) ||
    empty($confirm)
) {
    exit("Champs manquants");
}

if ($password !== $confirm) {
    exit("Les mots de passe ne correspondent pas");
}

try {

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, 'organizer')
    ");

    $stmt->execute([
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT)
    ]);

header("Location: ../views/homepage.php");
exit;

} catch (PDOException $e) {

    echo "Erreur SQL : " . $e->getMessage();
}