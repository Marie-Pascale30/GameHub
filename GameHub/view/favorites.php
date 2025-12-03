<?php
require_once '../models/config.php'; // connexion à ta BDD
require_once 'header.php';
require_once '../controllers/favoritesBack.php';
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">🎮 Liste des Favoris</h1>

        <?php if ($favorites && $favorites->num_rows > 0): ?>
            <div class="row">
                <?php while ($row = $favorites->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-image">
                                <img src="<?= htmlspecialchars($row['cover_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']); ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text">
                                    <strong>Genre :</strong> <?= htmlspecialchars($row['genre']); ?><br>
                                    <strong>Année :</strong> <?= htmlspecialchars($row['release_year']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                Aucun jeu enregistré pour l’instant.
            </div>
        <?php endif; ?>

    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/js/bootstrap.bundle.min.js"></script>

</body>

</html>
<?php
$conn->close();
?>