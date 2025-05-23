<?php
// session_start(); // Session is now started in header.php
require_once 'db_config.php'; // Database connection
$page_title = "Register - Rose & Gold"; // For header

// Ensure session is started (it should be by header.php, but as a fallback)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$username = '';
$email = '';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Basic Validation
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->fetch()) {
                $error_message = "Username or email already taken.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $insert_stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $insert_stmt->bindParam(':username', $username);
                $insert_stmt->bindParam(':email', $email);
                $insert_stmt->bindParam(':password', $hashed_password);

                if ($insert_stmt->execute()) {
                    $success_message = "Registration successful! You can now log in.";
                    // Optionally, log the user in directly or redirect to login page
                    // header("Location: login.php?registration=success");
                    // exit;
                    $username = ''; // Clear form on success
                    $email = '';
                } else {
                    $error_message = "Registration failed. Please try again.";
                }
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
        <h1>Create Your Account</h1>
        <p class="subtitle">Join the Rose & Gold community.</p>
    </section>

    <section class="content-section auth-form-section">
        <?php if ($success_message): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <div id="password-strength-meter" class="password-strength-meter"></div>
                <span class="password-toggle-icon" onclick="togglePasswordVisibility('password', this)">&#128065;</span> {/* Eye icon */}
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm Password:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
                <span class="password-toggle-icon" onclick="togglePasswordVisibility('password_confirm', this)">&#128065;</span> {/* Eye icon */}
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Register</button>
            </div>
            <p class="auth-switch">Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </section>
</main>

<script>
function togglePasswordVisibility(fieldId, toggleElement) {
    const passwordField = document.getElementById(fieldId);
    if (passwordField.type === "password") { // Password is dots, about to become text
        passwordField.type = "text";
        toggleElement.innerHTML = "&#10006;"; // "X" or "Hide" icon - indicating password is now visible, click to hide
        toggleElement.classList.add('visible'); // Can be used to style the "X" differently if needed
    } else { // Password is text, about to become dots
        passwordField.type = "password";
        toggleElement.innerHTML = "&#128065;"; // Eye icon - indicating password is now hidden, click to show
        toggleElement.classList.remove('visible');
    }
}

const passwordInput = document.getElementById('password');
const strengthMeter = document.getElementById('password-strength-meter');

passwordInput.addEventListener('input', function() {
    const password = passwordInput.value;
    let strength = 0;
    let feedback = "";

    if (password.length === 0) {
        strengthMeter.innerHTML = '';
        strengthMeter.className = 'password-strength-meter'; // Reset class
        return;
    }

    // Criteria for strength
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;
    if (password.match(/[a-z]/)) strength += 1;
    if (password.match(/[A-Z]/)) strength += 1;
    if (password.match(/[0-9]/)) strength += 1;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 1; // Symbols

    strengthMeter.className = 'password-strength-meter'; // Reset class
    if (password.length < 8) {
        feedback = "Too short";
        strengthMeter.classList.add('weak');
    } else if (strength <= 2) {
        feedback = "Weak";
        strengthMeter.classList.add('weak');
    } else if (strength <= 4) {
        feedback = "Medium";
        strengthMeter.classList.add('medium');
    } else {
        feedback = "Strong";
        strengthMeter.classList.add('strong');
    }
    strengthMeter.innerHTML = 'Strength: ' + feedback;
});
</script>

<?php require_once 'footer.php'; // Site footer ?>
