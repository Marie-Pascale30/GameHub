
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid vh-100 d-flex justify-content-center align-items-center bg-dark">
        <div class="card p-4 shadow-lg bg-secondary text-light" style="width: 25rem;">
            <h3 class="text-center mb-4">Login</h3>
            <form method="POST" action="../controllers/loginBack.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email or username</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" required>
                </div>
                <!-- Password Input -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
                </div>
                <!-- Remember Me Checkbox -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <!-- Additional Links -->
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="text-decoration-none text-secondary-emphasis">Forgot password?</a><br>
                    <span>Don't have an account? <a href="../view/register.php" class="text-decoration-none text-secondary-emphasis">Register</a></span>
                </div>
            </form>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>