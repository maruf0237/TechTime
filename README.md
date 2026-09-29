# Tech Time — E-Commerce Website

A full-stack e-commerce site for laptops, smartphones, monitors, keyboards,
headphones and accessories. Vanilla HTML/CSS/JS on the front end, PHP + MySQL
(PDO, prepared statements) on the back end, built for XAMPP. Includes light
and dark themes and a redesigned, more interactive UI throughout.

## 1. Setup (XAMPP)

1. Copy this whole `techtime` folder into `C:\xampp\htdocs\` (Windows) or
   `/Applications/XAMPP/htdocs/` (Mac).
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open `http://localhost/phpmyadmin`, click **Import**, and import
   `sql/techtime.sql`. This creates the `techtime` database, every table, and
   seed data (6 categories, 12 sample products, one admin account, and one
   sample review + chat message so the tabs aren't empty).
4. If your MySQL root user has a password, set it in `config/db.php`.
5. Make sure `uploads/products/` is writable — that's where admin-uploaded
   product photos are saved.
6. Visit `http://localhost/techtime/` — the store is live.

## 2. Demo logins

- **Admin panel:** `http://localhost/techtime/admin/login.php`
  Email: `admin@techtime.com` / Password: `admin123`
- **Customer:** register a new account from the site's "Sign up" link.

## 3. What was fixed / added in this pass

**Product images.** `product_image_path()` in `includes/functions.php` now
checks the file actually exists on disk before pointing to it, and every
image URL is built from an auto-detected site base path (`base_url()`) so
photos load correctly from root pages *and* from `admin/` pages, no matter
what the project folder is named. Products with no uploaded photo fall back
to a bundled category illustration (laptop, smartphone, monitor, keyboard,
headphones, accessory, or a generic icon) — nothing ever shows broken.

**Star reviews & Chat/Q&A.** Both tabs on the product page are wired to the
`reviews` and `product_chat` tables (both included in `sql/techtime.sql`),
with working tab switching, an interactive star-picker, and admin messages
tagged "Seller".

**Admin panel.**
- *Manage users* (`admin/users.php`) — promote/demote/delete, with your own
  account protected from self-demotion/deletion.
- *Update orders* (`admin/orders.php`) — every order has a status dropdown
  (pending → processing → shipped → delivered, or cancelled) that saves
  instantly, plus a status filter.
- *Most-sold product* and *monthly revenue* — both on the dashboard
  (`admin/dashboard.php`), along with total revenue, this month's revenue,
  a top-5 best-sellers bar chart, and recent orders.

**Dark / light theme.** A toggle in the header (and in the admin topbar)
switches themes instantly and remembers the choice via `localStorage`; it
also respects the visitor's OS-level preference on first visit. All colors
are CSS custom properties in `assets/css/style.css`, so nothing was
hand-duplicated per page.

**More interactive throughout:** an interactive star-rating picker, live
cart-quantity updates, a user-menu dropdown, toast confirmations, animated
hover states on product cards, an auto-saving order-status control in admin,
and inline client-side form validation on login/register/checkout.

## 4. Folder structure

```
techtime/
├── admin/                        admin panel (dashboard, products, orders, users)
├── assets/css/js                 shared stylesheet (incl. dark/light theme) + client-side JS
├── assets/images/categories/     bundled product illustrations (svg)
├── uploads/products/             admin-uploaded product photos
├── config/db.php                 database connection
├── includes/                     shared header/footer/functions
├── sql/techtime.sql              database schema + seed data (fresh installs)
├── sql/add_product_chat.sql      upgrade script (only if you had an older install)
├── index.php, product.php, cart.php, checkout.php, ...   customer pages
```

## 5. Suggested next phase

- CSV/PDF export for the monthly revenue report.
- Real payment gateway integration (Stripe/SSLCommerz) in place of the demo
  card option — v1 intentionally scopes payment to COD/demo card.
- Letting an admin reply inline to a specific chat message (right now every
  logged-in user, including admins, posts to the same open thread — admin
  messages are already visually tagged "Seller").
