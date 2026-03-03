<?php
$title = "Vendre un produit";

require __DIR__ . '/../database/db_connection.php';
require __DIR__ . '/../../templates/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] == "guest") {
    header("Location: login");
    exit();
}

if (isset($_POST["submit_sell"]) ){
    $product_name = $_POST["product_name"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $author_id = (int)$_SESSION['user_id'];
    
    // Vérifier si une image a été envoyée
    if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] === UPLOAD_ERR_OK) {
        $image_data = file_get_contents($_FILES["product_image"]["tmp_name"]);
        
        // Utiliser 's' pour le blob au lieu de 'b' pour pouvoir envoyer les données directement
        $stmt = $conn->prepare("INSERT INTO article (name, description, price, author_id, image_data) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $product_name, $description, $price, $author_id, $image_data);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Produit mis en vente avec succès !</p>";
        } else {
            echo "<p style='color: red;'>Erreur lors de la mise en vente : " . $conn->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>Veuillez sélectionner une image valide.</p>";
    }
}
?>

<div class="sell-container">
    <h1>Mettre un produit en vente</h1>
    
    <form action="sell" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_name">Nom du produit :</label>
            <input type="text" id="product_name" name="product_name" required placeholder="Ex: iPhone 15 Pro">
        </div>

        <div class="form-group">
            <label for="description">Description :</label>
            <textarea id="description" name="description" rows="5" required placeholder="Décrivez l'état et les caractéristiques de votre produit..."></textarea>
        </div>

        <div class="form-group">
            <label for="price">Prix (€) :</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required placeholder="0.00">
        </div>

        <div class="form-group">
            <label for="product_image">Image du produit :</label>
            <input type="file" id="product_image" name="product_image" accept="image/*" required>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit_sell">Publier l'annonce</button>
        </div>
    </form>
</div>

<?php 
require __DIR__ . '/../../templates/footer.php'; 
?>
