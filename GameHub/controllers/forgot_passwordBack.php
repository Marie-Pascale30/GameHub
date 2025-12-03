<?php

require_once '../models/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Vérifie si les mots de passe correspondent
    if ($newPassword !== $confirmPassword) {
        echo "Passwords do not match.";
        exit;
    }

    // Hache le nouveau mot de passe
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Préparer et exécuter la requête pour mettre à jour le mot de passe
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        echo "Password updated successfully.";
    } else {
        echo "Error updating password: " . $stmt->error;
    }

    $stmt->close();
}
