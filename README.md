# Simple-Agricultural-Website-
The Agri Shop Project is a web-based online store for selling agricultural products. Customers can browse products, place orders, choose payment methods, and send messages to the shop owner. Owners can manage products, track orders, handle payments, and monitor account balances.
Agri Shop – Complete Project Description
<br>
<h2>1. Introduction</h2>


The Agri Shop Project is a complete web-based agricultural e-commerce system designed to help customers buy farming products online and allow shop owners to manage their business efficiently. The system makes product management, order processing, payment tracking, and customer communication simple and organized.

<h3>The project is developed using:</h3>

MySQL – for database management

PHP – for backend/server-side processing

HTML, CSS & Tailwind CSS – for frontend design

XAMPP Server – for local hosting and deployment

<h2>2. Project Purpose</h2>

The main goal of this project is to:

Provide an easy online platform for selling agricultural products.

Help shop owners manage products, orders, and payments.

Maintain proper database relationships for accurate data storage.

Ensure smooth communication between customers and owners.

<h2>3.System Architecture</h2>

The system has three main layers:

<h3>1. Database Layer (MySQL)</h3>

Stores all project data in structured tables.

Maintains relationships using primary and foreign keys.

Ensures data integrity and organization.

<h3>2. Application Layer (PHP)</h3>

Handles user login and registration.

Manages product operations (add, update, delete).

Processes orders and payments.

Controls account transactions.

<h3>3. Presentation Layer (Frontend)</h3>

Built with HTML and Tailwind CSS.

Fully responsive and user-friendly design.

Includes animations and modern UI components.

<h2>4. Database Design</h2>

<h3>The database name is first_data.</h3>

**Main Tables:**
<h3>1. Users</h3>

Stores:

User ID

Username

Password

Role (Customer or Owner)

<h3>2. Categories</h3>

Stores product categories.

<h3>3. Products</h3>

Stores:

Product name

Category

Price

Quantity

Company

Contact information

<h3>4. Orders</h3>

Stores:

User ID

Product ID

Quantity

Total price

Payment method

Order status

Order date

<h2>5. Owner_Accounts</h2>

<h3>Stores different account types:</h3>

Main Account

EasyPaisa

JazzCash

Meezan Bank IDO
Also tracks their balances.

<h2>6. Transactions</h2>

Records:

Account type

Amount

Transfer type

Purpose

Status

Date

<h2>7. Contact_Messages</h2>

Stores:

Customer name

Email

Message

Submission time

 <h2>Key Features</h2>
✅ User Management

User registration and login.

Role-based access (Customer / Owner).

Secure session handling.

✅ Product Management

Add, update, delete products.

Search and filter by category, company, or stock.

View product details.

✅ Order Processing

Customers can place orders.

Payment options:

Cash on Delivery

JazzCash

EasyPaisa

Meezan Bank

Owners can update order status:

Pending

Shipped

Delivered

Paid

✅ Financial Management

Track balances in multiple accounts.

Record transactions.

Verify transfers.

Maintain complete financial history.

✅ Customer Support

Contact form for customer messages.

Owner can review inquiries.

✅ Analytics Dashboard

Shows:

Total products

Total orders

Total revenue

Account balances

<h2>6. Technical Implementation</h2>
🔹 Frontend

Built with HTML & Tailwind CSS.

Responsive design (mobile-friendly).

Modern UI with animations.

Clean agricultural-themed design (green and earthy colors).

🔹 Backend

PHP handles all logic.

Uses prepared statements to prevent SQL injection.

Secure session management.

Error handling with user-friendly messages.

🔹 Database Connection

Central file (db_connect.php).

MySQLi connection.

Error handling for connection issues.

🔹 Hosting Environment

The project is hosted locally using XAMPP Server.

Apache server runs PHP files.

MySQL manages the database.

phpMyAdmin is used for database management.

<h2>7. Security Measures</h2>

SQL Injection prevention using prepared statements.

Input sanitization using htmlspecialchars().

Role-based access control.

Client-side validation using HTML5.

Session-based authentication.

Proper error handling.
