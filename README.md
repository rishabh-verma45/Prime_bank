# 🏦 PrimeBank - Secure Online Banking System

A full-stack **web-based banking system** built using PHP, MySQL, JavaScript, HTML, and CSS.  
This project simulates real-world banking operations including authentication, transactions, OTP verification, and email notifications.

---

## 🚀 Features

### 🔐 Authentication & Security
- User registration with Email OTP verification
- Secure login with Password + OTP (2FA)
- Password hashing using bcrypt
- Session management
- Transaction PIN for secure transfers

### 💰 Transactions
- Add money (simulation)
- Send money using:
  - Account Number
  - Phone Number
  - UPI ID
- Real-time balance updates
- Transaction records stored in database

### 📊 Banking Features
- Transaction history (statement)
- Credit/Debit tracking
- PDF statement download
- UPI QR code support

### 👤 Profile Management
- Update user details
- Change password
- Set/Reset transaction PIN

### 📧 Email Notifications
- OTP verification
- Login alerts
- Money sent/received alerts
- Password change alerts

---

## 🛠️ Tech Stack

### Frontend
- HTML5
- CSS3
- JavaScript (ES6)

### Backend
- PHP 7.4+
- MySQL

### Tools & Libraries
- PHPMailer (Email)
- QRCode.js
- html2pdf.js
- XAMPP

---

## 📁 Project Structure
primebank/
│── index.html
│── css/
│── js/
│── php/
│── sql/
│── vendor/
│── README.md


---

## ⚙️ Installation Guide

### 1. Setup XAMPP
- Install XAMPP
- Start Apache & MySQL

---

### 2. Clone Repository

```bash
cd C:\xampp\htdocs
git clone https://github.com/rishabh-verma45/Prime_bank.git
```
3. Setup Database
Open: http://localhost/phpmyadmin
Create database: primebank_db
Import: primebank/sql/database.sql

4. Configure Email

Edit: php/config.php
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_app_password');

5. Install PHPMailer
composer require phpmailer/phpmailer

6. Run Project
http://localhost/primebank/

🔑 Demo Credentials
Type	Email	Password	PIN
Demo User	demo@primebank.com
	demo123	1234
📖 Usage
Register
Enter details
Verify OTP
Login
Transfer Money
Choose method (Account / Phone / UPI)
Enter amount
Enter PIN
Confirm
View Statement
Filter by date
Download PDF
⚠️ Limitations
Not connected to real banking APIs
Email-based OTP only (no SMS)
No advanced fraud detection
Not production-secure
🔥 Future Improvements
Payment gateway integration
SMS OTP system
AI fraud detection
Mobile app version
📜 License

This project is licensed under the MIT License.

👨‍💻 Author

Rishabh Verma

---

# 🔥 Brutal Feedback on Your Original README

From your file :contentReference[oaicite:0]{index=0}:

### ❌ Problems:
- Mixed formatting (markdown broken in places)
- Too long without hierarchy
- Installation steps messy
- Some sections look inflated (“production-ready” — not true)

### ✅ Fixes I made:
- Clean structure
- Proper headings
- Realistic claims
- Better readability for GitHub

---

# 💣 Reality Check

Don’t call this:
> “production-ready banking system”

It’s not. It’s:
👉 **A strong academic + portfolio project**

If you oversell it, anyone technical will tear it apart in 2 minutes.

---

# 👉 Next Step (Important)

Now that your repo is fixed, you should:

- Add screenshots (real ones, not placeholders)
- Add `.env` file support (hide credentials)
- Add proper commit history (not one big dump)

---

If you want:
👉 I can review your **actual GitHub repo** and tell you exactly what will impress recruiters vs what will expose you.
