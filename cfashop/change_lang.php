<?php
session_start();

// Si une langue est passée en paramètre, on la stocke en session
if (isset($_GET['lang'])) {
    $langues_autorisees = ['fr', 'en'];
    if (in_array($_GET['lang'], $langues_autorisees)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
}

// Redirection vers la page précédente, ou vers l'accueil par défaut
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: acceuilcfa.php");
}
exit();
?>