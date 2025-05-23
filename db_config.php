<?php
// Database configuration and setup for SQLite

$db_file = __DIR__ . '/rose_gold_db.sqlite'; // Store DB in the project root
$pdo = null;

try {
    // Create (connect to) SQLite database in file
    $pdo = new PDO('sqlite:' . $db_file);
    // Set errormode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL to create products table if it doesn't exist
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT,
        description TEXT,
        price REAL NOT NULL,
        image_url TEXT 
    );";
    $pdo->exec($createTableSQL);

    // SQL to create users table if it doesn't exist
    $createUsersTableSQL = "
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'user', -- Added role column, defaults to 'user'
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($createUsersTableSQL);

    // SQL to create cart_items table if it doesn't exist
    $createCartItemsTableSQL = "
    CREATE TABLE IF NOT EXISTS cart_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL, -- For logged-in user carts
        -- session_id TEXT, -- Alternatively, for guest carts, store session_id
        product_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 1,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE (user_id, product_id) -- Ensures a user has only one entry per product (quantity is updated)
        -- If using session_id for guest carts, UNIQUE constraint might be (session_id, product_id)
    );";
    $pdo->exec($createCartItemsTableSQL);

    // Optionally, create a default admin user if no users exist
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount == 0) {
        $adminUsername = 'admin';
        $adminEmail = 'admin@roseandgold.example';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT); // Default admin password
        $adminRole = 'admin';
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->bindParam(':username', $adminUsername);
        $stmt->bindParam(':email', $adminEmail);
        $stmt->bindParam(':password', $adminPassword);
        $stmt->bindParam(':role', $adminRole);
        $stmt->execute();
    }
    // Optionally, you could add some initial data here for testing if the table is empty
    // For example:
    // $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    // if ($count == 0) {
    //     $pdo->exec("INSERT INTO products (name, category, description, price, image_url) VALUES ('Sample Product 1', 'Category A', 'This is a great sample product.', 19.99, 'images/sample1.jpg');");
    //     $pdo->exec("INSERT INTO products (name, category, description, price, image_url) VALUES ('Sample Product 2', 'Category B', 'Another excellent sample product.', 29.99, 'images/sample2.jpg');");
    // }

} catch (PDOException $e) {
    // Print error message
    // In a production app, you'd log this or handle it more gracefully
    echo "Database Error: " . $e->getMessage();
    die(); // Stop script execution if DB connection fails
}

// The $pdo object can now be used by other scripts that include this file.
// No need to close the connection here if it's used throughout the script execution.
// PDO connections are automatically closed when the script ends or when the PDO object is destroyed.
?>
