<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. CONNEXION À LA BASE DE DONNÉES
$host = 'localhost'; $dbname = 'stockmaster'; $username = 'root'; $password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification de connexion
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }

    // L'email sert de référence fixe pour la mise à jour
    $user_email = $_SESSION['user']; 
    $success = "";
    $error = "";

    // 2. TRAITEMENT DU FORMULAIRE
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nouveau_nom = htmlspecialchars($_POST['nom_user']);
        $nom_image = $_SESSION['profil_image'] ?? 'default_user.png';

        try {
            // Gestion de l'upload de l'image
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $upload_dir = 'uploads/profils/';
                if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

                $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $nom_image = 'img_' . time() . '.' . $extension;
                $destination = $upload_dir . $nom_image;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                    // Supprimer l'ancienne image si ce n'est pas celle par défaut
                    if (!empty($_SESSION['profil_image']) && $_SESSION['profil_image'] !== 'default_user.png') {
                        @unlink($upload_dir . $_SESSION['profil_image']);
                    }
                }
            }

            // --- LA REQUÊTE CRUCIALE ---
            // On met à jour Nom_user et profil_image là où l'email correspond
            $sql = "UPDATE users SET Nom_user = :nom, profil_image = :img WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom'   => $nouveau_nom,
                ':img'   => $nom_image,
                ':email' => $user_email
            ]);

            // 3. MISE À JOUR DE LA SESSION (Pour effet immédiat sur le dashboard)
            $_SESSION['Nom_user'] = $nouveau_nom;
            $_SESSION['profil_image'] = $nom_image;

            $success = "Profil mis à jour avec succès dans la base de données !";

        } catch (PDOException $e) {
            $error = "Erreur SQL : " . $e->getMessage();
        }
    }

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - StockMaster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #0284c7; --bg-body: #f8fafc; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-body); display: flex; }

        /* Sidebar Style */
        .sidebar { width: 260px; background: var(--primary); color: white; min-height: 100vh; position: fixed; padding: 20px; }
        .sidebar-user { text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px; }
        .sidebar a { display: flex; align-items: center; color: white; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; margin-bottom: 5px; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); }

        /* Main Content */
        .main { margin-left: 260px; padding: 40px; width: calc(100% - 260px); display: flex; justify-content: center; }
        .profile-container { background: white; width: 100%; max-width: 500px; padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        
        .profile-header { text-align: center; margin-bottom: 30px; }
        .current-avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); padding: 3px; margin-bottom: 15px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.9rem; font-weight: 600; color: #475569; margin-bottom: 8px; }
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 1rem; outline: none; transition: 0.3s; box-sizing: border-box; }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1); }
        
        .btn-update { background: var(--primary); color: white; border: none; padding: 14px; width: 100%; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 1rem; }
        .btn-update:hover { background: #0369a1; transform: translateY(-2px); }
        
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-user">
        <?php if(!empty($_SESSION['profil_image'])): ?>
            <img src="uploads/profils/<?php echo $_SESSION['profil_image']; ?>" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 10px; object-fit: cover; border: 2px solid white;">
        <?php else: ?>
            <i class="fas fa-user-circle" style="font-size: 50px; margin-bottom: 10px;"></i>
        <?php endif; ?>
        <h3 style="font-size: 14px; margin: 0;"><?php echo htmlspecialchars($_SESSION['Nom_user'] ?? 'Utilisateur'); ?></h3>
    </div>
    <a href="dashboard.php"><i class="fas fa-arrow-left" style="margin-right:10px;"></i> Retour Dashboard</a>
</div>

<div class="main">
    <div class="profile-container">
        <div class="profile-header">
            <?php if(!empty($_SESSION['profil_image'])): ?>
                <img src="uploads/profils/<?php echo $_SESSION['profil_image']; ?>" class="current-avatar">
            <?php else: ?>
                <i class="fas fa-user-circle" style="font-size: 80px; color: #cbd5e1; margin-bottom: 15px;"></i>
            <?php endif; ?>
            <h2 style="margin:0;">Paramètres du Profil</h2>
            <p style="color: #64748b; font-size: 0.9rem;">Identifié en tant que : <strong><?php echo htmlspecialchars($user_email); ?></strong></p>
        </div>

        <?php if($success): ?> <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div> <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nom d'affichage</label>
                <input type="text" name="nom_user" value="<?php echo htmlspecialchars($_SESSION['Nom_user'] ?? ''); ?>" required placeholder="Votre nom complet">
            </div>

            <div class="form-group">
                <label>Photo de profil</label>
                <input type="file" name="photo" accept="image/*">
                <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 5px;">Format : JPG, PNG ou GIF.</p>
            </div>

            <button type="submit" class="btn-update">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

</body>
</html>