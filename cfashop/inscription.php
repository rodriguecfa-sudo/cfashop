<?php
ob_start();
session_start();

$host = 'localhost';
$db   = 'cfashop';
$user = 'root';
$pass = '';

$message = "";
$message_type = ""; // "danger" pour les erreurs, "success" pour la réussite

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Correct !
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement de l'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inscription'])) {
    $nom = trim($_POST['nom_complet']);
    $ville = trim($_POST['ville']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['telephone']);
    $mdp = $_POST['mdp'];
    $mdp_conf = $_POST['mdp_conf'];
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;

    // 1. Vérification que les mots de passe correspondent
    if ($mdp !== $mdp_conf) {
        $message = "Les deux mots de passe ne correspondent pas.";
        $message_type = "danger";
    } else {
        // 2. Vérification si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $message = "Cette adresse email est déjà associée à un compte.";
            $message_type = "danger";
        } else {
            // 3. Hachage du mot de passe pour la sécurité
            $mdp_hache = password_hash($mdp, PASSWORD_DEFAULT);

           // 4. Insertion du nouveau client
try {
    $sql = "INSERT INTO clients (nom_complet, ville, email, telephone, mot_de_passe, newsletter) VALUES (?, ?, ?, ?, ?, ?)";
    $insert = $pdo->prepare($sql);
    $insert->execute([$nom, $ville, $email, $phone, $mdp_hache, $newsletter]);

    // --- ICI ON AJOUTE LA CONNEXION AUTOMATIQUE ---
    // 1. On récupère l'ID généré pour ce nouveau client
    $client_id = $pdo->lastInsertId();

    // 2. On enregistre ses informations dans la Session
    $_SESSION['client_id'] = $client_id;
    $_SESSION['client_nom'] = $nom;
    $_SESSION['client_email'] = $email;

    // 3. Redirection instantanée vers l'accueil
    header("Location: acceuilcfa.php");
    exit();

} catch (PDOException $e) {
    $message = "Une erreur est survenue lors de l'inscription : " . $e->getMessage();
    $message_type = "danger";
} {
                $message = "Une erreur est survenue lors de l'inscription.";
                $message_type = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | CFA SHOP</title>
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
        .card-registration { border-radius: 0; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-black { background-color: #000; color: #fff; border-radius: 0; padding: 12px; font-weight: bold; letter-spacing: 1px; }
        .btn-black:hover { background-color: #333; color: #fff; }
        .form-control { border-radius: 0; padding: 12px; border: 1px solid #ddd; }
        .form-control:focus { border-color: #000; box-shadow: none; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card card-registration bg-white p-4 p-md-5">
                
                <div class="text-center mb-4">
                    <h2 class="fw-bold tracking-tighter">CFA SHOP</h2>
                    <img src="5d12ec0a5ff4272f4bdb42613a98bbbd.jpg" alt="Logo CFA SHOP" width="40" height="40" class="d-inline-block align-top me-2">
                    <p class="text-muted small uppercase">Créez votre compte client</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message); ?>
                        <button type="submit" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">NOM COMPLET</label>
                        <input type="text" name="nom_complet" class="form-control" placeholder="Ex: Rodrigue Design" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">VILLE</label>
                        <input type="text" name="ville" class="form-control" placeholder="Ex: BAFOUSSAM" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">ADRESSE EMAIL</label>
                        <input type="email" name="email" class="form-control" placeholder="nom@exemple.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">NUMERO DE TELEPHONE</label>
                        <input type="text" name="telephone" class="form-control" placeholder="6XXXXXXXX" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">MOT DE PASSE</label>
                        <div class="input-group">
                            <input type="password" name="mdp" class="form-control" id="passwordMain" placeholder="••••••••" required>
                            <span class="input-group-text bg-white border-start-0" style="cursor: pointer;" onclick="togglePassword('passwordMain', this)">
                                <i class="fa-solid fa-eye-slash text-muted"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">CONFIRMER MOT DE PASSE</label>
                        <div class="input-group">
                            <input type="password" name="mdp_conf" class="form-control" id="passwordConfirm" placeholder="••••••••" required>
                            <span class="input-group-text bg-white border-start-0" style="cursor: pointer;" onclick="togglePassword('passwordConfirm', this)">
                                <i class="fa-solid fa-eye-slash text-muted"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="newsletter" class="form-check-input" id="newsletter">
                        <label class="form-check-label small text-muted" for="newsletter">
                            Je souhaite recevoir les offres exclusives de CFA SHOP.
                        </label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="inscription" class="btn btn-black rounded-5 text-uppercase">S'inscrire</button>
                    </div>
                </form>

                <div class="position-relative my-4">
                    <hr>
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small italic">OU</span>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <a href="#" class="btn btn-outline-dark w-100 rounded-5 p-2">
                            <i class="fa-brands fa-google me-2"></i> Google
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#" class="btn btn-outline-dark w-100 rounded-5 p-2">
                            <i class="fa-brands fa-facebook me-2"></i> Facebook
                        </a>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="small mb-0">Déjà inscrit ? <a href="connexion.php" class="text-black fw-bold">Se connecter</a></p>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Petit script bonus pour faire fonctionner le bouton afficher/masquer le mot de passe !
    function togglePassword(inputId, iconContainer) {
        const input = document.getElementById(inputId);
        const icon = iconContainer.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>
</body>
</html>