<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_config.php'; // For $pdo

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store intended action and redirect to login
    // For simplicity, just redirecting with a generic message for now.
    // A more advanced system might store the cart action to resume after login.
    $_SESSION['redirect_message'] = "You need to login to manage your cart.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$action_message = '';
$action_error = '';

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if ($product_id && $quantity && $quantity > 0) {
        try {
            // Check if product already in cart for this user
            $stmt_check = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id");
            $stmt_check->bindParam(':user_id', $user_id);
            $stmt_check->bindParam(':product_id', $product_id);
            $stmt_check->execute();
            $existing_item = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existing_item) {
                // Update quantity
                $new_quantity = $existing_item['quantity'] + $quantity;
                $stmt_update = $pdo->prepare("UPDATE cart_items SET quantity = :quantity WHERE id = :id");
                $stmt_update->bindParam(':quantity', $new_quantity);
                $stmt_update->bindParam(':id', $existing_item['id']);
                if ($stmt_update->execute()) {
                    $action_message = "Product quantity updated in cart!";
                } else {
                    $action_error = "Failed to update product quantity.";
                }
            } else {
                // Insert new item
                $stmt_insert = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $stmt_insert->bindParam(':user_id', $user_id);
                $stmt_insert->bindParam(':product_id', $product_id);
                $stmt_insert->bindParam(':quantity', $quantity);
                if ($stmt_insert->execute()) {
                    $action_message = "Product added to cart!";
                } else {
                    $action_error = "Failed to add product to cart.";
                }
            }
        } catch (PDOException $e) {
            $action_error = "Database error: " . $e->getMessage();
        }
    } else {
        $action_error = "Invalid product data or quantity.";
    }
    // Store messages in session to display on the next page (e.g., products page or cart page)
    if ($action_message) $_SESSION['cart_action_message'] = $action_message;
    if ($action_error) $_SESSION['cart_action_error'] = $action_error;

    // Redirect back to products page or to cart page
    // For now, redirecting to cart page to see the result
    header("Location: cart.php");
    exit;
}

// Handle Update Cart Item Quantity (from cart.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart_item'])) {
    $cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);
    $new_quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if ($cart_item_id && $new_quantity && $new_quantity > 0) {
        try {
            // Ensure the cart item belongs to the current user before updating
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = :quantity WHERE id = :cart_item_id AND user_id = :user_id");
            $stmt->bindParam(':quantity', $new_quantity);
            $stmt->bindParam(':cart_item_id', $cart_item_id);
            $stmt->bindParam(':user_id', $user_id);
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $_SESSION['cart_action_message'] = "Cart updated successfully.";
            } else {
                $_SESSION['cart_action_error'] = "Failed to update cart or item not found.";
            }
        } catch (PDOException $e) {
            $_SESSION['cart_action_error'] = "Database error: " . $e->getMessage();
        }
    } elseif ($cart_item_id && $new_quantity !== false && $new_quantity == 0) { // If quantity is set to 0, remove item
        // Delegate to remove logic or implement here
        // For now, let's treat 0 as remove
        try {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id");
            $stmt->bindParam(':cart_item_id', $cart_item_id);
            $stmt->bindParam(':user_id', $user_id);
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $_SESSION['cart_action_message'] = "Item removed from cart.";
            } else {
                $_SESSION['cart_action_error'] = "Failed to remove item or item not found.";
            }
        } catch (PDOException $e) {
            $_SESSION['cart_action_error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['cart_action_error'] = "Invalid data for cart update.";
    }
    header("Location: cart.php");
    exit;
}


// Handle Remove Item from Cart (from cart.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);

    if ($cart_item_id) {
        try {
            // Ensure the cart item belongs to the current user before deleting
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id");
            $stmt->bindParam(':cart_item_id', $cart_item_id);
            $stmt->bindParam(':user_id', $user_id);
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $_SESSION['cart_action_message'] = "Item removed from cart.";
            } else {
                $_SESSION['cart_action_error'] = "Failed to remove item or item not found.";
            }
        } catch (PDOException $e) {
            $_SESSION['cart_action_error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['cart_action_error'] = "Invalid item ID for removal.";
    }
    header("Location: cart.php");
    exit;
}

// Handle Clear Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cart'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        if ($stmt->execute()) {
            $_SESSION['cart_action_message'] = "Cart cleared successfully.";
        } else {
            $_SESSION['cart_action_error'] = "Failed to clear cart.";
        }
    } catch (PDOException $e) {
        $_SESSION['cart_action_error'] = "Database error: " . $e->getMessage();
    }
    header("Location: cart.php");
    exit;
}


// If no specific action matched, redirect to cart or homepage
header("Location: cart.php");
exit;
?>
