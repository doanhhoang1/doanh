# Shopping Cart Management System

## Overview
This project is a Shopping Cart Management System that allows users to browse products and manage their shopping cart. Additionally, it provides an admin interface for managing products, including adding, editing, and deleting products from the database.

## Project Structure
```
cart-php
├── admin
│   ├── add_product.php       # Form and logic to add a new product
│   ├── auth.php              # User authentication management
│   ├── dashboard.php         # Admin dashboard overview
│   ├── delete_product.php     # Logic to delete a product
│   ├── edit_product.php      # Form and logic to edit an existing product
│   ├── index.php             # Admin landing page
│   ├── login.php             # Admin login form
│   ├── logout.php            # User logout logic
│   └── products.php          # Display all products for admin
├── assets
│   ├── css
│   │   └── admin.css         # CSS styles for admin section
│   └── js
│       └── admin.js          # JavaScript functions for admin section
├── cart.php                  # Shopping cart management
├── db.php                    # Database connection setup
├── detail.php                # Product detail view
├── index.php                 # Homepage displaying all products
├── shopping_cart.sql         # SQL queries for database setup
└── README.md                 # Project documentation
```

## Setup Instructions
1. **Database Setup**:
   - Import the `shopping_cart.sql` file into your MySQL database to create the necessary tables and seed data.
   - Ensure that the database connection details in `db.php` are correct.

2. **Running the Application**:
   - Place the project files in the `htdocs` directory of your XAMPP installation.
   - Start the Apache and MySQL services from the XAMPP control panel.
   - Access the application by navigating to `http://localhost/cart-php/index.php` in your web browser.

3. **Admin Access**:
   - Navigate to `http://localhost/cart-php/admin/login.php` to log in as an admin.
   - Use the credentials set in the `auth.php` file to access the admin dashboard.

## Functionalities
- **User Features**:
  - View all products on the homepage.
  - Add products to the shopping cart.
  - View product details.

- **Admin Features**:
  - Log in to the admin panel.
  - Add new products to the database.
  - Edit existing product details.
  - Delete products from the database.
  - View all products in the admin interface.

## SQL Query to Create Product Table
```sql
CREATE TABLE product (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(455) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL
);
```

## Conclusion
This Shopping Cart Management System provides a comprehensive solution for managing products and user interactions in an online store. The admin panel allows for easy product management, while the user interface offers a seamless shopping experience.