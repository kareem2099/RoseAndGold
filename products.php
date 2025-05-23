<?php require_once 'header.php'; ?>

<main class="content-area products-page">
    <section class="page-title-section animated-section">
        <h1>Our Exclusive Products</h1>
        <p class="subtitle">Discover handcrafted items with the signature Rose & Gold touch.</p>
    </section>

    <section class="content-section product-intro animated-section">
        <h3>Curated Elegance</h3>
        <p>Each product in our collection is designed with meticulous attention to detail, blending luxurious aesthetics with practical functionality. Explore our range of unique items, perfect for adding a touch of Rose & Gold elegance to your life.</p>
    </section>

    <div class="product-grid animated-section">
        <?php
        require_once 'db_config.php'; // Ensure $pdo is available
        try {
            // Corrected: Fetch id along with other product details
            $stmt = $pdo->query("SELECT id, name, category, description, price, image_url FROM products ORDER BY id DESC");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($products) > 0) {
                $animation_delay_base = 0.2;
                $delay_increment = 0.1;
                $current_delay = $animation_delay_base;

                foreach ($products as $product) {
                    echo '<div class="product-card" style="animation-delay: ' . $current_delay . 's;">';
                    // Use image_url if available, otherwise a placeholder text
                    $image_content = !empty($product['image_url']) 
                        ? '<img src="' . htmlspecialchars($product['image_url']) . '" alt="' . htmlspecialchars($product['name']) . '" style="width:100%; height: 220px; object-fit: cover;">' 
                        : '<span>' . htmlspecialchars(substr($product['name'], 0, 20)) . '...</span>';
                    echo '    <div class="product-image-placeholder">' . $image_content . '</div>';
                    echo '    <div class="product-info">';
                    echo '        <h4 class="product-name">' . htmlspecialchars($product['name']) . '</h4>';
                    echo '        <p class="product-category">' . htmlspecialchars($product['category']) . '</p>';
                    echo '        <p class="product-description">' . nl2br(htmlspecialchars($product['description'])) . '</p>';
                    echo '        <p class="product-price">$' . htmlspecialchars(number_format($product['price'], 2)) . '</p>';
                    echo '        <form action="cart_actions.php" method="POST" class="add-to-cart-form">';
                    echo '            <input type="hidden" name="product_id" value="' . htmlspecialchars($product['id']) . '">'; // Now $product['id'] is available
                    echo '            <input type="hidden" name="quantity" value="1">'; // Default quantity to add
                    echo '            <button type="submit" name="add_to_cart" class="btn-add-to-cart">Add to Cart</button>';
                    echo '        </form>';
                    echo '    </div>';
                    echo '</div>';
                    $current_delay += $delay_increment;
                }
            } else {
                echo '<p style="grid-column: 1 / -1; text-align:center;">No products currently available. Check back soon!</p>';
            }
        } catch (PDOException $e) {
            echo '<p style="grid-column: 1 / -1; text-align:center; color: red;">Error fetching products: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <section class="content-section product-outro animated-section">
        <h3>More to Explore</h3>
        <p>Our collection is constantly evolving. Sign up for our newsletter to be the first to know about new arrivals and exclusive offers in the world of Rose & Gold.</p>
        <!-- Placeholder for a newsletter signup form -->
    </section>

</main>

<?php require_once 'footer.php'; ?>
