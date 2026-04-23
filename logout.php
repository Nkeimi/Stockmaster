<?php
session_start(); // Initialise la session pour pouvoir la manipuler

// Supprime toutes les variables de session
$_SESSION = array();

// Détruit la session
session_destroy();

// Redirige l'utilisateur vers la page d'accueil (index)
header("Location: index.php");
exit();
?>