<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$host = 'localhost'; $dbname = 'stockmaster'; $username = 'root'; $password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }

    $user_role = $_SESSION['role'] ?? 'stagiere';

    // Récupération des commandes
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des produits pour le formulaire (Contrainte : Indexation stock)
    $stmt_prod = $pdo->query("SELECT id, nom, prix, quantite FROM produits WHERE quantite > 0");
    $liste_produits = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$totalAmountToday = 0;
$totalOrdersToday = 0;
$todayDate = date('Y-m-d');

foreach ($orders as $order) {
    if (date('Y-m-d', strtotime($order['order_date'])) === $todayDate) {
        $totalOrdersToday++;
        $totalAmountToday += $order['total_amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StockMaster - Commandes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles conservés à l'identique */
        :root {
            --primary: #0284c7; --success: #10b981; --warning: #f59e0b;
            --danger: #ef4444; --bg-body: #f8fafc;
        }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-body); display: flex; }

        
        /* Sidebar Bleue (Comme ton image) */
        .sidebar { width: 240px; background: var(--primary); color: white; min-height: 100vh; position: fixed; padding-top: 1px; padding-left:20px; padding-right: 20px; }
        .sidebar-user { text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;}
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 20px; margin-top: -30px; }
        .sidebar a { display: flex; align-items: center; color: white; text-decoration: none; padding: 12px; border-radius: 8px; transition: 0.3s; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); }
        .sidebar a i { width: 25px; margin-right: 10px; }

        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f1f5f9; color: #64748b; font-size: 12px; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; width: 450px; margin: 50px auto; padding: 30px; border-radius: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-add { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
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
    <a href="commandes.php" class="active"><i class="fas fa-shopping-cart"></i> Commandes</a>
    <a href="fournisseurs.php"><i class="fas fa-truck"></i> Fournisseurs</a>
    <?php if($user_role === 'chefss'): ?>
        <a href="rapports.php"><i class="fas fa-file-invoice-dollar"></i> Rapports</a>
        <a href="parametres.php"><i class="fas fa-cog"></i> Paramètres</a>
    <?php endif; ?>
    <a href="logout.php" style="margin-top: 50px; color: #ffbcbc;"><i class="fas fa-power-off"></i> Déconnexion</a>
</div>

<div class="main-content">
    <h1>Gestion des Commandes</h1>

    <div class="stats-grid">
        <div class="card"><h3>Commandes jour</h3><h2><?= $totalOrdersToday ?></h2></div>
        <div class="card"><h3>CA Aujourd'hui</h3><h2><?= number_format($totalAmountToday, 0, ',', ' ') ?> F</h2></div>
    </div>

    <div class="card">
        <div style="display:flex; justify-content: space-between; margin-bottom: 20px;">
            <h3>Historique</h3>
            <?php if($user_role !== 'stagiere'): ?>
                <button class="btn-add" onclick="openModal()">+ Nouvelle Commande</button>
            <?php endif; ?>
        </div>
        <table>
            <thead>
                <tr><th>REF</th><th>CLIENT</th><th>DATE</th><th>MONTANT</th><th>ACTIONS</th></tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $row): ?>
                <tr>
                    <td>#<?= $row['order_number'] ?></td>
                    <td><?= $row['customer_name'] ?></td>
                    <td><?= date('d/m/Y', strtotime($row['order_date'])) ?></td>
                    <td><?= number_format($row['total_amount'], 0, ',', ' ') ?> F</td>
                    <td>
                        <?php if($user_role === 'chefss'): ?>
                            <a href="supprimer_commande.php?id=<?= $row['id'] ?>" style="color:red"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="orderModal" class="modal">
    <div class="modal-content">
        <h3>Nouvelle Vente</h3>
        <form action="traitement_commande.php" method="POST">
            <div class="form-group">
                <label>N° Commande</label>
                <input type="text" name="order_number" value="CMD-<?= time() ?>" required>
            </div>
            <div class="form-group">
                <label>Produit (Déduit du stock)</label>
                <select name="product_id" id="prod_select" onchange="calcTotal()" required>
                    <option value="">Choisir...</option>
                    <?php foreach($liste_produits as $p): ?>
                        <option value="<?= $p['id'] ?>" data-prix="<?= $p['prix'] ?>">
                            <?= $p['nom'] ?> (Stock: <?= $p['quantite'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Quantité</label>
                <input type="number" name="quantity" id="qty" min="1" value="1" oninput="calcTotal()" required>
            </div>
            <div class="form-group">
                <label>Client</label>
                <input type="text" name="customer_name" required>
            </div>
            <div class="form-group">
                <label>Total à payer</label>
                <input type="text" name="total_amount" id="total" readonly>
            </div>
            <input type="hidden" name="status" value="completed">
            <button type="submit" class="btn-add">Valider la commande</button>
            <button type="button" onclick="closeModal()">Annuler</button>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById('orderModal').style.display = 'block'; }
    function closeModal() { document.getElementById('orderModal').style.display = 'none'; }
    function calcTotal() {
        let s = document.getElementById('prod_select');
        let p = s.options[s.selectedIndex].getAttribute('data-prix') || 0;
        let q = document.getElementById('qty').value || 0;
        document.getElementById('total').value = p * q;
    }
</script>
</body>
</html>