<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != 'owner') {
    header("Location: login.php");
    exit();
}

// Include database connection with error handling
include 'db_connect.php';
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Hardcoded password for demo (in production, store securely in database)
define('OWNER_PASSWORD', '787');

// Initialize owner_accounts with 5000 for each account type if empty
$result = $conn->query("SELECT COUNT(*) as count FROM owner_accounts");
if ($result && $result->fetch_assoc()['count'] == 0) {
    $account_types = ['Main', 'EasyPaisa', 'JazzCash', 'Meezan Bank IDO'];
    foreach ($account_types as $type) {
        $conn->query("INSERT INTO owner_accounts (account_type, amount) VALUES ('$type', 5000.00)");
    }
}
// Fetch contact messages
$contact_query = "SELECT id, name, email, message, submitted_at FROM contact_messages ORDER BY submitted_at DESC";
$contact_result = $conn->query($contact_query);

// Handle product addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_product"])) {
    if (!empty($_POST["name"]) && !empty($_POST["category"]) && isset($_POST["price"]) && isset($_POST["quantity"]) && !empty($_POST["company"]) && !empty($_POST["contact"])) {
        $name = $_POST["name"];
        $category = $_POST["category"];
        $price = floatval($_POST["price"]);
        $quantity = intval($_POST["quantity"]);
        $company = $_POST["company"];
        $contact = $_POST["contact"];
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, quantity, Company, Contact) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssdsss", $name, $category, $price, $quantity, $company, $contact);
            $stmt->execute();
        } else {
            error_log("Prepare failed: " . $conn->error);
        }
    }
}

// Handle product removal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_product"])) {
    if (isset($_POST["product_id"]) && isset($_POST["quantity"])) {
        $product_id = intval($_POST["product_id"]);
        $quantity = intval($_POST["quantity"]);
        $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        if ($stmt) {
            $stmt->bind_param("iii", $quantity, $product_id, $quantity);
            $stmt->execute();
        }
    }
}

// Handle product update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_product"])) {
    if (isset($_POST["product_id"]) && !empty($_POST["name"]) && !empty($_POST["category"]) && isset($_POST["price"]) && isset($_POST["quantity"]) && !empty($_POST["company"]) && !empty($_POST["contact"])) {
        $product_id = intval($_POST["product_id"]);
        $name = $_POST["name"];
        $category = $_POST["category"];
        $price = floatval($_POST["price"]);
        $quantity = intval($_POST["quantity"]);
        $company = $_POST["company"];
        $contact = $_POST["contact"];
        $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, quantity = ?, Company = ?, Contact = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssdsssi", $name, $category, $price, $quantity, $company, $contact, $product_id);
            $stmt->execute();
        }
    }
}

// Handle order status update and payment processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    if (isset($_POST["order_id"]) && isset($_POST["status"])) {
        $order_id = intval($_POST["order_id"]);
        $status = $_POST["status"];
        $stmt = $conn->prepare("SELECT total_price, payment_provider, status AS current_status FROM orders WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $amount = floatval($row['total_price']);
                $payment_provider = trim($row['payment_provider']);
                $current_status = $row['current_status'];

                // Debug the received payment_provider
                error_log("Debug [08:05 PM PKT, 24-Jun-2025]: payment_provider received = " . $payment_provider);

                // Only process payment if status is changing to 'Paid'
                if ($status == 'Paid' && $current_status != 'Paid') {
                    // Determine account_type based on payment_provider
                    if (strcasecmp($payment_provider, 'JazzCash') == 0) {
                        $account_type = 'JazzCash';
                    } elseif (strcasecmp($payment_provider, 'Easypaisa') == 0) {
                        $account_type = 'EasyPaisa';
                    } elseif (strcasecmp($payment_provider, 'Meezan Bank') == 0) {
                        $account_type = 'Meezan Bank IDO';
                    } else {
                        $account_type = 'Main';
                    }
                    error_log("Order $order_id: Payment Provider = $payment_provider, Assigned Account = $account_type, Amount = $amount");

                    // Update the balance for the account_type
                    $stmt_update = $conn->prepare("INSERT INTO owner_accounts (account_type, amount) VALUES (?, ?) ON DUPLICATE KEY UPDATE amount = amount + ?");
                    if ($stmt_update) {
                        $stmt_update->bind_param("sdd", $account_type, $amount, $amount);
                        if ($stmt_update->execute()) {
                            error_log("Updated $account_type balance by $amount for order $order_id");
                            // Log the payment in transactions table
                            $stmt_transaction = $conn->prepare("INSERT INTO transactions (account_type, amount, transfer_type, purpose, status, created_at) VALUES (?, ?, 'Payment', 'Order Payment', 'Completed', NOW())");
                            if ($stmt_transaction) {
                                $stmt_transaction->bind_param("sd", $account_type, $amount);
                                $stmt_transaction->execute();
                            }
                        } else {
                            error_log("Failed to update balance: " . $conn->error);
                        }
                    } else {
                        error_log("Prepare failed: " . $conn->error);
                    }
                }
                $stmt_update_status = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                if ($stmt_update_status) {
                    $stmt_update_status->bind_param("si", $status, $order_id);
                    $stmt_update_status->execute();
                }
            } else {
                error_log("No order found with ID $order_id");
            }
        }
    }
}

// Handle account transfer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["process_transfer"])) {
    if (!empty($_POST["password"]) && !empty($_POST["account_type"]) && !empty($_POST["recipient"]) && isset($_POST["amount"]) && !empty($_POST["purpose"])) {
        $password = $_POST["password"];
        $account_type = $_POST["account_type"];
        $recipient = trim($_POST["recipient"]);
        $amount = floatval($_POST["amount"]);
        $transfer_type = $_POST["transfer_type"];
        $purpose = trim($_POST["purpose"]);
        
        if ($password === OWNER_PASSWORD) {
            $balance_query = $conn->query("SELECT amount FROM owner_accounts WHERE account_type = '$account_type'");
            $current_balance = $balance_query && $balance_query->num_rows > 0 ? $balance_query->fetch_assoc()['amount'] : 0;
            
            if ($current_balance >= $amount && $amount > 0) {
                $stmt = $conn->prepare("INSERT INTO transactions (account_type, recipient, amount, transfer_type, purpose, status, created_at) VALUES (?, ?, ?, ?, ?, 'Completed', NOW())");
                if ($stmt) {
                    $stmt->bind_param("ssdss", $account_type, $recipient, $amount, $transfer_type, $purpose);
                    if ($stmt->execute()) {
                        $stmt = $conn->prepare("UPDATE owner_accounts SET amount = amount - ? WHERE account_type = ?");
                        if ($stmt) {
                            $stmt->bind_param("ds", $amount, $account_type);
                            $stmt->execute();
                        }
                        $transfer_message = "Transfer of " . number_format($amount, 2) . " PKR to $recipient ($account_type) completed successfully!";
                    } else {
                        $transfer_error = "Transfer failed: " . $conn->error;
                    }
                } else {
                    $transfer_error = "Prepare failed: " . $conn->error;
                }
            } else {
                $transfer_error = "Insufficient balance or invalid amount in $account_type.";
            }
        } else {
            $transfer_error = "Incorrect password.";
        }
    } else {
        $transfer_error = "All fields are required.";
    }
}

// Handle search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$sql = "SELECT * FROM products WHERE 1=1";
if ($search) {
    $sql .= " AND (name LIKE ? OR category LIKE ? OR Company LIKE ? OR Contact LIKE ?)";
}
if ($filter == 'low_stock') {
    $sql .= " AND quantity < 10";
}
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($search) {
        $search_term = "%$search%";
        $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = new mysqli_result($conn); // Fallback empty result
}

// Customer orders query
$customer_sql = "SELECT u.username, o.id, o.quantity, o.total_price, o.order_date, o.status, o.payment_method, o.payment_provider 
                FROM users u JOIN orders o ON u.id = o.user_id";
$customer_result = $conn->query($customer_sql) ?: new mysqli_result($conn);

// Categories query with error handling
$categories_sql = "SELECT * FROM categories";
$categories_result = $conn->query($categories_sql);
if (!$categories_result) {
    error_log("Categories table query failed: " . $conn->error);
    $categories_result = new mysqli_result($conn); // Fallback empty result
}

// Transactions query
$transactions_sql = "SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10";
$transactions_result = $conn->query($transactions_sql) ?: new mysqli_result($conn);

// Fetch individual account balances
$accounts_result = $conn->query("SELECT account_type, amount FROM owner_accounts");
$account_balances = [];
if ($accounts_result) {
    while ($row = $accounts_result->fetch_assoc()) {
        $account_balances[$row['account_type']] = $row['amount'];
    }
}

// Analytics queries
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'] ?? 0;
$total_revenue = $conn->query("SELECT SUM(total_price) as sum FROM orders")->fetch_assoc()['sum'] ?? 0;
$account_balance = $conn->query("SELECT SUM(amount) as sum FROM owner_accounts")->fetch_assoc()['sum'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Agri Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            background: url('images/agri_background.jpg') no-repeat center center/cover;
            position: relative;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.75);
            z-index: -1;
        }
        .bg-agri-green {
            background: linear-gradient(45deg, #2e7d32, #4caf50);
        }
        .hover-bg-agri-green:hover {
            background: linear-gradient(45deg, #1b5e20, #388e3c);
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease, opacity 0.5s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: slideIn 0.5s ease forwards;
        }
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.3s; }
        .card:nth-child(3) { animation-delay: 0.5s; }
        .card:nth-child(4) { animation-delay: 0.7s; }
        .card:nth-child(5) { animation-delay: 0.9s; }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .table-row {
            transition: background-color 0.3s ease;
        }
        .table-row:hover {
            background-color: rgba(46, 125, 50, 0.1);
        }
        .input-field {
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.2);
            transform: scale(1.02);
        }
        .btn {
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .modal {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .modal-hidden {
            opacity: 0;
            transform: scale(0.95);
            pointer-events: none;
        }
        .modal-visible {
            opacity: 1;
            transform: scale(1);
            pointer-events: auto;
        }
        .low-stock {
            background-color: rgba(239, 68, 68, 0.1);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        header {
            animation: fadeIn 0.5s ease forwards;
        }
        table {
            animation: fadeIn 0.7s ease forwards;
        }
        #accountDetails {
            display: none;
            margin-top: 1rem;
        }
        #accountDetails.show {
            display: block;
        }
       .content-section1 {
    background: #fff;
    padding: 2rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    margin-bottom: 2rem;
    opacity: 0;
    transform: translateY(20px);
    animation: slideIn 0.5s ease forwards;
    animation-delay: 1.1s;
}

.a1 {
    width: 100%;
    overflow-x: auto;
    max-width: 100%;
    margin: 0 auto;
}

.new {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.new thead {
    background: linear-gradient(45deg, #2e7d32, #4caf50);
}

.new th {
    padding: 1rem;
    color: #fff;
    font-weight: 600;
    text-align: left;
    font-size: 0.875rem;
}

.new td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
}

.new tbody tr {
    transition: background-color 0.3s ease;
}

.new tbody tr:hover {
    background-color: rgba(46, 125, 50, 0.1);
}

    </style>
</head>
<body class="font-sans">
    <div class="container mx-auto p-6 max-w-7xl relative z-10">
        <header class="flex justify-between items-center mb-8 bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-3xl font-extrabold text-gray-800">Owner Dashboard</h2>
            <a href="logout.php" class="text-white bg-agri-green px-6 py-3 rounded-full hover-bg-agri-green font-semibold btn">Logout</a>
        </header>

        <!-- Analytics Section -->
        <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 card">
            <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                <h4 class="text-lg font-semibold text-gray-700">Total Products</h4>
                <p class="text-3xl font-bold text-agri-green"><?php echo $total_products; ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                <h4 class="text-lg font-semibold text-gray-700">Total Orders</h4>
                <p class="text-3xl font-bold text-agri-green"><?php echo $total_orders; ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                <h4 class="text-lg font-semibold text-gray-700">Total Revenue</h4>
                <p class="text-3xl font-bold text-agri-green"><?php echo number_format($total_revenue, 2); ?> PKR</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                <h4 class="text-lg font-semibold text-gray-700">Account Balance</h4>
                <p class="text-3xl font-bold text-agri-green"><?php echo number_format($account_balance, 2); ?> PKR</p>
            </div>
        </section>

        <!-- Manage Categories Section -->
        <section class="bg-white p-8 rounded-xl shadow-lg mb-8 card">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-agri-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18" />
                </svg>
                Manage Categories
            </h3>
            <form method="post" class="mb-6">
                <div class="flex gap-4">
                    <input type="text" name="category_name" placeholder="Category Name" required class="p-3 border rounded-lg input-field w-full">
                    <button type="submit" name="add_category" class="bg-agri-green text-white p-3 rounded-lg hover-bg-agri-green font-semibold btn">Add Category</button>
                </div>
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_category"]) && !empty($_POST["category_name"])) {
                    echo '<p class="text-green-600 mt-2">Category added successfully!</p>';
                }
                ?>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-agri-green text-white">
                            <th class="p-4 font-semibold">ID</th>
                            <th class="p-4 font-semibold">Name</th>
                            <th class="p-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $categories_result->fetch_assoc()): ?>
                            <tr class="border-b table-row">
                                <td class="p-4"><?php echo $row["id"]; ?></td>
                                <td class="p-4"><?php echo $row["name"]; ?></td>
                                <td class="p-4">
                                    <form method="post" class="inline">
                                        <input type="hidden" name="category_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_category" class="text-red-600 underline btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Total Stock Section -->
        <section class="bg-white p-8 rounded-xl shadow-lg mb-8 card">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-agri-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V11a2 2 0 012-2z" />
                </svg>
                Total Stock
            </h3>
            <form method="get" class="flex gap-4 mb-6">
                <input type="text" name="search" placeholder="Search by name, category, company, or contact" value="<?php echo htmlspecialchars($search); ?>" class="p-3 border rounded-lg input-field w-full md:w-1/2">
                <select name="filter" class="p-3 border rounded-lg input-field">
                    <option value="">All Products</option>
                    <option value="low_stock" <?php if ($filter == 'low_stock') echo 'selected'; ?>>Low Stock</option>
                </select>
                <button type="submit" class="bg-agri-green text-white p-3 rounded-lg hover-bg-agri-green font-semibold btn">Search</button>
            </form>
            <form method="post" enctype="multipart/form-data" class="mb-6">
                <div class="flex gap-4">
                    <input type="file" name="csv_file" accept=".csv" required class="p-3 border rounded-lg input-field">
                    <button type="submit" class="bg-agri-green text-white p-3 rounded-lg hover-bg-agri-green font-semibold btn">Import Products</button>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-agri-green text-white">
                            <th class="p-4 font-semibold">ID</th>
                            <th class="p-4 font-semibold">Name</th>
                            <th class="p-4 font-semibold">Category</th>
                            <th class="p-4 font-semibold">Price</th>
                            <th class="p-4 font-semibold">Quantity</th>
                            <th class="p-4 font-semibold">Company</th>
                            <th class="p-4 font-semibold">Contact</th>
                            <th class="p-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b table-row <?php if ($row['quantity'] < 10) echo 'low-stock'; ?>">
                                <td class="p-4"><?php echo $row["id"]; ?></td>
                                <td class="p-4"><?php echo $row["name"]; ?></td>
                                <td class="p-4"><?php echo $row["category"]; ?></td>
                                <td class="p-4"><?php echo number_format($row["price"], 2); ?> PKR</td>
                                <td class="p-4"><?php echo $row["quantity"]; ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($row["Company"] ?? 'N/A'); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($row["Contact"] ?? 'N/A'); ?></td>
                                <td class="p-4">
                                    <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['category']); ?>', <?php echo $row['price']; ?>, <?php echo $row['quantity']; ?>, '<?php echo htmlspecialchars($row['Company'] ?? ''); ?>', '<?php echo htmlspecialchars($row['Contact'] ?? ''); ?>')" class="text-agri-green underline btn">Edit</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Manage Stock Section -->
        <section class="bg-white p-8 rounded-xl shadow-lg mb-8 card">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-agri-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Manage Stock
            </h3>
            <form method="post" class="grid grid-cols-1 md:grid-cols-7 gap-4 mb-6">
                <input type="text" name="name" placeholder="Product Name" required class="p-3 border rounded-lg input-field">
                <select name="category" required class="p-3 border rounded-lg input-field">
                    <?php
                    $categories_result_local = $conn->query("SELECT * FROM categories");
                    if ($categories_result_local && $categories_result_local->num_rows > 0) {
                        while ($cat = $categories_result_local->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($cat['name']) . "'>" . htmlspecialchars($cat['name']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No categories available</option>";
                    }
                    ?>
                </select>
                <input type="number" name="price" placeholder="Price" step="0.01" required class="p-3 border rounded-lg input-field">
                <input type="number" name="quantity" placeholder="Quantity" required class="p-3 border rounded-lg input-field">
                <input type="text" name="company" placeholder="Company" required class="p-3 border rounded-lg input-field">
                <input type="text" name="contact" placeholder="Contact" required class="p-3 border rounded-lg input-field">
                <button type="submit" name="add_product" class="bg-agri-green text-white p-3 rounded-lg hover-bg-agri-green font-semibold btn">Add Product</button>
            </form>
            <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="number" name="product_id" placeholder="Product ID" required class="p-3 border rounded-lg input-field">
                <input type="number" name="quantity" placeholder="Quantity to Remove" required class="p-3 border rounded-lg input-field">
                <button type="submit" name="remove_product" class="bg-red-600 text-white p-3 rounded-lg hover:bg-red-700 font-semibold btn">Remove Stock</button>
            </form>
        </section>

        <!-- Account Management Section -->
        <section class="bg-white p-8 rounded-xl shadow-lg mb-8 card">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-agri-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a5 5 0 00-10 0v2a3 3 0 00-3 3v7a3 3 0 003 3h10a3 3 0 003-3v-7a3 3 0 00-3-3zM9 9V7a3 3 0 016 0v2H9z" />
                </svg>
                Account Management
            </h3>
            <?php if (isset($transfer_message)): ?>
                <p class="text-green-600 mb-4"><?php echo $transfer_message; ?></p>
            <?php endif; ?>
            <?php if (isset($transfer_error)): ?>
                <p class="text-red-600 mb-4"><?php echo $transfer_error; ?></p>
            <?php endif; ?>
            <form method="post" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <select name="account_type" required class="p-3 border rounded-lg input-field">
                    <option value="">Select Account Type</option>
                    <option value="Main">Main (Cash)</option>
                    <option value="EasyPaisa">EasyPaisa</option>
                    <option value="JazzCash">JazzCash</option>
                    <option value="Meezan Bank IDO">Meezan Bank IDO</option>
                </select>
                <input type="text" name="recipient" placeholder="Recipient Account Number" required class="p-3 border rounded-lg input-field">
                <input type="number" name="amount" placeholder="Amount (PKR)" step="0.01" required class="p-3 border rounded-lg input-field">
                <select name="transfer_type" required class="p-3 border rounded-lg input-field">
                    <option value="Self">To My Account</option>
                    <option value="Other">To Other Account</option>
                </select>
                <input type="text" name="purpose" placeholder="Purpose" required class="p-3 border rounded-lg input-field">
                <input type="password" name="password" placeholder="Enter Password" required class="p-3 border rounded-lg input-field">
                <button type="submit" name="process_transfer" class="bg-agri-green text-white p-3 rounded-lg hover-bg-agri-green font-semibold btn col-span-1 md:col-span-5">Process Transfer</button>
            </form>
            <h4 class="text-lg font-semibold text-gray-700 mb-4">Recent Transactions</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-agri-green text-white">
                            <th class="p-4 font-semibold">ID</th>
                            <th class="p-4 font-semibold">Account Type</th>
                            <th class="p-4 font-semibold">Recipient</th>
                            <th class="p-4 font-semibold">Amount</th>
                            <th class="p-4 font-semibold">Transfer Type</th>
                            <th class="p-4 font-semibold">Purpose</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $transactions_result->fetch_assoc()): ?>
                            <tr class="border-b table-row">
                                <td class="p-4"><?php echo $row["id"]; ?></td>
                                <td class="p-4"><?php echo $row["account_type"]; ?></td>
                                <td class="p-4"><?php echo $row["recipient"] ?? 'N/A'; ?></td>
                                <td class="p-4"><?php echo number_format($row["amount"], 2); ?> PKR</td>
                                <td class="p-4"><?php echo $row["transfer_type"]; ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($row["purpose"] ?? 'N/A'); ?></td>
                                <td class="p-4"><?php echo $row["status"]; ?></td>
                                <td class="p-4"><?php echo $row["created_at"]; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button id="showAccountsBtn" class="bg-agri-green text-white p-3 rounded-lg hover-bg-agri-green font-semibold btn mt-4">Show Account Balances</button>
            <div id="accountDetails" class="bg-white p-4 rounded-xl shadow-lg mt-4">
                <h4 class="text-lg font-semibold text-gray-700">Individual Account Balances</h4>
                <?php if (empty($account_balances)): ?>
                    <p class="text-gray-600">No accounts available.</p>
                <?php else: ?>
                    <table class="w-full text-left border-collapse mt-2">
                        <thead>
                            <tr class="bg-agri-green text-white">
                                <th class="p-4 font-semibold">Account Type</th>
                                <th class="p-4 font-semibold">Balance (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $account_types = ['Main', 'EasyPaisa', 'JazzCash', 'Meezan Bank IDO'];
                            foreach ($account_types as $type) {
                                $balance = $account_balances[$type] ?? 0;
                                echo "<tr class='border-b table-row'>";
                                echo "<td class='p-4'>" . htmlspecialchars($type) . "</td>";
                                echo "<td class='p-4'>" . number_format($balance, 2) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>

        <!-- Customer Details Section -->
        <section class="bg-white p-8 rounded-xl shadow-lg card">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-2 text-agri-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Customer Details
                </div>
                <a href="?export_orders=1" class="text-white bg-agri-green px-4 py-2 rounded-lg hover-bg-agri-green font-semibold btn">Export Orders</a>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-agri-green text-white">
                            <th class="p-4 font-semibold">Username</th>
                            <th class="p-4 font-semibold">Quantity</th>
                            <th class="p-4 font-semibold">Total Price</th>
                            <th class="p-4 font-semibold">Order Date</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold">Payment Method</th>
                            <th class="p-4 font-semibold">Payment Provider</th>
                            <th class="p-4 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $customer_result->fetch_assoc()): ?>
                            <tr class="border-b table-row">
                                <td class="p-4"><?php echo $row["username"]; ?></td>
                                <td class="p-4"><?php echo $row["quantity"]; ?></td>
                                <td class="p-4"><?php echo number_format($row["total_price"], 2); ?> PKR</td>
                                <td class="p-4"><?php echo $row["order_date"]; ?></td>
                                <td class="p-4">
                                    <form method="post">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <select name="status" class="p-2 border rounded-lg input-field" onchange="this.form.submit()">
                                            <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                            <option value="Shipped" <?php if ($row['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                            <option value="Delivered" <?php if ($row['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                            <option value="Paid" <?php if ($row['status'] == 'Paid') echo 'selected'; ?>>Paid</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td class="p-4"><?php echo $row["payment_method"]; ?></td>
                                <td class="p-4"><?php echo $row["payment_provider"]; ?></td>
                                <td class="p-4">
                                    <?php if (isset($row['id'])): ?>
                                        <button onclick="openOrderModal(<?php echo $row['id']; ?>)" class="text-agri-green underline btn">View</button>
                                    <?php else: ?>
                                        <span class="text-gray-500">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Edit Product Modal -->
        <div id="editModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center modal-hidden">
            <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Edit Product</h3>
                <form method="post">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <input type="text" name="name" id="edit_name" placeholder="Product Name" required class="p-3 border rounded-lg input-field mb-4 w-full">
                    <select name="category" id="edit_category" required class="p-3 border rounded-lg input-field mb-4 w-full">
                        <?php
                        $categories_result_local = $conn->query("SELECT * FROM categories");
                        if ($categories_result_local && $categories_result_local->num_rows > 0) {
                            while ($cat = $categories_result_local->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($cat['name']) . "'>" . htmlspecialchars($cat['name']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>No categories available</option>";
                        }
                        ?>
                    </select>
                    <input type="number" name="price" id="edit_price" placeholder="Price" step="0.01" required class="p-3 border rounded-lg input-field mb-4 w-full">
                    <input type="number" name="quantity" id="edit_quantity" placeholder="Quantity" required class="p-3 border rounded-lg input-field mb-4 w-full">
                    <input type="text" name="company" id="edit_company" placeholder="Company" required class="p-3 border rounded-lg input-field mb-4 w-full">
                    <input type="text" name="contact" id="edit_contact" placeholder="Contact" required class="p-3 border rounded-lg input-field mb-4 w-full">
                    <div class="flex justify-end gap-4">
                        <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white p-3 rounded-lg btn">Cancel</button>
                        <button type="submit" name="update_product" class="bg-agri-green text-white p-3 rounded-lg hover-bg-agri-green font-semibold btn">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Details Modal -->
        <div id="orderModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center modal-hidden">
            <div class="bg-white p-8 rounded-xl shadow-lg max-w-lg w-full">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Order Details</h3>
                <div id="orderContent" class="overflow-x-auto">
                    <!-- Content will be loaded via JS -->
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" onclick="closeOrderModal()" class="bg-gray-500 text-white p-3 rounded-lg btn">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Customer Messages Section -->
<!-- Customer Messages Section -->
<div class="content-section1">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-agri-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        Customer Messages
    </h3>
    <div class="a1">
        <table class="new">
            <thead>
                <tr class="bg-agri-green text-white">
                    <th class="p-4 font-semibold">ID</th>
                    <th class="p-4 font-semibold">Name</th>
                    <th class="p-4 font-semibold">Email</th>
                    <th class="p-4 font-semibold">Message</th>
                    <th class="p-4 font-semibold">Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $contact_result->fetch_assoc()): ?>
                    <tr class="border-b table-row">
                        <td class="p-4"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['message']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($row['submitted_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
    <script>
        function openEditModal(id, name, category, price, quantity, company, contact) {
            document.getElementById('edit_product_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_company').value = company;
            document.getElementById('edit_contact').value = contact;
            document.getElementById('editModal').classList.remove('modal-hidden');
            document.getElementById('editModal').classList.add('modal-visible');
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.classList.remove('modal-visible');
                modal.classList.add('modal-hidden');
            }
        }

        function openOrderModal(orderId) {
            const content = document.getElementById('orderContent');
            if (content) {
                content.innerHTML = 'Loading...';
                fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?get_order=' + orderId)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            content.innerHTML = `<p class="text-red-600">Error: ${data.error}</p>`;
                        } else if (data.length > 0) {
                            let html = '<table class="w-full text-left border-collapse"><thead><tr class="bg-agri-green text-white"><th class="p-4">Product</th><th class="p-4">Quantity</th><th class="p-4">Price</th></tr></thead><tbody>';
                            data.forEach(row => {
                                html += `<tr class="border-b"><td class="p-4">${row.name || 'N/A'}</td><td class="p-4">${row.quantity || 'N/A'}</td><td class="p-4">${parseFloat(row.total_price || 0).toFixed(2)} PKR</td></tr>`;
                            });
                            html += '</tbody></table>';
                            content.innerHTML = html;
                        } else {
                            content.innerHTML = '<p>No order details available.</p>';
                        }
                    })
                    .catch(error => {
                        content.innerHTML = `<p class="text-red-600">Error loading order details: ${error.message}</p>`;
                    });
                const modal = document.getElementById('orderModal');
                if (modal) {
                    modal.classList.remove('modal-hidden');
                    modal.classList.add('modal-visible');
                }
            }
        }

        function closeOrderModal() {
            const modal = document.getElementById('orderModal');
            if (modal) {
                modal.classList.remove('modal-visible');
                modal.classList.add('modal-hidden');
            }
        }

        document.getElementById('showAccountsBtn').addEventListener('click', function() {
            const details = document.getElementById('accountDetails');
            details.classList.toggle('show');
            this.textContent = details.classList.contains('show') ? 'Hide Account Balances' : 'Show Account Balances';
        });
    </script>
    <?php
    // Handle AJAX request for order details
    if (isset($_GET['get_order'])) {
        header('Content-Type: application/json');
        $order_id = intval($_GET['get_order']);
        $stmt = $conn->prepare("SELECT p.name, o.quantity, o.total_price FROM orders o JOIN products p ON o.product_id = p.id WHERE o.id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $order_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $order = [];
                while ($row = $result->fetch_assoc()) {
                    $order[] = $row;
                }
                echo json_encode($order);
            } else {
                error_log("Query execution failed for order ID $order_id: " . $conn->error);
                echo json_encode(['error' => 'Query execution failed: ' . $conn->error]);
            }
        } else {
            error_log("Prepare failed for order ID $order_id: " . $conn->error);
            echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
        }
        exit();
    }

    
    ?>