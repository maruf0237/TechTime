# 🖥️ Tech Time — E-Commerce Website

**Tech Time** is a full-stack e-commerce website for browsing and purchasing technology products such as laptops, smartphones, monitors, keyboards, headphones, and other accessories.

The project is built using **HTML, CSS, JavaScript, PHP, and MySQL**, with a responsive customer interface and a dedicated admin dashboard for managing products, categories, users, and orders.

🌐 **Live Website:** https://techtime.free.je/
💻 **GitHub Repository:** https://github.com/maruf0237/techtime

---

## ✨ Features

### 🛍️ Customer Side

* Browse products by category
* Search products
* Product details with image gallery
* Product price and stock information
* ⭐ Star-based product reviews
* 💬 Product Chat / Q&A
* 🛒 Shopping cart with quantity and stock limits
* 📦 Checkout system
* 💳 Demo Card payment
* 💵 Cash on Delivery
* 🧾 Order history
* 📋 Order details and status
* 👤 User registration and login
* 🔐 Secure password hashing
* 🌗 Light / Dark theme
* 🔔 Toast notifications
* ✅ Inline form validation
* 📱 Responsive design

### ⚙️ Admin Panel

* 📊 Dashboard with sales statistics
* 💰 Total revenue tracking
* 📅 Monthly revenue tracking
* 🏆 Most-sold product
* 📈 Top-5 best-selling products chart
* 🧾 Recent orders
* 📦 Add, edit, and delete products
* 🖼️ Upload product images and gallery images
* 🗂️ Manage product categories
* 🚚 Update order status
* 👥 Manage users and roles
* 🔒 Protected admin routes
* 🛡️ Admin account protection

### 🛡️ Reliability & Security

* PDO prepared statements
* Protection against SQL injection
* Passwords stored using `password_hash()`
* Escaped output before rendering
* Session-based authentication
* Role-based admin access
* MIME-type validation for uploaded images
* Maximum upload size of 3 MB
* Category-based fallback images
* Automatic base-path detection for image URLs

---

## 🧰 Tech Stack

| Layer             | Technology                |
| ----------------- | ------------------------- |
| Frontend          | HTML5, CSS3, JavaScript   |
| Styling           | Custom CSS, CSS Variables |
| Icons             | Font Awesome              |
| Backend           | PHP                       |
| Database          | MySQL                     |
| Database Access   | PDO                       |
| Authentication    | PHP Sessions              |
| Local Development | XAMPP                     |
| Hosting           | Free PHP/MySQL Hosting    |

---

## 🗄️ Database

The project uses MySQL with the following main tables:

```text
users
categories
products
product_images
cart
orders
order_items
reviews
product_chat
```

The database includes relationships between users, products, carts, orders, reviews, and product discussions.

---

## 📁 Project Structure

```text
techtime/
│
├── admin/
│   ├── dashboard.php
│   ├── products.php
│   ├── product_form.php
│   ├── categories.php
│   ├── orders.php
│   ├── users.php
│   ├── login.php
│   ├── logout.php
│   └── includes/
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   └── images/
│
├── config/
│   └── db.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── helper functions
│
├── sql/
│   ├── techtime.sql
│   ├── add_product_chat.sql
│   └── add_product_images.sql
│
├── uploads/
│   └── products/
│
├── index.php
├── product.php
├── cart.php
├── add_to_cart.php
├── update_cart.php
├── remove_from_cart.php
├── checkout.php
├── place_order.php
├── my_orders.php
├── order_details.php
├── login.php
├── register.php
└── logout.php
```

---

## 🚀 Running Locally with XAMPP

### 1. Clone the repository

```bash
git clone https://github.com/maruf0237/techtime.git
```

### 2. Move the project

Place the project inside your XAMPP `htdocs` directory:

**Windows**

```text
C:\xampp\htdocs\
```

**macOS**

```text
/Applications/XAMPP/htdocs/
```

**Linux**

```text
/opt/lampp/htdocs/
```

### 3. Start XAMPP

Start:

* Apache
* MySQL

### 4. Import the database

Open:

```text
http://localhost/phpmyadmin
```

Import:

```text
sql/techtime.sql
```

This will create the `techtime` database and its required tables and sample data.

### 5. Configure the database

If your MySQL configuration is different, update:

```text
config/db.php
```

Example:

```php
$DB_HOST = 'localhost';
$DB_NAME = 'techtime';
$DB_USER = 'root';
$DB_PASS = '';
```

### 6. Open the website

```text
http://localhost/techtime/
```

---

## 🔑 Demo Admin Account

For testing the admin panel:

```text
Admin Login:
https://techtime.free.je/admin/login.php

Email:
admin@techtime.com

Password:
admin123
```

> ⚠️ The demo credentials are intended for testing purposes only. Change or remove them before using the project in a real production environment.

---

## 🌐 Live Demo

Visit the live website:

**https://techtime.free.je/**

The live version demonstrates the customer-facing e-commerce functionality and hosted PHP/MySQL application.

---

## 📸 Main Modules

### Customer

```text
Home
  ↓
Product Listing
  ↓
Product Details
  ↓
Cart
  ↓
Checkout
  ↓
Order
  ↓
Order History
```

### Admin

```text
Admin Login
     ↓
Dashboard
     ├── Products
     ├── Categories
     ├── Orders
     └── Users
```

---

## 🔐 Security

Security considerations implemented in the project include:

* PDO prepared statements for database queries
* Password hashing with `password_hash()`
* Output escaping
* Session-based authentication
* Role-based authorization
* Admin route protection
* File upload validation
* MIME-type checking
* File size restrictions
* Protected administrator account

This project is intended for **educational and portfolio purposes** and should receive additional security hardening before being used for a real commercial store.

---

## 🎯 Project Purpose

The main goal of **Tech Time** is to demonstrate the development of a complete e-commerce application using core web technologies without relying on frontend or backend frameworks.

The project covers:

* Frontend development
* Backend development
* Database design
* User authentication
* CRUD operations
* Shopping cart management
* Order processing
* Product reviews
* Admin dashboard development
* File uploads
* PHP/MySQL integration
* Basic web application security

---

## 👨‍💻 Author

**Md. Maruful Islam**

Computer Science & Engineering Student
Southeast University, Bangladesh

🔗 **GitHub:** https://github.com/maruf0237
🔗 **Portfolio:** https://maruf0237.github.io/maruf.dev/
🔗 **LinkedIn:** https://linkedin.com/in/md-maruful-islam98/

---

## 📄 License

This project is created for **learning and personal portfolio purposes**.

You may add an open-source license such as the **MIT License** if you want to allow others to reuse and modify the project.

---

⭐ If you find this project useful, consider giving the repository a star!
