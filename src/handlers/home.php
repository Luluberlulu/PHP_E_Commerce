<?php

$title = "Accueil - Ma Boutique";

require __DIR__ . "/../database/db_connection.php";
require __DIR__ . '/../../templates/header.php';

    $articles = $conn->query("SELECT id, name, description, price, published_at, author_id, image_data FROM article LIMIT 0, 10 ");

    foreach ($articles as $article) {
        $image_type = 'image/jpeg'; // Par défaut
        if ($article['image_data']) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detected_type = $finfo->buffer($article['image_data']);
            if ($detected_type) {
                $image_type = $detected_type;
            }
        }
        
        echo "<article style=\"border: 1px solid #ccc; margin: 10px; padding: 10px; display: inline-block; vertical-align: top; width: 200px;\">";
        echo "<h2>" . htmlspecialchars($article['name']) . "</h2>";
        echo "<p>" . htmlspecialchars($article['description']) . "</p>";
        echo "<p><strong>" . number_format($article['price'], 2) . " €</strong></p>";
        echo "<p><small>Publié le : " . $article['published_at'] . "</small></p>";
        
        if ($article['image_data']) {
            echo "<img src=\"data:" . $image_type . ";base64," . base64_encode($article['image_data']) . "\" alt=\"" . htmlspecialchars($article['name']) . "\" style=\"max-width: 100%; height: auto;\">";
        } else {
            echo "<img src=\"uploads/default.png\" alt=\"Image par défaut\" style=\"max-width: 100%; height: auto;\">";
        }
        echo "</article>";
    }
?>

<h1>Bienvenue : <?php if(isset($_SESSION['username'])){ echo $_SESSION['username'];}else{echo "Guest";} ?></h1>


<?php
require __DIR__ . '/../../templates/footer.php';
?>