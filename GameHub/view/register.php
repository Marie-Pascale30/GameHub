
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container-fluid vh-100 d-flex justify-content-center align-items-center bg-dark">
        <div class="card p-4 shadow-lg bg-secondary text-light" style="width: 25rem;">
            <h3 class="text-center mb-4">Register</h3>
            <form method="POST" action="../controllers/registerBack.php">
                <!-- Email Input -->
                <div class="mb-3">
                    <label for="name" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" id="name" placeholder="Enter your username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" required>
                </div>
                <!-- Password Input -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
                </div>
                <div class="mb-3">
                    <label for="repeat-password" class="form-label">Confirm password</label>
                    <input type="password" name="confirm_password" class="form-control" id="repeat-password" placeholder="Repeat your password" required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100">Register</button>
                <!-- Additional Links -->
                <div class="text-center mt-3">
                    <span>Already have an account? <a href="../view/login.php" class="text-decoration-none text-secondary-emphasis">Login</a></span>
                </div>
            </form>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>