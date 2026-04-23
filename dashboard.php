<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Connexion et Sécurité
$host = 'localhost'; $dbname = 'stockmaster'; $username = 'root'; $password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }

    $user_email = $_SESSION['user'];
    $user_role = $_SESSION['role'] ?? 'stagiere'; // Récupération du rôle
    $user_initiale = strtoupper(substr($user_email, 0, 1));

    // 2. Récupération des Statistiques
    $res_stock = $pdo->query("SELECT SUM(prix * quantite) AS total_val, COUNT(*) AS total_prod FROM produits")->fetch();
    $total_valeur = $res_stock['total_val'] ?? 0;
    $total_produits = $res_stock['total_prod'] ?? 0;
    $total_fournisseurs = $pdo->query("SELECT COUNT(*) FROM fournisseurs")->fetchColumn() ?: 0;
    $total_commandes = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;

    // 3. Données du Graphique
    $stmt_bar = $pdo->query("SELECT nom, quantite FROM produits ORDER BY quantite DESC LIMIT 5");
    $labels = []; $quantities = [];
    while($row = $stmt_bar->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = $row['nom'];
        $quantities[] = $row['quantite'];
    }
    $js_labels = json_encode($labels);
    $js_quantities = json_encode($quantities);

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StockMaster - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        /* Content */
        .main { margin-left: 300px; padding: 30px; width: calc(100% - 340px); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .user-tag { display: flex; align-items: center; background: white; padding: 8px 15px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .user-avatar { width: 35px; height: 35px; background: var(--primary); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px; }

        /* Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 16px; padding: 20px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .card-stat { text-align: center; transition: 0.3s; }
        .card-stat:hover { transform: translateY(-5px); }
        .card-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin: 0 auto 15px; }

        .icon-valeur { background: #e0f2fe; color: var(--primary); }
        .icon-produits { background: #fef3c7; color: var(--warning); }
        .icon-fournisseurs { background: #d1fae5; color: var(--success); }
        .icon-commandes { background: #fee2e2; color: var(--danger); }

        .dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .quick-action-btn { width: 100%; text-align: left; padding: 12px; margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; gap: 10px; }
        .quick-action-btn:hover { background: #f8fafc; }
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
        <h3 style="font-size: 14px; margin: 0;"><?php echo $_SESSION['Nom_user'] ?? ''; ?></h3>
        <p style="font-size: 11px; opacity: 0.8; margin-top: 5px; text-transform: uppercase;"><?php echo $user_role; ?></p>
        <p>
            <a href="mon_profil.php" style="font-size: 11px; color: #e0f2fe; text-decoration: underline; background: none; padding: 0; display: inline;">
        <i class="fas fa-user-edit"></i> Modifier mon profil
    </a>
        </p>
    </div>
    <h2>StockMaster</h2>
    <a href="dashboard.php" class="active"><i class="fas fa-chart-line"></i> Tableau de bord</a>
    <a href="inventaire.php"><i class="fas fa-boxes"></i> Inventaire</a>
    <a href="commandes.php"><i class="fas fa-shopping-cart"></i> Commandes</a>
    <a href="fournisseurs.php"><i class="fas fa-truck"></i> Fournisseurs</a>
    
    <?php if($user_role === 'chefss'): ?>
        <a href="rapports.php"><i class="fas fa-file-invoice-dollar"></i> Rapports</a>
        <a href="parametres.php"><i class="fas fa-cog"></i> Paramètres</a>
    <?php endif; ?>

    <a href="logout.php" style="margin-top: 20px; color: #ffbcbc;"><i class="fas fa-power-off"></i> Déconnexion</a>
</div>

<div class="main">
    <div class="top-bar">
        <h1>Tableau de Bord</h1>
        <div class="user-tag">
            <div class="user-avatar"><?php echo $user_initiale; ?></div>
            <div class="user-info" style="margin-right: 15px;">
                <span style="font-size: 0.85rem; font-weight: 600; display: block;"><?php echo htmlspecialchars($user_email); ?></span>
                <span style="font-size: 0.7rem; color: var(--success); font-weight: 500;"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> En ligne</span>
            </div>
            <a href="logout.php" style="color: #94a3b8;"><i class="fas fa-power-off"></i></a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="card card-stat">
            <div class="card-icon icon-valeur"><i class="fas fa-wallet"></i></div>
            <h3>Valeur du Stock</h3>
            <h2><?php echo number_format($total_valeur, 0, '.', ' '); ?> <small style="font-size: 0.8rem;">F</small></h2>
        </div>
        <div class="card card-stat">
            <div class="card-icon icon-produits"><i class="fas fa-box"></i></div>
            <h3>Produits en Stock</h3>
            <h2><?php echo $total_produits; ?></h2>
        </div>
        <div class="card card-stat">
            <div class="card-icon icon-fournisseurs"><i class="fas fa-truck-moving"></i></div>
            <h3>Fournisseurs</h3>
            <h2><?php echo $total_fournisseurs; ?></h2>
        </div>
        <div class="card card-stat">
            <div class="card-icon icon-commandes"><i class="fas fa-shopping-bag"></i></div>
            <h3>Total Commandes</h3>
            <h2><?php echo $total_commandes; ?></h2>
        </div>
    </div>

    <div class="dashboard-layout">
        <div class="card">
            <h3>Stock par Désignation</h3>
            <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 20px;">Répartition de l'inventaire par type de produit</p>
            <div style="height: 300px;">
                <canvas id="categoryStockChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>Actions Rapides</h3>
            
            <?php if($user_role === 'chefss' || $user_role === 'employe'): ?>
            <a href="inventaire.php?action=add" class="quick-action-btn" style="background: var(--primary); width: 90%; color: white; border: none; text-decoration: none;">
                <i class="fas fa-plus"></i> Ajouter un produit
            </a>
            <?php endif; ?>

            <?php if($user_role === 'chefss'): ?>
            <a href="rapports.php" class="quick-action-btn" style="text-decoration: none; width: 90%; color: inherit;">
                <i class="fas fa-file-pdf"></i> Générer Rapport
            </a>
            <?php endif; ?>

            <a href="inventaire.php" class="quick-action-btn" style="text-decoration: none; width: 90%; color: inherit;">
                <i class="fas fa-barcode"></i> Gérer le Stock
            </a>
            
            <div style="background: #f0f9ff; padding: 15px; border-radius: 12px; margin-top: 15px; border: 1px solid #e0f2fe;">
                <p style="margin: 0; font-size: 0.8rem; color: #0369a1; font-weight: 600;">System Tip</p>
                <p style="margin: 5px 0 0 0; font-size: 0.75rem; color: #0c4a6e;">
                    <?php
                    $alerte = $pdo->query("SELECT COUNT(*) FROM produits WHERE quantite < 5")->fetchColumn();
                    if($alerte > 0) {
                        echo "Attention : <strong>$alerte</strong> produits sont en stock faible !";
                    } else {
                        echo "Tout est sous contrôle. Le stock est suffisant.";
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxBar = document.getElementById('categoryStockChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?php echo $js_labels; ?>, 
            datasets: [{
                label: 'Quantité',
                data: <?php echo $js_quantities; ?>,
                backgroundColor: 'rgba(2, 132, 199, 0.8)',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
</body>
</html>