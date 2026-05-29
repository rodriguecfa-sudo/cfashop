<?php
session_start();

// Sécurité : On vérifie que le client est bien connecté
if (!isset($_SESSION['client_id'])) {
    header("Location: connexion.php");
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. On récupère l'email du client connecté depuis la session
    // (Assure-toi d'avoir bien fait $_SESSION['client_email'] = $row['email']; lors du login)
    $telephone_du_client = $_SESSION['client_telephone'] ?? ''; 

    // 2. On va chercher dans la table commandes les lignes où l'email correspond
    // NOTE : Si ta table utilise plutôt l'ID du client, remplace "email_client = ?" par "id_client = ?" et passe $_SESSION['client_id']
    $req = $db->prepare('SELECT * FROM commandes WHERE telephone = ? ORDER BY id DESC'); 
    $req->execute([$telephone_du_client]);
    $mes_commandes = $req->fetchAll();

} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CFA SHOP | Mes Commandes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen p-6">

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-black uppercase tracking-tight">Suivi de mes commandes</h1>
            <a href="acceuilcfa.php" class="text-xs font-bold uppercase text-gray-400 hover:text-white"><i class="fa-solid fa-arrow-left mr-1"></i> Boutique</a>
        </div>

        <div class="space-y-4">
            <?php if (empty($mes_commandes)): ?>
                <p class="text-gray-500 italic text-center">Vous n'avez pas encore passé de commande.</p>
            <?php else: ?>
                <?php foreach ($mes_commandes as $com): ?>
                    <div class="bg-gray-950 border border-gray-800 p-5 rounded-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        
                        <div>
                            <span class="text-xs text-gray-500">Commande #<?= $com['id'] ?> — <?= date('d/m/Y à H:i', strtotime($com['date_commande'])) ?></span>
                            <h3 class="text-sm font-bold text-gray-300 mt-1">Articles : <span class="font-normal text-gray-400"><?= htmlspecialchars($com['articles']) ?></span></h3>
                            <p class="text-pink-500 font-black mt-1"><?= number_format($com['total'], 0, ',', ' ') ?> FCFA</p>
                        </div>

                        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
                            <?php if ($com['statut'] === 'En attente'): ?>
                                <span class="bg-yellow-950/50 border border-yellow-800 text-yellow-400 px-3 py-1 rounded-full text-xs font-bold">⏳ En attente</span>
                                
                                <a href="annuler_commande.php?id=<?= $com['id'] ?>" 
                                   onclick="return confirm('Voulez-vous vraiment annuler cette commande ?');"
                                   class="bg-red-950/60 border border-red-800 text-red-400 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition">
                                    <i class="fa-solid fa-xmark mr-1"></i> Annuler
                                </a>

                            <?php elseif ($com['statut'] === 'Annulée'): ?>
                                <span class="bg-gray-900 border border-gray-800 text-gray-500 px-3 py-1 rounded-full text-xs font-bold line-through">❌ Annulée</span>
                            <?php else: ?>
                                <span class="bg-green-950/50 border border-green-800 text-green-400 px-3 py-1 rounded-full text-xs font-bold">✅ Livrée</span>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>