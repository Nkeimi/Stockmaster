<?php
session_start();

// 1. Paramètres de connexion
$host = 'localhost';
$dbname = 'stockmaster';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // Récupération des données du formulaire
        $order_number  = trim(htmlspecialchars($_POST['order_number']));
        $customer_name = trim(htmlspecialchars($_POST['customer_name']));
        $product_id    = $_POST['product_id']; // Nouvel ID produit
        $quantity      = intval($_POST['quantity']); // Quantité vendue
        $total_amount  = $_POST['total_amount'];
        $status        = $_POST['status'];

        // --- DÉBUT DE LA TRANSACTION ---
        // On commence ici pour que si une étape échoue, rien ne soit modifié en base
        $pdo->beginTransaction();

        // 1. VÉRIFICATION DU STOCK DISPONIBLE
        $checkStock = $pdo->prepare("SELECT nom, quantite FROM produits WHERE id = ?");
        $checkStock->execute([$product_id]);
        $product = $checkStock->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['quantite'] < $quantity) {
            // Pas assez de stock ! On annule tout.
            $pdo->rollBack();
            header("Location: commandes.php?error=insufficient_stock");
            exit();
        }

        // 2. VÉRIFICATION DES DOUBLONS (Numéro de commande)
        $checkOrder = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
        $checkOrder->execute([$order_number]);
        if ($checkOrder->fetchColumn() > 0) {
            $pdo->rollBack();
            header("Location: commandes.php?error=duplicate");
            exit();
        }

        // 3. INSERTION DE LA COMMANDE
        $sqlInsert = "INSERT INTO orders (order_number, customer_name, total_amount, status, order_date) 
                      VALUES (:num, :customer, :amount, :status, NOW())";
        
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([
            ':num'      => $order_number,
            ':customer' => $customer_name,
            ':amount'   => $total_amount,
            ':status'   => $status
        ]);

        // 4. MISE À JOUR DU STOCK (SOUSTRACTION)
        // On retire la quantité vendue de la table produits
        $sqlUpdateStock = $pdo->prepare("UPDATE produits SET quantite = quantite - ? WHERE id = ?");
        $sqlUpdateStock->execute([$quantity, $product_id]);

        // --- VALIDATION FINALE ---
        // Si on arrive ici sans erreur, on valide définitivement en base
        $pdo->commit();

        header("Location: commandes.php?success=1");
        exit();
    }

} catch (Exception $e) {
    // En cas d'erreur imprévue, on annule les modifications
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Erreur critique : " . $e->getMessage());
}
?>