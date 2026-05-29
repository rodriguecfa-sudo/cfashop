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

$translations = [
    'fr' => [
        'dashboard_title' => "CFA SHOP | Commandes", 'orders_mgmt' => "Gestion Commandes", 'all_orders' => "Toutes les commandes", 'pending' => "En attente", 'delivered' => "Livrées", 'refused' => "Refusées", 'orders_received' => "Commandes reçues", 'page_of' => "Page <strong>%d</strong> sur %d", 'th_client' => "Client", 'th_contact' => "Contact & Ville", 'th_ordered' => "Articles commandés", 'th_total' => "Montant Total", 'th_date' => "Date", 'th_status' => "Statut", 'no_order' => "Aucune commande passée pour le moment.", 'to_process' => "⏳ À traiter", 'btn_validate_delivery' => "Valider la livraison", 'confirm_delivery' => "Confirmez-vous que cette commande a bien été livrée au client ?", 'confirm_cancel' => "Confirmez-vous l'annulation de cette commande ?", 'msg_delivery_success' => "La commande #%d a été marquée comme livrée avec succès !", 'msg_cancel_success' => "La commande #%d a été annulée !", 'msg_db_error' => "Erreur lors de la mise à jour : ", 'stats' => "Statistiques", 'catalogue' => "Catalogue Articles", 'view_site' => "Voir le site client", 'logout' => "Se déconnecter"
    ],
    'en' => [
        'dashboard_title' => "CFA SHOP | Orders", 'orders_mgmt' => "Orders Management", 'all_orders' => "All orders", 'pending' => "Pending", 'delivered' => "Delivered", 'refused' => "Refused", 'orders_received' => "Orders Received", 'page_of' => "Page <strong>%d</strong> of %d", 'th_client' => "Client", 'th_contact' => "Contact & City", 'th_ordered' => "Ordered items", 'th_total' => "Total Amount", 'th_date' => "Date", 'th_status' => "Status", 'no_order' => "No orders placed yet.", 'to_process' => "To process", 'btn_validate_delivery' => "Validate delivery", 'confirm_delivery' => "Do you confirm this order has been delivered to the client?", 'confirm_cancel' => "Are you sure you want to cancel this order?", 'msg_delivery_success' => "Order #%d was successfully marked as delivered!", 'msg_cancel_success' => "Order #%d was successfully cancelled!", 'msg_db_error' => "Update error: ", 'stats' => "Statistics", 'catalogue' => "Items Catalog", 'view_site' => "View client site", 'logout' => "Logout"
    ]
];

function __($key) { global $translations, $langue_actuelle; return isset($translations[$langue_actuelle][$key]) ? $translations[$langue_actuelle][$key] : $key; }

try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) { die('Erreur : ' . $e->getMessage()); }

$message = ""; $status = "";

// [ACTION : VALIDER LA LIVRAISON]
if (isset($_GET['action']) && $_GET['action'] === 'livrer' && isset($_GET['id'])) {
    $id_commande = intval($_GET['id']);
    try {
        $updateStatus = $db->prepare('UPDATE commandes SET statut = "Livrée" WHERE id = ?');
        $updateStatus->execute([$id_commande]);
        $message = sprintf(__('msg_delivery_success'), $id_commande); $status = "success";
    } catch (Exception $e) { $message = __('msg_db_error') . $e->getMessage(); $status = "error"; }
}

// [ACTION : ANNULER LA COMMANDE]
if (isset($_GET['action']) && $_GET['action'] === 'annuler' && isset($_GET['id'])) {
    $id_commande = intval($_GET['id']);
    try {
        $updateStatus = $db->prepare('UPDATE commandes SET statut = "Annulée" WHERE id = ?');
        $updateStatus->execute([$id_commande]);
        $message = sprintf(__('msg_cancel_success'), $id_commande); $status = "success";
    } catch (Exception $e) { $message = __('msg_db_error') . $e->getMessage(); $status = "error"; }
}

// Données Sidebar & Pagination
$countAttente = $db->query("SELECT COUNT(*) FROM commandes WHERE statut IS NULL OR statut = 'En attente' OR statut = ''")->fetchColumn();
$countLivrees = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'Livrée'")->fetchColumn();
$countRefusees = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'Annulée' OR statut = 'Refusée'")->fetchColumn();

$totalCommandes = $db->query("SELECT COUNT(*) FROM commandes")->fetchColumn(); 
$commandesParPage = 10; $totalPages = ceil($totalCommandes / $commandesParPage);
$pageActuelle = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($pageActuelle < 1) { $pageActuelle = 1; }
if ($pageActuelle > $totalPages && $totalPages > 0) { $pageActuelle = $totalPages; }
$offset = ($pageActuelle - 1) * $commandesParPage;

$commandesReq = $db->prepare('SELECT * FROM commandes ORDER BY id DESC LIMIT :limite OFFSET :offset');
$commandesReq->bindValue(':limite', $commandesParPage, PDO::PARAM_INT);
$commandesReq->bindValue(':offset', $offset, PDO::PARAM_INT);
$commandesReq->execute();
$toutesLesCommandes = $commandesReq->fetchAll();


// ==========================================
//  EXTRACTION DES DONNÉES POUR LES GRAPHES
// ==========================================
// 1. Récupération des 7 derniers jours d'activité ayant des commandes
$datesReq = $db->query("SELECT DISTINCT DATE(date_commande) as date_pure FROM commandes ORDER BY date_pure DESC LIMIT 7");
$datesBrutes = array_reverse($datesReq->fetchAll(PDO::FETCH_COLUMN));

$labelsDates = [];
$dataCommandesEnCours = [];
$dataCommandes = []; // Livrées
$dataCommandesAnnulees = [];

$dataMontantsLivrees = [];
$dataMontantsEnCours = [];
$dataMontantsAnnulees = [];

foreach ($datesBrutes as $date) {
    $labelsDates[] = date('d/m', strtotime($date));
    
    // Stats Livrées (Volume & CA)
    $stmt = $db->prepare("SELECT COUNT(*), IFNULL(SUM(total), 0) FROM commandes WHERE DATE(date_commande) = ? AND statut = 'Livrée'");
    $stmt->execute([$date]);
    $res = $stmt->fetch();
    $dataCommandes[] = (int)$res[0];
    $dataMontantsLivrees[] = (float)$res[1];

    // Stats En Cours / En Attente (Volume & CA)
    $stmt = $db->prepare("SELECT COUNT(*), IFNULL(SUM(total), 0) FROM commandes WHERE DATE(date_commande) = ? AND (statut IS NULL OR statut = 'En attente' OR statut = '')");
    $stmt->execute([$date]);
    $res = $stmt->fetch();
    $dataCommandesEnCours[] = (int)$res[0];
    $dataMontantsEnCours[] = (float)$res[1];

    // Stats Annulées / Refusées (Volume & CA)
    $stmt = $db->prepare("SELECT COUNT(*), IFNULL(SUM(total), 0) FROM commandes WHERE DATE(date_commande) = ? AND (statut = 'Annulée' OR statut = 'Refusée')");
    $stmt->execute([$date]);
    $res = $stmt->fetch();
    $dataCommandesAnnulees[] = (int)$res[0];
    $dataMontantsAnnulees[] = (float)$res[1];
}
?>
<!DOCTYPE html>
<html lang="<?= $langue_actuelle ?>">
<head>
    <meta charset="UTF-8">
    <title><?= __('dashboard_title') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex">

    <?php include('admin_sidebar.php'); ?>

    <main class="flex-1 p-8 overflow-y-auto space-y-12">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold"><?= __('orders_received') ?></h1>
                <p class="text-sm text-gray-400 mt-1">Suivi et validation des commandes passées.</p>
            </div>
            
            <div class="bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-globe text-pink-500 text-sm"></i>
                <form action="admin_commandes.php" method="POST" id="form-langue">
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

        <section class="bg-gray-950 p-6 rounded-xl border border-gray-800 shadow-xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-900/60 p-4 rounded-lg border border-gray-800/80">
                    <h3 class="text-sm font-semibold text-gray-400 mb-4 uppercase tracking-wider">Chiffre d'affaires dynamique (FCFA)</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                <div class="bg-gray-900/60 p-4 rounded-lg border border-gray-800/80">
                    <h3 class="text-sm font-semibold text-gray-400 mb-4 uppercase tracking-wider">Statut et Volume des commandes</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="volumeChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-gray-950 p-6 rounded-xl border border-gray-800 shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 text-xs uppercase tracking-wider">
                            <th class="pb-3 font-semibold"># ID</th>
                            <th class="pb-3 font-semibold"><?= __('th_client') ?></th>
                            <th class="pb-3 font-semibold"><?= __('th_contact') ?></th>
                            <th class="pb-3 font-semibold"><?= __('th_ordered') ?></th>
                            <th class="pb-3 font-semibold"><?= __('th_total') ?></th>
                            <th class="pb-3 font-semibold"><?= __('th_date') ?></th>
                            <th class="pb-3 font-semibold"><?= __('th_status') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50 text-sm">
                        <?php if(empty($toutesLesCommandes)): ?>
                            <tr><td colspan="7" class="py-4 text-center text-gray-500 italic"><?= __('no_order') ?></td></tr>
                        <?php else: ?>
                            <?php foreach($toutesLesCommandes as $cmd): ?>
                                <tr>
                                    <td class="py-4 text-gray-400 font-mono">#<?= $cmd['id'] ?></td>
                                    <td class="py-4 font-semibold text-gray-200"><?= htmlspecialchars($cmd['nom_client']) ?></td>
                                    <td class="py-4 text-xs text-gray-400"><?= htmlspecialchars($cmd['telephone']) ?><br><span class="text-gray-500"><?= htmlspecialchars($cmd['ville']) ?></span></td>
                                    <td class="py-4 text-gray-300 text-xs max-w-xs truncate"><?= htmlspecialchars($cmd['articles']) ?></td>
                                    <td class="py-4 font-bold text-emerald-400"><?= number_format($cmd['total'], 0, '.', ' ') ?> FCFA</td>
                                    <td class="py-4 text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                                    <td class="py-4">
                                        <?php if(empty($cmd['statut']) || $cmd['statut'] === 'En attente'): ?>
                                            <div class="flex flex-col sm:flex-row gap-1.5">
                                                <a href="admin_commandes.php?action=livrer&id=<?= $cmd['id'] ?>" onclick="return confirm('<?= __('confirm_delivery') ?>')" class="inline-flex items-center justify-center gap-1.5 bg-yellow-950/80 hover:bg-yellow-900 border border-yellow-800/60 text-yellow-400 text-xs font-semibold px-2.5 py-1.5 rounded transition">
                                                    <?= __('to_process') ?>
                                                </a>
                                                <a href="admin_commandes.php?action=annuler&id=<?= $cmd['id'] ?>" onclick="return confirm('<?= __('confirm_cancel') ?>')" class="inline-flex items-center justify-center gap-1.5 bg-red-950/40 hover:bg-red-900/60 border border-red-900/60 text-red-400 text-xs font-semibold px-2 py-1.5 rounded transition">
                                                    ✕ Annuler
                                                </a>
                                            </div>
                                        <?php elseif($cmd['statut'] === 'Livrée'): ?>
                                            <span class="inline-flex items-center gap-1 bg-green-950 text-green-400 border border-green-900 text-xs px-2.5 py-1 rounded-full font-medium">✓ <?= __('delivered') ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 bg-red-950 text-red-400 border border-red-900 text-xs px-2.5 py-1 rounded-full font-medium">✕ <?= htmlspecialchars($cmd['statut']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($totalPages > 1): ?>
                <div class="flex justify-between items-center pt-6 border-t border-gray-800 text-xs mt-4">
                    <span class="text-gray-400"><?= sprintf(__('page_of'), $pageActuelle, $totalPages) ?></span>
                    <div class="inline-flex gap-1">
                        <a href="admin_commandes.php?page=<?= $pageActuelle - 1 ?>" class="px-3 py-1.5 rounded bg-gray-900 text-gray-400 border border-gray-800 hover:text-white transition <?= $pageActuelle <= 1 ? 'pointer-events-none opacity-40' : '' ?>">Précédent</a>
                        <a href="admin_commandes.php?page=<?= $pageActuelle + 1 ?>" class="px-3 py-1.5 rounded bg-gray-900 text-gray-400 border border-gray-800 hover:text-white transition <?= $pageActuelle >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>">Suivant</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        const labelsDates = <?= json_encode($labelsDates) ?>;
        
        const dataEnCours = <?= json_encode($dataCommandesEnCours) ?>;
        const dataLivrees = <?= json_encode($dataCommandes) ?>;
        const dataAnnulees = <?= json_encode($dataCommandesAnnulees) ?>;
        
        // Distribution du Chiffre d'Affaires filtré par Statut
        const caLivrees = <?= json_encode($dataMontantsLivrees) ?>; 
        const caEnCours = <?= json_encode($dataMontantsEnCours) ?>;
        const caAnnulees = <?= json_encode($dataMontantsAnnulees) ?>;

        // Init Graphe Chiffre d'Affaires (Pink line)
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: labelsDates,
                datasets: [{
                    label: 'CA Validé (FCFA)',
                    data: [...caLivrees], // Par défaut au chargement : Uniquement les ventes livrées
                    borderColor: '#ec4899',
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { y: { grid: { color: 'rgba(75, 85, 99, 0.1)' } } }
            }
        });

        // Init Graphe Volume Multi-statuts Interactif
        const ctxVolume = document.getElementById('volumeChart').getContext('2d');
        const volumeChart = new Chart(ctxVolume, {
            type: 'bar',
            data: {
                labels: labelsDates,
                datasets: [
                    {
                        label: 'En cours',
                        data: dataEnCours,
                        backgroundColor: '#eab308',
                        borderRadius: 4,
                        id: 'en_cours'
                    },
                    {
                        label: 'Livrées',
                        data: dataLivrees,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                        id: 'livrees'
                    },
                    {
                        label: 'Annulées',
                        data: dataAnnulees,
                        backgroundColor: '#ef4444',
                        borderRadius: 4,
                        id: 'annulees'
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#9ca3af' },
                        onClick: function(e, legendItem, legend) {
                            const index = legendItem.datasetIndex;
                            const ci = legend.chart;
                            
                            // Masquer ou afficher le statut cliqué
                            if (ci.isDatasetVisible(index)) {
                                ci.hide(index);
                                legendItem.hidden = true;
                            } else {
                                ci.show(index);
                                legendItem.hidden = false;
                            }
                            
                            // Déclencher le recalcul global du CA instantanément
                            recalculerCA(ci);
                        }
                    }
                },
                scales: {
                    x: { stacked: false },
                    y: { stacked: false, grid: { color: 'rgba(75, 85, 99, 0.1)' } }
                }
            }
        });

        // Fonction maîtresse d'interactivité : lie le CA aux statuts visibles
        function recalculerCA(chartVolume) {
            let nouveauCA = new Array(labelsDates.length).fill(0);
            let labelsActifs = [];

            chartVolume.data.datasets.forEach((dataset, index) => {
                if (chartVolume.isDatasetVisible(index)) {
                    if (dataset.id === 'livrees') {
                        nouveauCA = nouveauCA.map((val, i) => val + caLivrees[i]);
                        labelsActifs.push('Validé');
                    } else if (dataset.id === 'en_cours') {
                        nouveauCA = nouveauCA.map((val, i) => val + caEnCours[i]);
                        labelsActifs.push('En cours');
                    } else if (dataset.id === 'annulees') {
                        nouveauCA = nouveauCA.map((val, i) => val + caAnnulees[i]);
                        labelsActifs.push('Annulé');
                    }
                }
            });
            
            // Met à jour dynamiquement l'étiquette et la courbe du CA
            salesChart.data.datasets[0].label = 'CA Sélectionné (' + (labelsActifs.length > 0 ? labelsActifs.join(' + ') : 'Aucun') + ')';
            salesChart.data.datasets[0].data = nouveauCA;
            salesChart.update();
        }
    </script>
</body>
</html>