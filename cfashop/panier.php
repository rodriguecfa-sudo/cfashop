<?php
session_start();

// SÉCURITÉ
if (!isset($_SESSION['client_id'])) {
    header("Location: connexion.php");
    exit();
}

// TRAITEMENT : SUPPRIMER UN ARTICLE DU PANIER
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_retirer = intval($_GET['id']);
    if (isset($_SESSION['panier'][$id_retirer])) {
        unset($_SESSION['panier'][$id_retirer]); // Supprime l'élément de la session
    }
    header("Location: panier.php");
    exit();
}

// Calcul du sous-total global
$total_general = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $total_general += ($item['prix'] * $item['quantite']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFA SHOP | Mon Panier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #1a2424; }
        .navbar { height: 80px; background: #ffffff; border-bottom: 1px solid #eee; }
    </style>
</head>
<body class="text-gray-100 min-h-screen pt-24">

    <nav class="navbar navbar-expand-lg fixed-top bg-white">
        <div class="container-fluid px-4 flex justify-between items-center">
            <a class="navbar-brand fw-bold fs-2 tracking-tighter text-black" href="index.php">CFA SHOP</a>
            <a href="acceuilcfa.php" class="text-xs font-bold uppercase tracking-widest text-gray-600 hover:text-black transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Continuer mes achats
            </a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-black uppercase tracking-tight mb-8 flex items-center gap-3">
            <i class="fa-solid fa-basket-shopping text-pink-500"></i> Votre Panier d'achat
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-4">
                <?php if (empty($_SESSION['panier'])): ?>
                    <div class="bg-gray-950 border border-gray-800 p-8 rounded-xl text-center">
                        <i class="fa-solid fa-bag-shopping text-5xl text-gray-700 mb-4"></i>
                        <p class="text-gray-400 text-lg font-medium">Votre panier est actuellement vide.</p>
                        <a href="indexacceuilcfa.php" class="mt-4 inline-block bg-white text-black font-bold text-xs uppercase tracking-widest px-6 py-3 transition hover:bg-pink-600 hover:text-white">Découvrir les collections</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['panier'] as $id => $article): ?>
                        <div class="bg-gray-950 border border-gray-800 p-4 rounded-xl flex items-center gap-4 hover:border-gray-700 transition">
                            <img src="uploads/<?= htmlspecialchars($article['image']) ?>" alt="" class="w-20 h-20 object-cover rounded-lg bg-gray-900">
                            
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-bold text-white truncate"><?= htmlspecialchars($article['nom']) ?></h3>
                                <p class="text-sm text-pink-500 font-black mt-1"><?= number_format($article['prix'], 0, ',', ' ') ?> FCFA</p>
                                <div class="flex items-center gap-2 mt-2">
    <span class="text-xs text-gray-400 uppercase tracking-wider mr-1">Quantité :</span>
    
    <div class="flex items-center bg-gray-900 border border-gray-800 rounded-lg overflow-hidden h-8">
        <form action="modifier_quantite.php" method="POST" class="m-0">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="action" value="moins">
            <button type="submit" class="px-3 h-full text-gray-400 hover:bg-gray-800 hover:text-white transition font-bold">
                <i class="fa-solid fa-minus text-xs"></i>
            </button>
        </form>

        <span class="px-3 font-bold text-sm text-white border-x border-gray-800 bg-gray-950 flex items-center h-full">
            <?= $article['quantite'] ?>
        </span>

        <form action="modifier_quantite.php" method="POST" class="m-0">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="action" value="plus">
            <button type="submit" class="px-3 h-full text-gray-400 hover:bg-gray-800 hover:text-white transition font-bold">
                <i class="fa-solid fa-plus text-xs"></i>
            </button>
        </form>
    </div>
</div>
                            </div>

                            <div class="text-right hidden sm:block">
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Total produit</p>
                                <p class="text-base font-black text-white mt-0.5">
                                    <?= number_format($article['prix'] * $article['quantite'], 0, ',', ' ') ?> F
                                </p>
                            </div>

                            <div>
                                <a href="panier.php?action=supprimer&id=<?= $id ?>" 
                                   class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-950/40 border border-red-900/60 text-red-400 hover:bg-red-600 hover:text-white transition"
                                   title="Retirer du panier">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($_SESSION['panier'])): ?>
                <div class="bg-gray-950 border border-gray-800 p-6 rounded-xl shadow-xl h-fit sticky top-28">
                    <h2 class="text-lg font-bold uppercase tracking-wider text-gray-300 pb-4 border-b border-gray-800 mb-4">Récapitulatif</h2>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-400">
                            <span>Sous-total articles</span>
                            <span><?= number_format($total_general, 0, ',', ' ') ?> FCFA</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Livraison</span>
                            <span class="text-green-400 font-semibold">Calculée à l'étape suivante</span>
                        </div>
                        <div class="border-t border-gray-800 pt-4 mt-2 flex justify-between items-end">
                            <span class="text-base font-bold text-white">Montant total</span>
                            <span class="text-2xl font-black text-pink-500"><?= number_format($total_general, 0, ',', ' ') ?> F</span>
                        </div>
                    </div>

                    <a href="caisse.php" class="w-full mt-6 bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-lg transition uppercase tracking-widest text-xs text-center block shadow-lg shadow-pink-950/30">
                        <i class="fa-solid fa-credit-card mr-2"></i> Passer la commande
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>