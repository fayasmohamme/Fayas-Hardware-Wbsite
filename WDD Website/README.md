# E-Commerce Website with Admin Dashboard

A full-featured e-commerce website built with PHP, MySQL, HTML, CSS, and JavaScript. This system supports customers, suppliers, and administrators with a comprehensive admin dashboard.

## Features

### User Side
- **Home Page**: Banner/slider, featured products, navigation menu, footer
- **Product Page**: Display all products with search and category filter
- **About Page**: Company information, vision, and mission
- **Contact Page**: Contact form with message storage in database
- **Login & Registration**: Secure customer authentication
- **Customer Dashboard**: View profile and order history
- **Shopping Cart**: Add products to cart and checkout
- **Order Management**: View order details and history

### Admin Dashboard
- **Dashboard Overview**: Statistics (products, customers, suppliers, orders, revenue)
- **Product Management**: Add, update, delete products, manage stock
- **Supplier Management**: Add, update, delete suppliers, assign to products
- **Customer Management**: View, add, update customer details
- **Order Management**: View all orders, track status, view order details

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: Apache (XAMPP/WAMP)

## Installation & Setup

### Prerequisites
- XAMPP or WAMP installed
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Step 1: Database Setup

1. Start XAMPP/WAMP and ensure Apache and MySQL are running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the `database.sql` file:
   - Click on "Import" tab
   - Choose file: `database.sql`
   - Click "Go"

   OR run the SQL file directly in MySQL command line:
   ```bash
   mysql -u root -p < database.sql
   ```

### Step 2: Configure Database Connection

Edit `config/database.php` if your MySQL credentials are different:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password
define('DB_NAME', 'ecommerce_db');
```

### Step 3: File Structure

Place all files in your web server directory:
- **XAMPP**: `C:\xampp\htdocs\WDD Website\`
- **WAMP**: `C:\wamp\www\WDD Website\`

### Step 4: Setup Admin Account

1. Run the setup script to create admin user with correct password:
   - Visit: http://localhost/WDD%20Website/setup_admin.php
   - This will create/update the admin account
   - **Delete `setup_admin.php` file after use for security!**

### Step 5: Access the Website

1. **User Side**: http://localhost/WDD%20Website/
2. **Admin Login**: http://localhost/WDD%20Website/admin/login.php

### Default Admin Credentials

- **Username**: `admin`
- **Password**: `admin123`

⚠️ **Important**: 
- Run `setup_admin.php` first to set the correct password hash
- Delete `setup_admin.php` after use
- Change the default admin password after first login!

## Database Schema

The database includes the following tables:

- **admins**: Admin user accounts
- **customers**: Customer accounts
- **suppliers**: Supplier information
- **products**: Product catalog
- **orders**: Customer orders
- **order_items**: Order line items
- **contact_messages**: Contact form submissions

## Security Features

- ✅ Session-based authentication
- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Input validation

## File Structure

```
WDD Website/
├── admin/
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── products.php
│   ├── suppliers.php
│   ├── customers.php
│   ├── orders.php
│   └── order_details.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── config/
│   ├── database.php
│   ├── session.php
│   └── functions.php
├── includes/
│   ├── header.php
│   └── footer.php
├── uploads/ (create this folder for product images)
├── index.php
├── products.php
├── about.php
├── contact.php
├── login.php
├── register.php
├── dashboard.php
├── cart.php
├── checkout.php
├── order_details.php
├── order_success.php
├── logout.php
├── database.sql
└── README.md
```

## Usage Guide

### For Customers

1. **Register**: Create a new account at the registration page
2. **Browse Products**: View products on the products page
3. **Add to Cart**: Click "Add to Cart" on any product
4. **Checkout**: Go to cart and proceed to checkout
5. **View Orders**: Check order history in the dashboard

### For Administrators

1. **Login**: Use admin credentials to access admin panel
2. **Manage Products**: Add, edit, or delete products
3. **Manage Suppliers**: Add suppliers and assign them to products
4. **Manage Customers**: View and update customer information
5. **Manage Orders**: View orders and update order status

## Notes

- Product images should be uploaded to the `uploads/` folder
- The default admin password hash in the database is for 'admin123'
- Make sure to create the `uploads/` directory with write permissions
- For production, update security settings and use HTTPS

## Troubleshooting

### Database Connection Error
- Check if MySQL is running
- Verify database credentials in `config/database.php`
- Ensure database `ecommerce_db` exists

### Session Issues
- Check PHP session configuration
- Ensure cookies are enabled in browser
- Clear browser cache and cookies

### Image Upload Issues
- Create `uploads/` folder in root directory
- Set proper permissions (755 or 777)
- Check PHP upload settings in php.ini

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check the code comments or refer to the documentation.

