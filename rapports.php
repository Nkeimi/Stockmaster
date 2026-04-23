<?php 
include("config/database.php"); 
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
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
            --primary: #0284c7; --success: #10b981; --warning: #f59e0b;
            --danger: #ef4444; --bg-body: #f8fafc;
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-body); display: flex; }

        /* Sidebar */
        .sidebar { width: 240px; background: var(--primary); color: white; min-height: 100vh; position: fixed; padding-top: 1px; padding-left:20px; padding-right: 20px; }
        .sidebar-user { text-align: center; border-bottom: 2px solid rgba(255,255,255,0.1); padding-bottom: 10px;}
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 20px; margin-top: -30px; }
        .sidebar a { display: flex; align-items: center; color: white; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; margin-bottom: 5px; }
        .sidebar a i { width: 25px; margin-right: 10px; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); }
        .sidebar a.active { background: rgba(255,255,255,0.2); font-weight: 600; }

        .main { flex:1; padding:30px; display: flex; flex-direction: column; margin-left: 280px; }
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.stats{
    display:grid;
    grid-template-columns: repeat(4, 1fr);
    gap:20px;
    margin-bottom:30px;
}

.stat-card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

.stat-card h4{
    margin-bottom:10px;
    color:#777;
}

.stat-card h2{
    margin:0;
}

.table-container{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

table{
    width:100%;
    border-collapse:collapse;
}

table th, table td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:left;
}

.badge{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
}

.green{ background:#d4edda; color:#155724; }
.orange{ background:#fff3cd; color:#856404; }
.red{ background:#f8d7da; color:#721c24; }

</style>
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
    <a href="fournisseurs.php"><i class="fas fa-truck"></i> Fournisseurs</a>
    <a href="rapports.php"style="background: rgba(255,255,255,0.2);"><i class="fas fa-file-invoice-dollar"></i> Rapports</a>
    <a href="parametres.php"><i class="fas fa-cog"></i> Paramètres</a>
    <a href="logout.php" style="margin-top: 50px; color: #ffbcbc;"><i class="fas fa-power-off"></i> Déconnexion</a>
</div>

<div class="main">

<div class="top-bar">
    <div>
        <h1>Liste des Stocks & Inventaire</h1>
        <p>Suivi en temps réel de votre logistique.</p>
    </div>
    
    <div style="display: flex; align-items: center; gap: 20px;">
        <button onclick="window.print()" style="cursor:pointer; padding: 8px 15px; border-radius: 8px; border: none; background: #1e73d8; color: white;">Exporter PDF</button>
    </div>
</div>
<?php
$totalProduits = $conn->query("SELECT COUNT(*) as total FROM produits")->fetch_assoc()['total'];

$lowStock = $conn->query("SELECT COUNT(*) as total FROM produits WHERE quantite <= 10")->fetch_assoc()['total'];

$outStock = $conn->query("SELECT COUNT(*) as total FROM produits WHERE quantite = 0")->fetch_assoc()['total'];
?>

<div class="stats">
    <div class="stat-card">
        <h4>Total Articles</h4>
        <h2><?php echo $totalProduits; ?></h2>
    </div>

    <div class="stat-card">
        <h4>Stock Faible</h4>
        <h2><?php echo $lowStock; ?></h2>
    </div>

    <div class="stat-card">
        <h4>Rupture de Stock</h4>
        <h2><?php echo $outStock; ?></h2>
    </div>

    <div class="stat-card">
        <h4>Valeur Totale</h4>
        <h2>
        <?php
        $valeur = $conn->query("SELECT SUM(prix * quantite) as total FROM produits")->fetch_assoc()['total'];
        echo number_format($valeur,2);
        ?>F
        </h2>
    </div>
</div>

<div class="table-container">

<table>
<tr>
    <th>Produit</th>
    <th>Quantité</th>
    <th>Prix</th>
    <th>Statut</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM produits");

while($row = $result->fetch_assoc()){

    if($row['quantite'] == 0){
        $status = "<span class='badge red'>Rupture</span>";
    } elseif($row['quantite'] <= 10){
        $status = "<span class='badge orange'>Stock Faible</span>";
    } else {
        $status = "<span class='badge green'>En Stock</span>";
    }

    echo "
    <tr>
        <td>{$row['nom']}</td>
        <td>{$row['quantite']}</td>
        <td>{$row['prix']} F</td>
        <td>$status</td>
    </tr>
    ";
}
?>

</table>

</div>

</div>