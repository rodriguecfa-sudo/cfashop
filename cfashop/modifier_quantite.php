<?php
session_start();

// SÉCURITÉ
if (!isset($_SESSION['client_id'])) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if (isset($_SESSION['panier'][$id])) {
        if ($action === 'plus') {
            $_SESSION['panier'][$id]['quantite']++;
        } elseif ($action === 'moins') {
            $_SESSION['panier'][$id]['quantite']--;
            
            // Si la quantité tombe à 0, on supprime carrément l'article du panier
            if ($_SESSION['panier'][$id]['quantite'] <= 0) {
                unset($_SESSION['panier'][$id]);
            }
        }
    }
}

// Redirection instantanée vers le panier rafraîchi
header("Location: panier.php");
exit();