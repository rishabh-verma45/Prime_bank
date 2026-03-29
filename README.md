# 🏦 PrimeBank - Complete Secure Banking System

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-blue.svg)](https://mysql.com)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6-yellow.svg)](https://javascript.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A **complete, production-ready online banking system** with email OTP verification, real-time transactions, UPI QR code, PDF statements, and professional UI. Perfect for learning full-stack development or as a banking solution prototype.

## 📸 Screenshots

| Dashboard | Statement | Transfer | Profile |
|-----------|-----------|----------|---------|
| <img src="https://via.placeholder.com/400x250?text=Dashboard+View" width="100%"> | <img src="https://via.placeholder.com/400x250?text=Statement+Page" width="100%"> | <img src="https://via.placeholder.com/400x250?text=Transfer+Page" width="100%"> | <img src="https://via.placeholder.com/400x250?text=Profile+Page" width="100%"> |

## ✨ Features

### 🔐 Authentication & Security
- User registration with email OTP verification
- Secure login with password + OTP (2-Factor Authentication)
- Session management with PHP sessions
- Password change with email notification
- Transaction PIN for secure money transfers
- bcrypt password hashing

### 💰 Money Management
- Add money to account (₹1 - ₹100,000)
- Send money to other users via:
  - Account Number
  - Phone Number
  - UPI ID
- Real-time balance updates
- Transaction PIN verification for transfers

### 📊 Banking Features
- Complete transaction history
- Account statement with date filtering
- PDF download for statements
- UPI QR code generation for payments
- Transaction summary (Total Credits/Debits)
- Recent transactions display

### 👤 Profile Management
- View personal information
- Update profile (Name, Phone)
- Change password
- Set/Reset transaction PIN
- View UPI ID and Account Number

### 📧 Email Notifications
- Welcome email on registration
- OTP for login verification
- Money sent/received alerts
- Password change confirmation
- PIN set/reset confirmation

## 🛠️ Technology Stack

### Frontend
| Technology | Purpose |
|------------|---------|
| HTML5 | Structure & Layout |
| CSS3 | Styling & Animations |
| JavaScript (ES6) | Dynamic functionality |
| Font Awesome 6 | Icons |
| Google Fonts (Inter) | Typography |
| QRCode.js | QR code generation |
| html2pdf.js | PDF export |

### Backend
| Technology | Purpose |
|------------|---------|
| PHP 7.4+ | Server-side logic |
| MySQL 5.7+ | Database management |
| EmailJS / PHPMailer | Email sending |
| bcrypt | Password hashing |
| MD5 | PIN hashing |

### Development Tools
| Tool | Purpose |
|------|---------|
| XAMPP | Local server environment |
| phpMyAdmin | Database administration |
| Git | Version control |

## 📁 Project Structure
primebank/
├── index.html # Main application file
├── README.md # Documentation
├── LICENSE # MIT License
├── css/
│ └── style.css # Styling (embedded in index.html)
├── js/
│ └── script.js # Client-side logic (embedded)
├── php/
│ ├── config.php # Database & email configuration
│ ├── register.php # User registration API
│ ├── login.php # Login authentication API
│ ├── send_otp.php # OTP sending API
│ ├── get_user.php # Fetch user details
│ ├── get_transactions.php # Transaction history API
│ ├── add_money.php # Add funds API
│ ├── send_money.php # Money transfer API
│ ├── set_txn_pin.php # PIN management API
│ ├── change_password.php # Password update API
│ ├── update_profile.php # Profile update API
│ └── logout.php # Session destroy API
├── sql/
│ └── database.sql # Database schema
└── vendor/ # PHPMailer library (composer)


## 🚀 Installation Guide

### Prerequisites

| Requirement | Version | Download Link |
|-------------|---------|---------------|
| XAMPP | 7.4+ | [https://www.apachefriends.org/](https://www.apachefriends.org/) |
| Web Browser | Latest | Chrome/Firefox/Edge |
| Gmail Account | - | [https://gmail.com](https://gmail.com) |
| Internet Connection | - | For email services |

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Run the installer with default settings
3. Launch XAMPP Control Panel
4. Start **Apache** and **MySQL** services

### Step 2: Download Project

**Option A: Clone with Git**
```bash
cd C:\xampp\htdocs
git clone https://github.com/yourusername/primebank.git

Option B: Manual Download

Download ZIP from GitHub

Extract to C:\xampp\htdocs\primebank\

Step 3: Setup Database
Open phpMyAdmin: http://localhost/phpmyadmin

Click "New" to create database

Database name: primebank_db

Collation: utf8mb4_general_ci

Click "Create"

Go to "Import" tab

Select file: primebank/sql/database.sql

Click "Go"

Step 4: Configure Email (Gmail)
Generate Gmail App Password:

Go to Google Account Security

Enable 2-Step Verification

Go to App Passwords

Select app: "Mail"

Select device: "Other" (name it "PrimeBank")

Copy the 16-digit password

Update php/config.php:

php
// Find these lines and update
define('SMTP_USER', 'your_email@gmail.com');     // Your Gmail
define('SMTP_PASS', 'your_16_digit_password');   // App password
Step 5: Install PHPMailer
Using Composer (Recommended):

bash
cd C:\xampp\htdocs\primebank
composer require phpmailer/phpmailer
Manual Installation:

Download from https://github.com/PHPMailer/PHPMailer

Extract to primebank/vendor/ folder

Step 6: Run Application
Open your browser and navigate to:

text
http://localhost/primebank/
🔑 Default Login Credentials
Account Type	Email	Password	Transaction PIN
Demo User	demo@primebank.com	demo123	1234
Test Recipient	test@primebank.com	test123	Not set
📖 Usage Guide
For End Users
1. Registration
text
1. Click "Create New Account"
2. Fill in your details (Name, Email, Phone, Password)
3. Click "Send OTP" - Check your email for 6-digit code
4. Enter OTP and click "Register"
5. Login with your credentials
2. Adding Money
text
1. Login to your dashboard
2. Click "Add Money" button
3. Enter amount (₹1 - ₹100,000)
4. Click "Add Money"
5. Balance updates instantly ✓
3. Sending Money
text
1. Go to "Transfer" page from sidebar
2. Select send via:
   • Account Number
   • Phone Number  
   • UPI ID
3. Enter recipient details
4. Enter amount (use quick buttons for common amounts)
5. Enter your Transaction PIN
6. Click "Send Money"
7. Both parties receive email confirmation ✓
4. View Statement
text
1. Go to "Statement" page
2. Select date range (From Date - To Date)
3. Click "Apply Filter"
4. View filtered transactions
5. Click "Download PDF" to save statement
5. Set Transaction PIN
text
1. Go to "Reset PIN" page
2. View current PIN status
3. Enter new PIN (4-6 digits only)
4. Confirm PIN
5. Click "Set Transaction PIN"
6. Receive email confirmation ✓
6. Update Profile
text
1. Go to "Profile" page
2. Edit Name or Phone number
3. Click "Update Profile"
4. Changes saved instantly ✓
7. Change Password
text
1. Go to "Profile" page
2. Enter current password
3. Enter new password (min 4 characters)
4. Confirm new password
5. Click "Change Password"
6. Receive email confirmation ✓
8. UPI QR Code
text
1. Dashboard displays your UPI QR code
2. Scan with any UPI app (Google Pay, PhonePe, Paytm)
3. Enter amount and pay directly
