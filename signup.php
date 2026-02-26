
<?php
session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST["username"]));
    $password = htmlspecialchars(trim($_POST["password"]));
    $errors = [];

    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'customer')");
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                $message = "Signup successful! <a href='login.php' class='text-agri-green font-semibold'>Click here to login</a>.";
            } else {
                $message = "Error: " . $stmt->error;
            }
        }
    } else {
        $message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Agri Shop</title>
    
    <style>
        body {
            background: url('images/pic1.jpeg') no-repeat center center/cover;
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
            animation: kenBurns 7s infinite ease-in-out;
        }
        @keyframes kenBurns {
            0% { 
                transform: scale(1) translate(0, 0); 
                background-position: center center;
            }
            50% { 
                transform: scale(1) translate(0%,0%); 
                background-position: 80% 0;
            }
            100% { 
                transform: scale(1) translate(0, 0); 
                background-position: center center;
            }
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            animation: fadeGlow 2s infinite alternate, tintShift 10s infinite ease-in-out;
        }
        @keyframes tintShift {
            0% { 
                background: linear-gradient(135deg, rgba(46, 125, 50, 0.3), rgba(76, 175, 80, 0.2));
            }
            15% { 
                background: linear-gradient(135deg, rgba(255, 223, 0, 0.1), rgba(255, 245, 157, 0.1));
            }
            100% { 
                background: linear-gradient(135deg, rgba(255, 221, 0, 0.12), rgba(255, 245, 157, 0.1));
            }
        }
        .split-screen {
            position: absolute;
            top: 0;
            height: 100vh;
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
        }
        .left {
            left: 0;
            background-color: rgba(0, 200, 103, 0.0);
            width: 50%;
        }
        .right {
            right: 0;
            padding: 20px;
            width: 50%;
        }
        .prompt-content {
            height: 150px;
            margin-right: 270px;
            margin-top: -220px;
            padding: 25px;
            text-align: center;
            color: #fff;
            max-width: 380px;
            border: 3px solid rgba(100, 200, 150, 0);
            border-radius: 20px;
            background: linear-gradient(45deg, rgba(163, 141, 54, 0.44), rgba(15, 189, 208, 0.44), rgba(85, 17, 0, 0.44));
            box-shadow: 0 5px 12px rgba(42, 73, 165, 0.3);
            backdrop-filter: blur(100%);
            transition: transform 0.5s ease-in, opacity 0.5s ease-in, background-color 0.5s ease-in;
            animation: rotate180 2s infinite ease-in-out, fadeGlow 2s infinite alternate;
        }
        @keyframes rotate180 {
            0% { transform: rotate(-6deg) translateY(7px); }
            50% { transform: rotate(9deg) translateY(-7px); }
            100% { transform: rotate(-6deg) translateY(7px); }
        }
        .prompt-content h1:hover {
            transform: scale(1.1) translateY(-5px);
            text-shadow: 4px 4px 10px rgba(0, 0, 0, 0.5);
        }
        .prompt-content h1 {
            margin-top:-10px;
        }
        .prompt-content p {
            font-size: 1.1em;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            line-height: 1.6;
          margin-top:-10px;
            color: #e8e8e8;
            margin-bottom:60px;
           
          
        }
        .prompt-content ul {
            list-style: none;
            margin-bottom: 20px;
        }
        .prompt-content ul li {
            position: relative;
            padding-left: 20px;
            font-size: 1.1em;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            line-height: 1.5;
            margin-bottom: 16px;
            margin-right: 70px;
        }
        .prompt-content ul li:before {
            content: "•";
            color: rgb(175, 101, 76);
            position: absolute;
            left: 0;
            font-size: 1.2em;
            top: -1px;
        }
        .prompt-content a {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            background: linear-gradient(45deg, rgb(125, 66, 46), rgb(56, 28, 215));
            padding: 8px 80px;
            border-radius: 22px;
            font-size: 1.05em;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
            top:200px;
        }
        .prompt-content a:hover {
            background: linear-gradient(45deg, #1b5e20, #388e3c);
            color: #fff;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        .form-container {
            background: linear-gradient(45deg, rgba(163, 141, 54, 0.22), rgba(15, 189, 208, 0.13), rgba(85, 17, 0, 0.16));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(7, 238, 203, 0.338);
            border-radius: 30px;
            padding: 40px;
            width: 100%;
            max-width: 300px;
            text-align: center;
            z-index: 2;
            animation: float 120000s ease-in-out infinite; /* Corrected to 12s if intended */
            margin-left: 420px;
            margin-top: -30px;
            height:370px;
        }
        .form-container h2 {
            margin-bottom: 30px;
            background: linear-gradient(45deg, #a38d36, #0fbdd0, #551100);
            font-size: 2.5em;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .form-container input {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            font-size: 1.1em;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: pulse 1.5s infinite;
        }
        .form-container input:focus {
            transform: scale(1.02);
            box-shadow: 0 0 15px rgba(0, 152, 121, 0.4);
            outline: none;
        }
         .form-container a {
          color:red;
          text-decoration:none;
        }
         .form-container a :hover{
          color:green;
          text-decoration:none;
        }
        .form-container button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #009879, #007b63, #00664f);
            color: white;
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
        .toggle-form {
            margin-top: 0px;
            color: #f2e9ea;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1em;
            transition: color 0.2s ease, text-shadow 0.2s ease;
            animation: bounce 2.5s infinite;
          
        }
        .toggle-form:hover {
            color: #c24529;
            text-shadow: 0 0 10px rgba(0, 152, 121, 0.5);
            text-decoration: underline;
        }
       
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
        @keyframes fadeGlow {
            0% { opacity: 0.8; }
            100% { opacity: 1; }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        @keyframes slideIn {
            0% { transform: translateY(50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div class="split-screen left">
        <div class="prompt-content">
            <h1>Join Agri Shop</h1>
            <p>
                Become part of our farming community. Sign up to access premium agricultural products, real-time stock updates, and dedicated support.
            </p>
            <ul>
                <li>Exclusive product offers</li>
                <li>Real-time inventory</li>
                <li>24/7 customer service</li>
                <li>Safe and secure</li>
            </ul>
            <p>
                <a href="login.php">Already a member? Login</a>
            </p>
        </div>
    </div>
    <div class="split-screen right">
        <div class="form-container" id="form-area">
            <h2>Sign Up</h2>
            <?php if (!empty($message)): ?>
                <p class="error-message"><?php echo $message; ?></p>
            <?php endif; ?>
            <form method="post" action="signup.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Sign Up</button>
            </form>
            <p class="toggle-form">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>
</html>
