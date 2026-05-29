<?php
session_start();

// Si l'admin est déjà connecté, on le redirige directement vers le dashboard
if (isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true) {
    header('Location: admin.php');
    exit();
}

// Connexion BDD
try {
    $db = new PDO('mysql:host=localhost;dbname=cfashop;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {
    $identifiant = htmlspecialchars(trim($_POST['identifiant']));
    $mot_depasse = trim($_POST['mot_depasse']);

    if (!empty($identifiant) && !empty($mot_depasse)) {
        // Rechercher l'admin dans la base
        $req = $db->prepare('SELECT * FROM admin_users WHERE identifiant = ?');
        $req->execute([$identifiant]);
        $admin = $req->fetch();

        // Vérification du mot de passe haché
        if ($admin && password_verify($mot_depasse, $admin['mot_depasse'])) {
            // Initialisation des variables de session admin
            $_SESSION['admin_connecte'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_identifiant'] = $admin['identifiant'];

            header('Location: admin.php');
            exit();
        } else {
            $erreur = "Identifiant ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CFA SHOP | Connexion Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center p-4 transition-colors duration-300">

    <div class="max-w-md w-full bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 p-8 rounded-2xl shadow-xl">
        
        <div class="text-center mb-8">
            <span class="text-3xl font-black tracking-wider text-pink-500">CFA SHOP</span>
            <h1 class="text-xl font-bold mt-3 text-gray-800 dark:text-gray-200">Espace Administrateur</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Connectez-vous pour gérer votre catalogue et vos commandes.</p>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="p-4 mb-4 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/60 text-sm flex items-center gap-2 font-medium">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= $erreur; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs uppercase font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-2">Identifiant</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="identifiant" required placeholder="Ex: admin" 
                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl pl-11 pr-4 py-3 text-sm focus:outline-none focus:border-pink-500 dark:focus:border-pink-500 transition text-gray-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs uppercase font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-2">Mot de passe</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="mot_depasse" required placeholder="••••••••" 
                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl pl-11 pr-4 py-3 text-sm focus:outline-none focus:border-pink-500 dark:focus:border-pink-500 transition text-gray-900 dark:text-white">
                </div>
            </div>

            <button type="submit" name="connexion" 
                    class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-xl transition uppercase tracking-wider text-xs shadow-lg shadow-pink-900/20 mt-2 flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Se connecter
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="index.php" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Retourner sur le site client
            </a>
        </div>
    </div>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</body>
</html>