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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agri Shop - Home</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        header {
    z-index: 30 !important; /* Ensure header is above other elements */
}
.btn {
    opacity: 1 !important; /* Ensure buttons are fully visible */
    visibility: visible !important; /* Prevent buttons from being hidden */
}
.carousel-content a:hover {
    background: linear-gradient(45deg, #1b5e20, #388e3c);
    transform: scale(1.05);
}
.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(25, 214, 44, 0.5);
    color: white;
    border: none;
    padding: 15px;
    cursor: pointer;
    z-index: 3;
    border-radius: 50%;
    transition: background 0.3s ease;
}
.carousel-btn:hover {
    background: rgba(0, 0, 0, 0.7);
}
.prev-btn {
    left: 20px;
}
.next-btn {
    right: 20px;
}
.content-section {
    background: linear-gradient(135deg, rgba(15, 182, 101, 0.63), rgba(144, 171, 24, 0.3));
    border-radius: 20px;
    padding: 40px;
    max-width: 1400px;
    margin: 0 auto 40px; /* Maintains bottom margin */
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.7s ease forwards;
}
.content-section h2 {
    font-size: 2em;
    color: #2e7d32;
    margin-bottom: 20px;
    font-weight: 700;
    letter-spacing: 1.5px;
}
       body {
   background: url('images/again.jpg') no-repeat center center/cover; 
    background-attachment: relative; /* Keeps background static */
    position: relative;
   height: 200px;
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
    min-height: 2000px;
    background: linear-gradient(45deg, rgba(76, 165, 116, 0.22), rgba(5, 5, 5, 0.55), rgba(112, 189, 180, 0.41));
    z-index: -1;
}
        .content-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh; /* Ensures content fills viewport at minimum */
            padding-top: 100px; /* Maintains space below header */
            padding-bottom: 20px; /* Ensures footer has space */
          animation: gradientShift 6s ease-in-out infinite;
        }
        @keyframes gradientShift {
    0% {
        background: linear-gradient(135deg, rgba(181, 188, 34, 0.18), rgba(29, 153, 106, 0.29));
    }
    60% {
        background: linear-gradient(135deg, rgba(207, 205, 110, 0.33), rgba(66, 190, 64, 0.48));
    }
    100% {
        background: linear-gradient(135deg, rgba(207, 205, 110, 0.33), rgba(93, 211, 166, 0.21));
    }
}
        header {
            background: rgba(16, 247, 127, 0.68);
            backdrop-filter: blur(12px);
            padding: 20px 40px;
            border-radius: 20px;
            margin: 0 auto 60px; /* Maintains bottom margin */
            max-width: 1400px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease forwards;
            position: relative; /* Ensures header stays in flow */
            margin-top:-200px;
        }
        header h2 {
            font-size: 2.2em;
            font-weight: 800;
            color:rgba(11, 10, 10, 0.65);
            letter-spacing: 2px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
           background: linear-gradient(45deg,rgba(76, 165, 116, 0.22),rgba(5, 5, 5, 0.55),rgba(112, 189, 180, 0.41));
            color: white;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1.1em;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.89);
            margin-top:5px;
            margin-right:20px;
        }
        .btn:hover {
            background: linear-gradient(45deg, #1b5e20, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .hero-section {
            text-align: center;
           /* Increased bottom padding to lower "Shop Now" further */
             background: linear-gradient(135deg, rgba(216, 177, 85, 0.63), rgba(23, 197, 17, 0.2));
            backdrop-filter: blur(12px);
            border-radius: 10px;
           margin-top:-180px;
            margin-bottom:50px ;/* Maintains bottom margin */
            max-width: 900px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            animation: heroPop 1.5s ease-in-out forwards infinite;
            max-height: 900px;
            padding-bottom:50px;
            margin-left:310px;
              
        }
       @keyframes heroPop {
    0% {
        opacity: 0.6;
        transform: translateY(30px) scale(0.95);
    }
    50% {
        opacity: 1;
        transform: translateY(-10px) scale(1.05);
    }
    100% {
        opacity: 0.6;
        transform: translateY(30px) scale(0.95);
    }
}

        .hero-section h1 {
            font-size: 3em;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 4px;
          background: linear-gradient(45deg,rgb(253, 253, 253),rgb(249, 249, 249),rgba(206, 210, 212, 0.98));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
           margin-top:360px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            padding-top:50px;
        }
        .hero-section p {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 30px; 
                margin-top: 30px;/* Increased to balance spacing above "Shop Now" */
            line-height: 1.7;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 520;
        }
        .carousel-container {
    position: relative;
    max-width: 900px; /* Match your design width */
    margin: 0 auto 40px;
    overflow: hidden;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    height: 450px; /* Set a fixed height to fill the container */
    width: 100%; /* Ensure it takes full width */
}


       .carousel {
    display: flex;
    transition: transform 0.7s ease-in-out;
    height: 100%; /* Match the container height */
    
}
      .carousel-item {
    min-width: 100%;
    height: 100%; /* Fill the container height */
    background-size: center; /* Stretch to fill, cropping if necessary */
    background-position: center; /* Center the image */
    background-repeat: no-repeat; /* Prevent tiling */
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    position: relative;
}
        .carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        .carousel-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
            padding: 20px;
            margin-top:30px;
        }
        .carousel-content h3 {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 40px;
              margin-top: 40px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-left:0px;
        }
        .carousel-content p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .carousel-content a {
            padding: 12px 25px;
            background: linear-gradient(45deg, #2e7d32, #4caf50);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top:20px;
            margin-right:10px;
        
        }
        .carousel-content a:hover {
            background: linear-gradient(45deg, #1b5e20, #388e3c);
            transform: scale(1.05);
        }
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(25, 214, 44, 0.5);
            color: white;
            border: none;
            padding: 15px;
            cursor: pointer;
            z-index: 3;
            border-radius: 50%;
            transition: background 0.3s ease;
           
        }
        .carousel-btn:hover {
            background: rgba(0, 0, 0, 0.7);
        }
        .prev-btn {
            left: 20px;
        }
        .next-btn {
            right: 20px;
        }
        .content-section {
            background: linear-gradient(135deg, rgba(181, 188, 34, 0.18), rgba(29, 153, 106, 0.29));
            border-radius: 20px;
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto 40px; /* Maintains bottom margin */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.41);
            animation: fadeIn 0.7s ease forwards;
        }
        .content-section h2 {
            font-size: 2em;
           color:rgba(11, 10, 10, 0.65);
            margin-bottom: 20px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }
        .content-section p {
            font-size: 20px;
            color: #333;
            line-height: 1.7;
            margin-bottom: 20px;
            font-weight:1000px;
        }
        .content-section ul {
            list-style: none;
            padding-left: 20px;
        }
        .content-section ul li {
            position: relative;
            font-size: 1.1em;
            color:black;
            margin-bottom: 12px;
            line-height: 1.6;
        }
        .content-section ul li:before {
            content: "•";
            color: #4caf50;
            position: absolute;
            left: -20px;
            font-size: 1.4em;
        }
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .content-card {
             background: linear-gradient(45deg,rgba(26, 202, 105, 0.68),rgba(251, 247, 247, 0.17),rgba(46, 178, 163, 0.53));
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .content-card:hover {
            transform: translateY(-5px);
        }
        .content-card h3 {
            font-size: 1.5em;
            font-weight:bold;
            color:rgba(245, 251, 246, 0.79);
            margin-bottom: 10px;
        }
         .content-card h2 {
            font-size: 1.5em;
            font-weight:bold;
            color:rgba(11, 12, 11, 0.58);
            margin-bottom: 10px;
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
        header {
    z-index: 100 !important; /* Further increase z-index to ensure header is on top */
    position: sticky !important; /* Ensure header stays visible at the top */
    top: 0 !important; /* Stick to top of viewport */
}
.btn {
    display: inline-block !important; /* Reinforce display property */
    opacity: 1 !important; /* Ensure full visibility */
    visibility: visible !important; /* Prevent hiding */
    position: relative !important; /* Ensure buttons are in normal flow */
}
header div {
    display: flex !important; /* Ensure the container div shows buttons */
    align-items: center !important; /* Align buttons vertically */
}
    </style>
</head>
<body>
    <div class="content-wrapper">
        <header>
            <h2>Agri Shop</h2>
            <div>
                <a href="customer_dashboard.php" class="btn mr-4">Go to Dashboard</a>
                <a href="contact.php" class="btn mr-4">Contact Us</a>
                <a href="login.php" class="btn">Logout</a>

            </div>
        </header>

        <div class="hero-section">
            <div>
                <h1>Welcome to Agri Shop</h1>
                <p>Welcome to Agri Shop, your trusted partner in modern farming! We’re dedicated to empowering you with innovative solutions for a sustainable future. Explore our premium products and expert advice to grow your success today!</p>
                <a href="customer_dashboard.php" class="btn">Shop Now</a>
            </div>
        </div>

        <div class="carousel-container">
            <div class="carousel" id="carousel">
                <div class="carousel-item" style="background-image: url('images/s1.jpg');">
                    <div class="carousel-content">
                        <h3>Premium Seeds Collection</h3>
                       
                        <a href="customer_dashboard.php">Shop Seeds</a>
                    </div>
                </div>
                <div class="carousel-item" style="background-image: url('images/k3.jpg');">
                    <div class="carousel-content">
                        <h3>Organic Fertilizers</h3>
                       
                        <a href="customer_dashboard.php">Shop Fertilizers</a>
                    </div>
                </div>
                <div class="carousel-item" style="background-image: url('images/df2.jpg');">
                    <div class="carousel-content">
                        <h3>Advanced Farming Tools</h3>
                       
                        <a href="customer_dashboard.php">Shop Tools</a>
                    </div>
                </div>
                <div class="carousel-item" style="background-image: url('images/f2.jpg');">
                    <div class="carousel-content">
                        <h3>Livestock Supplies</h3>
                       
                        <a href="customer_dashboard.php">Shop Supplies</a>
                    </div>
                </div>
            </div>
            <button class="carousel-btn prev-btn">❮</button>
            <button class="carousel-btn next-btn">❯</button>
        </div>

        <div class="content-section">
            <h2>Why Choose Agri Shop?</h2>
            <p>At Agri Shop, we empower farmers with top-tier products and knowledge to achieve unparalleled success. Our platform offers real-time stock updates, secure transactions, and dedicated support for a seamless experience.</p>
            <div class="content-grid">
                <div class="content-card">
                    <h3>Quality Assurance</h3>
                    <p>Every product is sourced from trusted suppliers to ensure top performance and reliability.</p>
                </div>
                <div class="content-card">
                    <h3>Expert Guidance</h3>
                    <p>Access expert advice on crop management, soil health, and sustainable farming practices.</p>
                </div>
                <div class="content-card">
                    <h3>Fast Delivery</h3>
                    <p>Reliable and swift delivery to get your supplies when you need them most.</p>
                </div>
            </div>
            <h2>Farming Insights</h2>
            <p >Elevate your farming with these expert tips:</p>
            <ul>
                <li>Implement crop rotation to enhance soil fertility and reduce pest pressures.</li>
                <li>Use precision irrigation like drip systems to conserve water and boost yields.</li>
                <li>Test soil regularly to maintain optimal pH and nutrient levels for crops.</li>
                <li>Opt for hybrid seeds to improve resistance to diseases and environmental stress.</li>
                <li>Integrate organic compost to promote long-term soil health and sustainability.</li>
            </ul>
        </div>

        <footer>
            <p>© 2025 Agri Shop. All rights reserved. Empowering farmers, cultivating success.</p>
        </footer>
    </div>

   <script>
    const carousel = document.getElementById('carousel');
    const items = document.querySelectorAll('.carousel-item');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn'); // Corrected typo from queryselector to querySelector
    let currentIndex = 0;

    function showSlide(index) {
        if (index >= items.length) currentIndex = 0;
        else if (index < 0) currentIndex = items.length - 1;
        else currentIndex = index;
        carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    prevBtn.addEventListener('click', () => showSlide(currentIndex - 1));
    nextBtn.addEventListener('click', () => showSlide(currentIndex + 1));

    // Auto-slide every 5 seconds
    setInterval(() => showSlide(currentIndex + 1), 2000);

    // Initialize carousel
    showSlide(currentIndex);
</script>
</body>
</html>