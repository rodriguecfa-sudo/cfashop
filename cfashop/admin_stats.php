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

// Inclure vos traductions ici de la même manière
$translations = [ 'fr' => [ 'dashboard_title' => "CFA SHOP | Stats", 'stats' => "Statistiques", 'catalogue' => "Catalogue Articles", 'orders_mgmt' => "Gestion Commandes", 'all_orders' => "Toutes les commandes", 'pending' => "En attente", 'delivered' => "Livrées", 'refused' => "Refusées", 'view_site' => "Voir le site client", 'logout' => "Se déconnecter", 'sales_stats' => "Analyse des Ventes" ], 'en' => [ 'dashboard_title' => "CFA SHOP | Stats", 'stats' => "Statistics", 'catalogue' => "Items Catalog", 'orders_mgmt' => "Orders Management", 'all_orders' => "All orders", 'pending' => "Pending", 'delivered' => "Delivered", 'refused' => "Refused", 'view_site' => "View client site", 'logout' => "Logout", 'sales_stats' => "Sales Analytics" ] ];

function __($key) { global $translations, $langue_actuelle; return isset($translations[$langue_actuelle][$key]) ? $translations[$langue_actuelle][$key] : $key; }

try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) { die('Erreur : ' . $e->getMessage()); }

// Comptages rapides pour la sidebar
$countAttente = $db->query("SELECT COUNT(*) FROM commandes WHERE statut IS NULL OR statut = 'En attente' OR statut = ''")->fetchColumn();
$countLivrees = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'Livrée'")->fetchColumn();
$countRefusees = $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'Annulée' OR statut = 'Refusée'")->fetchColumn();

// Requête de statistiques (Ici j'ai corrigé 'montant_total' par 'total' suite à votre précédente erreur)
$statsQuery = $db->query("SELECT DATE(date_commande) as date_vente, SUM(total) as total_jour, COUNT(id) as nb_commandes FROM commandes GROUP BY DATE(date_commande) ORDER BY date_vente ASC LIMIT 30");
$statsData = $statsQuery->fetchAll(PDO::FETCH_ASSOC);

$labelsDates = []; $dataMontants = []; $dataCommandes = [];
foreach($statsData as $row) {
    $labelsDates[] = date('d M', strtotime($row['date_vente']));
    $dataMontants[] = (float)$row['total_jour'];
    $dataCommandes[] = (int)$row['nb_commandes'];
}
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

    <?php include('admin_sidebar.php'); ?>

    <main class="flex-1 p-8 overflow-y-auto space-y-12">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold"><?= __('sales_stats') ?></h1>
                <p class="text-sm text-gray-400 mt-1">Consultez l'activité de votre boutique en temps réel.</p>
            </div>
            
            <div class="bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 flex items-center gap-2 shadow-md">
                <i class="fa-solid fa-globe text-pink-500 text-sm"></i>
                <form action="admin_stats.php" method="POST" id="form-langue">
                    <input type="hidden" name="changer_langue" value="1">
                    <select name="langue" onchange="document.getElementById('form-langue').submit();" class="bg-transparent text-sm font-semibold text-gray-300 focus:outline-none cursor-pointer pr-2">
                        <option value="fr" <?= $langue_actuelle === 'fr' ? 'selected' : '' ?>>Français (FR)</option>
                        <option value="en" <?= $langue_actuelle === 'en' ? 'selected' : '' ?>>English (EN)</option>
                    </select>
                </form>
            </div>
        </header>

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

<script>
    // --- 1. PRÉPARATION DES DONNÉES PHP ---
    const labelsDates = <?= json_encode($labelsDates) ?>;
    
    // Données de volumes par statut
    const dataEnCours = <?= json_encode($dataCommandesEnCours ?? []) ?>;
    const dataLivrees = <?= json_encode($dataCommandes ?? []) ?>; // Tes commandes validées
    const dataAnnulees = <?= json_encode($dataCommandesAnnulees ?? []) ?>;
    
    // CA détaillé par statut (Ex: si tu as le détail en PHP, sinon on utilise des approximations)
    // Idéalement, prépare $dataMontantsLivrees en PHP pour n'avoir que le CA des ventes réussies
    const caLivrees = <?= json_encode($dataMontantsLivrees ?? $dataMontants) ?>; 
    const caEnCours = <?= json_encode($dataMontantsEnCours ?? array_fill(0, count($labelsDates), 0)) ?>;
    const caAnnulees = <?= json_encode($dataMontantsAnnulees ?? array_fill(0, count($labelsDates), 0)) ?>;

    // --- 2. INITIALISATION DU GRAPHIQUE CA (LINE) ---
    const ctxSales = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: labelsDates,
            datasets: [{
                label: 'CA Sélectionné',
                data: [...caLivrees], // Par défaut : uniquement le CA des commandes livrées
                borderColor: '#ec4899',
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: {
                y: { grid: { color: 'rgba(75, 85, 99, 0.1)' } }
            }
        }
    });

    // --- 3. INITIALISATION DU GRAPHIQUE VOLUME (BAR) INTERACTIF ---
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
                    // Fonction d'interactivité personnalisée lors du clic sur la légende
                    onClick: function(e, legendItem, legend) {
                        const index = legendItem.datasetIndex;
                        const ci = legend.chart;
                        
                        // Basculer la visibilité de la barre cliquée
                        if (ci.isDatasetVisible(index)) {
                            ci.hide(index);
                            legendItem.hidden = true;
                        } else {
                            ci.show(index);
                            legendItem.hidden = false;
                        }
                        
                        // Recalculer le Chiffre d'Affaires en fonction des statuts restants visibles
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

    // --- 4. LOGIQUE DE RECALCUL DYNAMIQUE ---
    function recalculerCA(chartVolume) {
        // Initialiser un tableau de zéros pour reconstruire le CA combiné
        let nouveauCA = new Array(labelsDates.length).fill(0);
        
        // Vérifier quels datasets sont visibles sur le graphique des volumes
        chartVolume.data.datasets.forEach((dataset, index) => {
            if (chartVolume.isDatasetVisible(index)) {
                // Ajouter le CA correspondant au statut visible
                if (dataset.id === 'livrees') {
                    nouveauCA = nouveauCA.map((val, i) => val + caLivrees[i]);
                } else if (dataset.id === 'en_cours') {
                    nouveauCA = nouveauCA.map((val, i) => val + caEnCours[i]);
                } else if (dataset.id === 'annulees') {
                    nouveauCA = nouveauCA.map((val, i) => val + caAnnulees[i]);
                }
            }
        });
        
        // Mettre à jour les données du graphique CA et rafraîchir l'affichage
        salesChart.data.datasets[0].data = nouveauCA;
        salesChart.update();
    }
</script>
</body>
</html>