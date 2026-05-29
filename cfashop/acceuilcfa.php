<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Définir la langue actuelle (fr par défaut)
$langue_actuelle = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';

// S'assurer que la variable de session lang existe toujours pour le code HTML plus bas
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $langue_actuelle;
}

// 2. Si l'utilisateur change de langue via un formulaire
if (isset($_POST['changer_langue'])) {
    $langue_selectionnee = htmlspecialchars($_POST['langue']);
    if (in_array($langue_selectionnee, ['fr', 'en'])) { 
        $langue_actuelle = $langue_selectionnee;
        $_SESSION['lang'] = $langue_actuelle; 
    }
}

// 3. CRUCIAL : Charger le fichier et stocker le tableau dans la variable $lang
$chemin_lang = __DIR__ . '/lang/' . $langue_actuelle . '.php';

if (file_exists($chemin_lang)) {
    $donnees_lang = require_once($chemin_lang);
    $lang = is_array($donnees_lang) ? $donnees_lang : [];
} else {
    // Sécurité si le fichier n'existe pas, pour éviter le crash du site
    $lang = []; 
}

// Fonction pour filtrer les accès abonnés
function filtrerLien($pageDestination) {
    return isset($_SESSION['client_id']) ? $pageDestination : 'connexion.php';
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang']); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFA SHOP | <?= $_SESSION['lang'] == 'fr' ? 'Accueil' : 'Home'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-bg: #1a1a1a; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fcfdfd; }
        .navbar { height: 80px; background: #fff; z-index: 1030; border-bottom: 1px solid #eee; }
        .carousel-item { height: 70vh; min-height: 500px; background: #000; }
        .carousel-item img { object-fit: cover; opacity: 0.7; height: 100%; width: 100%; }
        .carousel-caption { bottom: 20%; z-index: 10; }
        .category-card { border: none; overflow: hidden; height: 450px; position: relative; border-radius: 15px; }
        .category-card img { height: 100%; width: 100%; object-fit: cover; transition: transform 0.8s ease; }
        .category-card:hover img { transform: scale(1.05); }
        .card-overlay { position: absolute; bottom: 30px; left: 30px; z-index: 2; }
        .card-title { color: white; font-size: 2rem; font-weight: 800; text-transform: uppercase; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top bg-white">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold fs-2 tracking-tighter" href="#">CFA SHOP</a>
            <img src="5d12ec0a5ff4272f4bdb42613a98bbbd.jpg" alt="Logo" width="40" height="40" class="d-inline-block align-top me-2">
            
            <div class="d-none d-lg-flex flex-grow-1 justify-content-center px-4">
                <div class="input-group search-bar border rounded-pill overflow-hidden max-w-md">
                    <input type="text" class="form-control border-0 px-4" placeholder="<?= isset($lang['recherche']) ? $lang['recherche'] : 'Rechercher...'; ?>">
                    <button class="btn bg-white border-0" type="button"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle fw-bold text-uppercase" type="button" data-bs-toggle="dropdown">
                        <?= htmlspecialchars($_SESSION['lang']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="change_lang.php?lang=fr">Français</a></li>
                        <li><a class="dropdown-item" href="change_lang.php?lang=en">English</a></li>
                    </ul>
                </div>

                <?php
                $total_articles_panier = 0;
                if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {
                    foreach ($_SESSION['panier'] as $item) {
                        $total_articles_panier += isset($item['quantite']) ? $item['quantite'] : 0;
                    }
                }
                ?>
                <a href="panier.php" class="relative text-lg text-black hover:text-pink-600 transition me-2">
                    <i class="fa-solid fa-bag-shopping text-xl"></i>
                    <span class="absolute -top-2 -right-2 bg-pink-600 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center font-bold <?= $total_articles_panier > 0 ? 'animate-bounce' : ''; ?>">
                        <?= $total_articles_panier; ?>
                    </span>
                </a>

                <?php if(isset($_SESSION['client_id'])): ?>
                    <a href="mes_commandes.php" class="text-xs font-bold text-gray-600 hover:text-black uppercase tracking-wider me-2 transition">
                        <i class="fa-solid fa-truck-fast me-1"></i> <?= isset($lang['suivi']) ? $lang['suivi'] : 'Suivi'; ?>
                    </a>
                    <span class="text-xs font-bold text-gray-700">
                        <i class="fa-solid fa-user me-1"></i> <?= isset($lang['hello']) ? $lang['hello'] : 'Hello'; ?>, <?= htmlspecialchars($_SESSION['client_nom']); ?>
                    </span>
                    <a href="deconnexion.php" class="bg-red-600 text-white px-3 py-2 text-xs font-bold uppercase tracking-widest"><?= isset($lang['deconnexion']) ? $lang['deconnexion'] : 'Déconnexion'; ?></a>
                <?php else: ?>
                    <a href="connexion.php" class="text-xs font-bold uppercase tracking-widest text-gray-600 hover:text-black transition"><?= isset($lang['connexion']) ? $lang['connexion'] : 'Connexion'; ?></a>
                    <a href="inscription.php" class="bg-black text-white px-4 py-2 text-xs font-bold uppercase tracking-widest"><?= isset($lang['inscription']) ? $lang['inscription'] : "S'inscrire"; ?></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div id="headerCarousel" class="carousel slide carousel-fade pt-20" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="741c6c0e2cc6eb255a7d27eb782efc4d.jpg" alt="Collection 1">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= isset($lang['titre_carousel_1']) ? $lang['titre_carousel_1'] : ''; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= isset($lang['texte_carousel_1']) ? $lang['texte_carousel_1'] : ''; ?></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="4f9d5155305b8605ada76cb3ed6ea7dc.jpg" alt="Collection 2">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= isset($lang['titre_carousel_2']) ? $lang['titre_carousel_2'] : ''; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= isset($lang['texte_carousel_2']) ? $lang['texte_carousel_2'] : ''; ?></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="R.jpg" alt="Collection 3">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= isset($lang['titre_carousel_3']) ? $lang['titre_carousel_3'] : ''; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= isset($lang['texte_carousel_3']) ? $lang['texte_carousel_3'] : ''; ?></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="525f3ae38babf2535e84c60c1a5e970d.jpg" alt="Collection 4">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= isset($lang['titre_carousel_4']) ? $lang['titre_carousel_4'] : ''; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= isset($lang['texte_carousel_4']) ? $lang['texte_carousel_4'] : ''; ?></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="R (1).jpg" alt="Collection 5">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= isset($lang['titre_carousel_5']) ? $lang['titre_carousel_5'] : ''; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= isset($lang['texte_carousel_5']) ? $lang['texte_carousel_5'] : ''; ?></p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#headerCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#headerCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>

    <main class="container-fluid px-4 py-4">
        <div class="flex flex-wrap -m-2"> 
            <div class="w-full md:w-1/2 p-2">
                <div class="category-card group">
                    <img src="photo-1490578474895-699cd4e2cf59.jpg" alt="Homme">
                    <div class="card-overlay">
                        <h2 class="card-title mb-4"><?= isset($lang['cat_homme']) ? $lang['cat_homme'] : ''; ?></h2>
                        <a href="<?= filtrerLien('homme.php'); ?>" class="bg-white text-black px-6 py-2 font-bold text-sm uppercase rounded"><?= isset($lang['btn_decouvrir']) ? $lang['btn_decouvrir'] : ''; ?></a>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 p-2">
                <div class="category-card group">
                    <img src="photo-1483985988355-763728e1935b.jpg" alt="Femme">
                    <div class="card-overlay">
                        <h2 class="card-title mb-4"><?= isset($lang['cat_femme']) ? $lang['cat_femme'] : ''; ?></h2>
                        <a href="<?= filtrerLien('femme.php'); ?>" class="bg-white text-black px-6 py-2 font-bold text-sm uppercase rounded"><?= isset($lang['btn_decouvrir']) ? $lang['btn_decouvrir'] : ''; ?></a>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 p-2">
                <div class="category-card group">
                    <img src="R (1).jpg" alt="Enfant">
                    <div class="card-overlay">
                        <h2 class="card-title mb-4"><?= isset($lang['cat_enfants']) ? $lang['cat_enfants'] : ''; ?></h2>
                        <a href="<?= filtrerLien('enfant.php'); ?>" class="bg-white text-black px-6 py-2 font-bold text-sm uppercase rounded"><?= isset($lang['btn_decouvrir']) ? $lang['btn_decouvrir'] : ''; ?></a>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 p-2">
                <div class="category-card group">
                    <img src="Accessoires-hommes-printemps-été-2018-trucsdemec.fr-blog-lifestyle-masculin-blog-mode-homme-beauté-homme-6.jpg" alt="Accessoires">
                    <div class="card-overlay">
                        <h2 class="card-title mb-4"><?= isset($lang['cat_accessoires']) ? $lang['cat_accessoires'] : ''; ?></h2>
                        <a href="<?= filtrerLien('accessoires.php'); ?>" class="bg-white text-black px-6 py-2 font-bold text-sm uppercase rounded"><?= isset($lang['btn_decouvrir']) ? $lang['btn_decouvrir'] : ''; ?></a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-blue-100 border-t mt-10">
        <div class="max-w-7xl mx-auto px-4 py-12 text-gray-600">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div>
                    <h3 class="text-lg font-bold mb-6 italic text-black">CFA SHOP</h3>
                    <p class="text-sm"><?= isset($lang['footer_desc']) ? $lang['footer_desc'] : ''; ?></p>
                    <div class="flex space-x-4 mt-6">
                        <a href="https://www.instagram.com/rodrigue_tatsinkounde" target="_blank" class="text-gray-400 hover:text-black"><i class="fa-brands fa-instagram text-xl"></i></a>
                        <a href="https://www.facebook.com/Rodrigue_cfa" target="_blank" class="text-gray-400 hover:text-black"><i class="fa-brands fa-facebook text-xl"></i></a>
                        <a href="https://wa.me/237695509631" target="_blank" class="text-gray-400 hover:text-black"><i class="fa-brands fa-whatsapp text-xl"></i></a>
                        <a href="https://www.tiktok.com/@votre_compte" target="_blank" class="text-gray-400 hover:text-black"><i class="fa-brands fa-tiktok text-xl"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold mb-6 uppercase text-sm text-black"><?= isset($lang['footer_boutique']) ? $lang['footer_boutique'] : ''; ?></h4>
                    <ul class="space-y-4 text-sm">
                        <li><a href="<?= filtrerLien('homme.php'); ?>"><?= isset($lang['cat_homme']) ? $lang['cat_homme'] : ''; ?></a></li>
                        <li><a href="<?= filtrerLien('femme.php'); ?>"><?= isset($lang['cat_femme']) ? $lang['cat_femme'] : ''; ?></a></li>
                        <li><a href="<?= filtrerLien('enfant.php'); ?>"><?= isset($lang['cat_enfants']) ? $lang['cat_enfants'] : ''; ?></a></li>
                        <li><a href="<?= filtrerLien('accessoires.php'); ?>"><?= isset($lang['cat_accessoires']) ? $lang['cat_accessoires'] : ''; ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-6 uppercase text-sm text-black"><?= isset($lang['footer_support']) ? $lang['footer_support'] : ''; ?></h4>
                    <ul class="space-y-4 text-sm text-gray-600">
                        <li><a href="<?= filtrerLien('mes_commandes.php'); ?>" class="hover:text-black"><?= isset($lang['suivi_commande']) ? $lang['suivi_commande'] : ''; ?></a></li>
                        <li><a href="#" class="hover:text-black"><?= isset($lang['livraison_retours']) ? $lang['livraison_retours'] : ''; ?></a></li>
                        <li><a href="#" class="hover:text-black"><?= isset($lang['guide_tailles']) ? $lang['guide_tailles'] : ''; ?></a></li>
                        <li><a href="#" class="hover:text-black"><?= isset($lang['contact']) ? $lang['contact'] : ''; ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-6 uppercase text-sm text-black"><?= isset($lang['mon_compte']) ? $lang['mon_compte'] : ''; ?></h4>
                    <?php if(isset($_SESSION['client_id'])): ?>
                        <p class="text-xs text-gray-700 mb-2"><?= isset($lang['connecte_en_tant_que']) ? $lang['connecte_en_tant_que'] : ''; ?> <strong><?= htmlspecialchars($_SESSION['client_nom']); ?></strong></p>
                        <a href="deconnexion.php" class="bg-red-600 text-white px-4 py-2 text-xs font-bold uppercase tracking-widest inline-block shadow"><?= isset($lang['deconnexion']) ? $lang['deconnexion'] : ''; ?></a>
                    <?php else: ?>
                        <a href="connexion.php" class="text-xs font-bold uppercase tracking-widest text-gray-600 hover:text-black transition mb-2 block"><?= isset($lang['connexion']) ? $lang['connexion'] : ''; ?></a>
                        <a href="inscription.php" class="bg-black text-white px-4 py-2 text-xs font-bold uppercase tracking-widest inline-block"><?= isset($lang['inscription']) ? $lang['inscription'] : ''; ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="border-t mt-12 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-black">
                <p>© 2026 CFA SHOP. <?= isset($lang['droits_reserves']) ? $lang['droits_reserves'] : ''; ?></p>
                <div class="flex space-x-6 mt-4 md:mt-0 italic">
                    <span><?= isset($lang['paiement_secu']) ? $lang['paiement_secu'] : ''; ?></span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>