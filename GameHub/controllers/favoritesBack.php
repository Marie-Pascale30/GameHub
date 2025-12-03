<?php


require_once '../models/config.php';
// Vérifie si le formulaire est soumis via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $gameId = $_POST['game_id'];

    // Préparer et exécuter la requête pour ajouter aux favoris
    $stmt = $conn->prepare("INSERT INTO favorites (user_id, game_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $gameId);

    if ($stmt->execute()) {
        echo "Game added to favorites.";
    } else {
        echo "Error adding game to favorites: " . $stmt->error;
    }

    $stmt->close();
}

if(isset($_GET['remove']) && isset($_GET['game_id'])) {
    $userId = $_SESSION['user_id'];
    $gameId = $_GET['game_id'];

    // Préparer et exécuter la requête pour supprimer des favoris
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $userId, $gameId);

    if ($stmt->execute()) {
        echo "Game removed from favorites.";
    } else {
        echo "Error removing game from favorites: " . $stmt->error;
    }

    $stmt->close();
}

if(isset($_GET['view_favorites'])) {
    $userId = $_SESSION['user_id'];
    // Préparer et exécuter la requête pour récupérer les favoris
    $stmt = $conn->prepare("SELECT game_id FROM favorites WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $favorites = $result->fetch_all(MYSQLI_ASSOC);  
    echo json_encode($favorites);
    $stmt->close();
}


