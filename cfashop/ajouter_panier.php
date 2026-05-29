<?php
session_start();

// SÉCURITÉ : Uniquement pour les membres connectés
if (!isset($_SESSION['client_id'])) {
    header("Location: connexion.php");
    exit();
}

// Connexion BDD pour vérifier que l'article existe
try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

if (isset($_GET['id'])) {
    $id_article = intval($_GET['id']);

    // Vérifier si l'article existe bien en BDD
    $req = $db->prepare('SELECT * FROM articles WHERE id = ?');
    $req->execute([$id_article]);
    $article = $req->fetch();

    if ($article) {
        // Initialiser le panier s'il n'existe pas encore
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }

        // Si l'article est déjà dans le panier, on augmente la quantité
        if (isset($_SESSION['panier'][$id_article])) {
            $_SESSION['panier'][$id_article]['quantite']++;
        } else {
            // Sinon, on l'ajoute avec une quantité de 1
            $_SESSION['panier'][$id_article] = [
                'nom' => $article['nom'],
                'prix' => $article['prix'],
                'image' => $article['image'],
                'quantite' => 1
            ];
        }
    }
}

// Redirection vers la page précédente (ou l'index si non trouvée)
$redirection = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: " . $redirection);
exit();