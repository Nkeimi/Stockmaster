<?php
// Paramètres de connexion
$host = 'localhost'; $dbname = 'stockmaster'; $username = 'root'; $password = '';

if (isset($_GET['id'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Requête de suppression sécurisée
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        
        if ($stmt->execute([$_GET['id']])) {
            header("Location: commandes.php?delete=success");
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
}
?>