<?php 
// Ensure session is started (header.php should do this, but good practice for standalone access)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_config.php'; // For $pdo
require_once 'header.php';   // Site header
$page_title = "Shopping Cart - Rose & Gold";

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_message'] = "Please login to view your cart.";
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$cart_items = [];
$subtotal = 0;
$shipping_cost = 15.00; // Placeholder
$grand_total = 0;

try {
    $stmt = $pdo->prepare("
        SELECT ci.id as cart_item_id, ci.quantity, p.id as product_id, p.name, p.price, p.image_url, p.category
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = :user_id
        ORDER BY ci.added_at DESC
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $grand_total = $subtotal + $shipping_cost; // Assuming coupon is not yet applied

} catch (PDOException $e) {
    // Handle error, maybe display a message
    $cart_error_message = "Error fetching cart items: " . $e->getMessage();
}

// Display and clear action messages from session
$cart_action_message = '';
if (isset($_SESSION['cart_action_message'])) {
    $cart_action_message = $_SESSION['cart_action_message'];
    unset($_SESSION['cart_action_message']);
}
$cart_action_error = '';
if (isset($_SESSION['cart_action_error'])) {
    $cart_action_error = $_SESSION['cart_action_error'];
    unset($_SESSION['cart_action_error']);
}

?>

<main class="content-area cart-page">
    <section class="page-title-section animated-section">
        <h1>Your Shopping Cart</h1>
        <p class="subtitle">Review your selected Rose & Gold treasures before proceeding to checkout.</p>
    </section>

    <?php if ($cart_action_message): ?>
        <div class="message success animated-section"><?php echo htmlspecialchars($cart_action_message); ?></div>
    <?php endif; ?>
    <?php if ($cart_action_error): ?>
        <div class="message error animated-section"><?php echo htmlspecialchars($cart_action_error); ?></div>
    <?php endif; ?>
    <?php if (isset($cart_error_message)): ?>
        <div class="message error animated-section"><?php echo htmlspecialchars($cart_error_message); ?></div>
    <?php endif; ?>


    <section class="content-section cart-items-section animated-section">
        <h3>Items in Your Cart</h3>
        <div class="cart-table">
            <?php if (!empty($cart_items)): ?>
            <div class="cart-table-header">
                <div class="header-item product-col">Product</div>
                <div class="header-item price-col">Price</div>
                <div class="header-item quantity-col">Quantity</div>
                <div class="header-item total-col">Total</div>
                <div class="header-item action-col">Action</div>
            </div>

            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <div class="product-details">
                        <div class="product-image-placeholder-small">
                            <?php 
                            $image_content_cart = !empty($item['image_url']) 
                                ? '<img src="' . htmlspecialchars($item['image_url']) . '" alt="' . htmlspecialchars($item['name']) . '" style="width:100%; height:100%; object-fit: cover;">' 
                                : '<span>' . htmlspecialchars(substr($item['name'], 0, 10)) . '</span>';
                            echo $image_content_cart;
                            ?>
                        </div>
                        <div class="product-info-col">
                            <h4 class="product-name-cart"><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="product-category-cart"><?php echo htmlspecialchars($item['category']); ?></p>
                        </div>
                    </div>
                    <div class="item-price">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></div>
                    <div class="item-quantity">
                        <form action="cart_actions.php" method="POST" class="update-quantity-form">
                            <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($item['cart_item_id']); ?>">
                            <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="0" class="quantity-input"> 
                            <button type="submit" name="update_cart_item" class="btn-update-qty">Update</button> <!-- Hidden or styled subtly -->
                        </form>
                    </div>
                    <div class="item-total">$<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?></div>
                    <div class="item-action">
                        <form action="cart_actions.php" method="POST">
                            <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($item['cart_item_id']); ?>">
                            <button type="submit" name="remove_from_cart" class="btn-remove-item">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php else: ?>
                <div class="cart-empty">
                    <p>Your cart is currently empty.</p>
                    <a href="products.php" class="btn-primary">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($cart_items)): ?>
    <section class="content-section cart-summary-section animated-section">
        <h3>Order Summary</h3>
        <div class="summary-details">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span class="summary-value">$<?php echo htmlspecialchars(number_format($subtotal, 2)); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping (Estimated):</span>
                <span class="summary-value">$<?php echo htmlspecialchars(number_format($shipping_cost, 2)); ?></span>
            </div>
            <div class="summary-row coupon-row">
                <input type="text" placeholder="Enter Coupon Code" class="coupon-input">
                <button class="btn-apply-coupon">Apply</button>
            </div>
            <div class="summary-row total-row">
                <span>Grand Total:</span>
                <span class="summary-value grand-total-value">$<?php echo htmlspecialchars(number_format($grand_total, 2)); ?></span>
            </div>
        </div>
        <div class="cart-actions">
            <a href="products.php" class="btn-secondary">Continue Shopping</a>
            <button class="btn-primary btn-checkout">Proceed to Checkout</button> <!-- Checkout not implemented -->
        </div>
        <div class="cart-actions-extra">
            <form action="cart_actions.php" method="POST">
                <button type="submit" name="clear_cart" class="btn-link btn-clear-cart" onclick="return confirm('Are you sure you want to clear your entire cart?');">Clear Cart</button>
            </form>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
// Add event listeners to quantity inputs to auto-submit their form on change
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        // Find the closest form and submit it
        // This assumes the button is not strictly needed if JS is enabled
        // and the form structure is simple.
        // For a more robust solution, you might click a hidden submit button.
        this.closest('.update-quantity-form').submit();
    });
});
// Hide update buttons if JS is enabled, as change event handles it.
document.querySelectorAll('.btn-update-qty').forEach(button => {
    button.style.display = 'none';
});
</script>

<?php require_once 'footer.php'; ?>
