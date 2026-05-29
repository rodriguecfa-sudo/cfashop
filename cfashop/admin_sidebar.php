<?php
// On récupère le nom de la page actuelle pour gérer l'état "actif" dans le menu
$page_courante = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-gray-950 border-r border-gray-800 flex flex-col justify-between shrink-0">
    <div class="p-6">
        <div class="flex items-center gap-3 mb-8">
            <span class="text-2xl font-black tracking-wider text-pink-500">CFA SHOP</span>
            <span class="bg-gray-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded text-gray-400">Admin</span>
        </div>
        
        <nav class="space-y-1">
            <a href="admin_stats.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm mb-3 border <?= $page_courante == 'admin_stats.php' ? 'text-white bg-pink-950/30 border-pink-500/40 font-bold' : 'text-gray-400 border-dashed border-gray-800 hover:border-pink-500/40 hover:text-white' ?> transition">
                <i class="fa-solid fa-chart-line text-lg w-6 text-center text-pink-500"></i> <?= __('stats') ?>
            </a>

            <a href="admin_catalogue.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition <?= $page_courante == 'admin_catalogue.php' ? 'text-white font-bold bg-gray-900 border border-gray-800' : 'text-gray-400 hover:bg-gray-900/50 hover:text-white' ?>">
                <i class="fa-solid fa-boxes-stacked text-lg w-6 text-center text-pink-500"></i> <?= __('catalogue') ?>
            </a>

            <?php if($page_courante == 'admin_catalogue.php'): ?>
            <div class="space-y-0.5 pl-6 pt-1">
                <a href="admin_catalogue.php?cat=homme" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium <?= $categorie_selectionnee === 'homme' ? 'text-white bg-pink-950/30 font-bold border border-pink-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-900' ?> transition">
                    <span>♂ <?= __('man') ?></span>
                    <span class="bg-gray-900 text-gray-400 px-1.5 py-0.5 rounded text-[10px] border border-gray-800"><?= $countHomme ?></span>
                </a>
                <a href="admin_catalogue.php?cat=femme" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium <?= $categorie_selectionnee === 'femme' ? 'text-white bg-pink-950/30 font-bold border border-pink-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-900' ?> transition">
                    <span>♀ <?= __('woman') ?></span>
                    <span class="bg-gray-900 text-gray-400 px-1.5 py-0.5 rounded text-[10px] border border-gray-800"><?= $countFemme ?></span>
                </a>
                <a href="admin_catalogue.php?cat=enfants" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium <?= $categorie_selectionnee === 'enfants' ? 'text-white bg-pink-950/30 font-bold border border-pink-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-900' ?> transition">
                    <span>👶 <?= __('children') ?></span>
                    <span class="bg-gray-900 text-gray-400 px-1.5 py-0.5 rounded text-[10px] border border-gray-800"><?= $countEnfants ?></span>
                </a>
                <a href="admin_catalogue.php?cat=accessoires" class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs font-medium <?= $categorie_selectionnee === 'accessoires' ? 'text-white bg-pink-950/30 font-bold border border-pink-900/50' : 'text-gray-400 hover:text-white hover:bg-gray-900' ?> transition">
                    <span>🎒 <?= __('accessories') ?></span>
                    <span class="bg-gray-900 text-gray-400 px-1.5 py-0.5 rounded text-[10px] border border-gray-800"><?= $countAccessoires ?></span>
                </a>
            </div>
            <?php endif; ?>
            
            <div class="pt-4 pb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-gray-500"><?= __('orders_mgmt') ?></div>

            <a href="admin_commandes.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm transition <?= $page_courante == 'admin_commandes.php' ? 'text-white bg-gray-900 font-bold' : 'text-gray-400 hover:bg-gray-900 hover:text-white' ?>">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-gray-500 w-5 text-center"></i> <?= __('all_orders') ?>
                </div>
            </a>

            <a href="admin_commandes.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-gray-400 hover:bg-gray-900 hover:text-white transition text-sm pl-6">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock text-yellow-500"></i> <?= __('pending') ?>
                </div>
                <span class="bg-yellow-950 text-yellow-400 border border-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full"><?= $countAttente ?></span>
            </a>

            <a href="admin_commandes.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-gray-400 hover:bg-gray-900 hover:text-white transition text-sm pl-6">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-green-500"></i> <?= __('delivered') ?>
                </div>
                <span class="bg-green-950 text-green-400 border border-green-900 text-xs font-bold px-2 py-0.5 rounded-full"><?= $countLivrees ?></span>
            </a>

            <a href="admin_commandes.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-gray-400 hover:bg-gray-900 hover:text-white transition text-sm pl-6">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-xmark text-red-500"></i> <?= __('refused') ?>
                </div>
                <span class="bg-red-950 text-red-400 border border-red-900 text-xs font-bold px-2 py-0.5 rounded-full"><?= $countRefusees ?></span>
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