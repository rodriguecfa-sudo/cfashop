<?php
session_start();

if (!isset($_SESSION['client_id']) || empty($_SESSION['panier'])) {
    header("Location: acceuilcfa.php");
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// --- RÉCUPÉRATION DES INFOS DU CLIENT EN BDD ---
$id_client = $_SESSION['client_id'];
$queryClient = $db->prepare('SELECT nom_complet, telephone, ville FROM clients WHERE id = ?'); // Adapte 'utilisateurs' et 'nom' selon ta table
$queryClient->execute([$id_client]);
$infosClient = $queryClient->fetch(PDO::FETCH_ASSOC);

// Variables par défaut si l'utilisateur n'a pas encore complété son profil en BDD
$nom_auto = isset($infosClient['nom']) ? $infosClient['nom'] : '';
$tel_auto = isset($infosClient['telephone']) ? $infosClient['telephone'] : '';
$ville_auto = isset($infosClient['ville']) ? $infosClient['ville'] : '';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $telephone = htmlspecialchars($_POST['telephone']);
    $ville = htmlspecialchars($_POST['ville']);
    
    // Calcul du total et préparation du texte des articles
    $total = 0;
    $liste_articles = [];
    foreach ($_SESSION['panier'] as $item) {
        $total += ($item['prix'] * $item['quantite']);
        $liste_articles[] = $item['nom'] . " (x" . $item['quantite'] . ")";
    }
    
    // Transformation du tableau d'articles en une seule chaîne de caractères séparée par des virgules
    $articles_texte = implode(", ", $liste_articles);
    
    // Insertion dans la base de données
    $req = $db->prepare('INSERT INTO commandes (nom_client, telephone, ville, articles, total) VALUES (?, ?, ?, ?, ?)');
    $req->execute([$nom, $telephone, $ville, $articles_texte, $total]);
    
    // Vider le panier après commande réussie
    unset($_SESSION['panier']);
    
    $message = "Votre commande a été enregistrée avec succès ! Notre équipe va vous contacter.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CFA SHOP | Finaliser la Commande</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-gray-950 border border-gray-800 p-6 rounded-xl shadow-xl">
        <h2 class="text-2xl font-bold text-center mb-6 text-pink-500">Finaliser votre achat</h2>
        
        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 rounded-lg bg-green-950 text-green-400 border border-green-800 text-center font-semibold">
                <?= $message; ?>
                <a href="acceuilcfa.php" class="block mt-3 text-sm underline text-white">Retour à la boutique</a>
            </div>
        <?php else: ?>
            <form action="caisse.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs uppercase font-bold text-gray-400 mb-1">Nom complet</label>
                    <input type="text" name="nom" required 
                           value="<?= htmlspecialchars($nom_auto) ?>" 
                           placeholder="Ex: Rodrigue Jovi" 
                           class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-gray-400 mb-1">Numéro de Téléphone WhatsApp</label>
                    <input type="tel" name="telephone" required 
                           value="<?= htmlspecialchars($tel_auto) ?>" 
                           placeholder="Ex: 651717964" 
                           class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-gray-400 mb-1">Ville de livraison</label>
                    <input type="text" name="ville" required 
                           value="<?= htmlspecialchars($ville_auto) ?>" 
                           placeholder="Ex: Bafoussam ou Douala" 
                           class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500">
                </div>
                
                <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-lg transition uppercase tracking-wider text-sm mt-2">
                    Confirmer ma commande
                </button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>