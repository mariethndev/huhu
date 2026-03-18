<?php
session_start();

// on vide toutes les variables stockées dans la session

$_SESSION = [];

// cela supprime complètement la session utilisateur

session_destroy();

// après la déconnexion on renvoie l'utilisateur
// vers la page de connexion

header("Location: ../views/login_form.php");
exit;