# 🛒 Store Management System with POS (PHP + MySQL)

A simple **Store Management System with Point of Sale (POS)** built using **PHP (mysqli), MySQL, HTML, and basic CSS/Bootstrap**. Designed for beginners and small businesses to manage products, categories, and sales transactions.

---

## 🚀 Features

### 🔐 Authentication
- Login system (Admin & Cashier roles)
- Secure password hashing (`password_hash`, `password_verify`)
- Session-based authentication
- Logout system

### 📊 Dashboard
- Total sales summary
- Quick navigation to modules

### 📦 Product Management
- Add, edit, delete products
- Manage stock quantity
- Set product price
- Assign categories

### 🗂 Category Management
- Create and manage product categories

### 🧾 POS (Point of Sale)
- Search and select products
- Add to cart system
- Quantity adjustment
- Automatic total computation
- Cash input and change calculation
- Complete transaction processing
- Stock auto-update after purchase

### 📜 Sales History
- View all transactions
- View receipt details per sale

---

## 🗄 Database Structure

### Tables:
- `users` – system users (admin/cashier)
- `products` – product inventory
- `categories` – product categories
- `sales` – transaction records
- `sale_items` – items per transaction

---

## 📁 Project Structure

STORE/
│
├── ajax/
  ├── assets/
│   ├── css/
│   ├── js/
│   ├── images/


├── includes/
│ ├── db.php
│ ├── auth.php
│
├── uploads/


├── index.php
├── login.php
├── logout.php
├── dashboard.php
├── products.php
├── categories.php
├── pos.php
├── process_sale.php
├── sales.php
├── store_db.sql

---

## ⚙️ Installation Guide (XAMPP)

### 1. Install Requirements
- XAMPP (Apache + MySQL + PHP)
- Browser (Chrome recommended)

---

### 2. Setup Project
1. Copy project folder to:

2. Start XAMPP:
- Start **Apache**
- Start **MySQL**

---

### 3. Setup Database
1. Open browser:
2. Create database:
store_db
3. Import:

---

### 4. Run Project
Open browser:
http://localhost/STORE


---

## 🔑 Default Login

| Role  | Username | Password |
|------|----------|----------|
| Admin | admin    | admin |

---

## 🧠 Technologies Used

- PHP (Procedural / Simple OOP)
- MySQL (mysqli)
- HTML5 / CSS3
- Bootstrap (optional)
- JavaScript (basic cart logic)

---

## 📌 Notes

- Make sure `uploads/` folder is writable
- Always use prepared statements for security
- Stock automatically updates after each transaction

---

## 👨‍💻 Developer

Created for educational purposes and academic projects (BS Computer Engineering).

---

## 📷 Preview (Optional)
Add screenshots here:
- Login page
- Dashboard
- POS interface
- Receipt view

---

## 📜 License
This project is free to use for learning and academic purposes.
