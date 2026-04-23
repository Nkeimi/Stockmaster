<?php
session_start();

// 1. Connexion à la base de données
if (file_exists('db.php')) {
    include_once 'db.php';
} else {
    $host = 'localhost'; $dbname = 'stockmaster'; $username = 'root'; $password_db = '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur critique de connexion : " . $e->getMessage());
    }
}

// Sécurité : Redirection si non connecté et récupération du rôle
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}
$user_role = $_SESSION['role'] ?? 'stagiere';

// --- TRAITEMENTS SÉCURISÉS (Rôle requis : employé ou chefss) ---

// Modification
if(isset($_POST['modifier_valider'])){
    if($user_role === 'chefss' || $user_role === 'employe') {
        $stmt = $pdo->prepare("UPDATE produits SET quantite = :qty, prix = :prix, categorie = :cat, entrepot = :ent WHERE nom = :nom");
        $stmt->execute([
            ':qty' => $_POST['quantite'],
            ':prix' => $_POST['prix'],
            ':cat' => $_POST['categorie'],
            ':ent' => $_POST['entrepot'],
            ':nom' => $_POST['nom_original']
        ]);
        echo "<script>window.location.href='inventaire.php';</script>";
    }
}

// Ajout
if(isset($_POST['ajouter'])){
    if($user_role === 'chefss' || $user_role === 'employe') {
        $stmt = $pdo->prepare("INSERT INTO produits (nom, quantite, prix, categorie, entrepot) 
                               VALUES (:nom, :qty, :prix, :cat, :ent) 
                               ON DUPLICATE KEY UPDATE quantite = quantite + :qty, prix = :prix, categorie = :cat, entrepot = :ent");
        $stmt->execute([
            ':nom' => $_POST['nom'], ':qty' => $_POST['quantite'], ':prix' => $_POST['prix'],
            ':cat' => $_POST['secteur'], ':ent' => $_POST['entrepot']
        ]);
        $msg_success = "✅ Produit mis à jour ou ajouté avec succès !";
    }
}

// Suppression (Rôle requis : chefss uniquement)
if(isset($_GET['supprimer'])){
    if($user_role === 'chefss') {
        $id = intval($_GET['supprimer']);
        $pdo->query("DELETE FROM produits WHERE id=$id");
        $msg_delete = "Produit supprimé";
    }
}

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE :search ORDER BY nom ASC");
$stmt->execute([':search' => "%$search%"]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* ... (Tes styles restent identiques) ... */
:root {
            --primary: #0284c7; --success: #10b981; --warning: #f59e0b;
            --danger: #ef4444; --bg-body: #f8fafc;
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-body); display: flex; }

        /* Sidebar */
        .sidebar { width: 240px; background: var(--primary); color: white; min-height: 100vh; position: fixed; padding: 20px; }
        .sidebar-user { text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;}
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 20px; }
        .sidebar a { display: flex; align-items: center; color: white; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; margin-bottom: 5px; }
        .sidebar a i { width: 25px; margin-right: 10px; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); }
        .sidebar a.active { background: rgba(255,255,255,0.2); font-weight: 600; }
.main{ margin-left: 280px; flex:1; padding:30px; }
form.search-form{ display:flex; gap:10px; margin-bottom:20px; }
form.search-form input{ flex:1; padding:10px; border:1px solid #ccc; border-radius:6px; }
form.search-form button{ padding:10px 20px; border:none; background:#1e73d8; color:white; border-radius:6px; cursor:pointer; }
input, select{ padding:10px; margin-right:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:6px; }
button{ padding:10px 15px; border:none; background:#1e73d8; color:white; border-radius:6px; cursor:pointer; }
.table-container{ max-height:400px; overflow-y:auto; background:white; padding: 10px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-top:20px; }
table{ border-collapse: collapse; width:100%; }
table thead th{ position:sticky; top:0; background:#1e73d8; color:white; padding:12px; }
table td { padding:12px; text-align: center; border-bottom:1px solid #eee; }
@media(max-width:900px){ .sidebar{ display:none; } .main{ margin-left:0; } }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="sidebar">
    <div class="sidebar-user">
        <?php if(!empty($_SESSION['profil_image'])): ?>
            <img src="uploads/profils/<?php echo $_SESSION['profil_image']; ?>" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 10px; object-fit: cover;">
        <?php else: ?>
            <i class="fas fa-user-circle" style="font-size: 50px; margin-bottom: 10px;"></i>
        <?php endif; ?>
        <h3 style="font-size: 14px; margin: 0;"><?php echo $_SESSION['Nom_user'] ?? ''; ?></h3>
        <p style="font-size: 10px; opacity: 0.7; text-transform: uppercase;"><?php echo $user_role; ?></p>
    </div>
    <h2>StockMaster</h2>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Tableau de bord</a>
    <a href="inventaire.php" style="background: rgba(255,255,255,0.2);"><i class="fas fa-boxes"></i> Inventaire</a>
    <a href="commandes.php"><i class="fas fa-shopping-cart"></i> Commandes</a>
    <a href="fournisseurs.php"><i class="fas fa-truck"></i> Fournisseurs</a>
    
    <?php if($user_role === 'chefss'): ?>
        <a href="rapports.php"><i class="fas fa-file-invoice-dollar"></i> Rapports</a>
        <a href="parametres.php"><i class="fas fa-cog"></i> Paramètres</a>
    <?php endif; ?>
    
    <a href="logout.php" style="margin-top: 50px; color: #ffbcbc;"><i class="fas fa-power-off"></i> Déconnexion</a>
</div>

<div class="main">
    <h1>Gestion de l'inventaire</h1>

    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Rechercher un produit" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Rechercher</button>
    </form>

    <button onclick="window.print()"><i class="fas fa-print"></i> Exporter PDF</button>

    <hr style="margin: 20px 0; opacity: 0.2;">

    <?php if($user_role === 'chefss' || $user_role === 'employe'): ?>
        <h2>Ajouter un produit</h2>
        <form method="POST">
            <input type="text" name="nom" placeholder="Nom du produit" required>
            <input type="number" name="quantite" placeholder="Quantité" required>
            <input type="number" step="0.01" name="prix" placeholder="Prix" required>
            <select name="secteur" required>
                <option value="" disabled selected>Sélectionner la Marque</option>
                <option value="hp">HP</option>
                <option value="dell">DELL</option>
                <option value="lenovo">LENOVO</option>
                <option value="acer">ACER</option>
            </select>
            <select name="entrepot" required>
                <option value="" disabled selected>Sélectionner l'Entrepôt</option>
                <option value="Mag1">Magasin 1</option>
                <option value="Mag2">Magasin 2</option>
                <option value="Mag3">Magasin 3</option>
            </select>
            <button type="submit" name="ajouter">Ajouter</button>
        </form>
        <?php if(isset($msg_success)) echo "<p style='color:green; padding:10px; background:#d4edda; border-radius:8px;'>$msg_success</p>"; ?>
    <?php endif; ?>

    <?php if(isset($msg_delete)) echo "<p style='color:red'>$msg_delete</p>"; ?>

    <h2>Liste des Produits</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Quantité</th>
                    <th>Prix</th>
                    <th>Catégorie</th>
                    <th>Entrepôt</th>
                    <th>Statut</th>
                    <?php if($user_role !== 'stagiere'): ?><th>Opérations</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($result as $row): 
                    $qty = $row['quantite'];
                    $status = ($qty == 0) ? "<span style='color:red'>Rupture</span>" : (($qty <= 10) ? "<span style='color:orange'>Stock faible</span>" : "<span style='color:green'>Disponible</span>");
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['nom']); ?></strong></td>
                    <td><?php echo $qty; ?></td>
                    <td><?php echo number_format($row['prix'], 0, ',', ' '); ?> F</td>
                    <td><?php echo htmlspecialchars($row['categorie']); ?></td>
                    <td><?php echo htmlspecialchars($row['entrepot']); ?></td>
                    <td><?php echo $status; ?></td>
                    
                    <?php if($user_role !== 'stagiere'): ?>
                    <td>
                        <button style="border:none;background:transparent;cursor:pointer" 
                                onclick="editData('<?php echo addslashes($row['nom']); ?>', '<?php echo $qty; ?>', '<?php echo $row['prix']; ?>', '<?php echo addslashes($row['categorie']); ?>', '<?php echo addslashes($row['entrepot']); ?>')">
                            ✏️
                        </button>
                        <?php if($user_role === 'chefss'): ?>
                            <a href="?supprimer=<?php echo $row['id']; ?>" onclick="return confirm('Supprimer ?')" style="text-decoration:none;">❌</a>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="background:white; width:400px; margin:100px auto; padding:20px; border-radius:10px; position:relative;">
            <span onclick="document.getElementById('modal').style.display='none'" style="position:absolute; right:15px; cursor:pointer; font-size:20px;">&times;</span>
            <h3>Modifier le produit</h3>
            <form method="POST">
                <input type="hidden" name="nom_original" id="edit_nom_hidden">
                <p>Produit : <strong id="display_nom"></strong></p>
                <label>Quantité</label>
                <input type="number" name="quantite" id="edit_quantite" required style="width:100%">
                <label>Prix</label>
                <input type="number" step="0.01" name="prix" id="edit_prix" required style="width:100%">
                <label>Categorie</label>
                <input type="text" name="categorie" id="edit_categorie" style="width:100%">
                <label>Entrepôt</label>
                <input type="text" name="entrepot" id="edit_entrepot" style="width:100%">
                <button type="submit" name="modifier_valider" style="width:100%; margin-top:10px;">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</div>

<script>
function editData(nom, quantite, prix, categorie, entrepot) {
    document.getElementById("modal").style.display = "block";
    document.getElementById("edit_nom_hidden").value = nom;
    document.getElementById("display_nom").innerText = nom;
    document.getElementById("edit_quantite").value = quantite;
    document.getElementById("edit_prix").value = prix;
    document.getElementById("edit_categorie").value = categorie;
    document.getElementById("edit_entrepot").value = entrepot;
}
</script>