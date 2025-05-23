<?php 
// Session check should be done after header.php (which starts the session) or ensure session is started here first.
// Since header.php now starts the session, we can check it directly.
// However, for a protected page, it's common to do the check *before* any HTML output.
// Let's ensure session is started here if not by header (though header.php should handle it)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Protect this page: redirect to login if user is not logged in or not an admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect_message=" . urlencode("You must be logged in to access this page."));
    exit;
} elseif ($_SESSION['role'] !== 'admin') {
    // Optionally, redirect to a different page or show an access denied message on the current page
    // For simplicity, redirecting to homepage with an error message
    $_SESSION['error_message_permission'] = "You do not have permission to access the admin area.";
    header("Location: index.php"); 
    exit;
}

require_once 'db_config.php'; // Establishes $pdo connection and creates table if not exists
require_once 'header.php';   // Site header

// Initialize variables for messages or form persistence
$message = '';
$error_message = '';
$edit_product = null; // For pre-filling the form in edit mode

// --- Q3: Super global area $_POST & Q4: Insert statement ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $image_url = trim($_POST['image_url']);

    if (empty($name) || $price === false || $price < 0) {
        $error_message = "Product Name and a valid Price are required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, category, description, price, image_url) VALUES (:name, :category, :description, :price, :image_url)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':image_url', $image_url);
            
            if ($stmt->execute()) {
                $message = "Product added successfully!";
            } else {
                $error_message = "Failed to add product.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// --- Q6: Delete statement ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt->bindParam(':id', $delete_id);
            if ($stmt->execute()) {
                $message = "Product deleted successfully!";
            } else {
                $error_message = "Failed to delete product.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    } else {
        $error_message = "Invalid product ID for deletion.";
    }
}

// --- Part of Q7: Fetch product for editing when $_GET['edit_id'] is set ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    if ($edit_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->bindParam(':id', $edit_id);
            $stmt->execute();
            $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$edit_product) {
                $error_message = "Product not found for editing.";
                $edit_product = null; // Reset if not found
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
            $edit_product = null;
        }
    } else {
        $error_message = "Invalid product ID for editing.";
    }
}

// --- Q7: Update statement (part 2 - handling form submission) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $image_url = trim($_POST['image_url']);

    if (!$product_id) {
        $error_message = "Invalid Product ID for update.";
    } elseif (empty($name) || $price === false || $price < 0) {
        $error_message = "Product Name and a valid Price are required for update.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = :name, category = :category, description = :description, price = :price, image_url = :image_url WHERE id = :id");
            $stmt->bindParam(':id', $product_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':image_url', $image_url);
            
            if ($stmt->execute()) {
                $message = "Product updated successfully!";
                // Consider redirecting or clearing edit_product state here
                // header("Location: admin_products.php?message=" . urlencode($message));
                // exit;
                $edit_product = null; // Clear edit mode after successful update
            } else {
                $error_message = "Failed to update product.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
    // If update failed and we were in edit mode, we might want to repopulate $edit_product
    // or let the form retain its submitted values. For simplicity, current $edit_product might be stale if update fails.
    // A better UX would re-fetch or use POSTed values to repopulate.
    if ($error_message && $product_id && !$edit_product) { // If error and not already in edit mode (e.g. from GET)
        // Repopulate $edit_product with submitted values to keep form filled on error
        $edit_product = [
            'id' => $product_id, 
            'name' => $_POST['name'], 
            'category' => $_POST['category'], 
            'description' => $_POST['description'], 
            'price' => $_POST['price'], 
            'image_url' => $_POST['image_url']
        ];
    }
}

?>

<main class="content-area admin-page">
    <section class="page-title-section animated-section">
        <h1>Manage Products</h1>
        <p class="subtitle">Add, view, edit, and delete products in the Rose & Gold collection.</p>
    </section>

    <?php if ($message): ?>
        <div class="message success animated-section"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="message error animated-section"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Add/Edit Product Form (Q3, Q4, Q7) -->
    <section class="content-section add-product-form-section animated-section">
        <h3><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h3>
        <form action="admin_products.php" method="POST">
            <?php if ($edit_product): ?>
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($edit_product['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($edit_product['category'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($edit_product['price'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="image_url">Image URL (e.g., images/product.jpg):</label>
                <input type="text" id="image_url" name="image_url" value="<?php echo htmlspecialchars($edit_product['image_url'] ?? ''); ?>">
            </div>
            
            <div class="form-actions">
                <?php if ($edit_product): ?>
                    <button type="submit" name="update_product" class="btn-primary">Update Product</button>
                    <a href="admin_products.php" class="btn-secondary">Cancel Edit</a>
                <?php else: ?>
                    <button type="submit" name="add_product" class="btn-primary">Add Product</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- Product List (Q5, Q6) -->
    <section class="content-section product-list-section animated-section">
        <h3>Current Products</h3>
        <div class="admin-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // TODO: Fetch and display products from database (Q5)
                    // Example row:
                    // echo "<tr>";
                    // echo "<td>1</td>";
                    // echo "<td>Sample Product</td>";
                    // echo "<td>Sample Category</td>";
                    // echo "<td>$19.99</td>";
                    // echo "<td>";
                    // echo "<a href='admin_products.php?edit_id=1' class='btn-edit'>Edit</a> ";
                    // echo "<form action='admin_products.php' method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this product?\");'>";
                    // echo "<input type='hidden' name='delete_id' value='1'>";
                    // echo "<button type='submit' name='delete_product' class='btn-delete'>Delete</button>";
                    // echo "</form>";
                    // echo "</td>";
                    // echo "</tr>";
                    ?>
                    <?php
                    // --- Q5: Select statement ---
                    try {
                        $stmt_select = $pdo->query("SELECT id, name, category, price FROM products ORDER BY id DESC");
                        $products = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

                        if (count($products) > 0) {
                            foreach ($products as $product) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($product['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($product['category']) . "</td>";
                                echo "<td>$" . htmlspecialchars(number_format($product['price'], 2)) . "</td>";
                                echo "<td>";
                                // TODO: Add Edit and Delete buttons/forms here (for Q6, Q7)
                                echo "<a href='admin_products.php?edit_id=" . htmlspecialchars($product['id']) . "' class='btn-edit'>Edit</a> ";
                                echo "<form action='admin_products.php' method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this product?\");'>";
                                echo "<input type='hidden' name='delete_id' value='" . htmlspecialchars($product['id']) . "'>";
                                echo "<button type='submit' name='delete_product' class='btn-delete'>Delete</button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center;'>No products found. Add some!</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='5' style='text-align:center;'>Error fetching products: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once 'footer.php'; // Site footer ?>
