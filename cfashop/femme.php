<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Définir la langue actuelle
$langue_actuelle = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $langue_actuelle;
}

// 2. Charger le fichier de langue
$chemin_lang = __DIR__ . '/lang/' . $langue_actuelle . '.php';
if (file_exists($chemin_lang)) {
    $donnees_lang = require_once($chemin_lang);
    $lang = is_array($donnees_lang) ? $donnees_lang : [];
} else {
    $lang = []; 
}

// 3. CONNEXION À LA BASE DE DONNÉES (Ce qui te manque !)
try {
    // On utilise "cfashop" sans l'underscore comme sur tes autres pages fonctionnelles
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    
    // Activer les erreurs SQL pour voir directement s'il y a un autre problème
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Fonction pour filtrer les accès abonnés
if (!function_exists('filtrerLien')) {
    function filtrerLien($pageDestination) {
        return isset($_SESSION['client_id']) ? $pageDestination : 'connexion.php';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFA SHOP | <?= $_SESSION['lang'] == 'fr' ? 'Collection Femme' : 'Women Collection'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-bg: #1a1a1a; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #1a2424; }

        /* Navbar */
        .navbar { height: 80px; background: #ffffff; z-index: 1030; border-bottom: 1px solid #eee; }

        /* Carousel Header */
        .carousel-item { height: 70vh; min-height: 500px; background: #000; }
        .carousel-item img { object-fit: cover; opacity: 0.7; height: 100%; width: 100%; }
        .carousel-caption { bottom: 20%; z-index: 10; }

        /* Grid des Cards */
        .category-card { border: none; overflow: hidden; height: 450px; position: relative; border-radius: 15px; }
        .category-card img { height: 100%; width: 100%; opacity: 0.8; object-fit: cover; transition: transform 0.8s ease; }
        .category-card:hover img { transform: scale(1.05); }
        .card-overlay { position: absolute; bottom: 30px; left: 30px; z-index: 2; }
        .card-title { color: rgb(253, 6, 191); font-size: 2rem; font-weight: 800; text-transform: uppercase; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top bg-white">
        <div class="container-fluid px-4">

            <a class="navbar-brand fw-bold fs-2 tracking-tighter" href="index.php">CFA SHOP</a>
            <img src="5d12ec0a5ff4272f4bdb42613a98bbbd.jpg" alt="Logo CFA SHOP" width="40" height="40" class="d-inline-block align-top me-2 ">
            
            <div class="d-none d-lg-flex flex-grow-1 justify-content-center px-4">
                <div class="input-group search-bar border rounded-pill overflow-hidden max-w-md">
                    <input type="text" class="form-control border-0 px-4" placeholder="<?= $lang['recherche']; ?>">
                    <button class="btn bg-white border-0" type="button"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle fw-bold text-uppercase" type="button" data-bs-toggle="dropdown">
                        <?= $_SESSION['lang']; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="change_lang.php?lang=fr">Français</a></li>
                        <li><a class="dropdown-item" href="change_lang.php?lang=en">English</a></li>
                    </ul>
                </div>

                <?php
                // Calcule le nombre total d'articles dans le panier
                $total_articles_panier = 0;
                if (isset($_SESSION['panier'])) {
                    foreach ($_SESSION['panier'] as $item) {
                        $total_articles_panier += $item['quantite'];
                    }
                }
                ?>

                <a href="panier.php" class="relative text-lg text-black hover:text-pink-600 transition">
                    <i class="fa-solid fa-bag-shopping text-xl"></i>
                    <span class="absolute -top-2 -right-2 bg-pink-600 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center font-bold <?= $total_articles_panier > 0 ? 'animate-bounce' : ''; ?>">
                        <?= $total_articles_panier; ?>
                    </span>
                </a>

                <div class="relative group">
                    <button class="flex items-center gap-1 text-gray-700 dark:text-gray-300 hover:text-pink-600 transition text-lg py-2">
                        <i class="fa-solid fa-circle-user text-xl"></i>
                        <span class="text-xs font-bold text-gray-700 ms-1">
                            <?= $lang['hello']; ?>, <?= htmlspecialchars($_SESSION['client_nom']); ?>
                        </span>
                    </button>

                    <div class="absolute right-0 w-48 bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl shadow-xl hidden group-hover:block z-50 pt-2">
                        <div class="py-2">
                            <a href="mes_commandes.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900 transition">
                                <i class="fa-solid fa-box-open text-gray-400"></i> <?= isset($lang['suivre_commandes']) ? $lang['suivre_commandes'] : 'Suivre mes commandes'; ?>
                            </a>
                            <hr class="border-gray-200 dark:border-gray-800 my-1">
                            <a href="deconnexion.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition">
                                <i class="fa-solid fa-power-off"></i> <?= $lang['deconnexion']; ?>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="deconnexion.php" class="bg-red-600 text-white px-3 py-2 text-xs font-bold uppercase tracking-widest hidden md:inline-block"><?= $lang['deconnexion']; ?></a>
            </div>
        </div>
    </nav>

    <div id="headerCarousel" class="carousel slide carousel-fade pt-20" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="525f3ae38babf2535e84c60c1a5e970d.jpg" alt="Collection 1">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= $lang['titre_carousel_1']; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= $lang['texte_carousel_1']; ?></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="7c05063b873eb767924f072f9ce84bae.jpg" alt="Collection 2">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= $lang['titre_carousel_2']; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= $lang['texte_carousel_2']; ?></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="photo-1483985988355-763728e1935b.jpg" alt="Collection 3">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= $lang['titre_carousel_4']; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= $lang['texte_carousel_3']; ?></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="45468e81462604b724bab8b2ad2dbc28.jpg" alt="Collection 4">
                <div class="carousel-caption">
                    <h2 class="text-5xl md:text-7xl font-black text-white mb-6 uppercase"><?= isset($lang['titre_carousel_4_homme']) ? $lang['titre_carousel_4_homme'] : 'Meilleure Qualité'; ?></h2>
                    <p class="bg-white text-black px-8 py-3 font-bold uppercase tracking-widest hover:bg-black hover:text-white transition"><?= isset($lang['texte_carousel_4_homme']) ? $lang['texte_carousel_4_homme'] : 'Simplicité de Jeunesse'; ?></p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#headerCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#headerCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>

    <main class="container-fluid px-4 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <?php
            // Récupérer uniquement les articles de la catégorie 'femme'
            $requete = $db->query("SELECT * FROM articles WHERE categorie = 'femme' ORDER BY id DESC");
            $articles = $requete->fetchAll();

            if (empty($articles)) {
                echo '<p class="text-white italic col-span-full text-center">' . (isset($lang['aucun_article']) ? $lang['aucun_article'] : 'Aucun article disponible pour le moment.') . '</p>';
            } else {
                foreach ($articles as $article) {
                    ?>
                    <div class="category-card group !h-[300px]">
                        <img src="uploads/<?= htmlspecialchars($article['image']); ?>" alt="<?= htmlspecialchars($article['nom']); ?>">
                        <div class="card-overlay !bottom-5 !left-5">
                            <h2 class="card-title !text-lg mb-1 text-white"><?= htmlspecialchars($article['nom']); ?></h2>
                            <p class="text-white font-black mb-2"><?= number_format($article['prix'], 0, ',', ' '); ?> FCFA</p>
                            <a href="ajouter_panier.php?id=<?= $article['id']; ?>" class="bg-white text-black px-4 py-1 font-bold text-[10px] uppercase rounded hover:bg-pink-600 hover:text-white transition">
                                <?= isset($lang['ajouter_panier']) ? $lang['ajouter_panier'] : 'Ajouter au panier'; ?>
                            </a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>

        </div>
    </main>

    <footer class="bg-blue-100 border-t mt-10">
        <div class="max-w-7xl mx-auto px-4 py-12 text-gray-600">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div>
                    <h3 class="text-lg font-bold mb-6 italic text-black">CFA SHOP</h3>
                    <p class="text-sm"><?= $lang['footer_desc']; ?></p>
                    <div class="flex space-x-4 mt-6">
                        <a href="#" class="text-gray-400 hover:text-black"><i class="fa-brands fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-black"><i class="fa-brands fa-facebook text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-black"><i class="fa-brands fa-whatsapp text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-black"><i class="fa-brands fa-tiktok text-xl"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold mb-6 uppercase text-sm text-black"><?= $lang['footer_boutique']; ?></h4>
                    <ul class="space-y-4 text-sm">
                        <li><a href="homme.php"><?= $lang['cat_homme']; ?></a></li>
                        <li><a href="femme.php"><?= $lang['cat_femme']; ?></a></li>
                        <li><a href="enfant.php"><?= $lang['cat_enfants']; ?></a></li>
                        <li><a href="accessoires.php"><?= $lang['cat_accessoires']; ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-6 uppercase text-sm text-black"><?= $lang['footer_support']; ?></h4>
                    <ul class="space-y-4 text-sm text-gray-600">
                        <li><a href="mes_commandes.php" class="hover:text-black"><?= $lang['suivi_commande']; ?></a></li>
                        <li><a href="#" class="hover:text-black"><?= $lang['livraison_retours']; ?></a></li>
                        <li><a href="#" class="hover:text-black"><?= $lang['guide_tailles']; ?></a></li>
                        <li><a href="#" class="hover:text-black"><?= $lang['contact']; ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-6 uppercase text-sm text-black"><?= $lang['mon_compte']; ?></h4>
                    <p class="text-xs text-gray-700 mb-2"><?= $lang['connecte_en_tant_que']; ?> <strong><?= htmlspecialchars($_SESSION['client_nom']); ?></strong></p>
                    <a href="deconnexion.php" class="bg-red-600 text-white px-4 py-2 text-xs font-bold uppercase tracking-widest inline-block shadow"><?= $lang['deconnexion']; ?></a>
                </div>
            </div>
            <div class="border-t mt-12 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-black">
                <p>© 2026 CFA SHOP. <?= $lang['droits_reserves']; ?></p>
                <div class="flex space-x-6 mt-4 md:mt-0 italic">
                    <span><?= $lang['paiement_secu']; ?></span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>