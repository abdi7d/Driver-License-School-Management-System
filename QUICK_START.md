# Quick Start Guide - Driver License School

Fast setup guide for getting the database and application running.

---

## ⚡ 5-Minute Setup (Windows with XAMPP)

### 1. Install XAMPP

```
1. Download from: https://www.apachefriends.org/
2. Run installer
3. Accept defaults
4. Finish
```

### 2. Start Services

```
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Wait for "Running" status
```

### 3. Open Project

```
1. Copy Driver-License-School folder to C:\xampp\htdocs\
2. Open browser: http://localhost/Driver-License-School/client/
```

### 4. Import Database

```
1. Go to http://localhost/phpmyadmin
2. Click "Databases" tab
3. Create new database: "driver_license_school"
4. Select new database
5. Click "Import" tab
6. Choose file: Driver-License-School/server/database/schema.sql
7. Click "Import"
```

### 5. Done! ✅

```
Login: http://localhost/Driver-License-School/client/login.html
```

---

## ⚡ 10-Minute Setup (Windows with MySQL Installer)

### Step 1: Install MySQL

```bash
1. Download MySQL Community Server 8.0+
2. Run installer
3. Choose "Developer Default"
4. Install all components
5. Configure MySQL port as 3306
6. Set root password (e.g., root123)
```

### Step 2: Import Database

```bash
# Open Command Prompt as Administrator
cd C:\path\to\Driver-License-School\server\database

# Import schema
mysql -u root -p driver_license_school < schema.sql

# Enter password when prompted
# Type: root123 (or your password)
```

### Step 3: Verify Installation

```bash
# Check tables created
mysql -u root -p driver_license_school -e "SHOW TABLES;"

# Should show 10 tables
```

### Step 4: Start Application

```bash
# Open Command Prompt
cd C:\path\to\Driver-License-School\server

# Start PHP server
php -S localhost:8000 -t .

# Open browser: http://localhost:8000
```

### Step 5: Done! ✅

```
Access: http://localhost:8000
```

---

## ⚡ 5-Minute Setup (Mac/Linux)

### Step 1: Install MySQL (Homebrew)

```bash
# Install Homebrew (if needed)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install MySQL
brew install mysql

# Start MySQL
brew services start mysql
```

### Step 2: Import Database

```bash
# Navigate to project
cd /path/to/Driver-License-School/server/database

# Import schema
mysql -u root driver_license_school < schema.sql
```

### Step 3: Verify Installation

```bash
# Check tables
mysql -u root driver_license_school -e "SHOW TABLES;"
```

### Step 4: Start Application

```bash
# Navigate to server
cd /path/to/Driver-License-School/server

# Start PHP server
php -S localhost:8000 -t .

# Open browser: http://localhost:8000
```

### Step 5: Done! ✅

```
Access: http://localhost:8000
```

---

## 🔧 Database Only (Command Line - All Platforms)

### Just the Database Steps

```bash
# 1. Navigate to schema directory
cd path/to/Driver-License-School/server/database

# 2. Login to MySQL
mysql -u root -p

# 3. At MySQL prompt, run:
CREATE DATABASE driver_license_school
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE driver_license_school;
SOURCE schema.sql;

# 4. Exit
EXIT;

# 5. Verify
mysql -u root -p driver_license_school -e "SHOW TABLES;"
```

---

## 🎯 Common Commands Reference

```bash
# Create database from SQL file
mysql -u root -p driver_license_school < schema.sql

# Check if database exists
mysql -u root -p -e "SHOW DATABASES;"

# View all tables
mysql -u root -p driver_license_school -e "SHOW TABLES;"

# View table structure
mysql -u root -p driver_license_school -e "DESCRIBE users;"

# View sample data
mysql -u root -p driver_license_school -e "SELECT * FROM training_programs;"

# Count records in table
mysql -u root -p driver_license_school -e "SELECT COUNT(*) FROM users;"

# Delete database (WARNING!)
mysql -u root -p -e "DROP DATABASE driver_license_school;"

# Start PHP server
php -S localhost:8000 -t .

# Start from specific port
php -S localhost:9000 -t .
```

---

## 🐛 Quick Fixes

### Issue: "Access Denied"

```bash
# Make sure password is correct
mysql -u root -p

# Reset password (Windows)
net stop MySQL80
mysqld --skip-grant-tables
# In new terminal:
mysql -u root
FLUSH PRIVILEGES;
UPDATE mysql.user SET authentication_string=PASSWORD('newpass') WHERE User='root';
EXIT;
```

### Issue: "Can't connect to MySQL"

```bash
# Start MySQL
# Windows:
net start MySQL80

# Mac:
brew services start mysql

# Linux:
sudo systemctl start mysql
```

### Issue: "Unknown database"

```bash
# Create database first
mysql -u root -p
CREATE DATABASE driver_license_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Then import
mysql -u root -p driver_license_school < schema.sql
```

### Issue: "PHP server won't start"

```bash
# Use different port
php -S localhost:9000 -t .

# Or specify IP
php -S 127.0.0.1:8000 -t .
```

---

## 📋 Checklist

- ✅ MySQL installed
- ✅ MySQL running
- ✅ Database created
- ✅ Schema imported
- ✅ 10 tables visible
- ✅ PHP server running
- ✅ Can access http://localhost:8000
- ✅ Can login or register

---

## 🚀 First Login

After setup:

1. Go to `http://localhost:8000`
2. Click "Register"
3. Create account as **Manager**
4. Login with created credentials
5. You're ready to manage the system!

---

## 📞 Need Help?

Refer to full guide: `DATABASE_SETUP.md`

Common issues are covered in Troubleshooting section.

---

**Ready? Let's go!** 🎉
