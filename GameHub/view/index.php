<?php
require_once '../models/config.php'; // connexion à ta BDD
require_once 'header.php';
session_start();

// Requête pour récupérer tous les jeux
$sql = "SELECT cover_url, title, platform FROM games ORDER BY id DESC";
$result = $conn->query($sql);
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
        <h1 class="text-center mb-4">🎮 Liste des Jeux</h1>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-image">
                                <img src="<?= htmlspecialchars($row['cover_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']); ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text">
                                    <strong>platform :</strong> <?= htmlspecialchars($row['platform']); ?><br>
                                <div data-testid="action-button" type="actionButton" class="sc-f5d776e9-1 kqDdav">
                                    <div data-testid="icon-wrapper" class="sc-f5d776e9-2 eXSqMT"><svg data-testid="icon-heart" fill="full" height="17.5" viewBox="0 0 25 25" width="17.5" xmlns="http://www.w3.org/2000/svg" class="sc-90e10613-0 on-clickable kqDdav fQzvtB "><!--! Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. -->
                                            <title>heart</title>
                                            <path d="M20.4502 5.28442L20.4502 5.2844C19.6474 4.73818 18.7175 4.45001 17.7709 4.44918C16.4219 4.45772 15.122 5.03184 14.1594 6.06073L14.1567 6.06357C14.0094 6.21946 13.8829 6.36796 13.7347 6.54193C13.633 6.66131 13.5211 6.79267 13.3853 6.94665L12.8241 7.58261L12.2616 6.94789C12.1151 6.78255 11.9976 6.64242 11.8922 6.51674C11.7278 6.32073 11.5929 6.15985 11.4233 5.99323L11.4159 5.98595L11.4087 5.97847C10.3817 4.91228 9.24873 4.42643 8.02627 4.42643H8.02068V4.4264C7.54649 4.42287 7.07383 4.48859 6.61599 4.6221L6.61144 4.62342L6.61143 4.62341C5.57825 4.91762 4.65657 5.56479 3.99305 6.47511L3.99305 6.47512C3.32909 7.38604 2.96267 8.50774 2.95628 9.66813L2.95626 9.67276C2.93743 11.2849 3.5315 12.6329 4.62278 13.767L4.63001 13.7745L4.62996 13.7746C6.71744 16.0053 8.82621 18.2361 10.935 20.4443L10.9376 20.447L10.9518 20.4621C11.5828 21.1296 12.2162 21.7996 12.8604 22.492L21.1038 13.7718L21.109 13.7663L21.109 13.7663C21.2902 13.5785 21.4574 13.3756 21.609 13.1591C22.8446 11.3789 23.1455 9.50905 22.3009 7.54771L20.4502 5.28442ZM20.4502 5.28442C21.2542 5.8314 21.8986 6.61541 22.3008 7.54764L20.4502 5.28442Z" stroke="#000000" stroke-width="1.5"></path>
                                        </svg></div>
                                    <p type="default" class="sc-e6f263fc-0 sc-f5d776e9-0 ATVeW fQzvtB">Coup de cœur</p>
                                </div>
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

<!--  <strong>Année :</strong> <?= htmlspecialchars($row['release_year']); ?> -->