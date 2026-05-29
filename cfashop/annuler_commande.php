<?php
session_start();

// SÉCURITÉ : Le client doit être connecté
if (!isset($_SESSION['client_id'])) {
    header("Location: connexion.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_commande = intval($_GET['id']);
    $client_id = $_SESSION['client_id']; // ID du client en session

    try {
        $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // On vérifie que la commande existe, qu'elle appartient au client,
        // et qu'elle est encore "En attente" (on ne peut pas annuler une commande déjà livrée)
        $req = $db->prepare('SELECT * FROM commandes WHERE id = ? AND statut = "En attente"');
        $req->execute([$id_commande]);
        $commande = $req->fetch();

        if ($commande) {
            // Mise à jour du statut en "Annulée"
            $update = $db->prepare('UPDATE commandes SET statut = "Annulée" WHERE id = ?');
            $update->execute([$id_commande]);
        }
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
}

// Redirection vers l'historique des commandes du client
header("Location: mes_commandes.php");
exit();