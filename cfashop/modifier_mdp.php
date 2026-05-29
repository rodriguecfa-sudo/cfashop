<?php
ob_start();
session_start();

if (!isset($_SESSION['recup_email'])) {
    header("Location: connexion.php");
    exit();
}

$host = 'localhost';
$db   = 'cfashop';
$user = 'root';
$pass = '';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['changement_mdp'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $nouveau_mdp = $_POST['nouveau_mdp'];
        $conf_mdp = $_POST['conf_mdp'];
        $email = $_SESSION['recup_email'];

        if ($nouveau_mdp === $conf_mdp) {
            $mdp_hache = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE clients SET mot_de_passe = ? WHERE email = ?");
            $stmt->execute([$mdp_hache, $email]);
            
            unset($_SESSION['recup_email']); // Nettoyage de la session de secours
            
            header("Location: connexion.php?status=reset_success");
            exit();
        } else {
            $message = "Les mots de passe ne correspondent pas.";
            $message_type = "danger";
        }
    } catch (PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe | CFA SHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('R.JPG'); background-size: cover; min-height: 100vh; font-family: sans-serif; }
        .card-custom { border-radius: 0; box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
        .btn-black { background: #000; color: #fff; border-radius: 0; padding: 12px; font-weight: bold; }
        .btn-black:hover { background: #333; color: #fff; }
        .form-control { border-radius: 0; padding: 12px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-custom bg-white p-4 p-md-5">
                    <h4 class="fw-bold text-center mb-4 text-uppercase tracking-tight">Nouveau Mot de Passe</h4>
                    
                    <?php if(!empty($message)): ?>
                        <div class="alert alert-<?= $message_type ?> small"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">NOUVEAU MOT DE PASSE</label>
                            <input type="password" name="nouveau_mdp" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">CONFIRMER LE MOT DE PASSE</label>
                            <input type="password" name="conf_mdp" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="changement_mdp" class="btn btn-black text-uppercase">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>