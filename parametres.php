<?php
// 1. Initialisation de la session et sécurité
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sécurité : Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// On récupère le secteur stocké en session (pour l'affichage sidebar)
$user_role_session = $_SESSION['secteur'] ?? $_SESSION['role'] ?? 'stagiere';

// 2. Connexion à la base de données
include("config/database.php"); 

// --- LOGIQUE DE MISE À JOUR PRODUIT ---
if (isset($_POST['update_product'])) {
    $id = $_POST['id_prod'];
    $nom = $_POST['nom'];
    $prix = $_POST['prix'];
    $qte = $_POST['quantite'];

    $stmt = $conn->prepare("UPDATE produits SET nom=?, prix=?, quantite=? WHERE id=?");
    $stmt->bind_param("sdii", $nom, $prix, $qte, $id);
    
    if($stmt->execute()) {
        header("Location: parametres.php?notify=prod_ok");
        exit();
    }
}

// --- LOGIQUE UTILISATEURS (Suppression) ---
if (isset($_GET['delete_user'])) {
    $id_user = $_GET['delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id_user);
    if($stmt->execute()) {
        header("Location: parametres.php?notify=user_deleted");
        exit();
    }
}

// --- LOGIQUE UTILISATEURS (Mise à jour adaptée à votre BD) ---
if (isset($_POST['update_user'])) {
    $id = $_POST['id_user'];
    $email = $_POST['email_user']; // On récupère l'email du formulaire
    $secteur = $_POST['role_user']; // On récupère le secteur (chefss, employe, stagiere)

    // Correction : Utilisation des colonnes 'email' et 'secteur' visibles sur votre phpMyAdmin
    $stmt = $conn->prepare("UPDATE users SET email=?, secteur=? WHERE id=?");
    $stmt->bind_param("ssi", $email, $secteur, $id);
    
    if($stmt->execute()) {
        // Mettre à jour la session si vous vous modifiez vous-même
        if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['role'] = $secteur;
        }
        header("Location: parametres.php?notify=user_ok");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres - StockMaster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0284c7; --success: #10b981; --warning: #f59e0b;
            --danger: #ef4444; --bg-body: #f8fafc;
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-body); display: flex; }

        /* Sidebar */
        .sidebar { width: 280px; background: var(--primary); color: white; min-height: 100vh; position: fixed; padding: 20px; box-sizing: border-box; z-index: 100;}
        .sidebar-user { text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px; }
        .sidebar h2 { font-size: 1.5rem; margin: 20px 0; text-align: center; }
        .sidebar a { display: flex; align-items: center; color: white; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; margin-bottom: 5px; }
        .sidebar a i { width: 25px; margin-right: 10px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); }
        
        .main { margin-left: 300px; padding: 30px; width: calc(100% - 240px); }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .card h3 { margin-top: 0; margin-bottom: 20px; color: #1e73d8; border-bottom: 2px solid #f4f6f9; padding-bottom: 10px; }
        
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        .btn-save { background: #1e73d8; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; transition: 0.3s; }
        
        table { width: 100%; border-collapse: collapse; }
        table th { text-align: left; padding: 12px; background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; }
        table td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .btn-edit-small { background: #28a745; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: 0.2s; margin-right: 5px; }
        
        /* Badges Rôles basés sur votre colonne 'secteur' */
        .badge { font-size: 11px; padding: 5px 12px; border-radius: 20px; font-weight: bold; display: inline-block; }
        .badge-admin { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-employe { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
        .badge-stagiere { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        #notification { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-user">
        <?php if(!empty($_SESSION['profil_image'])): ?>
            <img src="uploads/profils/<?php echo $_SESSION['profil_image']; ?>" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 10px; object-fit: cover;">
        <?php else: ?>
            <i class="fas fa-user-circle" style="font-size: 50px; margin-bottom: 10px;"></i>
        <?php endif; ?>
        <h3 style="font-size: 14px; margin: 0;"><?php echo $_SESSION['Nom_user'] ?? 'Utilisateur'; ?></h3>
        <p style="font-size: 10px; opacity: 0.7; text-transform: uppercase; margin-top:5px;"><?php echo $user_role_session; ?></p>
    </div>
    <h2>StockMaster</h2>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a>
    <a href="inventaire.php"><i class="fas fa-boxes"></i> Inventaire</a>
    <a href="commandes.php"><i class="fas fa-shopping-cart"></i> Commandes</a>
    <a href="fournisseurs.php"><i class="fas fa-truck"></i> Fournisseurs</a>
    <a href="rapports.php"><i class="fas fa-file-invoice-dollar"></i> Rapports</a>
    <a href="parametres.php" class="active"><i class="fas fa-cog"></i> Paramètres</a>
    <a href="logout.php" style="margin-top: 50px; color: #ffbcbc;"><i class="fas fa-power-off"></i> Déconnexion</a>
</div>


<div class="main">
    
    <?php if (isset($_GET['notify'])): ?>
        <div id="notification">
            <?php 
                switch($_GET['notify']) {
                    case 'prod_ok': echo "✅ Produit mis à jour avec succès !"; break;
                    case 'user_ok': echo "✅ Informations utilisateur et secteur mis à jour !"; break;
                    case 'user_deleted': echo "🗑️ Utilisateur supprimé de la base de données."; break;
                }
            ?>
        </div>
        <script>setTimeout(() => { document.getElementById('notification').style.display='none'; }, 4000);</script>
    <?php endif; ?>

    <div style="margin-bottom: 30px;">
        <h1>Paramètres du Système</h1>
    </div>

    <section class="card">
        <h3><i class="fas fa-box-open"></i> Inventaire</h3>
        <table>
            <thead>
                <tr><th>Désignation</th><th>Prix (F)</th><th>Stock</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM produits ORDER BY nom ASC");
                while($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['nom']) ?></strong></td>
                    <td><?= number_format($row['prix'], 0, '.', ' ') ?></td>
                    <td><?= $row['quantite'] ?></td>
                    <td>
                        <button class="btn-edit-small" onclick="openModal(<?= $row['id'] ?>, '<?= addslashes($row['nom']) ?>', <?= $row['prix'] ?>, <?= $row['quantite'] ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h3><i class="fas fa-users-cog"></i> Comptes Utilisateurs</h3>
        <table>
            <thead>
                <tr><th>Email / Utilisateur</th><th>Secteur</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php
                $res_users = $conn->query("SELECT * FROM users ORDER BY email ASC");
                while($u = $res_users->fetch_assoc()):
                    $badge_class = "badge-stagiere";
                    if($u['secteur'] == 'chefss') $badge_class = "badge-admin";
                    elseif($u['secteur'] == 'employe') $badge_class = "badge-employe";
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['email']) ?></strong></td>
                    <td><span class="badge <?= $badge_class ?>"><?= htmlspecialchars($u['secteur']) ?></span></td>
                    <td>
                        <button class="btn-edit-small" onclick="openUserModal(<?= $u['id'] ?>, '<?= addslashes($u['email']) ?>', '<?= $u['secteur'] ?>')">
                            <i class="fas fa-user-edit"></i>
                        </button>
                        <a href="parametres.php?delete_user=<?= $u['id'] ?>" onclick="return confirm('Supprimer ?')" class="btn-edit-small" style="background: var(--danger); text-decoration: none;">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</div>

<div id="modalProduit" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000;">
    <div style="background:white; width:400px; margin:10% auto; padding:25px; border-radius:12px;">
        <h3>Modifier le produit</h3>
        <form method="POST">
            <input type="hidden" name="id_prod" id="edit_id">
            <label>Désignation</label><input type="text" name="nom" id="edit_nom" required>
            <label>Prix</label><input type="number" name="prix" id="edit_prix" required>
            <label>Stock</label><input type="number" name="quantite" id="edit_qte" required>
            <button type="submit" name="update_product" class="btn-save">Enregistrer</button>
            <button type="button" onclick="closeModal('modalProduit')" style="width:100%; background:#eee; border:none; padding:10px; margin-top:5px; border-radius:8px; cursor:pointer;">Annuler</button>
        </form>
    </div>
</div>

<div id="modalUser" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000;">
    <div style="background:white; width:400px; margin:10% auto; padding:25px; border-radius:12px;">
        <h3>Modifier l'accès</h3>
        <form method="POST">
            <input type="hidden" name="id_user" id="user_id">
            <label>Email / Identifiant</label>
            <input type="text" name="email_user" id="user_email" required>
            <label>Secteur (Status)</label>
            <select name="role_user" id="user_secteur">
                <option value="chefss">Chef de Service (chefss)</option>
                <option value="employe">Employé</option>
                <option value="stagiere">Stagiaire</option>
            </select>
            <button type="submit" name="update_user" class="btn-save">Mettre à jour</button>
            <button type="button" onclick="closeModal('modalUser')" style="width:100%; background:#eee; border:none; padding:10px; margin-top:5px; border-radius:8px; cursor:pointer;">Annuler</button>
        </form>
    </div>
</div>

<script>
function openModal(id, nom, prix, qte) {
    document.getElementById('modalProduit').style.display = 'block';
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nom').value = nom;
    document.getElementById('edit_prix').value = prix;
    document.getElementById('edit_qte').value = qte;
}

function openUserModal(id, email, secteur) {
    document.getElementById('modalUser').style.display = 'block';
    document.getElementById('user_id').value = id;
    document.getElementById('user_email').value = email;
    document.getElementById('user_secteur').value = secteur;
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>

</body>
</html>