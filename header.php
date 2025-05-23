<?php 
if (session_status() == PHP_SESSION_NONE) { // Start session if not already started
    session_start();
}
require_once 'script.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : "Rose and Gold Project"; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/about.css">
    <link rel="stylesheet" href="css/products.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/admin.css"> 
    <link rel="stylesheet" href="css/auth.css"> 
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="css/index.css"> <!-- Added homepage specific CSS -->
    <!-- Link to a potential index.css or other page-specific CSS could go here, loaded conditionally or directly -->
</head>
<body>
    <header class="site-header animated-header">
        <div class="header-container">
            <div class="logo-area">
                <a href="index.php" class="logo-link">
                    <span class="logo-main">Rose & Gold</span>
                    <span class="logo-tagline">Crafting Digital Elegance</span>
                </a>
            </div>
            <nav class="main-navigation">
                <ul>
                    <li><a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About Us</a></li>
                    <li><a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Products</a></li>
                    <li><a href="cart.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">Cart</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item-dropdown">
                            <a href="javascript:void(0);" id="userDropdownToggle" class="nav-link welcome-user dropdown-toggle">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! &#9662;</a>
                            <ul class="dropdown-menu" id="userDropdownMenu">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <li><a href="admin_products.php" class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_products.php' ? 'active' : ''; ?>">Manage Products</a></li>
                                <?php endif; ?>
                                <li><a href="logout.php" class="dropdown-item">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">Login</a></li>
                        <li><a href="register.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">Register</a></li>
                    <?php endif; ?>
                     <li><a href="contact.php" class="nav-link contact-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>
