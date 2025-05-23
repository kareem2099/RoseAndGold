<?php
// session_start(); // Session is now started in header.php
require_once 'db_config.php'; // Database connection
$page_title = "Login - Rose & Gold"; // For header

// Ensure session is started (it should be by header.php, but as a fallback)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$username_or_email = '';
$error_message = '';

// If user is already logged in, redirect them from login page
if (isset($_SESSION['user_id'])) {
    header("Location: admin_products.php"); // Or a user dashboard page
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    if (empty($username_or_email) || empty($password)) {
        $error_message = "Username/Email and Password are required.";
    } else {
        try {
            // Check if input is email or username
            $field_type = filter_var($username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            
            // Fetch id, username, password, and role
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE $field_type = :username_or_email");
            $stmt->bindParam(':username_or_email', $username_or_email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Store user role in session
                
                // Regenerate session ID for security
                session_regenerate_id(true);

                // Redirect to a protected page (e.g., admin or dashboard)
                header("Location: admin_products.php"); 
                exit;
            } else {
                $error_message = "Invalid username/email or password.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

require_once 'header.php'; // Site header
?>

<main class="content-area auth-page animated-section">
    <section class="page-title-section">
        <h1>Login to Your Account</h1>
        <p class="subtitle">Access your Rose & Gold dashboard.</p>
    </section>

    <section class="content-section auth-form-section">
        <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
            <div class="message success">Registration successful! You can now log in.</div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username_or_email">Username or Email:</label>
                <input type="text" id="username_or_email" name="username_or_email" value="<?php echo htmlspecialchars($username_or_email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Login</button>
            </div>
            <p class="auth-switch">Don't have an account? <a href="register.php">Register here</a>.</p>
        </form>
    </section>
</main>

<?php require_once 'footer.php'; // Site footer ?>
