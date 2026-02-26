<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != 'customer') {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["place_order"])) {
    $product_id = $_POST["product_id"];
    $quantity = $_POST["quantity"];
    $user_id = $_SESSION["user_id"];
    $payment_method = $_POST["payment_method"];
    $payment_provider = $payment_method == 'Online' ? $_POST["payment_provider"] : 'None';

    $product_stmt = $conn->prepare("SELECT price, quantity FROM products WHERE id = ?");
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    $product = $product_result->fetch_assoc();

    if ($product && $product["quantity"] >= $quantity) {
        $total_price = $product["price"] * $quantity;
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price, payment_method, payment_provider, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $order_stmt->bind_param("iiidss", $user_id, $product_id, $quantity, $total_price, $payment_method, $payment_provider);
        if ($order_stmt->execute()) {
            $order_id = $conn->insert_id;
            $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $update_stmt->bind_param("ii", $quantity, $product_id);
            $update_stmt->execute();
        }
    }
}

$sql = "SELECT * FROM products WHERE quantity > 0";
$result = $conn->query($sql);
$order_sql = "SELECT p.name, o.quantity, o.total_price, o.order_date, o.payment_method, o.payment_provider, o.status 
              FROM orders o JOIN products p ON o.product_id = p.id WHERE o.user_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $_SESSION["user_id"]);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Agri Shop</title>
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
        .bg-agri-blue {
            background: linear-gradient(45deg, #1976d2, #42a5f5);
        }
        .hover-bg-agri-blue:hover {
            background: linear-gradient(45deg, #115293, #1e88e5);
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
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .table-row {
            transition: background-color 0.3s ease;
        }
        .table-row:hover {
            background-color: rgba(25, 118, 210, 0.1);
        }
        .input-field {
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.2);
            transform: scale(1.02);
        }
        .btn {
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
    </style>
</head>
<body class="font-sans">
    <div class="container mx-auto p-6 max-w-7xl relative z-10">
        <header class="flex justify-between items-center mb-8 bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-3xl font-extrabold text-gray-800">Customer Dashboard</h2>
<a href="index.php" class="text-white bg-agri-blue px-6 py-3 rounded-full hover-bg-agri-blue font-semibold btn mr-4">Home</a>
            <a href="logout.php" class="text-white bg-agri-blue px-6 py-3 rounded-full hover-bg-agri-blue font-semibold btn">Logout</a>
        </header>

        <!-- Available Products Section -->
        <section class="bg-white p-8 rounded-xl shadow-lg mb-8 card">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-agri-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V11a2 2 0 012-2z" />
                </svg>
                Available Products
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-agri-blue text-white">
                            <th class="p-4 font-semibold">ID</th>
                            <th class="p-4 font-semibold">Name</th>
                            <th class="p-4 font-semibold">Category</th>
                            <th class="p-4 font-semibold">Price</th>
                            <th class="p-4 font-semibold">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b table-row">
                                <td class="p-4"><?php echo $row["id"]; ?></td>
                                <td class="p-4"><?php echo $row["name"]; ?></td>
                                <td class="p-4"><?php echo $row["category"]; ?></td>
                                <td class="p-4"><?php echo number_format($row["price"], 2); ?> PKR</td>
                                <td class="p-4"><?php echo $row["quantity"]; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Place Order Section -->
        <section class="bg-white p-8 rounded-xl shadow-lg mb-8 card">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-agri-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Place Order
            </h3>
            <form method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="number" name="product_id" placeholder="Product ID" required class="p-3 border rounded-lg input-field">
                <input type="number" name="quantity" placeholder="Quantity" required class="p-3 border rounded-lg input-field">
                <select name="payment_method" id="payment_method" required class="p-3 border rounded-lg input-field" onchange="togglePaymentProvider()">
                    <option value="">Select Payment Method</option>
                    <option value="Cash">Cash on Delivery</option>
                    <option value="Online">Online Payment</option>
                </select>
                <select name="payment_provider" id="payment_provider" class="p-3 border rounded-lg input-field hidden">
                    <option value="">Select Payment Provider</option>
                    <option value="JazzCash">JazzCash</option>
                    <option value="Easypaisa">Easypaisa</option>
                    <option value="Meezan Bank">Meezan Bank</option>
                </select>
                <button type="submit" name="place_order" class="bg-agri-blue text-white p-3 rounded-lg hover-bg-agri-blue font-semibold btn">Buy</button>
            </form>
        </section>

        <!-- Your Orders Section -->
        <section class="bg-white p-8 rounded-xl shadow-lg card">
            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-agri-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Your Orders
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-agri-blue text-white">
                            <th class="p-4 font-semibold">Product Name</th>
                            <th class="p-4 font-semibold">Quantity</th>
                            <th class="p-4 font-semibold">Total Price</th>
                            <th class="p-4 font-semibold">Order Date</th>
                            <th class="p-4 font-semibold">Payment Method</th>
                            <th class="p-4 font-semibold">Payment Provider</th>
                            <th class="p-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $order_result->fetch_assoc()): ?>
                            <tr class="border-b table-row">
                                <td class="p-4"><?php echo $row["name"]; ?></td>
                                <td class="p-4"><?php echo $row["quantity"]; ?></td>
                                <td class="p-4"><?php echo number_format($row["total_price"], 2); ?> PKR</td>
                                <td class="p-4"><?php echo $row["order_date"]; ?></td>
                                <td class="p-4"><?php echo $row["payment_method"]; ?></td>
                                <td class="p-4"><?php echo $row["payment_provider"]; ?></td>
                                <td class="p-4"><?php echo $row["status"]; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <script>
        function togglePaymentProvider() {
            const paymentMethod = document.getElementById('payment_method').value;
            const paymentProvider = document.getElementById('payment_provider');
            paymentProvider.classList.toggle('hidden', paymentMethod !== 'Online');
            if (paymentMethod === 'Online') {
                paymentProvider.setAttribute('required', 'required');
            } else {
                paymentProvider.removeAttribute('required');
            }
        }
    </script>
</body>
</html>