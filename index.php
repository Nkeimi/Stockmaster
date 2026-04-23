<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- 1. CONFIGURATION CONNEXION ---
$host = 'localhost';
$dbname = 'stockmaster';
$username = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";
$messageType = "";

// Gestion du mode (Connexion ou Inscription)
if (isset($_GET['mode'])) {
    $_SESSION['auth_mode'] = $_GET['mode'];
}
$mode = $_SESSION['auth_mode'] ?? 'register';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // --- CAS A : INSCRIPTION ---
    if (isset($_POST['auth_action']) && $_POST['auth_action'] === 'register') {
        
        $nom_utilisateur = htmlspecialchars($_POST['Nom_user']);
        $secteur = htmlspecialchars($_POST['secteur'] ?? 'stagiere'); 
        $image_name = "default_user.png";

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $upload_dir = 'uploads/profils/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $image_name);
        }

        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            $message = "⚠️ Cet email existe déjà.";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare("INSERT INTO users (email, password, secteur, profil_image, Nom_user) VALUES (?, ?, ?, ?, ?)");
            
            if ($insert->execute([$email, $hashedPassword, $secteur, $image_name, $nom_utilisateur])) {
                // Initialisation session après inscription
                $_SESSION['user'] = $email;
                $_SESSION['Nom_user'] = $nom_utilisateur;
                $_SESSION['profil_image'] = $image_name;
                $_SESSION['role'] = $secteur;
                header('Location: dashboard.php');
                exit();
            }
        }
    } 
    
    // --- CAS B : CONNEXION (C'est ici que les mises à jour sont récupérées) ---
    else if (isset($_POST['auth_action']) && $_POST['auth_action'] === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe haché
        if ($user && password_verify($password, $user['password'])) {
            
            // RÉCUPÉRATION DES DONNÉES À JOUR DEPUIS LA TABLE 'users'
            $_SESSION['user'] = $user['email'];
            $_SESSION['Nom_user'] = $user['Nom_user'];      // Le nom modifié dans profil.php
            $_SESSION['profil_image'] = $user['profil_image']; // La photo modifiée dans profil.php
            $_SESSION['role'] = $user['secteur'];            // Le rôle (chefss, employe, etc.)
            
            header('Location: dashboard.php');
            exit();
        } else {
            $message = "❌ Email ou mot de passe incorrect.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvenTrack - Gestion Multi-Profils</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* STYLE CONSERVÉ À 100% */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; height: 100vh; display: flex; background-color: #f9fafb; }
        .left-panel { flex: 1; background: linear-gradient(135deg, #0284c7 0%, #14b8a6 100%); color: white; padding: 4rem; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
        .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.5rem; }
        .logo-icon { background: rgba(255,255,255,0.2); padding: 8px; border-radius: 6px; }
        .hero-content { max-width: 600px; z-index: 2; }
        .hero-content h1 { font-size: 3.5rem; line-height: 1.1; margin-bottom: 1.5rem; font-weight: 700; }
        .hero-content p { font-size: 1.1rem; line-height: 1.6; opacity: 0.9; margin-bottom: 3rem; }
        .stats { display: flex; gap: 3rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 2rem; }
        .stat-item h3 { font-size: 1.5rem; font-weight: 700; }
        .stat-item span { font-size: 0.875rem; opacity: 0.8; }
        .copyright { font-size: 0.8rem; opacity: 0.6; }
        .bg-illustration { position: absolute; top: 10%; right: 10%; font-size: 20rem; color: white; opacity: 0.05; transform: rotate(-10deg); pointer-events: none; }
        .right-panel { flex: 1; background: white; display: flex; align-items: center; justify-content: center; padding: 2rem; overflow-y: auto; }
        .form-container { width: 100%; max-width: 450px; }
        .form-header h2 { font-size: 2rem; font-weight: 700; color: #111827; margin-top: 3.5em; }
        .form-header p { color: #6b7280; margin-bottom: 2rem; }
        .auth-toggle { background: #f3f4f6; padding: 4px; border-radius: 8px; display: flex; margin-bottom: 0.5rem; }
        .toggle-btn { flex: 1; padding: 10px; text-align: center; border: none; background: transparent; font-weight: 500; font-size: 0.9rem; color: #6b7280; cursor: pointer; border-radius: 6px; transition: all 0.2s; text-decoration: none; }
        .toggle-btn.active { background: white; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .input-group { margin-bottom: 1.2rem; }
        .input-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
        .input-wrapper input, .input-wrapper select { width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border-color 0.2s; font-family: inherit; background: white; }
        .btn-primary { width: 100%; padding: 14px; background-color: #0ea5e9; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .divider { display: flex; align-items: center; text-align: center; margin: 1.5rem 0; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #e5e7eb; }
        .divider::before { margin-right: 1rem; }
        .divider::after { margin-left: 1rem; }
        .btn-social { flex: 1; padding: 10px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500; color: #374151; cursor: pointer; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid transparent; }
        .alert.error { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; }
        @media (max-width: 900px) { body { flex-direction: column; height: auto; } .left-panel { padding: 3rem 2rem; min-height: 400px; } }
    </style>
</head>
<body>

    <div class="left-panel">
        <i class="fas fa-box bg-illustration"></i>
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-cubes"></i></div>
            <span>PGSEGIM🌍</span>
        </div>
        <div class="hero-content">
            <h1>Gérez votre inventaire avec précision.</h1>
            <p>La plateforme de Gestion d 'Entreprise de GIM multi-utilisateurs pour les entreprises modernes. Chaque membre dispose de ses propres accès.</p>
            <div class="stats">
                <div class="stat-item"><h3>10k+</h3><span>Entreprises</span></div>
                <div class="stat-item"><h3>99.9%</h3><span>Disponibilité</span></div>
                <div class="stat-item"><h3>24/7</h3><span>Support</span></div>
            </div>
        </div>
        <div class="copyright">&copy; 2026 InvenTrack Solutions Inc.</div>
    </div>

    <div class="right-panel">
        <div class="form-container">
            <div class="form-header">
                <h2><?php echo ($mode === 'login') ? 'Bon retour !' : 'Créer un profil'; ?></h2>
                <p><?php echo ($mode === 'login') ? 'Accédez à votre espace de travail personnel.' : "Rejoignez l'équipe GIM dès aujourd'hui."; ?></p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="auth-toggle">
                <a href="?mode=login" class="toggle-btn <?php echo ($mode === 'login') ? 'active' : ''; ?>">Connexion</a>
                <a href="?mode=register" class="toggle-btn <?php echo ($mode === 'register') ? 'active' : ''; ?>">Nouvel Utilisateur</a>
            </div>

            <form action="index.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="auth_action" value="<?php echo $mode; ?>">

                <?php if ($mode === 'register'): ?>
                    <div class="input-group">
                        <label>Nom d'Utilisateur</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="Nom_user" placeholder="Ex: Jean Dupont" required>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="input-group">
                    <label>Email professionnel</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="nom@entreprise.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <?php if ($mode === 'register'): ?>
                    <div class="input-group">
                        <label>Rôle / Fonction</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-tag"></i>
                            <select name="secteur">
                                <option value="employe">Employé</option>
                                <option value="stagiere">Stagiaire</option>
                                <option value="chefss">Administrateur / Chef Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Photo de profil</label>
                        <div class="input-wrapper">
                            <i class="fas fa-camera"></i>
                            <input type="file" name="photo" accept="image/*" style="padding-top: 10px;">
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-primary">
                    <span><?php echo ($mode === 'login') ? 'Se connecter au profil' : 'Créer le profil'; ?></span> 
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="divider">Ou continuer avec</div>
            <div class="social-buttons" style="display: flex; gap: 1rem;">
                <button class="btn-social"><i class="fab fa-google"></i> Google</button>
                <button class="btn-social"><i class="fas fa-key"></i> SSO</button>
            </div>
        </div>
    </div>
</body>
</html>