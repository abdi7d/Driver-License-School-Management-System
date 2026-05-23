# Visual Step-by-Step Setup Guide

Complete visual walkthrough for setting up the Driver License School database.

---

## 📊 Setup Overview

```
┌─────────────────────────────────────────────────────────┐
│  INSTALLATION OVERVIEW                                  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Step 1: Install MySQL                                  │
│  └──→ Download & Install MySQL Server                   │
│                                                          │
│  Step 2: Start MySQL Service                            │
│  └──→ Verify MySQL is running                           │
│                                                          │
│  Step 3: Create Database                                │
│  └──→ Create "driver_license_school" database           │
│                                                          │
│  Step 4: Import Schema                                  │
│  └──→ Load database tables & structure                  │
│                                                          │
│  Step 5: Verify Installation                            │
│  └──→ Check all tables are created                      │
│                                                          │
│  Step 6: Configure Application                          │
│  └──→ Set database credentials in config                │
│                                                          │
│  Step 7: Test Connection                                │
│  └──→ Verify PHP can connect to database                │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## Step 1: Install MySQL

### Windows Users

```
STEP 1A: DOWNLOAD
┌──────────────────────────────────┐
│ Go to: mysql.com/downloads/mysql │
└──────────────────────────────────┘
                ↓
          Choose "MySQL Community Server"
                ↓
        Download Windows (x86, 64-bit) MSI
                ↓
        Save to Downloads folder


STEP 1B: INSTALL
┌──────────────────────────────────┐
│ Double-click: mysql-installer... │
│              .msi                │
└──────────────────────────────────┘
                ↓
        1. Accept License Agreement
                ↓
        2. Choose "Developer Default"
                ↓
        3. Click "Execute" to install
                ↓
        4. Set Root Password: root123
                ↓
        5. Click "Finish"


STEP 1C: VERIFY
┌──────────────────────────────────┐
│ Open Command Prompt and run:     │
│ mysql --version                  │
└──────────────────────────────────┘
                ↓
Expected: mysql  Ver 8.0.xx for Win64
```

### Mac Users

```
STEP 1A: INSTALL HOMEBREW
┌────────────────────────────────────────┐
│ Open Terminal and paste:               │
│ /bin/bash -c "$(curl -fsSL ...)"       │
└────────────────────────────────────────┘
                ↓
        Wait for installation to complete
                ↓

STEP 1B: INSTALL MYSQL
┌────────────────────────────────────────┐
│ In Terminal, run:                      │
│ brew install mysql                     │
└────────────────────────────────────────┘
                ↓
        Wait for installation


STEP 1C: START MYSQL
┌────────────────────────────────────────┐
│ In Terminal, run:                      │
│ brew services start mysql              │
└────────────────────────────────────────┘
                ↓

STEP 1D: VERIFY
┌────────────────────────────────────────┐
│ In Terminal, run:                      │
│ mysql --version                        │
└────────────────────────────────────────┘
                ↓
Expected: mysql  Ver 8.0.xx for macos...
```

### Linux Users

```
STEP 1A: UPDATE PACKAGES
┌────────────────────────────────────────┐
│ Open Terminal and run:                 │
│ sudo apt-get update                    │
└────────────────────────────────────────┘
                ↓

STEP 1B: INSTALL MYSQL
┌────────────────────────────────────────┐
│ In Terminal, run:                      │
│ sudo apt-get install mysql-server      │
└────────────────────────────────────────┘
                ↓
        Type 'Y' when prompted
                ↓

STEP 1C: VERIFY
┌────────────────────────────────────────┐
│ In Terminal, run:                      │
│ mysql --version                        │
└────────────────────────────────────────┘
                ↓
Expected: mysql  Ver 8.0.xx for Linux...
```

---

## Step 2: Start MySQL Service

### Windows

```
METHOD 1: COMMAND PROMPT (Recommended)
┌──────────────────────────────────┐
│ 1. Open Command Prompt as Admin  │
│ 2. Type: net start MySQL80       │
│ 3. Press Enter                   │
└──────────────────────────────────┘
                ↓
        Expected: "The MySQL80 service
                   is starting..."


METHOD 2: SERVICES
┌──────────────────────────────────┐
│ 1. Press Win + R                 │
│ 2. Type: services.msc            │
│ 3. Find "MySQL80"                │
│ 4. Right-click → Start           │
└──────────────────────────────────┘


VERIFY RUNNING
┌──────────────────────────────────┐
│ Type in Command Prompt:          │
│ mysql -u root -p                 │
│ Password: (your set password)    │
│ mysql>                           │
│ EXIT;                            │
└──────────────────────────────────┘
                ↓
        If you see "mysql>" prompt,
        MySQL is running ✓
```

### Mac/Linux

```
VERIFY RUNNING
┌────────────────────────────────────────┐
│ In Terminal, run:                      │
│ mysql -u root                          │
│ mysql>                                 │
│ EXIT;                                  │
└────────────────────────────────────────┘
                ↓
        If you see "mysql>" prompt,
        MySQL is running ✓
```

---

## Step 3: Create Database

### Method 1: Command Line (Recommended)

```
WINDOWS:
┌──────────────────────────────────┐
│ 1. Open Command Prompt           │
│ 2. Type: mysql -u root -p        │
│ 3. Enter password                │
└──────────────────────────────────┘
                ↓
        mysql>
                ↓
┌──────────────────────────────────────────────┐
│ Copy & paste:                                │
│                                              │
│ CREATE DATABASE driver_license_school       │
│   CHARACTER SET utf8mb4                      │
│   COLLATE utf8mb4_unicode_ci;               │
│                                              │
│ Press Enter                                 │
└──────────────────────────────────────────────┘
                ↓
        Query OK, 1 row affected
                ↓
┌──────────────────────────────────┐
│ Type: EXIT;                      │
│ Press Enter                      │
└──────────────────────────────────┘
```

### Method 2: phpMyAdmin (Easy Visual)

```
IF USING XAMPP:
┌───────────────────────────────────────┐
│ 1. Open browser                       │
│ 2. Go to: localhost/phpmyadmin        │
│ 3. Login (root, no password)          │
└───────────────────────────────────────┘
                ↓
        You see phpMyAdmin dashboard
                ↓
┌───────────────────────────────────────┐
│ 1. Click "Databases" tab              │
│ 2. Enter: driver_license_school       │
│ 3. Select collation: utf8mb4_unicode  │
│ 4. Click "Create"                     │
└───────────────────────────────────────┘
                ↓
        Success! Database created
```

---

## Step 4: Import Schema

### Method 1: Command Line (Recommended)

```
WINDOWS:
┌──────────────────────────────────┐
│ 1. Open Command Prompt           │
│ 2. Navigate to project:          │
│    cd C:\path\to\project         │
│    cd server\database            │
└──────────────────────────────────┘
                ↓
        C:\...\database>
                ↓
┌──────────────────────────────────┐
│ Copy & paste:                    │
│                                  │
│ mysql -u root -p driver_license_school < schema.sql
│                                  │
│ Press Enter                      │
│ Enter password when prompted     │
└──────────────────────────────────┘
                ↓
        (Wait 5-10 seconds)
                ↓
        Command prompt returns with
        no error message
                ↓
        SUCCESS! ✓


MAC/LINUX:
┌──────────────────────────────────┐
│ 1. Open Terminal                 │
│ 2. Navigate to project:          │
│    cd /path/to/project           │
│    cd server/database            │
└──────────────────────────────────┘
                ↓
        $ (path)/database
                ↓
┌──────────────────────────────────┐
│ Type:                            │
│                                  │
│ mysql -u root driver_license_school < schema.sql
│                                  │
│ Press Enter                      │
└──────────────────────────────────┘
                ↓
        (Wait 5-10 seconds)
                ↓
        $ (prompt returns)
                ↓
        SUCCESS! ✓
```

### Method 2: phpMyAdmin (Easy Visual)

```
┌───────────────────────────────────────┐
│ 1. Open: localhost/phpmyadmin         │
│ 2. Click on database:                 │
│    driver_license_school              │
└───────────────────────────────────────┘
                ↓
        You see empty database
                ↓
┌───────────────────────────────────────┐
│ 1. Click "Import" tab                 │
│ 2. Click "Choose File"                │
│ 3. Select:                            │
│    server/database/schema.sql         │
│ 4. Click "Open"                       │
│ 5. Click "Import" button              │
└───────────────────────────────────────┘
                ↓
        (Import progress bar appears)
                ↓
        (Wait for completion)
                ↓
        "Import has been successful"
                ↓
        SUCCESS! ✓
```

---

## Step 5: Verify Installation

### Check Tables Created

```
WINDOWS/MAC/LINUX:
┌──────────────────────────────────┐
│ Open Command Prompt/Terminal     │
└──────────────────────────────────┘
                ↓
┌──────────────────────────────────┐
│ Type:                            │
│                                  │
│ mysql -u root -p driver_license_school -e "SHOW TABLES;"
│                                  │
│ Press Enter                      │
│ Enter password (if Windows)      │
└──────────────────────────────────┘
                ↓
        Expected output:
        ┌──────────────────────────┐
        │ Tables_in_...            │
        │ certificates             │
        │ documents                │
        │ enrollments              │
        │ exams                    │
        │ instructor_details       │
        │ lessons                  │
        │ notifications            │
        │ student_details          │
        │ training_programs        │
        │ users                    │
        └──────────────────────────┘
                ↓
        Total: 10 tables ✓ SUCCESS!
```

### Check Pre-seeded Data

```
┌──────────────────────────────────┐
│ Type:                            │
│                                  │
│ mysql -u root -p driver_license_school -e "SELECT * FROM training_programs;"
│                                  │
│ Press Enter                      │
│ Enter password (if Windows)      │
└──────────────────────────────────┘
                ↓
        Expected output:
        ┌────────────────────────────────────────┐
        │ id | name                  | theory... │
        │ 1  | Level 1 - Motorcycle  | 20 ...   │
        │ 2  | Level 2 - Private Car | 25 ...   │
        │ 3  | Level 3 - Heavy Truck | 35 ...   │
        │ 4  | Level 4 - People...   | 30 ...   │
        │ 5  | Level 5 - Bus Driver  | 40 ...   │
        └────────────────────────────────────────┘
                ↓
        5 programs pre-seeded ✓ SUCCESS!
```

---

## Step 6: Configure Application

### Edit Database Configuration

```
WINDOWS:
┌──────────────────────────────────┐
│ 1. Open: server\config\db.php    │
│    (with Notepad or VS Code)    │
└──────────────────────────────────┘
                ↓
        Find these lines:
        ┌──────────────────────────┐
        │ $db_host = "localhost";  │
        │ $db_name = "driver...";  │
        │ $db_user = "root";       │
        │ $db_pass = "";           │
        └──────────────────────────┘
                ↓
        UPDATE if needed:
        ┌──────────────────────────┐
        │ $db_host = "localhost";  │
        │ $db_name = "driver_..."; │
        │ $db_user = "root";       │
        │ $db_pass = "root123";    │
        │      (your password)     │
        └──────────────────────────┘
                ↓
        Save (Ctrl + S)
                ↓
        SUCCESS! ✓
```

---

## Step 7: Test Connection

### Test with PHP

```
┌──────────────────────────────────┐
│ 1. Create file: test-db.php      │
│    in server/ directory          │
└──────────────────────────────────┘
                ↓
        Paste this code:
        ┌──────────────────────────┐
        │ <?php                    │
        │ $conn = new mysqli(      │
        │   "localhost",           │
        │   "root",                │
        │   "your_password",       │
        │   "driver_license_...");  │
        │                          │
        │ if ($conn->connect_...) │
        │   die("Failed");         │
        │                          │
        │ echo "✓ Connected!";     │
        │ echo "✓ Tables: ";       │
        │ $res = $conn->query...   │
        │ echo $row['count'];      │
        │ ?>                       │
        └──────────────────────────┘
                ↓
        Run:
        php test-db.php
                ↓
        Expected:
        ┌──────────────────────────┐
        │ ✓ Connected!             │
        │ ✓ Tables: 10             │
        └──────────────────────────┘
                ↓
        SUCCESS! ✓
```

---

## Step 8: Start Application

```
WINDOWS/MAC/LINUX:
┌──────────────────────────────────┐
│ 1. Open Command Prompt/Terminal  │
│ 2. Navigate: cd server/          │
│ 3. Type:                         │
│    php -S localhost:8000 -t .    │
│ 4. Press Enter                   │
└──────────────────────────────────┘
                ↓
        Expected:
        ┌──────────────────────────┐
        │ Listening on             │
        │ http://localhost:8000    │
        │                          │
        │ Press Ctrl-C to quit     │
        └──────────────────────────┘
                ↓
        Server running! ✓


┌──────────────────────────────────┐
│ 5. Open browser                  │
│ 6. Go to:                        │
│    http://localhost:8000         │
└──────────────────────────────────┘
                ↓
        Application loads ✓ SUCCESS!
```

---

## Final Checklist

```
✅ MySQL installed and running
✅ Database "driver_license_school" created
✅ Schema imported (10 tables)
✅ Training programs seeded (5 programs)
✅ Configuration file updated
✅ Database connection tested
✅ Application server started
✅ Browser can access application

🎉 COMPLETE! Ready to use the system.
```

---

## Next Steps

```
FIRST TIME SETUP:
┌────────────────────────────────────┐
│ 1. Go to: http://localhost:8000    │
│ 2. Click "Register"                │
│ 3. Create account as "MANAGER"     │
│ 4. Login                           │
│ 5. You're now administrator!       │
└────────────────────────────────────┘
                ↓
        ✅ Ready to manage the system
```

---

## Troubleshooting Visual

```
PROBLEM: "Can't connect to MySQL"
         ↓
   Is MySQL running?
   ├─→ Windows: Type "net start MySQL80"
   ├─→ Mac: Type "brew services start mysql"
   └─→ Linux: Type "sudo systemctl start mysql"
         ↓
   ✅ Try again


PROBLEM: "Access Denied for user root"
         ↓
   Is password correct?
   ├─→ Try without password first
   ├─→ Or use: mysql -u root -p (then enter password)
   └─→ If forgotten, see DATABASE_SETUP.md
         ↓
   ✅ Try again


PROBLEM: "Unknown database driver_license_school"
         ↓
   Did you create the database?
   ├─→ Verify: mysql -u root -p -e "SHOW DATABASES;"
   ├─→ If missing, create it using Step 3
   └─→ Then import schema using Step 4
         ↓
   ✅ Try again


PROBLEM: "ERROR at line X in SQL syntax"
         ↓
   Schema file may be corrupted
   ├─→ Verify schema.sql file exists and is readable
   ├─→ Try: mysql -u root -p driver_license_school < C:\full\path\schema.sql
   └─→ Use full file path, not relative
         ↓
   ✅ Try again
```

---

**All set! Your database is ready to use.** 🎉
