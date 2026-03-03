<?php
session_start();
require __DIR__ . '/../database/db_connection.php';

// Sécurité : Vérifier si l'utilisateur est connecté et si c'est une requête POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: home");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$message = "";
$message_type = "error";

try {
    if ($action === 'update_email') {
        $new_email = trim($_POST['new_email']);
        if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("UPDATE userdata SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $new_email, $user_id);
            if ($stmt->execute()) {
                $_SESSION['email'] = $new_email;
                $message = "Email mis à jour avec succès.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de la mise à jour de l'email.";
            }
            $stmt->close();
        } else {
            $message = "Format d'email invalide.";
        }
    } 
    
    elseif ($action === 'update_password') {
        $new_password = $_POST['new_password'];
        if (strlen($new_password) >= 6) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE userdata SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                $message = "Mot de passe modifié avec succès.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de la modification du mot de passe.";
            }
            $stmt->close();
        } else {
            $message = "Le mot de passe doit faire au moins 6 caractères.";
        }
    }

    elseif ($action === 'add_balance') {
        $amount = (float)$_POST['amount'];
        if ($amount > 0) {
            $stmt = $conn->prepare("UPDATE userdata SET balance = balance + ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $user_id);
            if ($stmt->execute()) {
                // Mettre à jour la session pour l'affichage immédiat
                $_SESSION['balance'] += $amount;
                $message = "Votre compte a été crédité de " . number_format($amount, 2) . " €.";
                $message_type = "success";
            } else {
                $message = "Erreur lors de l'ajout des fonds.";
            }
            $stmt->close();
        } else {
            $message = "Le montant doit être supérieur à 0.";
        }
    }

    elseif ($action === 'update_pp') {
        if (isset($_FILES['pp']) && $_FILES['pp']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['pp']['tmp_name'];
            $fileName = $_FILES['pp']['name'];
            $fileSize = $_FILES['pp']['size'];
            $fileType = $_FILES['pp']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedExtensions = ['jpg', 'png', 'jpeg', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadFileDir = __DIR__ . '/../../uploads/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }

                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if (!is_writable($uploadFileDir)) {
                    $message = "Le dossier de destination n'est pas accessible en écriture.";
                } elseif (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Récupérer l'ancienne photo pour la supprimer (si pas default.png)
                    $stmt_old = $conn->prepare("SELECT PP FROM userdata WHERE id = ?");
                    $stmt_old->bind_param("i", $user_id);
                    $stmt_old->execute();
                    $res_old = $stmt_old->get_result();
                    if ($res_old->num_rows > 0) {
                        $oldPP = $res_old->fetch_assoc()['PP'];
                        if ($oldPP !== 'default.png' && file_exists($uploadFileDir . $oldPP)) {
                            unlink($uploadFileDir . $oldPP);
                        }
                    }
                    $stmt_old->close();

                    // Mettre à jour la DB
                    $stmt = $conn->prepare("UPDATE userdata SET PP = ? WHERE id = ?");
                    $stmt->bind_param("si", $newFileName, $user_id);
                    if ($stmt->execute()) {
                        $_SESSION['PP'] = $newFileName;
                        $message = "Photo de profil mise à jour.";
                        $message_type = "success";
                    } else {
                        $message = "Erreur lors de la mise à jour en base de données.";
                    }
                    $stmt->close();
                } else {
                    $message = "Erreur lors du déplacement du fichier téléchargé.";
                }
            } else {
                $message = "Extension de fichier non autorisée : " . implode(',', $allowedExtensions);
            }
        } else {
            $message = "Erreur lors de l'envoi du fichier.";
        }
    }

} catch (Exception $e) {
    $message = "Une erreur système est survenue.";
}

// Stockage du message pour l'affichage sur la page profil
$_SESSION['flash_message'] = $message;
$_SESSION['flash_type'] = $message_type;

header("Location: profil");
exit();
