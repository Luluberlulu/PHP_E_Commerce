<?php

$title = "Accueil - Ma Boutique";


require __DIR__ . '/../../templates/header.php';
?>

<h1>Bienvenue : <?php if(isset($_SESSION['username'])){ echo $_SESSION['username'];}else{echo "Guest";} ?></h1>


<?php
require __DIR__ . '/../../templates/footer.php';
?>