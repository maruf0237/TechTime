🖥️ Tech Time — E-Commerce Website
A full-stack e-commerce store for laptops, smartphones, monitors, keyboards, headphones and accessories.
Built with vanilla HTML/CSS/JavaScript on the front end and PHP + MySQL (PDO, prepared statements) on the back end. Runs locally on XAMPP.

✨ Features
Customer side
🛍️ Browse products by category, with search and a clean product grid
🖼️ Product pages with photo gallery, stock status and price
⭐ Star reviews with an interactive star picker
💬 Chat / Q&A tab on each product (admin replies are tagged Seller)
🛒 Cart with live quantity updates and stock limits
📦 Checkout with Cash on Delivery or Demo Card payment
🧾 Order history and order details pages
👤 Register / login with hashed passwords (`password\\\_hash`)
🌗 Light / dark theme toggle (remembers your choice and respects OS preference)
🔔 Toast notifications, dropdown menus, inline form validation
Admin panel
📊 Dashboard: total revenue, this month's revenue, most-sold product, top-5 best-sellers chart, recent orders
📦 Products: add / edit / delete, upload a main photo plus extra gallery photos (JPG, PNG, WEBP, GIF, up to 3 MB)
🗂️ Categories: add / rename / delete
🚚 Orders: change status (pending → processing → shipped → delivered, or cancelled) with instant save and status filter
👥 Users: promote / demote / delete (your own account is protected)
Reliability
Products without a photo fall back to a bundled category illustration, so nothing shows as a broken image
Image URLs are built from an auto-detected base path, so the project works whatever the folder is named
---
🧰 Tech Stack
Layer	Technology
Front end	HTML5, CSS3 (custom properties for theming), vanilla JavaScript, Font Awesome
Back end	PHP (PDO with prepared statements, sessions)
Database	MySQL
Local env	XAMPP (Apache + MySQL)
---
🚀 Getting Started (XAMPP)
Copy the project
Put the `techtime` folder into your XAMPP web root:
Windows: `C:\\\\xampp\\\\htdocs\\\\`
macOS: `/Applications/XAMPP/htdocs/`
Linux: `/opt/lampp/htdocs/`
Start services — open the XAMPP control panel and start Apache and MySQL.
Import the database — go to `http://localhost/phpmyadmin`, click Import, and choose `sql/techtime.sql`.
This creates the `techtime` database, all tables, and seed data (6 categories, 12 sample products, an admin account, and a sample review and chat message).
Configure the database (only if needed) — if your MySQL `root` user has a password, edit `config/db.php`:
```php
   $DB\\\_HOST = 'localhost';
   $DB\\\_NAME = 'techtime';
   $DB\\\_USER = 'root';
   $DB\\\_PASS = '';
   ```
Make uploads writable — `uploads/products/` must be writable by the web server (admin product photos are saved here).
Open the site → `http://localhost/techtime/`
🔑 Demo Accounts
Role	URL	Email	Password
Admin	`http://localhost/techtime/admin/login.php`	`admin@techtime.com`	`admin123`
Customer	Register via the Sign up link	your own	your own
📁 Project Structure

techtime/
├── admin/                    Admin panel
│   ├── dashboard.php         Revenue stats \\\& best-sellers
│   ├── products.php          Product list
│   ├── product\\\_form.php      Add / edit product + gallery
│   ├── categories.php        Manage categories
│   ├── orders.php            Update order status
│   ├── users.php             Manage users \\\& roles
│   ├── login.php / logout.php
│   └── includes/             Admin header \\\& footer
├── assets/
│   ├── css/style.css         Stylesheet (light/dark theme)
│   ├── js/app.js             Theme toggle, dropdowns, validation, toasts
│   └── images/               Hero image + category illustrations (SVG)
├── config/db.php             Database connection
├── includes/                 Shared header, footer, helper functions
├── sql/
│   ├── techtime.sql          Full schema + seed data (fresh install)
│   ├── add\\\_product\\\_chat.sql  Upgrade script for older installs
│   └── add\\\_product\\\_images.sql Upgrade script for older installs
├── uploads/products/         Admin-uploaded product photos
├── index.php                 Home / product listing
├── product.php               Product details, reviews, Q\\\&A
├── cart.php, add\\\_to\\\_cart.php, update\\\_cart.php, remove\\\_from\\\_cart.php
├── checkout.php, place\\\_order.php
├── my\\\_orders.php, order\\\_details.php
└── login.php, register.php, logout.php
```
---
🗄️ Database Tables
`users` · `categories` · `products` · `product\\\_images` · `cart` · `orders` · `order\\\_items` · `reviews` · `product\\\_chat`
If you installed an older version, run `sql/add\\\_product\\\_chat.sql` and/or `sql/add\\\_product\\\_images.sql` instead of re-importing everything.
---
🔒 Security Notes
PDO prepared statements throughout (protects against SQL injection)
Passwords stored with `password\\\_hash()`
Output escaped before rendering
Admin pages are role-protected
Uploaded photos are validated by MIME type and size (max 3 MB)
---
📤 Uploading to GitHub
cd techtime
git init
git add .
git commit -m "Initial commit: Tech Time e-commerce"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/techtime.git
git push -u origin main


📄 License
This project is for learning and personal use. Add a license (e.g. MIT) if you want others to reuse it.
