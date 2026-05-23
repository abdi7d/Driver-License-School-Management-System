# Driver License School - Database Setup Guide

Complete step-by-step instructions for creating and configuring the database for the Driver License School Management System.

---

## 📋 Table of Contents

- [Step 1: Install MySQL](#step-1-install-mysql)
- [Step 2: Start MySQL Service](#step-2-start-mysql-service)
- [Step 3: Create Database](#step-3-create-database)
- [Step 4: Import Schema](#step-4-import-schema)
- [Step 5: Verify Installation](#step-5-verify-installation)
- [Step 6: Configure PHP Connection](#step-6-configure-php-connection)
- [Step 7: Test Connection](#step-7-test-connection)
- [Troubleshooting](#troubleshooting)

---

## Step 1: Install MySQL

### Option A: Windows (Using MySQL Installer)

1. **Download MySQL Installer**
   - Go to [mysql.com](https://dev.mysql.com/downloads/mysql/)
   - Download "MySQL Community Server" (latest version, e.g., 8.0+)
   - Choose Windows (x86, 64-bit) MSI Installer

2. **Run the Installer**
   - Double-click `mysql-installer-community-x.x.x.msi`
   - Click "Next" to proceed

3. **Choose Setup Type**
   - Select: **"Developer Default"** (includes MySQL Server, MySQL Workbench)
   - Click "Next"

4. **Check Requirements**
   - The installer checks for dependencies
   - Install any missing prerequisites
   - Click "Next"

5. **Installation**
   - Click "Execute" to download and install components
   - Wait for installation to complete
   - Click "Next"

6. **MySQL Server Configuration**
   - **Config Type**: Select "Development Machine"
   - **MySQL Port**: Keep default `3306`
   - **MySQL X Protocol Port**: Keep default `33060`
   - Click "Next"

7. **Accounts and Roles**
   - **MySQL Root Password**: Enter a strong password (e.g., `root123`)
   - **Re-enter Password**: Confirm password
   - Click "Next"
   - Click "Finish" to complete setup

8. **Verify Installation**
   ```bash
   mysql --version
   # Output: mysql  Ver 8.0.xx for Win64 on x86_64 (MySQL Community Server - GPL)
   ```

### Option B: Windows (Using XAMPP)

1. **Download XAMPP**
   - Go to [apachefriends.org](https://www.apachefriends.org/)
   - Download XAMPP for Windows (includes MySQL, Apache, PHP)

2. **Install XAMPP**
   - Run the installer
   - Choose installation directory (default: `C:\xampp`)
   - Click "Install"

3. **Start MySQL in XAMPP Control Panel**
   - Open XAMPP Control Panel
   - Click "Start" button next to MySQL
   - Status should show "Running"

### Option C: Mac (Using Homebrew)

```bash
# Install Homebrew (if not already installed)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install MySQL
brew install mysql

# Start MySQL service
brew services start mysql

# Verify installation
mysql --version
```

### Option D: Linux (Ubuntu/Debian)

```bash
# Update package list
sudo apt-get update

# Install MySQL Server
sudo apt-get install mysql-server

# Run secure installation
sudo mysql_secure_installation

# Start MySQL service
sudo systemctl start mysql

# Verify installation
mysql --version
```

---

## Step 2: Start MySQL Service

### Windows

**Method 1: Using Services (Recommended)**

1. Press `Win + R`
2. Type: `services.msc`
3. Find "MySQL80" (or your MySQL version)
4. Right-click → "Start"
5. Verify status shows "Running"

**Method 2: Using Command Prompt**

```bash
# Open Command Prompt as Administrator
net start MySQL80

# Stop MySQL (when needed)
net stop MySQL80
```

**Method 3: Using XAMPP**

1. Open XAMPP Control Panel
2. Click "Start" next to MySQL

### Mac/Linux

```bash
# Start MySQL
sudo systemctl start mysql

# Verify MySQL is running
sudo systemctl status mysql

# Enable auto-start on boot
sudo systemctl enable mysql
```

---

## Step 3: Create Database

### Method 1: Using Command Line (Recommended)

1. **Open Command Prompt/Terminal**

   ```bash
   # Login to MySQL
   mysql -u root -p
   ```

2. **Enter Password**
   - Type the root password you set during installation
   - Press Enter

3. **Create Database**

   ```sql
   CREATE DATABASE driver_license_school
   CHARACTER SET utf8mb4
   COLLATE utf8mb4_unicode_ci;
   ```

4. **Verify Database Created**

   ```sql
   SHOW DATABASES;
   ```

5. **Select Database**

   ```sql
   USE driver_license_school;
   ```

6. **Exit MySQL**
   ```sql
   EXIT;
   ```

### Method 2: Using MySQL Workbench

1. **Open MySQL Workbench**
2. **Connect to MySQL Server**
   - Double-click your local connection
   - Or click "+" to create new connection
3. **Open Query Tab**
   - Click "File" → "New Query Tab"
   - Or press `Ctrl + T`
4. **Run Create Database Query**
   ```sql
   CREATE DATABASE driver_license_school
   CHARACTER SET utf8mb4
   COLLATE utf8mb4_unicode_ci;
   ```
5. **Execute Query**
   - Click the lightning bolt icon
   - Or press `Ctrl + Enter`

### Method 3: Using phpMyAdmin

1. **Open phpMyAdmin** (if using XAMPP)
   - Go to `http://localhost/phpmyadmin`
2. **Login**
   - Username: `root`
   - Password: (leave blank or your set password)
3. **Create Database**
   - Click "Databases" tab
   - Enter "driver_license_school" in "Create new database" field
   - Select "utf8mb4_unicode_ci" collation
   - Click "Create"

---

## Step 4: Import Schema

### Method 1: Using Command Line (Recommended)

1. **Navigate to Project Directory**

   ```bash
   cd C:\path\to\Driver-License-School\server\database
   # Or on Mac/Linux:
   cd /path/to/Driver-License-School/server/database
   ```

2. **Import Schema**

   ```bash
   mysql -u root -p driver_license_school < schema.sql
   ```

3. **Enter Password**
   - Type root password when prompted
   - Press Enter

4. **Verify Import Completed**
   - If successful, command returns to prompt with no errors
   - If there are errors, note them for troubleshooting

### Method 2: Using MySQL Workbench

1. **Open MySQL Workbench**
2. **Connect to MySQL Server**
3. **Import SQL File**
   - Click "File" → "Open SQL Script"
   - Navigate to `server/database/schema.sql`
   - Click "Open"
4. **Execute Script**
   - Click lightning bolt icon
   - Or press `Ctrl + Enter`
5. **Monitor Execution**
   - Watch "Output" section for completion message
   - Check for any errors

### Method 3: Using phpMyAdmin

1. **Open phpMyAdmin**
   - Go to `http://localhost/phpmyadmin`

2. **Select Database**
   - Click on `driver_license_school` in left sidebar

3. **Import SQL File**
   - Click "Import" tab
   - Click "Choose File"
   - Select `server/database/schema.sql`
   - Click "Open"

4. **Start Import**
   - Click "Import" button
   - Wait for completion message

---

## Step 5: Verify Installation

### Verify Tables Created

**Using Command Line:**

```bash
mysql -u root -p driver_license_school -e "SHOW TABLES;"
```

**Expected Output:**

```
Tables_in_driver_license_school
certificates
documents
enrollments
exams
instructor_details
lessons
notifications
student_details
training_programs
users
```

### Verify Training Programs (Pre-seeded Data)

```bash
mysql -u root -p driver_license_school -e "SELECT * FROM training_programs;"
```

**Expected Output:**

```
id | name                          | theory_hours | practical_hours | fee
1  | Level 1 - Motorcycle          | 20           | 30              | 3500.00
2  | Level 2 - Private Car         | 25           | 40              | 5500.00
3  | Level 3 - Heavy Truck         | 35           | 55              | 8500.00
4  | Level 4 - People Transportation | 30         | 50              | 7800.00
5  | Level 5 - Bus Driver          | 40           | 60              | 9200.00
```

### Count Tables and Records

```bash
# Using MySQL Workbench or Command Line
mysql -u root -p driver_license_school
```

```sql
-- Check table counts
SELECT table_name, TABLE_ROWS
FROM information_schema.tables
WHERE table_schema = 'driver_license_school';

-- Check specific table structure
DESCRIBE users;
```

---

## Step 6: Configure PHP Connection

### Edit Database Configuration File

1. **Open Configuration File**
   - Location: `server/config/db.php`
   - Use any text editor (VS Code, Notepad++, etc.)

2. **Update Database Credentials**

   **Find these lines:**

   ```php
   $db_host = getenv('DB_HOST') ?: "localhost";
   $db_name = getenv('DB_NAME') ?: "driver_license_school";
   $db_user = getenv('DB_USER') ?: "root";
   $db_pass = getenv('DB_PASS') ?: "";
   ```

   **Modify if needed:**

   ```php
   $db_host = "localhost";           // MySQL host
   $db_name = "driver_license_school"; // Database name
   $db_user = "root";               // MySQL username
   $db_pass = "your_password";      // Your MySQL password
   ```

3. **Save File**
   - Press `Ctrl + S` to save

### Create .env File (Optional)

Create `server/.env` file for environment variables:

```env
DB_HOST=localhost
DB_NAME=driver_license_school
DB_USER=root
DB_PASS=your_password
JWT_SECRET=your_secret_key_here_at_least_32_characters
```

---

## Step 7: Test Connection

### Test 1: Using Command Line

```bash
mysql -u root -p -h localhost driver_license_school
```

If successful, you'll see the `mysql>` prompt.

```sql
-- Run test query
SELECT COUNT(*) as total_tables FROM information_schema.tables
WHERE table_schema = 'driver_license_school';

-- Exit
EXIT;
```

### Test 2: Using PHP Script

Create file: `server/test-db.php`

```php
<?php
// Database Configuration
$db_host = "localhost";
$db_name = "driver_license_school";
$db_user = "root";
$db_pass = "";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Database connection successful!<br>";

// Get table count
$result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables
                        WHERE table_schema = 'driver_license_school'");
$row = $result->fetch_assoc();

echo "✅ Total tables: " . $row['count'] . "<br>";

// List all tables
$tables_result = $conn->query("SHOW TABLES");
echo "<br>Tables in database:<br>";
while ($table = $tables_result->fetch_row()) {
    echo "  • " . $table[0] . "<br>";
}

$conn->close();
?>
```

**Run Test:**

```bash
cd server
php test-db.php
```

**Expected Output:**

```
✅ Database connection successful!
✅ Total tables: 10
Tables in database:
  • certificates
  • documents
  • enrollments
  • exams
  • instructor_details
  • lessons
  • notifications
  • student_details
  • training_programs
  • users
```

### Test 3: Using Application

1. **Start Server**

   ```bash
   cd server
   npm start
   ```

2. **Access Application**
   - Open browser: `http://localhost:8000`

3. **Try Login**
   - System will connect to database
   - If successful, you can proceed with registration

---

## Troubleshooting

### Issue 1: "MySQL Connection Error: Access Denied"

**Possible Causes:**

- Wrong password
- Wrong username
- MySQL service not running

**Solutions:**

```bash
# Verify MySQL is running
# Windows:
net start MySQL80

# Mac/Linux:
sudo systemctl status mysql

# Try connecting with correct credentials
mysql -u root -p
# Enter password when prompted

# If password forgotten, reset it (Windows):
# Stop MySQL service first
net stop MySQL80
# Restart without grant tables
mysqld --skip-grant-tables
# In another terminal:
mysql -u root
# Then run: FLUSH PRIVILEGES;
# UPDATE mysql.user SET authentication_string=PASSWORD('newpassword') WHERE User='root';
```

### Issue 2: "Can't connect to MySQL server on 'localhost'"

**Possible Causes:**

- MySQL service not running
- MySQL port blocked
- Wrong host

**Solutions:**

```bash
# Windows - Start MySQL service
net start MySQL80

# Mac/Linux - Start MySQL service
sudo systemctl start mysql

# Verify MySQL is listening on port 3306
# Windows:
netstat -an | findstr 3306

# Mac/Linux:
sudo netstat -an | grep 3306

# Try with TCP/IP explicitly
mysql -h 127.0.0.1 -u root -p
```

### Issue 3: "ERROR 1064 at line X: You have an error in your SQL syntax"

**Possible Causes:**

- Corrupted schema file
- Character encoding issue
- MySQL version incompatibility

**Solutions:**

```bash
# Download fresh schema.sql from repository

# Ensure UTF-8 encoding
# Windows Command Prompt:
chcp 65001

# Try importing with explicit charset
mysql -u root -p --default-character-set=utf8mb4 driver_license_school < schema.sql

# Try importing step by step
mysql -u root -p
USE driver_license_school;
SOURCE server/database/schema.sql;
```

### Issue 4: "ERROR 1049: Unknown database 'driver_license_school'"

**Possible Causes:**

- Database not created
- Database name misspelled
- Using wrong database

**Solutions:**

```bash
# Check if database exists
mysql -u root -p -e "SHOW DATABASES;"

# Create database if missing
mysql -u root -p -e "CREATE DATABASE driver_license_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Verify it was created
mysql -u root -p -e "SHOW DATABASES;"
```

### Issue 5: "Lost connection to MySQL server"

**Possible Causes:**

- MySQL service crashed
- Network timeout
- Server overload

**Solutions:**

```bash
# Restart MySQL service
# Windows:
net stop MySQL80
net start MySQL80

# Mac/Linux:
sudo systemctl restart mysql

# Check MySQL error log
# Windows (XAMPP):
cd C:\xampp\mysql\data
# Look for .err files

# Increase connection timeout in db.php
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
```

### Issue 6: "1215 - Cannot add foreign key constraint"

**Possible Causes:**

- Foreign key constraint violation
- Table doesn't exist yet
- Character set mismatch

**Solutions:**

```bash
# Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS=0;

# Run import
mysql -u root -p driver_license_school < schema.sql

# Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

# Or ensure tables created in correct order
# The schema.sql already handles this correctly
```

### Issue 7: PHP "No such file or directory"

**Possible Causes:**

- Wrong path to schema.sql
- Not in correct directory

**Solutions:**

```bash
# Make sure you're in correct directory
cd C:\path\to\Driver-License-School\server\database

# Verify file exists
dir schema.sql
# Or on Mac/Linux:
ls -la schema.sql

# Use full path
mysql -u root -p driver_license_school < C:\full\path\to\schema.sql
```

---

## Quick Reference - All Steps Combined

**For experienced users:**

```bash
# 1. Verify MySQL running
mysql -u root -p -e "SELECT 1;"

# 2. Create database
mysql -u root -p -e "CREATE DATABASE driver_license_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Import schema
cd server/database
mysql -u root -p driver_license_school < schema.sql

# 4. Verify installation
mysql -u root -p driver_license_school -e "SHOW TABLES;"

# 5. Check pre-seeded data
mysql -u root -p driver_license_school -e "SELECT * FROM training_programs;"
```

---

## Post-Installation Checklist

- ✅ MySQL installed and running
- ✅ Database `driver_license_school` created
- ✅ Schema imported successfully (10 tables)
- ✅ Training programs seeded (5 programs)
- ✅ `server/config/db.php` configured
- ✅ Connection tested successfully
- ✅ No error messages in logs
- ✅ Ready to start application

---

## Next Steps

After database setup:

1. **Configure Application**
   - Set environment variables in `.env`
   - Configure JWT secret

2. **Start Backend Server**

   ```bash
   cd server
   npm start
   ```

3. **Access Application**
   - Open `http://localhost:8000`

4. **Create First User**
   - Register as manager (first admin user)
   - Then create other users

5. **Setup Initial Data**
   - Programs already seeded (Level 1-5)
   - Create instructors and students
   - Begin training management

---

## Support & Help

If you encounter issues:

1. Check this guide's Troubleshooting section
2. Review MySQL error messages carefully
3. Verify all prerequisites are installed
4. Check file paths are correct
5. Ensure MySQL is running
6. Try using phpMyAdmin for GUI approach
7. Check database credentials match application config

---

**Database Setup Complete!** 🎉

You're now ready to run the Driver License School Management System.
