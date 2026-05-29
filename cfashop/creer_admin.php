<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // On vide d'abord la table pour éviter les doublons
    $db->exec('TRUNCATE TABLE admin_users');
    
    // C'est PHP qui hache proprement le mot de passe ici
    $identifiant = 'admin';
    $password_clair = 'CfaShop2026';
    $password_hache = password_hash($password_clair, PASSWORD_BCRYPT);
    
    $req = $db->prepare('INSERT INTO admin_users (identifiant, mot_depasse) VALUES (?, ?)');
    $req->execute([$identifiant, $password_hache]);
    
    echo "Le compte admin a été créé avec succès par PHP !";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>