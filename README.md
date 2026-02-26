# Simple-Agricultural-Website-
The Agri Shop Project is a web-based online store for selling agricultural products. Customers can browse products, place orders, choose payment methods, and send messages to the shop owner. Owners can manage products, track orders, handle payments, and monitor account balances.
Agri Shop – Complete Project Description
**1. Introduction**

The Agri Shop Project is a complete web-based agricultural e-commerce system designed to help customers buy farming products online and allow shop owners to manage their business efficiently. The system makes product management, order processing, payment tracking, and customer communication simple and organized.

**The project is developed using:**

MySQL – for database management

PHP – for backend/server-side processing

HTML, CSS & Tailwind CSS – for frontend design

XAMPP Server – for local hosting and deployment

**2. Project Purpose**

The main goal of this project is to:

Provide an easy online platform for selling agricultural products.

Help shop owners manage products, orders, and payments.

Maintain proper database relationships for accurate data storage.

Ensure smooth communication between customers and owners.

3.**System Architecture**

The system has three main layers:

1. Database Layer (MySQL)

Stores all project data in structured tables.

Maintains relationships using primary and foreign keys.

Ensures data integrity and organization.

2. Application Layer (PHP)

Handles user login and registration.

Manages product operations (add, update, delete).

Processes orders and payments.

Controls account transactions.

3. Presentation Layer (Frontend)

Built with HTML and Tailwind CSS.

Fully responsive and user-friendly design.

Includes animations and modern UI components.

4. **Database Design**

The database name is first_data.

Main Tables:
1. Users

Stores:

User ID

Username

Password

Role (Customer or Owner)

2. Categories

Stores product categories.

3. Products

Stores:

Product name

Category

Price

Quantity

Company

Contact information

4. Orders

Stores:

User ID

Product ID

Quantity

Total price

Payment method

Order status

Order date

5. **Owner_Accounts**

Stores different account types:

Main Account

EasyPaisa

JazzCash

Meezan Bank IDO
Also tracks their balances.

6. **Transactions**

Records:

Account type

Amount

Transfer type

Purpose

Status

Date

7. **Contact_Messages**

Stores:

Customer name

Email

Message

Submission time

5. Key Features
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

6. **Technical Implementation**
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

7. Security Measures

SQL Injection prevention using prepared statements.

Input sanitization using htmlspecialchars().

Role-based access control.

Client-side validation using HTML5.

Session-based authentication.

Proper error handling.
