<?php
session_start();

require __DIR__ . '/../database/db_connection.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Une seule requête pour tout récupérer d'un coup
    $query = "SELECT id, username, password, balance, PP, role FROM userdata WHERE email = ?";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Vérification du mot de passe haché
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $email;
                $_SESSION['balance']  = $user['balance'];
                $_SESSION['PP']       = $user['PP'];
                $_SESSION['role']     = $user['role'];

                header("Location: home");
                exit();
            } else {
                $message = "Identifiants incorrects.";
            }
        } else {
            $message = "Identifiants incorrects.";
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        $message = "Erreur de base de données : la table 'userdata' semble absente. Veuillez importer le fichier SQL.";
    }
}

$title = "Login - Ma Boutique";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
</head>
<body>
    <h1>Connexion</h1>
    <nav><a href="home">Accueil</a></nav>

    <form action="login" method="post">
        <div>
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div>
            <input type="password" name="password" placeholder="Mot de passe" required>
        </div>
        <button type="submit" name="login">Se connecter</button>
    </form>

    <?php if($message): ?>
        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <p>
        <a href="resetpassword">Mot de passe oublié</a><br>
        <a href="register">Pas encore de compte ?</a>
    </p>

</body>
</html>
<?php 
require __DIR__ . '/../../templates/footer.php'; 
?>