<?php
session_start();
session_unset(); // Vide les variables de session
session_destroy(); // Détruit la session

// Redirection vers l'accueil
header("Location: acceuilcfa.php");
exit();