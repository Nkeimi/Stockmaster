<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sécurité : Redirection si pas connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$user_role = $_SESSION['role'] ?? 'stagiere';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stockmaster", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

/* LOGIQUE PHP SÉCURISÉE */
if(isset($_POST['add']) && ($user_role === 'chefss' || $user_role === 'employe')){
    $stmt=$pdo->prepare("INSERT INTO fournisseurs(id,nom,telephone,email) VALUES(?,?,?,?)");
    $stmt->execute([$_POST['id'], $_POST['nom'], $_POST['telephone'], $_POST['email']]);
    header("Location: fournisseurs.php");
    exit();
}

if(isset($_POST['update']) && ($user_role === 'chefss' || $user_role === 'employe')){
    $stmt=$pdo->prepare("UPDATE fournisseurs SET nom=?,telephone=?,email=? WHERE id=?");
    $stmt->execute([$_POST['nom'], $_POST['telephone'], $_POST['email'], $_POST['id']]);
    header("Location: fournisseurs.php");
    exit();
}

if(isset($_GET['delete']) && $user_role === 'chefss'){
    $stmt=$pdo->prepare("DELETE FROM fournisseurs WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: fournisseurs.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StockMaster - Fournisseurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles conservés à l'identique */
        :root {
            --primary: #0284c7; --success: #10b981; --warning: #f59e0b;
            --danger: #ef4444; --bg-body: #f8fafc;
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-body); display: flex; }

        /* Sidebar */
        .sidebar { width: 240px; background: var(--primary); color: white; min-height: 100vh; position: fixed; padding-top: 1px; padding-left:20px; padding-right: 20px; }
        .sidebar-user { text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;}
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 20px; margin-top: -30px; }
        .sidebar a { display: flex; align-items: center; color: white; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; margin-bottom: 5px; }
        .sidebar a i { width: 25px; margin-right: 10px; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); }
        .sidebar a.active { background: rgba(255,255,255,0.2); font-weight: 600; }
        .main { flex:1; padding:30px; display: flex; flex-direction: column; margin-left: 280px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .search-form { display:flex; gap:10px; margin-bottom:30px; max-width:450px; }
        .search-form input { flex:1; padding:12px; border:1px solid #ddd; border-radius:8px; outline: none; }
        .search-form button { padding:0 20px; border:none; background:#1e73d8; color:white; border-radius:8px; cursor:pointer; font-weight: bold; }
        .content-grid { display: flex; gap: 30px; align-items: flex-start; }
        section { background:white; padding:25px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05); width:350px; flex-shrink: 0; }
        section h2 { margin-bottom: 20px; font-size: 1.2rem; color: #1e73d8; }
        section input { width:90%; padding:12px; margin-bottom:15px; border:1px solid #eee; border-radius:8px; background: #f9f9f9; }
        section button { width:100%; padding:12px; border:none; background:#1e73d8; color:white; border-radius:8px; cursor:pointer; font-weight: bold; transition: 0.3s; }
        article { flex:1; background:white; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05); overflow: hidden; }
        table { width:100%; border-collapse:collapse; }
        table th { background:#f8f9fa; color:#666; padding:15px; text-align: left; font-size: 0.9rem; border-bottom: 2px solid #eee; }
        table td { padding:15px; border-bottom:1px solid #f1f1f1; }
        .edit { background:#28a745; color:white; border:none; padding:8px; border-radius:6px; cursor:pointer; }
        .delete { background:#dc3545; color:white; border:none; padding:8px; border-radius:6px; cursor:pointer; }
        #modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index: 1000; }
        .modal-box { background:white; padding:30px; border-radius:12px; width:400px; margin:10% auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-user">
        <div class="sidebar-user" style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
            <?php if(!empty($_SESSION['profil_image'])): ?>
                <img src="uploads/profils/<?php echo $_SESSION['profil_image']; ?>" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 10px; object-fit: cover;">
            <?php else: ?>
                <i class="fas fa-user-circle" style="font-size: 50px; margin-bottom: 10px;"></i>
            <?php endif; ?>
            <h3 style="font-size: 14px; margin: 0;"><?php echo $_SESSION['Nom_user'] ?? ''; ?></h3>
            <p style="font-size: 10px; opacity: 0.7; text-transform: uppercase; margin-top:5px;"><?php echo $user_role; ?></p>
        </div>
    </div>
    <h2>StockMaster</h2>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a>
    <a href="inventaire.php"><i class="fas fa-boxes"></i> Inventaire</a>
    <a href="commandes.php"><i class="fas fa-shopping-cart"></i> Commandes</a>
    <a href="fournisseurs.php" style="background: rgba(255,255,255,0.2);"><i class="fas fa-truck"></i> Fournisseurs</a>
    
    <?php if($user_role === 'chefss'): ?>
        <a href="rapports.php"><i class="fas fa-file-invoice-dollar"></i> Rapports</a>
        <a href="parametres.php"><i class="fas fa-cog"></i> Paramètres</a>
    <?php endif; ?>
    
    <a href="logout.php" style="margin-top: 50px; color: #ffbcbc;"><i class="fas fa-power-off"></i> Déconnexion</a>
</div>

<div class="main">
    <div class="header-flex">
        <h1>Gestion des Fournisseurs</h1>
    </div>

    <form class="search-form" onsubmit="return false;">
        <input type="text" id="search" placeholder="Rechercher un fournisseur par nom...">
        <button type="button" onclick="searchFournisseur()">Rechercher</button>
    </form>

    <div class="content-grid">
        <?php if($user_role !== 'stagiere'): ?>
        <section>
            <h2>Nouveau Fournisseur</h2>
            <form method="POST">
                <input type="text" name="id" placeholder="ID (ex: F001)" required>
                <input type="text" name="nom" placeholder="Nom de l'entreprise" required>
                <input type="text" name="telephone" placeholder="Téléphone (9 chiffres)" pattern="^[0-9]{9}$" required>
                <input type="email" name="email" placeholder="Email" required>
                <button type="submit" name="add">Enregistrer</button>
            </form>
        </section>
        <?php endif; ?>

        <article>
            <table id="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <?php if($user_role !== 'stagiere'): ?>
                            <th style="text-align: center;">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt=$pdo->query("SELECT * FROM fournisseurs ORDER BY id DESC");
                    while($row=$stmt->fetch()){
                    ?>
                    <tr>
                        <td><strong><?=htmlspecialchars($row['nom'])?></strong></td>
                        <td><?=htmlspecialchars($row['telephone'])?></td>
                        <td><?=htmlspecialchars($row['email'])?></td>
                        
                        <?php if($user_role !== 'stagiere'): ?>
                        <td style="display: flex; justify-content: center; align-items: center; gap: 8px;">
                            <button class="edit" onclick="editData('<?=$row['id']?>','<?=addslashes($row['nom'])?>','<?=$row['telephone']?>','<?=$row['email']?>')">✏️</button>
                            
                            <?php if($user_role === 'chefss'): ?>
                                <a href="?delete=<?=$row['id']?>" onclick="return confirm('Supprimer ce fournisseur ?')" style="text-decoration: none;">
                                    <button class="delete">❌</button>
                                </a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </article>
    </div>
</div>

<?php if($user_role !== 'stagiere'): ?>
<div id="modal">
    <div class="modal-box">
        <h3>Modifier Fournisseur</h3><br>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="nom" id="edit_nom" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:8px; border:1px solid #ccc;">
            <input type="text" name="telephone" id="edit_telephone" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:8px; border:1px solid #ccc;">
            <input type="text" name="email" id="edit_email" required style="width:100%; padding:10px; margin-bottom:20px; border-radius:8px; border:1px solid #ccc;">
            
            <button type="submit" name="update" style="background:#28a745; color:white; padding:10px 20px; border:none; border-radius:8px; cursor:pointer;">Mettre à jour</button>
            <button type="button" onclick="closeModal()" style="background:#6c757d; color:white; padding:10px 20px; border:none; border-radius:8px; cursor:pointer; margin-left:10px;">Annuler</button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function searchFournisseur(){
    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#table tbody tr");
    rows.forEach(row => {
        let nom = row.cells[0].textContent.toLowerCase();
        row.style.display = nom.includes(input) ? "" : "none";
    });
}

function editData(id, nom, telephone, email){
    if(document.getElementById("modal")) {
        document.getElementById("modal").style.display="block";
        document.getElementById("edit_id").value=id;
        document.getElementById("edit_nom").value=nom;
        document.getElementById("edit_telephone").value=telephone;
        document.getElementById("edit_email").value=email;
    }
}

function closeModal(){
    document.getElementById("modal").style.display="none";
}
</script>
</body>
</html>