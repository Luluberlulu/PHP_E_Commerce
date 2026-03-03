<?php
session_start();
require __DIR__ . '/../database/db_connection.php';

// Vérification sécurisée de la session
if (!isset($_SESSION['role']) || $_SESSION['role'] == "guest") {
    header("Location: login");
    exit();
}

$title = "Profil";

// Récupération de l'ID de l'utilisateur cible (soit soi-même, soit un tiers via GET)
$target_user_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_SESSION['user_id'] ?? 0);

if ($target_user_id === 0) {
    header("Location: login");
    exit();
}

$is_own_profile = ($target_user_id === (int)($_SESSION['user_id'] ?? -1));

// Récupération des infos utilisateur
$stmt = $conn->prepare("SELECT balance, PP, role, username, email FROM userdata WHERE id = ?");
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h1>Utilisateur introuvable</h1><a href='home'>Retour à l'accueil</a>";
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Extraction des données pour plus de clarté
$username = $user['username'];
$email    = $user['email'];
$balance  = $user['balance'];
$PP       = $user['PP'];
$role     = $user['role'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($username); ?></title>
</head>
<body>

<nav>
    <a href="home">Accueil</a> 
</nav>

<h1>Profil de <?php echo htmlspecialchars($username); ?></h1>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div style="padding: 10px; margin-bottom: 20px; border-radius: 5px; 
                background-color: <?php echo $_SESSION['flash_type'] === 'success' ? '#d4edda' : '#f8d7da'; ?>;
                color: <?php echo $_SESSION['flash_type'] === 'success' ? '#155724' : '#721c24'; ?>;
                border: 1px solid <?php echo $_SESSION['flash_type'] === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
        <?php 
            echo htmlspecialchars($_SESSION['flash_message']); 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        ?>
    </div>
<?php endif; ?>

<img src="uploads/<?php echo htmlspecialchars($PP ?: 'default.png'); ?>" alt="Photo de profil" style="width: 150px; border-radius: 50%;">

<?php if ($is_own_profile): ?>
    <form action="update_profile" method="POST" enctype="multipart/form-data" style="margin-top: 10px;">
        <label for="pp">Changer de photo de profil :</label><br>
        <input type="file" name="pp" id="pp" accept="image/*" required>
        <button type="submit" name="action" value="update_pp">Mettre à jour</button>
    </form>
<?php endif; ?>

<section>
    <h2>Articles en vente</h2>
    <?php
    // Correction : "article" en minuscules pour correspondre à la DB
    $stmt_art = $conn->prepare("SELECT name, price FROM article WHERE author_id = ?");
    $stmt_art->bind_param("i", $target_user_id);
    $stmt_art->execute();
    $result_art = $stmt_art->get_result();

    if ($result_art->num_rows > 0) {
        echo "<ul>";
        while ($article = $result_art->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($article['name']) . " — " . number_format($article['price'], 2) . " €</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Aucun article en vente.</p>";
    }
    $stmt_art->close();
    ?>
</section>

<?php if ($is_own_profile): ?>

<hr>

<section>
    <h2>Mon compte</h2>
    <p>Email : <?php echo htmlspecialchars($email); ?></p>
    <p>Solde actuel : <strong><?php echo number_format($balance, 2); ?> €</strong></p>

    <h3>Mes factures</h3>
    <?php
    // Correction : "invoice" en minuscules pour correspondre à la DB
    $stmt_inv = $conn->prepare("SELECT id, transaction_date, amount FROM invoice WHERE user_id = ?");
    $stmt_inv->bind_param("i", $target_user_id);
    $stmt_inv->execute();
    $result_inv = $stmt_inv->get_result();

    if ($result_inv->num_rows > 0) {
        echo "<ul>";
        while ($invoice = $result_inv->fetch_assoc()) {
            echo "<li>Facture #" . htmlspecialchars($invoice['id']) . " — " . number_format($invoice['amount'], 2) . " € le " . htmlspecialchars($invoice['transaction_date']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Aucune facture enregistrée.</p>";
    }
    $stmt_inv->close();
    ?>

    <h3>Modifier mes informations</h3>
    <!-- Note : assurez-vous que update_profile.php existe ou pointe vers le bon handler -->
    <form action="update_profile" method="POST">
        <label>Nouvel email :</label>
        <input type="email" name="new_email" placeholder="<?php echo htmlspecialchars($email); ?>" required>
        <button type="submit" name="action" value="update_email">Modifier</button>
    </form>

    <form action="update_profile" method="POST">
        <label>Nouveau mot de passe :</label>
        <input type="password" name="new_password" required>
        <button type="submit" name="action" value="update_password">Modifier</button>
    </form>

    <h3>Ajouter des fonds</h3>
    <form action="update_profile" method="POST">
        <label>Montant (€) :</label>
        <input type="number" step="0.01" min="1" name="amount" required>
        <button type="submit" name="action" value="add_balance">Créditer mon compte</button>
    </form>
</section>

<?php endif; ?>

</body>
</html>
<?php 
require __DIR__ . '/../../templates/footer.php'; 
?>