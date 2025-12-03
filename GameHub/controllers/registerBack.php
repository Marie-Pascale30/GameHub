<?php
require_once '../models/config.php';
session_start();

// Vérifie si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ((isset($_POST['username']) || isset($_POST['email'])) && isset($_POST['password'])) {
        $username = htmlspecialchars(trim($_POST['username']));
        $email = htmlspecialchars(trim($_POST['email']));
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']); // on ne fait pas htmlspecialchars pour le mot de passe

        // Vérification : mots de passe identiques ?
        if ($password !== $confirm_password) {
            echo  $message = '<div class="alert alert-danger text-center">❌ Les mots de passe ne correspondent pas !</div>';
            echo  $link = '<a href="../view/register.php" class="btn btn-primary">⏪ Retour à l\'inscription</a>';
            header("Location: ../view/register.php?register_err=password_mismatch");

            exit();
        }

        // Vérifie si le username ou l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Utilisateur déjà utilisé
            echo    $message = '<div class="alert alert-danger text-center">❌ Cet email ou username est déjà utilisé !</div>';
            echo    $link = '<a href="../view/register.php" class="btn btn-primary">⏪ Retour à l\'inscription</a>';
            header("Location: ../view/register.php?register_err=already_exists");
            exit();
        } else {
            $stmt->close();

            // Vérification : mot de passe fort ?

            if (strlen($password) < 10) {
                header("Location: ../view/register.php?register_err=weak_password");
                exit();
            } else {

                if (!preg_match('/[A-Z]/', $password) && !preg_match('/[a-z]/', $password) && !preg_match('/[0-9]/', $password)) {
                    header("Location: ../view/register.php?register_err=weak_password");
                    exit();
                } else {
                    if (!preg_match('/[\W]/', $password)) {
                        header("Location: ../view/register.php?register_err=weak_password");
                        exit();
                    } else {
                        // Hachage du mot de passe
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);

                        // Insertion dans la BDD
                        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $username, $email, $password_hash);

                        if ($stmt->execute()) {
                            // Inscription réussie → on connecte directement l’utilisateur
                            $_SESSION['username'] = $username;
                            $_SESSION['email'] = $email;
                            $_SESSION['type'] = 'users';
                            echo $message = '<div class="alert alert-success text-center">✅ Bienvenue <b>' . $username . '</b> 🎉<br>Ton compte a été créé avec succès.</div>';
                            echo $link = '<a href="../view/login.php" class="btn btn-success">🔑 Connecte-toi maintenant</a>';
                            header("Location: ../view/login.php");
                            exit();
                        } else {
                            // Erreur SQL
                            echo  $message = '<div class="alert alert-danger text-center">⚠️ Une erreur est survenue. Merci de réessayer plus tard.</div>';
                            echo  $link = '<a href="../view/register.php" class="btn btn-primary">⏪ Retour</a>';
                            header("Location: ../view/register.php?register_err=sql_error");
                            exit();
                        }

                        $stmt->close();
                        $conn->close();
                    }
                }
            }
        }
    } else {
        echo  $message = '<div class="alert alert-warning text-center">⚠️ Formulaire incomplet.</div>';
        echo  $link = '<a href="../view/register.php" class="btn btn-primary">⏪ Retour</a>';
        echo "Erreur : formulaire incomplet.";
    }
}
