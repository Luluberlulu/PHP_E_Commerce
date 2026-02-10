<?php
session_start();

include __DIR__ . '/../database/db_connection.php'; 
//message = holder pour erreur 
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    //Prépare la query pour éviter les injections sql
    $stmt = $conn->prepare("SELECT password, username FROM userdata WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    //Si $stmt retourne une valeur cv dire qu'il existe
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password, $db_username);
        $stmt->fetch();
    //dcp on vérifie le mdp
        if (password_verify($password, $db_password)) {
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $db_username;
            header("Location: home");
            exit();
        } else {
            $message = "Identifiants invalides.";
        }
    } else {
        $message = "Identifiants invalides.";
    }
    $stmt->close();
}

$title = "Login - Ma Boutique";

require __DIR__ . '/../../templates/header.php';
?>

<body>
    <form action="login" method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
    <?php if($message) echo "<p>$message</p>"; ?>
    <a href="resetpassword">Mot de passe oublié</a>
</body>

<?php 
require __DIR__ . '/../../templates/footer.php'; 
?>