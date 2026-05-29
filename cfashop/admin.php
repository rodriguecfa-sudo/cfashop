<?php
session_start();
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login_admin.php');
    exit();
}

// --- AJOUT : LOGIQUE DE CHANGEMENT DE LANGUE ---
if (isset($_POST['changer_langue'])) {
    $langue_selectionnee = htmlspecialchars($_POST['langue']);
    $langues_autorisees = ['fr', 'en']; 
    if (in_array($langue_selectionnee, $langues_autorisees)) {
        $_SESSION['lang'] = $langue_selectionnee;
    }
}
// Langue par défaut si aucune n'est définie
$langue_actuelle = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';

// --- TABLEAU DE TRADUCTION ---
$translations = [
    'fr' => [
        'dashboard_title' => "CFA SHOP | Dashboard Admin",
        'catalogue' => "Catalogue Articles",
        'man' => "Homme",
        'woman' => "Femme",
        'children' => "Enfants",
        'accessories' => "Accessoires",
        'orders_mgmt' => "Gestion Commandes",
        'all_orders' => "Toutes les commandes",
        'pending' => "En attente",
        'delivered' => "Livrées",
        'refused' => "Refusées",
        'view_site' => "Voir le site client",
        'logout' => "Se déconnecter",
        'header_title' => "Gestion du Catalogue",
        'header_subtitle' => "Ajoutez, modifiez ou supprimez les articles de la boutique.",
        'add_title' => "Ajouter un nouvel article",
        'edit_title' => "Modifier l'article",
        'item_name' => "Nom de l'article",
        'target_cat' => "Catégorie cible",
        'price' => "Prix (FCFA)",
        'photo' => "Photo",
        'photo_empty_alert' => "(Laissez vide pour conserver l'ancienne)",
        'select_file' => "Sélectionner un fichier",
        'current_img' => "Image actuelle :",
        'btn_edit' => "Enregistrer les modifications",
        'btn_add' => "Mettre en ligne l'article",
        'cancel' => "Annuler la modification",
        'all_items' => "Tous les articles",
        'items_filtered' => "Articles : ",
        'show_all' => "Afficher tout",
        'th_item' => "Article",
        'th_cat' => "Catégorie",
        'th_price' => "Prix",
        'th_actions' => "Actions",
        'no_item' => "Aucun article disponible dans cette catégorie.",
        'confirm_delete' => "Êtes-vous sûr de vouloir supprimer cet article définitivement ?",
        'orders_received' => "Commandes reçues",
        'page_of' => "Page <strong>%d</strong> sur %d",
        'th_client' => "Client",
        'th_contact' => "Contact & Ville",
        'th_ordered' => "Articles commandés",
        'th_total' => "Montant Total",
        'th_date' => "Date",
        'th_status' => "Statut",
        'no_order' => "Aucune commande passée pour le moment.",
        'to_process' => "⏳ À traiter",
        'btn_validate_delivery' => "Valider la livraison",
        'confirm_delivery' => "Confirmez-vous que cette commande a bien été livrée au client ?",
        'msg_deleted' => "L'article a été supprimé avec succès.",
        'msg_added' => "L'article a été ajouté avec succès !",
        'msg_moved_error' => "Erreur lors du déplacement de l'image.",
        'msg_format_error' => "Format d'image non valide.",
        'msg_select_error' => "Veuillez sélectionner une image valide.",
        'msg_edited' => "L'article a été modifié avec succès !",
        'msg_delivery_success' => "La commande #%d a été marquée comme livrée avec succès !",
        'msg_db_error' => "Erreur lors de la mise à jour : ",
        'stats' => "Statistiques",
        'sales_stats' => "Analyse des Ventes"
    ],
    'en' => [
        'dashboard_title' => "CFA SHOP | Admin Dashboard",
        'catalogue' => "Items Catalog",
        'man' => "Men",
        'woman' => "Women",
        'children' => "Children",
        'accessories' => "Accessories",
        'orders_mgmt' => "Orders Management",
        'all_orders' => "All orders",
        'pending' => "Pending",
        'delivered' => "Delivered",
        'refused' => "Refused",
        'view_site' => "View client site",
        'logout' => "Logout",
        'header_title' => "Catalog Management",
        'header_subtitle' => "Add, edit or delete shop items.",
        'add_title' => "Add new item",
        'edit_title' => "Edit item",
        'item_name' => "Item Name",
        'target_cat' => "Target Category",
        'price' => "Price (FCFA)",
        'photo' => "Photo",
        'photo_empty_alert' => "(Leave empty to keep the old one)",
        'select_file' => "Select a file",
        'current_img' => "Current image:",
        'btn_edit' => "Save changes",
        'btn_add' => "Publish item",
        'cancel' => "Cancel edition",
        'all_items' => "All items",
        'items_filtered' => "Items: ",
        'show_all' => "Show all",
        'th_item' => "Item",
        'th_cat' => "Category",
        'th_price' => "Price",
        'th_actions' => "Actions",
        'no_item' => "No items available in this category.",
        'confirm_delete' => "Are you sure you want to permanently delete this item?",
        'orders_received' => "Orders Received",
        'page_of' => "Page <strong>%d</strong> of %d",
        'th_client' => "Client",
        'th_contact' => "Contact & City",
        'th_ordered' => "Ordered items",
        'th_total' => "Total Amount",
        'th_date' => "Date",
        'th_status' => "Status",
        'no_order' => "No orders placed yet.",
        'to_process' => "To process",
        'btn_validate_delivery' => "Validate delivery",
        'confirm_delivery' => "Do you confirm this order has been delivered to the client?",
        'msg_deleted' => "Item deleted successfully.",
        'msg_added' => "Item added successfully!",
        'msg_moved_error' => "Error while moving the uploaded image.",
        'msg_format_error' => "Invalid image format.",
        'msg_select_error' => "Please select a valid image.",
        'msg_edited' => "Item updated successfully!",
        'msg_delivery_success' => "Order #%d was successfully marked as delivered!",
        'msg_db_error' => "Update error: ",
        'stats' => "Statistics",
        'sales_stats' => "Sales Analytics"
    ]
];

// Fonction d'aide pour afficher le texte traduit
function __($key) {
    global $translations, $langue_actuelle;
    return isset($translations[$langue_actuelle][$key]) ? $translations[$langue_actuelle][$key] : $key;
}

// Connexion à la base de données
try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

$message = "";
$status = "";

// --- 1. ACTION : SUPPRESSION D'UN ARTICLE ---
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_suppr = intval($_GET['id']);
    
    $reqImg = $db->prepare('SELECT image FROM articles WHERE id = ?');
    $reqImg->execute([$id_suppr]);
    $articleImg = $reqImg->fetch();
    
    if ($articleImg) {
        $cheminImage = 'uploads/' . $articleImg['image'];
        if (file_exists($cheminImage)) {
            unlink($cheminImage); 
        }
        
        $deleteReq = $db->prepare('DELETE FROM articles WHERE id = ?');
        $deleteReq->execute([$id_suppr]);
        
        $message = __('msg_deleted');
        $status = "success";
    }
}

// --- 2. PREPARATION POUR LA MODIFICATION (MODE ÉDITION) ---
$mode_edition = false;
$art_a_modifier = null;

if (isset($_GET['action']) && $_GET['action'] === 'modifier' && isset($_GET['id'])) {
    $id_modif = intval($_GET['id']);
    $reqModif = $db->prepare('SELECT * FROM articles WHERE id = ?');
    $reqModif->execute([$id_modif]);
    $art_a_modifier = $reqModif->fetch();
    
    if ($art_a_modifier) {
        $mode_edition = true;
    }
}

// --- 3. TRAITEMENT DU FORMULAIRE (AJOUT OU MODIFICATION) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['ajouter_article'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $categorie = htmlspecialchars($_POST['categorie']);
        $prix = intval($_POST['prix']);
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $infoImg = pathinfo($_FILES['image']['name']);
            $extension = strtolower($infoImg['extension']);
            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($extension, $extensionsAutorisees)) {
                $nomImage = md5(uniqid()) . '.' . $extension;
                $cheminDestination = 'uploads/' . $nomImage;
                
                if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $cheminDestination)) {
                    $req = $db->prepare('INSERT INTO articles (nom, categorie, prix, image) VALUES (?, ?, ?, ?)');
                    $req->execute([$nom, $categorie, $prix, $nomImage]);
                    
                    $message = __('msg_added');
                    $status = "success";
                } else {
                    $message = __('msg_moved_error');
                    $status = "error";
                }
            } else {
                $message = __('msg_format_error');
                $status = "error";
            }
        } else {
            $message = __('msg_select_error');
            $status = "error";
        }
    }
    
    if (isset($_POST['modifier_article'])) {
        $id = intval($_POST['id_article']);
        $nom = htmlspecialchars($_POST['nom']);
        $categorie = htmlspecialchars($_POST['categorie']);
        $prix = intval($_POST['prix']);  
        
        $nomImage = $_POST['ancienne_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $infoImg = pathinfo($_FILES['image']['name']);
            $extension = strtolower($infoImg['extension']);
            $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($extension, $extensionsAutorisees)) {
                $nouveauNomImage = md5(uniqid()) . '.' . $extension;
                if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $nouveauNomImage)) {
                    if (file_exists('uploads/' . $nomImage)) { unlink('uploads/' . $nomImage); }
                    $nomImage = $nouveauNomImage;
                }
            }
        }

        $updateReq = $db->prepare('UPDATE articles SET nom = ?, categorie = ?, prix = ?, image = ? WHERE id = ?');
        $updateReq->execute([$nom, $categorie, $prix, $nomImage, $id]);
        
        $message = __('msg_edited');
        $status = "success";
        $mode_edition = false;
    }
}

// --- 4. ACTION : MARQUER UNE COMMANDE COMME LIVRÉE ---
if (isset($_GET['action']) && $_GET['action'] === 'livrer' && isset($_GET['id'])) {
    $id_commande = intval($_GET['id']);
    
    try {
        $updateStatus = $db->prepare('UPDATE commandes SET statut = "Livrée" WHERE id = ?');
        $updateStatus->execute([$id_commande]);
        
        $message = sprintf(__('msg_delivery_success'), $id_commande);
        $status = "success";
    } catch (Exception $e) {
        $message = __('msg_db_error') . $e->getMessage();
        $status = "error";
    }
}

// ==========================================
// LOGIQUE DE FILTRAGE DES ARTICLES
// ==========================================
$categorie_selectionnee = isset($_GET['cat']) ? htmlspecialchars($_GET['cat']) : '';

if (!empty($categorie_selectionnee)) {
    $articlesReq = $db->prepare('SELECT * FROM articles WHERE categorie = ? ORDER BY id DESC');
    $articlesReq->execute([$categorie_selectionnee]);
} else {
    $articlesReq = $db->query('SELECT * FROM articles ORDER BY id DESC');
}
$tousLesArticles = $articlesReq->fetchAll();

// --- 5. COMPTAGE DES STATUTS POUR LA SIDEBAR ---
$countAttente = $db->query("SELECT COUNT(*) FROM commandes WHERE statut IS NULL OR statut = 'En attente' OR statut = ''")->fetchColumn();
$countLivrees = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'Livrée'")->fetchColumn();
$countRefusees = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'Annulée' OR statut = 'Refusée'")->fetchColumn();

// --- Récupération des totaux par catégorie ---
$countHomme = $db->query("SELECT COUNT(*) FROM articles WHERE categorie = 'homme'")->fetchColumn();
$countFemme = $db->query("SELECT COUNT(*) FROM articles WHERE categorie = 'femme'")->fetchColumn();
$countEnfants = $db->query("SELECT COUNT(*) FROM articles WHERE categorie = 'enfants'")->fetchColumn();
$countAccessoires = $db->query("SELECT COUNT(*) FROM articles WHERE categorie = 'accessoires'")->fetchColumn();

// --- 5b. LOGIQUE D'EXTRACTION DES DONNÉES DE STATISTIQUES (GROUPÉ PAR DATE OU ID) ---
// On extrait le montant total dépensé et le nombre de commandes passées au fil du temps
$statsQuery = $db->query("SELECT DATE(date_commande) as date_vente, SUM(total) as total_jour, COUNT(id) as nb_commandes FROM commandes GROUP BY DATE(date_commande) ORDER BY date_vente ASC LIMIT 30");
$statsData = $statsQuery->fetchAll(PDO::FETCH_ASSOC);

$labelsDates = [];
$dataMontants = [];
$dataCommandes = [];

foreach($statsData as $row) {
    $labelsDates[] = date('d M', strtotime($row['date_vente']));
    $dataMontants[] = (float)$row['total_jour'];
    $dataCommandes[] = (int)$row['nb_commandes'];
}

// --- 6. LOGIQUE DE PAGINATION POUR LES COMMANDES ---
$totalCommandes = $db->query("SELECT COUNT(*) FROM commandes")->fetchColumn(); 
$commandesParPage = 10; 
$totalPages = ceil($totalCommandes / $commandesParPage);

$pageActuelle = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($pageActuelle < 1) { $pageActuelle = 1; }
if ($pageActuelle > $totalPages && $totalPages > 0) { $pageActuelle = $totalPages; }

$offset = ($pageActuelle - 1) * $commandesParPage;

$commandesReq = $db->prepare('SELECT * FROM commandes ORDER BY id DESC LIMIT :limite OFFSET :offset');
$commandesReq->bindValue(':limite', $commandesParPage, PDO::PARAM_INT);
$commandesReq->bindValue(':offset', $offset, PDO::PARAM_INT);
$commandesReq->execute();
$toutesLesCommandes = $commandesReq->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $langue_actuelle ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('dashboard_title') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex">

    <aside class="w-64 bg-gray-950 border-r border-gray-800 flex flex-col justify-between shrink-0">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-8">
                <span class="text-2xl font-black tracking-wider text-pink-500">CFA SHOP</span>
                <span class="bg-gray-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded text-gray-400">Admin</span>
            </div>
            
            <nav class="space-y-1">
                <a href="admin.php#section-stats" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-400 hover:bg-gray-900 hover:text-white transition text-sm mb-3 border border-dashed border-gray-800 hover:border-pink-500/40">
                    <i class="fa-solid fa-chart-line text-lg w-6 text-center text-pink-500"></i> <?= __('stats') ?>
                </a>

                <a href="admin.php#catalogue" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white font-bold bg-gray-900/50 border border-gray-800/80 transition">
                    <i class="fa-solid fa-boxes-stacked text-lg w-6 text-center text-pink-500"></i> <?= __('catalogue') ?>
                </a>

                <div class="space-y-0.5 pl-6 pt-1">
                    <a href="admin.php?cat=homme#catalogue" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium <?= $categorie_selectionnee === 'homme' ? 'text-white bg-pink-950/30 font-bold border border-pink-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-900' ?> transition">
                        <span>♂ <?= __('man') ?></span>
                        <span class="bg-gray-900 text-gray-400 px-1.5 py-0.5 rounded text-[10px] border border-gray-800"><?= $countHomme ?></span>
                    </a>
                    <a href="admin.php?cat=femme#catalogue" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium <?= $categorie_selectionnee === 'femme' ? 'text-white bg-pink-950/30 font-bold border border-pink-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-900' ?> transition">
                        <span>♀ <?= __('woman') ?></span>
                        <span class="bg-gray-900 text-gray-400 px-1.5 py-0.5 rounded text-[10px] border border-gray-800"><?= $countFemme ?></span>
                    </a>
                    <a href="admin.php?cat=enfants#catalogue" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium <?= $categorie_selectionnee === 'enfants' ? 'text-white bg-pink-950/30 font-bold border border-pink-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-900' ?> transition">
                        <span>👶 <?= __('children') ?></span>
                        <span class="bg-gray-900 text-gray-400 px-1.5 py-0.5 rounded text-[10px] border border-gray-800"><?= $countEnfants ?></span>
                    </a>
                    <a href="admin.php?cat=accessoires#catalogue" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium <?= $categorie_selectionnee === 'accessoires' ? 'text-white bg-pink-950/30 font-bold border border-pink-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-900' ?> transition">
                        <span>🎒 <?= __('accessories') ?></span>
                        <span class="bg-gray-900 text-gray-400 px-1.5 py-0.5 rounded text-[10px] border border-gray-800"><?= $countAccessoires ?></span>
                    </a>
                </div>
                
                <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-gray-500"><?= __('orders_mgmt') ?></div>

                <a href="admin.php#section-commandes" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-gray-400 hover:bg-gray-900 hover:text-white transition text-sm">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-gray-500 w-5 text-center"></i> <?= __('all_orders') ?>
                    </div>
                </a>

                <a href="admin.php#section-commandes" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-gray-400 hover:bg-gray-900 hover:text-white transition text-sm pl-6">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock text-yellow-500"></i> <?= __('pending') ?>
                    </div>
                    <span class="bg-yellow-950 text-yellow-400 border border-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full">
                        <?= $countAttente ?>
                    </span>
                </a>

                <a href="admin.php#section-commandes" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-gray-400 hover:bg-gray-900 hover:text-white transition text-sm pl-6">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-green-500"></i> <?= __('delivered') ?>
                    </div>
                    <span class="bg-green-950 text-green-400 border border-green-900 text-xs font-bold px-2 py-0.5 rounded-full">
                        <?= $countLivrees ?>
                    </span>
                </a>

                <a href="admin.php#section-commandes" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-gray-400 hover:bg-gray-900 hover:text-white transition text-sm pl-6">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark text-red-500"></i> <?= __('refused') ?>
                    </div>
                    <span class="bg-red-950 text-red-400 border border-red-900 text-xs font-bold px-2 py-0.5 rounded-full">
                        <?= $countRefusees ?>
                    </span>
                </a>
            </nav>
        </div>
        
        <div class="p-6 border-t border-gray-800 space-y-4">
            <a href="acceuilcfa.php" class="flex items-center gap-3 text-sm text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-arrow-left"></i> <?= __('view_site') ?>
            </a>
            <a href="logout_admin.php" class="flex items-center gap-3 text-sm text-red-400 hover:text-red-500 transition font-semibold">
                <i class="fa-solid fa-power-off"></i> <?= __('logout') ?>
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto space-y-12">
        
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8" id="catalogue">
            <div>
                <h1 class="text-3xl font-bold"><?= __('header_title') ?></h1>
                <p class="text-sm text-gray-400 mt-1"><?= __('header_subtitle') ?></p>
            </div>
            
            <div class="bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-globe text-pink-500 text-sm"></i>
                <form action="admin.php<?= !empty($categorie_selectionnee) ? '?cat='.$categorie_selectionnee : '' ?>" method="POST" id="form-langue">
                    <input type="hidden" name="changer_langue" value="1">
                    <select name="langue" onchange="document.getElementById('form-langue').submit();" class="bg-transparent text-sm font-semibold text-gray-300 focus:outline-none cursor-pointer pr-2">
                        <option value="fr" class="bg-gray-950 text-white" <?= $langue_actuelle === 'fr' ? 'selected' : '' ?>>Français (FR)</option>
                        <option value="en" class="bg-gray-950 text-white" <?= $langue_actuelle === 'en' ? 'selected' : '' ?>>English (EN)</option>
                    </select>
                </form>
            </div>
        </header>

        <?php if (!empty($message)): ?>
            <div class="p-4 mb-6 rounded-lg font-semibold flex items-center gap-3 <?= $status === 'success' ? 'bg-green-950 text-green-400 border border-green-800' : 'bg-red-950 text-red-400 border border-red-800' ?>">
                <i class="fa-solid <?= $status === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <section id="section-stats" class="bg-gray-950 p-6 rounded-xl border border-gray-800 shadow-xl scroll-mt-6">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-pink-500">
                <i class="fa-solid fa-chart-line"></i> <?= __('sales_stats') ?>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-900/60 p-4 rounded-lg border border-gray-800/80">
                    <h3 class="text-sm font-semibold text-gray-400 mb-4 uppercase tracking-wider">Chiffre d'affaires journalier (FCFA)</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                <div class="bg-gray-900/60 p-4 rounded-lg border border-gray-800/80">
                    <h3 class="text-sm font-semibold text-gray-400 mb-4 uppercase tracking-wider">Volume des commandes passées</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="volumeChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="bg-gray-950 p-6 rounded-xl border border-gray-800 shadow-xl h-fit">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-pink-500">
                    <i class="fa-solid <?= $mode_edition ? 'fa-pen-to-square' : 'fa-plus-circle' ?>"></i> 
                    <?= $mode_edition ? __('edit_title') : __('add_title') ?>
                </h2>
                
                <form action="admin.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    
                    <?php if ($mode_edition): ?>
                        <input type="hidden" name="id_article" value="<?= $art_a_modifier['id'] ?>">
                        <input type="hidden" name="ancienne_image" value="<?= $art_a_modifier['image'] ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-xs uppercase font-bold tracking-wider text-gray-400 mb-2"><?= __('item_name') ?></label>
                        <input type="text" name="nom" required 
                               value="<?= $mode_edition ? htmlspecialchars($art_a_modifier['nom']) : '' ?>"
                               placeholder="Ex: Robe d'été Fleurie" 
                               class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs uppercase font-bold tracking-wider text-gray-400 mb-2"><?= __('target_cat') ?></label>
                        <select name="categorie" required 
                                 class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500 transition">
                            <option value="homme" <?= $mode_edition && $art_a_modifier['categorie'] === 'homme' ? 'selected' : '' ?>><?= __('man') ?> ♂</option>
                            <option value="femme" <?= $mode_edition && $art_a_modifier['categorie'] === 'femme' ? 'selected' : '' ?>><?= __('woman') ?> ♀</option>
                            <option value="enfants" <?= $mode_edition && $art_a_modifier['categorie'] === 'enfants' ? 'selected' : '' ?>><?= __('children') ?> 👶</option>
                            <option value="accessoires" <?= $mode_edition && $art_a_modifier['categorie'] === 'accessoires' ? 'selected' : '' ?>><?= __('accessories') ?> 🎒</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs uppercase font-bold tracking-wider text-gray-400 mb-2"><?= __('price') ?></label>
                        <input type="number" name="prix" required 
                               value="<?= $mode_edition ? $art_a_modifier['prix'] : '' ?>"
                               placeholder="Ex: 15000" 
                               class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs uppercase font-bold tracking-wider text-gray-400 mb-2">
                            <?= __('photo') ?> <?= $mode_edition ? __('photo_empty_alert') : '' ?>
                        </label>
                        <div class="border-2 border-dashed border-gray-800 hover:border-pink-500 rounded-lg p-4 text-center cursor-pointer transition relative bg-gray-900">
                            <input type="file" name="image" <?= $mode_edition ? '' : 'required' ?> class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl text-gray-500 mb-1"></i>
                            <p class="text-xs text-gray-300"><?= __('select_file') ?></p>
                        </div>
                        <?php if ($mode_edition): ?>
                            <div class="mt-2 text-xs text-gray-400 flex items-center gap-2">
                                <span><?= __('current_img') ?></span>
                                <img src="uploads/<?= $art_a_modifier['image'] ?>" class="w-8 h-8 object-cover rounded">
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($mode_edition): ?>
                        <button type="submit" name="modifier_article" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition uppercase tracking-wider text-sm shadow-lg">
                            <i class="fa-solid fa-save mr-2"></i> <?= __('btn_edit') ?>
                        </button>
                        <a href="admin.php" class="block text-center text-xs text-gray-400 hover:text-white underline mt-2"><?= __('cancel') ?></a>
                    <?php else: ?>
                        <button type="submit" name="ajouter_article" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-lg transition uppercase tracking-wider text-sm shadow-lg">
                            <i class="fa-solid fa-paper-plane mr-2"></i> <?= __('btn_add') ?>
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="lg:col-span-2 bg-gray-950 p-6 rounded-xl border border-gray-800 shadow-xl">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-blue-400">
                        <i class="fa-solid fa-boxes-stacked"></i> 
                        <?= !empty($categorie_selectionnee) ? __('items_filtered') . ucfirst($categorie_selectionnee) : __('all_items') ?> 
                        (<?= count($tousLesArticles) ?>)
                    </h2>
                    
                    <?php if(!empty($categorie_selectionnee)): ?>
                        <a href="admin.php#catalogue" class="text-xs font-bold uppercase bg-gray-900 border border-gray-800 text-gray-400 hover:text-white px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                            <i class="fa-solid fa-rotate-left"></i> <?= __('show_all') ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-800 text-gray-400 text-xs uppercase tracking-wider">
                                <th class="pb-3 font-semibold"><?= __('th_item') ?></th>
                                <th class="pb-3 font-semibold"><?= __('th_cat') ?></th>
                                <th class="pb-3 font-semibold"><?= __('th_price') ?></th>
                                <th class="pb-3 font-semibold text-center"><?= __('th_actions') ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/50 text-sm">
                            <?php if(empty($tousLesArticles)): ?>
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-gray-500 italic"><?= __('no_item') ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($tousLesArticles as $art): ?>
                                    <tr class="hover:bg-gray-900/40 transition">
                                        <td class="py-3 flex items-center gap-3">
                                            <img src="uploads/<?= $art['image'] ?>" alt="" class="w-10 h-10 object-cover rounded bg-gray-800">
                                            <span class="font-medium text-white truncate max-w-[180px]"><?= htmlspecialchars($art['nom']) ?></span>
                                        </td>
                                        <td class="py-3">
                                            <span class="bg-gray-900 border border-gray-800 px-2 py-0.5 rounded text-xs text-gray-300 uppercase">
                                                <?= $art['categorie'] ?>
                                            </span>
                                        </td>
                                        <td class="py-3 font-semibold text-pink-500">
                                            <?= number_format($art['prix'], 0, ',', ' ') ?> F
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="flex justify-center gap-2">
                                                <a href="admin.php?action=modifier&id=<?= $art['id'] ?>" 
                                                   class="bg-blue-950/60 border border-blue-800 text-blue-400 px-2 py-1 rounded text-xs hover:bg-blue-600 hover:text-white transition" 
                                                   title="Modifier">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="admin.php?action=supprimer&id=<?= $art['id'] ?>" 
                                                   onclick="return confirm('<?= __('confirm_delete') ?>');"
                                                   class="bg-red-950/60 border border-red-800 text-red-400 px-2 py-1 rounded text-xs hover:bg-red-600 hover:text-white transition" 
                                                   title="Supprimer">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        const datesLabels = <?= json_encode($labelsDates); ?>;
        const montantsData = <?= json_encode($dataMontants); ?>;
        const commandesData = <?= json_encode($dataCommandes); ?>;

        // Configuration Graphique Ventes (Chiffre d'Affaires)
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: datesLabels,
                datasets: [{
                    label: 'Revenus (FCFA)',
                    data: montantsData,
                    borderColor: '#ec4899', // Pink-500
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#1f2937' }, ticks: { color: '#9ca3af' } },
                    y: { grid: { color: '#1f2937' }, ticks: { color: '#9ca3af' } }
                }
            }
        });

        // Configuration Graphique Volumes (Commandes)
        const ctxVolume = document.getElementById('volumeChart').getContext('2d');
        new Chart(ctxVolume, {
            type: 'bar',
            data: {
                labels: datesLabels,
                datasets: [{
                    label: 'Commandes',
                    data: commandesData,
                    backgroundColor: '#3b82f6', // Blue-500
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#9ca3af' } },
                    y: { grid: { color: '#1f2937' }, ticks: { color: '#9ca3af', stepSize: 1 } }
                }
            }
        });
    </script>
</body>
</html>