<?php
require_once '../models/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Vérifie si le formulaire est soumis via POST
if ((isset($_POST['email']) || isset($_POST['username'])) && isset($_POST['password'])) {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password']); // on ne fait pas htmlspecialchars pour le mot de passe

    // Préparation de la requête sécurisée (évite les injections SQL)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Vérifie le mot de passe hashé avec password_verify()
        if (password_verify($password, $row['password_hash'])) {
            // Connexion réussie → on stocke les infos en session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['type'] = 'users';

            header("Location: ../view/index.php");
            exit();
        } else {
            // Mot de passe incorrect
        
            header("Location: ../view/login.php?login_err=incorrect");
            exit();
        }
    } else {
        // Aucun utilisateur trouvé
        header("Location: ../view/login.php?login_err=not_found");
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    echo "Erreur : formulaire incomplet.";
}
}
?>


