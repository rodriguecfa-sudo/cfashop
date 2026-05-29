<?php
session_start();
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login_admin.php');
    exit();
}

if (isset($_POST['changer_langue'])) {
    $langue_selectionnee = htmlspecialchars($_POST['langue']);
    $langues_autorisees = ['fr', 'en']; 
    if (in_array($langue_selectionnee, $langues_autorisees)) { $_SESSION['lang'] = $langue_selectionnee; }
}
$langue_actuelle = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';

// Insérez ici l'intégralité de votre grand tableau de traduction d'origine pour le catalogue ($translations)
$translations = [ 'fr' => [ 'dashboard_title' => "CFA SHOP | Catalogue", 'catalogue' => "Catalogue Articles", 'man' => "Homme", 'woman' => "Femme", 'children' => "Enfants", 'accessories' => "Accessoires", 'orders_mgmt' => "Gestion Commandes", 'all_orders' => "Toutes les commandes", 'pending' => "En attente", 'delivered' => "Livrées", 'refused' => "Refusées", 'view_site' => "Voir le site client", 'logout' => "Se déconnecter", 'header_title' => "Gestion du Catalogue", 'header_subtitle' => "Ajoutez, modifiez ou supprimez les articles de la boutique.", 'add_title' => "Ajouter un nouvel article", 'edit_title' => "Modifier l'article", 'item_name' => "Nom de l'article", 'target_cat' => "Catégorie cible", 'price' => "Prix (FCFA)", 'photo' => "Photo", 'photo_empty_alert' => "(Laissez vide pour conserver l'ancienne)", 'select_file' => "Sélectionner un fichier", 'current_img' => "Image actuelle :", 'btn_edit' => "Enregistrer les modifications", 'btn_add' => "Mettre en ligne l'article", 'cancel' => "Annuler la modification", 'all_items' => "Tous les articles", 'items_filtered' => "Articles : ", 'show_all' => "Afficher tout", 'th_item' => "Article", 'th_cat' => "Catégorie", 'th_price' => "Prix", 'th_actions' => "Actions", 'no_item' => "Aucun article disponible dans cette catégorie.", 'confirm_delete' => "Êtes-vous sûr de vouloir supprimer cet article définitivement ?", 'msg_deleted' => "L'article a été supprimé avec succès.", 'msg_added' => "L'article a été ajouté avec succès !", 'msg_moved_error' => "Erreur lors du déplacement de l'image.", 'msg_format_error' => "Format d'image non valide.", 'msg_select_error' => "Veuillez sélectionner une image valide.", 'msg_edited' => "L'article a été modifié avec succès !", 'stats' => "Statistiques" ], 'en' => [ 'dashboard_title' => "CFA SHOP | Catalog", 'catalogue' => "Items Catalog", 'man' => "Men", 'woman' => "Women", 'children' => "Children", 'accessories' => "Accessories", 'orders_mgmt' => "Orders Management", 'all_orders' => "All orders", 'pending' => "Pending", 'delivered' => "Delivered", 'refused' => "Refused", 'view_site' => "View client site", 'logout' => "Logout", 'header_title' => "Catalog Management", 'header_subtitle' => "Add, edit or delete shop items.", 'add_title' => "Add new item", 'edit_title' => "Edit item", 'item_name' => "Item Name", 'target_cat' => "Target Category", 'price' => "Price (FCFA)", 'photo' => "Photo", 'photo_empty_alert' => "(Leave empty to keep the old one)", 'select_file' => "Select a file", 'current_img' => "Current image:", 'btn_edit' => "Save changes", 'btn_add' => "Publish item", 'cancel' => "Cancel edition", 'all_items' => "All items", 'items_filtered' => "Items: ", 'show_all' => "Show all", 'th_item' => "Item", 'th_cat' => "Category", 'th_price' => "Price", 'th_actions' => "Actions", 'no_item' => "No items available in this category.", 'confirm_delete' => "Are you sure you want to permanently delete this item?", 'msg_deleted' => "Item deleted successfully.", 'msg_added' => "Item added successfully!", 'msg_moved_error' => "Error while moving the uploaded image.", 'msg_format_error' => "Invalid image format.", 'msg_select_error' => "Please select a valid image.", 'msg_edited' => "Item updated successfully!", 'stats' => "Statistics" ] ];

function __($key) { global $translations, $langue_actuelle; return isset($translations[$langue_actuelle][$key]) ? $translations[$langue_actuelle][$key] : $key; }

try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) { die('Erreur : ' . $e->getMessage()); }

$message = ""; $status = "";

// [LOGIQUE 1 : SUPPRESSION D'UN ARTICLE]
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_suppr = intval($_GET['id']);
    $reqImg = $db->prepare('SELECT image FROM articles WHERE id = ?');
    $reqImg->execute([$id_suppr]);
    $articleImg = $reqImg->fetch();
    if ($articleImg) {
        $cheminImage = 'uploads/' . $articleImg['image'];
        if (file_exists($cheminImage)) { unlink($cheminImage); }
        $deleteReq = $db->prepare('DELETE FROM articles WHERE id = ?');
        $deleteReq->execute([$id_suppr]);
        $message = __('msg_deleted'); $status = "success";
    }
}

// [LOGIQUE 2 : MODE ÉDITION]
$mode_edition = false; $art_a_modifier = null;
if (isset($_GET['action']) && $_GET['action'] === 'modifier' && isset($_GET['id'])) {
    $id_modif = intval($_GET['id']);
    $reqModif = $db->prepare('SELECT * FROM articles WHERE id = ?');
    $reqModif->execute([$id_modif]);
    $art_a_modifier = $reqModif->fetch();
    if ($art_a_modifier) { $mode_edition = true; }
}

// [LOGIQUE 3 : ENREGISTREMENTS POST]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_article'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $categorie = htmlspecialchars($_POST['categorie']);
        $prix = intval($_POST['prix']);
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $infoImg = pathinfo($_FILES['image']['name']);
            $extension = strtolower($infoImg['extension']);
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                $nomImage = md5(uniqid()) . '.' . $extension;
                if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
                if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $nomImage)) {
                    $req = $db->prepare('INSERT INTO articles (nom, categorie, prix, image) VALUES (?, ?, ?, ?)');
                    $req->execute([$nom, $categorie, $prix, $nomImage]);
                    $message = __('msg_added'); $status = "success";
                } else { $message = __('msg_moved_error'); $status = "error"; }
            } else { $message = __('msg_format_error'); $status = "error"; }
        } else { $message = __('msg_select_error'); $status = "error"; }
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
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                $nouveauNomImage = md5(uniqid()) . '.' . $extension;
                if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $nouveauNomImage)) {
                    if (file_exists('uploads/' . $nomImage)) { unlink('uploads/' . $nomImage); }
                    $nomImage = $nouveauNomImage;
                }
            }
        }
        $updateReq = $db->prepare('UPDATE articles SET nom = ?, categorie = ?, prix = ?, image = ? WHERE id = ?');
        $updateReq->execute([$nom, $categorie, $prix, $nomImage, $id]);
        $message = __('msg_edited'); $status = "success"; $mode_edition = false;
    }
}

// Données requises pour le filtrage et la Sidebar
$categorie_selectionnee = isset($_GET['cat']) ? htmlspecialchars($_GET['cat']) : '';
if (!empty($categorie_selectionnee)) {
    $articlesReq = $db->prepare('SELECT * FROM articles WHERE categorie = ? ORDER BY id DESC');
    $articlesReq->execute([$categorie_selectionnee]);
} else { $articlesReq = $db->query('SELECT * FROM articles ORDER BY id DESC'); }
$tousLesArticles = $articlesReq->fetchAll();

$countAttente = $db->query("SELECT COUNT(*) FROM commandes WHERE statut IS NULL OR statut = 'En attente' OR statut = ''")->fetchColumn();
$countLivrees = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'Livrée'")->fetchColumn();
$countRefusees = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'Annulée' OR statut = 'Refusée'")->fetchColumn();
$countHomme = $db->query("SELECT COUNT(*) FROM articles WHERE categorie = 'homme'")->fetchColumn();
$countFemme = $db->query("SELECT COUNT(*) FROM articles WHERE categorie = 'femme'")->fetchColumn();
$countEnfants = $db->query("SELECT COUNT(*) FROM articles WHERE categorie = 'enfants'")->fetchColumn();
$countAccessoires = $db->query("SELECT COUNT(*) FROM articles WHERE categorie = 'accessoires'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="<?= $langue_actuelle ?>">
<head>
    <meta charset="UTF-8">
    <title><?= __('dashboard_title') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex">

    <?php include('admin_sidebar.php'); ?>

    <main class="flex-1 p-8 overflow-y-auto space-y-12">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold"><?= __('header_title') ?></h1>
                <p class="text-sm text-gray-400 mt-1"><?= __('header_subtitle') ?></p>
            </div>
            
            <div class="bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-globe text-pink-500 text-sm"></i>
                <form action="admin_catalogue.php<?= !empty($categorie_selectionnee) ? '?cat='.$categorie_selectionnee : '' ?>" method="POST" id="form-langue">
                    <input type="hidden" name="changer_langue" value="1">
                    <select name="langue" onchange="document.getElementById('form-langue').submit();" class="bg-transparent text-sm font-semibold text-gray-300 focus:outline-none cursor-pointer pr-2">
                        <option value="fr" <?= $langue_actuelle === 'fr' ? 'selected' : '' ?>>Français (FR)</option>
                        <option value="en" <?= $langue_actuelle === 'en' ? 'selected' : '' ?>>English (EN)</option>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-gray-950 p-6 rounded-xl border border-gray-800 shadow-xl h-fit">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-pink-500">
                    <i class="fa-solid <?= $mode_edition ? 'fa-pen-to-square' : 'fa-plus-circle' ?>"></i> <?= $mode_edition ? __('edit_title') : __('add_title') ?>
                </h2>
                
                <form action="admin_catalogue.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <?php if ($mode_edition): ?>
                        <input type="hidden" name="id_article" value="<?= $art_a_modifier['id'] ?>">
                        <input type="hidden" name="ancienne_image" value="<?= $art_a_modifier['image'] ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-xs uppercase font-bold tracking-wider text-gray-400 mb-2"><?= __('item_name') ?></label>
                        <input type="text" name="nom" required value="<?= $mode_edition ? htmlspecialchars($art_a_modifier['nom']) : '' ?>" class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs uppercase font-bold tracking-wider text-gray-400 mb-2"><?= __('target_cat') ?></label>
                        <select name="categorie" required class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500 transition">
                            <option value="homme" <?= $mode_edition && $art_a_modifier['categorie'] === 'homme' ? 'selected' : '' ?>><?= __('man') ?> ♂</option>
                            <option value="femme" <?= $mode_edition && $art_a_modifier['categorie'] === 'femme' ? 'selected' : '' ?>><?= __('woman') ?> ♀</option>
                            <option value="enfants" <?= $mode_edition && $art_a_modifier['categorie'] === 'enfants' ? 'selected' : '' ?>><?= __('children') ?> 👶</option>
                            <option value="accessoires" <?= $mode_edition && $art_a_modifier['categorie'] === 'accessoires' ? 'selected' : '' ?>><?= __('accessories') ?> 🎒</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs uppercase font-bold tracking-wider text-gray-400 mb-2"><?= __('price') ?></label>
                        <input type="number" name="prix" required value="<?= $mode_edition ? $art_a_modifier['prix'] : '' ?>" class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-pink-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs uppercase font-bold tracking-wider text-gray-400 mb-2"><?= __('photo') ?> <?= $mode_edition ? __('photo_empty_alert') : '' ?></label>
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

                    <button type="submit" name="<?= $mode_edition ? 'modifier_article' : 'ajouter_article' ?>" class="w-full <?= $mode_edition ? 'bg-blue-600 hover:bg-blue-700' : 'bg-pink-600 hover:bg-pink-700' ?> text-white font-bold py-3 px-4 rounded-lg transition uppercase tracking-wider text-sm shadow-lg">
                        <i class="fa-solid fa-save mr-2"></i> <?= $mode_edition ? __('btn_edit') : __('btn_add') ?>
                    </button>
                    <?php if ($mode_edition): ?>
                        <a href="admin_catalogue.php" class="block text-center text-xs text-gray-400 hover:text-white underline mt-2"><?= __('cancel') ?></a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="lg:col-span-2 bg-gray-950 p-6 rounded-xl border border-gray-800 shadow-xl">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-blue-400">
                        <i class="fa-solid fa-boxes-stacked"></i> <?= !empty($categorie_selectionnee) ? __('items_filtered') . ucfirst($categorie_selectionnee) : __('all_items') ?> (<?= count($tousLesArticles) ?>)
                    </h2>
                    <?php if(!empty($categorie_selectionnee)): ?>
                        <a href="admin_catalogue.php" class="text-xs font-bold uppercase bg-gray-900 border border-gray-800 text-gray-400 hover:text-white px-3 py-1.5 rounded-lg transition flex items-center gap-1"><i class="fa-solid fa-rotate-left"></i> <?= __('show_all') ?></a>
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
                                <tr><td colspan="4" class="py-4 text-center text-gray-500 italic"><?= __('no_item') ?></td></tr>
                            <?php else: ?>
                                <?php foreach($tousLesArticles as $art): ?>
                                    <tr>
                                        <td class="py-3 flex items-center gap-3">
                                            <img src="uploads/<?= $art['image'] ?>" class="w-10 h-10 object-cover rounded-lg border border-gray-800">
                                            <span class="font-medium text-gray-200"><?= htmlspecialchars($art['nom']) ?></span>
                                        </td>
                                        <td class="py-3 text-gray-400 uppercase text-xs"><?= htmlspecialchars($art['categorie']) ?></td>
                                        <td class="py-3 font-bold text-pink-500"><?= number_format($art['prix'], 0, '.', ' ') ?> FCFA</td>
                                        <td class="py-3 text-center space-x-2">
                                            <a href="admin_catalogue.php?action=modifier&id=<?= $art['id'] ?>" class="text-blue-400 hover:text-blue-500 bg-blue-950/40 border border-blue-900/50 px-2.5 py-1 rounded-md text-xs transition"><i class="fa-solid fa-pen"></i></a>
                                            <a href="admin_catalogue.php?action=supprimer&id=<?= $art['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="text-red-400 hover:text-red-500 bg-red-950/40 border border-red-900/50 px-2.5 py-1 rounded-md text-xs transition"><i class="fa-solid fa-trash"></i></a>
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
</body>
</html>