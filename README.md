# VJ Digital - Digital Product Selling Website

A comprehensive, responsive digital product marketplace built with PHP, MySQL, and MDBootstrap. Features include user authentication, product management, shopping cart, payment integration, and a full admin dashboard.

## 🚀 Features

### User Side (Frontend)
- **Authentication System**
  - User registration and login
  - Password reset functionality
  - Profile management with password change

- **Main Pages**
  - Responsive landing page with hero section
  - Product listing with filters (category, price, search)
  - Detailed product pages with screenshots
  - Shopping cart functionality
  - Checkout with multiple payment options
  - Order history and download management
  - FAQ page
  - Contact form with support ticket creation

- **Checkout & Orders**
  - Secure checkout process
  - Coupon/discount code application
  - Multiple payment gateway support (Razorpay, Stripe, PayPal)
  - Order confirmation and invoice generation
  - Secure product download system

- **Responsive Design**
  - Mobile-first design with native app-like experience
  - Bottom navigation bar for mobile
  - Professional desktop layout
  - Dark/Light mode support
  - Smooth animations and transitions

### Admin Side (Backend Dashboard)
- **Authentication**
  - Secure admin login
  - Role-based access (Super Admin, Editor)

- **Product Management**
  - Add/Edit/Delete products
  - Upload digital files with secure access
  - Manage product screenshots
  - Set product status (Active/Inactive)
  - Category management

- **Order & Payment Management**
  - View all orders with details
  - Update payment and order status
  - Process refunds
  - Configure payment gateway API keys
  - Currency and tax settings

- **User Management**
  - View all registered users
  - Block/Unblock users
  - View user purchase history

- **Coupons & Discounts**
  - Create discount codes (flat/percentage)
  - Set expiry dates and usage limits
  - Track coupon usage

- **Reports & Analytics**
  - Daily/Monthly sales reports
  - Top-selling products
  - Revenue and tax collection summary

- **Support Management**
  - Manage support tickets
  - Respond to user queries
  - FAQ management

- **Settings**
  - Payment gateway configuration
  - Tax settings (GST/VAT %)
  - Website branding (logo, name)
  - Email notification settings

## 📋 Tech Stack

- **Frontend**: PHP, MDBootstrap UI, HTML5, CSS3, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **UI Framework**: MDBootstrap (Material Design for Bootstrap)
- **Icons**: Font Awesome

## 🛠️ Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)

### Step 1: Setup Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `vj_digital`
3. Import the SQL schema file:
   - Navigate to `database/schema.sql`
   - Import the file into your database

### Step 2: Configure Database Connection

1. Open `config/database.php`
2. Update the database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vj_digital');
```

### Step 3: Configure Site Settings

1. Open `config/config.php`
2. Update site configuration:
```php
define('SITE_NAME', 'VJ Digital');
define('SITE_URL', 'http://localhost/VJ Digital/');
define('ADMIN_EMAIL', 'admin@vjdigital.com');
```

### Step 4: File Permissions

Ensure the following directories have write permissions:
- `uploads/products/`
- `uploads/screenshots/`

### Step 5: Access the Website

1. Open your browser
2. Navigate to: `http://localhost/VJ Digital/`

### Step 6: Admin Access

- **Admin URL**: `http://localhost/VJ Digital/admin/login.php`
- **Default Email**: `admin@vjdigital.com`
- **Default Password**: `admin123`

⚠️ **Important**: Change the default admin password immediately after first login!

## 📁 Project Structure

```
VJ Digital/
├── admin/                      # Admin panel
│   ├── login.php              # Admin login
│   ├── dashboard.php          # Admin dashboard
│   ├── products.php           # Product management
│   ├── orders.php             # Order management
│   ├── users.php              # User management
│   ├── coupons.php            # Coupon management
│   ├── reports.php            # Reports & analytics
│   ├── support.php            # Support tickets
│   └── settings.php           # System settings
├── assets/                    # Static assets
│   ├── css/
│   │   └── style.css          # Custom styles
│   ├── js/
│   │   └── main.js            # Custom JavaScript
│   └── images/                # Images
├── config/                    # Configuration files
│   ├── config.php             # Site configuration
│   └── database.php           # Database connection
├── database/                  # Database files
│   └── schema.sql            # Database schema
├── includes/                  # Include files
│   ├── header.php            # Page header
│   ├── footer.php            # Page footer
│   └── auth_functions.php    # Authentication functions
├── uploads/                   # Upload directories
│   ├── products/             # Product files
│   └── screenshots/          # Product screenshots
├── index.php                 # Landing page
├── login.php                 # User login
├── signup.php                # User registration
├── forgot-password.php        # Password reset request
├── reset-password.php        # Password reset form
├── products.php              # Product listing
├── product-detail.php        # Product details
├── cart.php                  # Shopping cart
├── checkout.php              # Checkout page
├── order-confirmation.php    # Order confirmation
├── orders.php                # User orders
├── profile.php               # User profile
├── faq.php                   # FAQ page
├── contact.php               # Contact page
├── download.php              # File download handler
├── invoice.php               # Invoice generation
└── logout.php                # Logout handler
```

## 🔧 Configuration

### Payment Gateways

To enable payment gateways, configure them in the admin panel:

1. Login to admin dashboard
2. Go to Settings → Payment Gateways
3. Enter your API keys for:
   - Razorpay (Key & Secret)
   - Stripe (Key & Secret)
   - PayPal (Client ID & Secret)

### Email Notifications

To enable email notifications:

1. Configure your SMTP settings in PHP (php.ini)
2. Update email notification settings in admin panel
3. For production, integrate with email service like SendGrid or Mailgun

## 🎨 Customization

### Changing Colors

Edit `assets/css/style.css` and modify the CSS variables:

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    /* ... other variables */
}
```

### Adding New Pages

1. Create a new PHP file in the root directory
2. Include the header and footer:
```php
<?php
$pageTitle = 'Page Title';
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';

// Your page content here

require_once 'includes/footer.php';
?>
```

### Modifying Database

1. Update `database/schema.sql` with your changes
2. Run the updated schema in phpMyAdmin
3. Update corresponding PHP files to handle new fields

## 🔒 Security Recommendations

1. **Change Default Passwords**: Immediately change the default admin password
2. **HTTPS**: Enable SSL/HTTPS in production
3. **File Uploads**: Restrict file types in production
4. **SQL Injection**: All queries use prepared statements (already implemented)
5. **XSS Protection**: All user input is sanitized (already implemented)
6. **CSRF Protection**: Add CSRF tokens for forms (recommended for production)
7. **Rate Limiting**: Implement rate limiting for login attempts
8. **Backup**: Regular database backups

## 📱 Responsive Design

The website is fully responsive with:

- **Mobile View** (< 768px):
  - Bottom navigation bar
  - Full-width cards
  - Touch-friendly buttons
  - Native app-like experience

- **Tablet View** (768px - 1024px):
  - Optimized grid layouts
  - Touch-friendly interface

- **Desktop View** (> 1024px):
  - Professional navbar
  - Grid product layout (3-4 per row)
  - Sidebar navigation in admin panel

## 🌐 Dark/Light Mode

The website supports dark/light mode:

- Toggle button in navbar
- User preference saved in cookies
- Smooth transition between modes
- All components support both themes

## 📊 User Flow

1. **Browse Products**: Home → Products → Product Detail
2. **Purchase**: Add to Cart → Checkout → Payment
3. **Download**: Orders → Download purchased products
4. **Support**: Contact → Support Ticket → Admin Response

## 🐛 Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL server is running
- Verify database name exists

### File Upload Not Working
- Check directory permissions for `uploads/` folders
- Ensure PHP upload limits are sufficient (php.ini)
- Verify file size limits

### Session Issues
- Check PHP session configuration
- Ensure session.save_path is writable
- Clear browser cookies

### Payment Gateway Errors
- Verify API keys are correct
- Check payment gateway account status
- Ensure callback URLs are configured

## 📝 License

This project is provided as-is for educational and commercial use.

## 🤝 Support

For support:
- Email: support@vjdigital.com
- Create a support ticket through the contact form

## 🔄 Updates

Regular updates may include:
- New payment gateway integrations
- Enhanced security features
- UI/UX improvements
- Bug fixes and performance optimizations

## 📞 Contact

For questions or support regarding this project, please contact the development team.

---

**Built with ❤️ using PHP, MySQL, and MDBootstrap**
