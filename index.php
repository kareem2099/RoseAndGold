<?php 
$page_title = "Welcome to Rose & Gold - Digital Elegance";
require_once 'header.php'; 
require_once 'db_config.php'; // For fetching products
?>

<main class="content-area homepage">

    <!-- Hero Section -->
    <section class="hero-section animated-section">
        <div class="hero-content">
            <h1>Experience Digital Elegance</h1>
            <p class="subtitle">Crafting unique web experiences with a touch of rose and a hint of gold.</p>
            <a href="products.php" class="btn-primary hero-cta">Explore Our Products</a>
        </div>
        <div class="hero-image-placeholder">
            <!-- Placeholder for a beautiful hero image/graphic -->
            <span>Rose & Gold Themed Visual</span>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products-section animated-section">
        <h2 class="section-title">Featured Collection</h2>
        <div class="product-grid homepage-product-grid">
            <?php
            try {
                // Fetch a few products (e.g., 3 most recent or random)
                $stmt_featured = $pdo->query("SELECT id, name, category, price, image_url FROM products ORDER BY id DESC LIMIT 3");
                $featured_products = $stmt_featured->fetchAll(PDO::FETCH_ASSOC);

                if (count($featured_products) > 0) {
                    foreach ($featured_products as $fp) {
                        echo '<div class="product-card homepage-product-card">';
                        $fp_image_content = !empty($fp['image_url']) 
                            ? '<img src="' . htmlspecialchars($fp['image_url']) . '" alt="' . htmlspecialchars($fp['name']) . '">'
                            : '<span>' . htmlspecialchars(substr($fp['name'], 0, 15)) . '...</span>';
                        echo '    <div class="product-image-placeholder">' . $fp_image_content . '</div>';
                        echo '    <div class="product-info">';
                        echo '        <h4 class="product-name">' . htmlspecialchars($fp['name']) . '</h4>';
                        echo '        <p class="product-category">' . htmlspecialchars($fp['category']) . '</p>';
                        echo '        <p class="product-price">$' . htmlspecialchars(number_format($fp['price'], 2)) . '</p>';
                        echo '        <a href="products.php#product-' . htmlspecialchars($fp['id']) . '" class="btn-secondary btn-view-product">View Details</a>'; // Link to product on products page
                        echo '    </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No featured products available at the moment.</p>';
                }
            } catch (PDOException $e) {
                echo '<p style="color: red;">Error fetching featured products: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        <div class="section-cta">
            <a href="products.php" class="btn-secondary">View All Products</a>
        </div>
    </section>

    <!-- About Us Snippet Section -->
    <section class="about-snippet-section content-section animated-section">
        <h2 class="section-title">Discover Rose & Gold</h2>
        <p>We are passionate about creating beautiful, intuitive, and high-performing digital solutions. Our philosophy centers around the blend of elegant design (the "Rose") and robust, valuable functionality (the "Gold"). From stunning websites to engaging applications, we bring your vision to life with a unique aesthetic and unwavering commitment to quality.</p>
        <div class="section-cta">
            <a href="about.php" class="btn-primary">Learn More About Us</a>
        </div>
    </section>
    
    <!-- Call to Action / Contact Snippet -->
    <section class="cta-contact-section animated-section">
        <h2 class="section-title">Let's Create Something Beautiful</h2>
        <p>Have a project in mind or just want to say hello? We're here to help.</p>
        <div class="section-cta">
            <a href="contact.php" class="btn-secondary">Get In Touch</a>
        </div>
    </section>

</main>

<?php require_once 'footer.php'; ?>
