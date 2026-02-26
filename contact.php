
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Redirect to owner dashboard if user is an owner
if ($_SESSION["role"] == 'owner') {
    header("Location: owner_dashboard.php");
    exit();
}

include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $message_text = trim($_POST["message"]);

    if (!empty($name) && !empty($email) && !empty($message_text)) {
        // Validate email
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $message_text);
            if ($stmt->execute()) {
                $message = "Your message has been sent successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Please enter a valid email address.";
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Agri Shop</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: url('images/bgbg.jpg') no-repeat center center/cover;
            background-attachment: fixed;
            position: relative;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            overflow-y: auto;
            margin: 0;
            padding: 0;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(245, 222, 179, 0.68), rgba(255, 248, 220, 0.74));
            z-index: -1;
            animation: fadeGlow 3s infinite alternate;
        }
        .content-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            padding-top: 100px;
            padding-bottom: 20px;
            background: linear-gradient(135deg, rgba(181, 188, 34, 0.18), rgba(29, 153, 106, 0.29));
        }
        header {
            background: rgba(228, 199, 73, 0.26);
            backdrop-filter: blur(12px);
            padding: 20px 40px;
            border-radius: 20px;
            margin: 0 auto 60px;
            max-width: 1400px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease forwards;
            position: sticky;
            top: 0;
            z-index: 100;
            margin-top: -200px;
        }
        header h2 {
            font-size: 2.2em;
            font-weight: 800;
            color: rgba(11, 10, 10, 0.65);
            letter-spacing: 2px;
        }
        header div {
            display: flex;
            align-items: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg,rgb(232, 164, 69),rgb(229, 198, 105));
            color: white;
            font-weight: 600;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-size: 1.1em;
            box-shadow: 0 4px 10px rgb(10, 10, 10);
            margin-right: 20px;
        }
        .btn:hover {
            background: linear-gradient(45deg, #1b5e20, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .contact-section {
            background: transparent;
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            margin: 0 auto 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.41);
            animation: fadeIn 0.7s ease forwards;
            margin-top: 0px;
        }
        .contact-section h2 {
            font-size: 2em;
            color: rgba(11, 10, 10, 0.65);
            margin-bottom: 20px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-align: center;
            border: 5px solid;
            border-radius: 20px;
        }
        .contact-section p {
            font-size: 1.2em;
            color: #333;
            line-height: 1.7;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-container {
            background: transparent;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            border: 2px solid rgba(10, 10, 10, 0.53);
            border-top: none;
            border-bottom-left-radius: 30px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 30px;
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
           
        }
        .form-container input,
        .form-container textarea {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            font-size: 1.1em;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: pulse 1.5s infinite;
        }
        .form-container textarea {
            resize: vertical;
            min-height: 120px;
        }
        .form-container input:focus,
        .form-container textarea:focus {
            transform: scale(1.02);
            box-shadow: 0 0 15px rgba(0, 152, 121, 0.4);
            outline: none;
        }
        .form-container button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg,rgb(232, 164, 69),rgb(229, 198, 105));
            color: rgb(255, 255, 255);
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: transform 0.3s ease, background 0.3s ease;
            animation: slideIn 0.8s ease-out;
        }
        .form-container button:hover {
            transform: scale(1.08);
            background: linear-gradient(45deg, #007b63, #286f9b);
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.1em;
        }
        .success-message {
            color: rgba(6, 29, 235, 0.62);
        }
        .error-message {
            color: red;
        }
        footer {
            background: rgba(46, 125, 50, 0.9);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            border-radius: 20px 20px 0 0;
        }
        footer p {
            font-size: 1em;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <header>
            <h2>Agri Shop</h2>
            <div>
                <a href="index.php" class="btn">Home</a>
                <a href="logout.php" class="btn">Logout</a>
            </div>
        </header>

        <div class="contact-section">
            <h2>Contact Us</h2>
           
            <div class="form-container">
                <?php if (!empty($message)): ?>
                    <p class="<?php echo strpos($message, 'successfully') !== false ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </p>
                <?php endif; ?>
                <form method="post" action="contact.php">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <textarea name="message" placeholder="Your Message" required></textarea>
                    <button type="submit" name="submit_contact">Send Message</button>
                </form>
            </div>
        </div>

        <footer>
            <p>© 2025 Agri Shop. All rights reserved. Empowering farmers, cultivating success.</p>
        </footer>
    </div>
</body>
</html>
