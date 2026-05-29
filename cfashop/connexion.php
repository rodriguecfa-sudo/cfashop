<?php
ob_start();
session_start();

if (isset($_SESSION['client_id'])) {
    header("Location: acceuilcfa.php");
    exit();
}

$host = 'localhost';
$db   = 'cfashop';
$user = 'root';
$pass = '';

$message = "";
$message_type = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Détection d'un retour réussi de modification de mot de passe
if (isset($_GET['status']) && $_GET['status'] === 'reset_success') {
    $message = "Votre mot de passe a bien été mis à jour. Vous pouvez vous connecter.";
    $message_type = "success";
}

// TRAITEMENT DEMANDE MOT DE PASSE OUBLIÉ (SIMULATION LOCALHOST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recuperation_mdp'])) {
    $email_recup = trim($_POST['email_recup']);
    
    if (!empty($email_recup)) {
        // On vérifie si cet email existe en BDD
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ?");
        $stmt->execute([$email_recup]);
        
        if ($stmt->fetch()) {
            // L'email existe, on prépare une session temporaire pour la page suivante
            $_SESSION['recup_email'] = $email_recup;
            
            // En local, au lieu d'envoyer un vrai mail, on génère un message avec un lien direct cliquable
            $message = "Simulateur Localhost : Un email de récupération a été simulé. <a href='modifier_mdp.php' class='fw-bold text-success'>[ Cliquez ici pour réinitialiser le mot de passe ]</a>";
            $message_type = "success";
        } else {
            $message = "Cette adresse email n'est associée à aucun compte client.";
            $message_type = "danger";
        }
    }
}

// Traitement du formulaire de connexion classique
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['connexion'])) {
    $email = trim($_POST['email']);
    $mdp = $_POST['mdp'];

    if (!empty($email) && !empty($mdp)) {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
        $stmt->execute([$email]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client && password_verify($mdp, $client['mot_de_passe'])) {
            $_SESSION['client_id'] = $client['id'];
            $_SESSION['client_nom'] = $client['nom_complet'];
            $_SESSION['client_email'] = $client['email'];
            
            header("Location: acceuilcfa.php");
            exit();
        } else {
            $message = "Adresse email ou mot de passe incorrect.";
            $message_type = "danger";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | CFA SHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('R.JPG');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        .login-card { border-radius: 0; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .btn-black { background-color: #000; color: #fff; border-radius: 0; padding: 12px; font-weight: bold; letter-spacing: 1px; border: none; }
        .btn-black:hover { background-color: #333; color: #fff; }
        .form-control { border-radius: 0; padding: 12px; border: 1px solid #eee; background-color: #fdfdfd; }
        .form-control:focus { border-color: #000; box-shadow: none; background-color: #fff; }
        .link-muted { color: #6c757d; text-decoration: none; font-size: 0.85rem; transition: 0.2s; }
        .link-muted:hover { color: #000; text-decoration: underline; }
        .modal-content { border-radius: 0; border: none; }
    </style>
</head>
<body>

<div class="container py-5 mt-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card login-card bg-white p-4 p-md-5">
                
                <div class="text-center mb-5">
                    <h2 class="fw-bold tracking-tighter mb-1">CFA SHOP</h2> 
                    <img src="5d12ec0a5ff4272f4bdb42613a98bbbd.jpg" alt="Logo CFA SHOP" width="80" height="80" class="d-inline-block align-top me-2">
                    <p class="text-muted small text-uppercase">Heureux de vous revoir</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show" role="alert">
                        <?= $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Adresse Email</label>
                        <input type="email" name="email" class="form-control" placeholder="votre@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label small fw-bold text-uppercase">Mot de passe</label>
                            <a href="#" class="link-muted italic" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Oublié ?</a>
                        </div>
                        <input type="password" name="mdp" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="rememberMe" class="form-check-input" id="rememberMe">
                        <label class="form-check-label small text-muted" for="rememberMe">
                            Rester connecté
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="connexion" class="btn btn-black rounded-5 text-uppercase">Se connecter</button>
                    </div>
                </form>

                <div class="position-relative my-4">
                    <hr class="text-muted opacity-25">
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small italic">OU</span>
                </div>

                <div class="d-flex flex-column gap-2">
                    <a href="#" class="btn btn-outline-secondary w-100 rounded-5 p-2 text-sm d-flex align-items-center justify-content-center">
                        <i class="fa-brands fa-google me-2 text-danger"></i> Continuer avec Google
                    </a>
                </div>

                <div class="text-center mt-5">
                    <p class="small mb-0 text-muted">Pas encore de compte ?</p>
                    <a href="inscription.php" class="text-black fw-bold text-decoration-none">CRÉER UN COMPTE</a>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade text-dark" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-uppercase tracking-tight" id="forgotPasswordModalLabel">Récupération</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p class="text-muted small">Saisissez l'adresse email liée à votre compte CFA SHOP. Nous allons vérifier sa validité.</p>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">ADRESSE EMAIL</label>
                        <input type="email" name="email_recup" class="form-control" placeholder="nom@exemple.com" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-sm btn-light rounded-0" data-bs-shadow="none" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="recuperation_mdp" class="btn btn-sm btn-dark rounded-0 px-3">Vérifier l'adresse</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>