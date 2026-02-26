<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "first_data";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agri Shop Products</title>
    <style>
        table {
            border-collapse: collapse;
            width: 70%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #009879;
            color: white;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Agri Shop Product List</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Category</th>
        <th>Price (PKR)</th>
        <th>Quantity</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>" . $row["id"]. "</td>
                <td>" . $row["name"]. "</td>
                <td>" . $row["category"]. "</td>
                <td>" . $row["price"]. "</td>
                <td>" . $row["quantity"]. "</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No products found</td></tr>";
    }

    $conn->close();
    ?>
</table>

</body>
</html>
