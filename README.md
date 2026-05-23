# Driver License School Management System

A comprehensive web-based platform designed to automate and manage the complete lifecycle of learner drivers in driving schools. This system handles student registration, lesson scheduling, performance tracking, exam management, and certification—specifically tailored for Ethiopian driving school operations.

![License](https://img.shields.io/badge/license-ISC-blue.svg)
![PHP](https://img.shields.io/badge/php-8.0+-green.svg)
![MySQL](https://img.shields.io/badge/mysql-8.0+-blue.svg)
![Status](https://img.shields.io/badge/status-production-brightgreen.svg)

---

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Project Structure](#project-structure)
- [User Roles & Workflows](#user-roles--workflows)
- [Database Schema](#database-schema)
- [API Documentation](#api-documentation)
- [Getting Started](#getting-started)
- [Development](#development)
- [License](#license)

---

## ✨ Features

### 🎓 Student Management

- **Registration & Profile Management**
  - Self-service registration with email verification
  - Personal information and document uploads
  - Progress tracking dashboard
  - Certification and graduation status

### 👨‍🏫 Training Management

- **Lesson Scheduling**
  - Theory and practical lesson scheduling
  - Instructor assignment and management
  - Attendance tracking
  - Performance evaluation and scoring

- **Progress Tracking**
  - Visual progress indicators
  - Milestone tracking
  - Performance history
  - Training completion status

### 📝 Exam Management

- **Exam Scheduling & Administration**
  - Theory and practical exam scheduling
  - Automated result recording
  - Pass/fail tracking
  - Exam approval workflow
  - Score management

### 🏆 Certification & Graduation

- **Certificate Management**
  - Automatic certificate generation
  - QR code verification system
  - Digital and printable certificates
  - Graduation approval workflow

### 📊 Reporting & Analytics

- **Comprehensive Reports**
  - Student enrollment statistics
  - Exam pass/fail rates
  - Instructor performance metrics
  - Financial/revenue tracking
  - Training completion analytics

### 🔐 Security & Access Control

- **Authentication & Authorization**
  - JWT token-based authentication
  - Role-based access control (RBAC)
  - Encrypted password storage
  - Session management

### 📬 Communication

- **Notification System**
  - Schedule notifications
  - Exam result notifications
  - Performance alerts
  - System announcements

---

## 🛠️ Tech Stack

### Backend

- **PHP 8.0+** - Server-side logic and API
- **MySQL 8.0+** - Relational database
- **JWT** - Token-based authentication
- **RESTful API** - Standardized API endpoints

### Frontend

- **HTML5** - Semantic markup
- **CSS3** - Responsive styling with dark mode
- **JavaScript (ES6+)** - Interactive features
- **Modern Browsers** - Chrome, Firefox, Edge, Safari

### Architecture

- **Modular API Design** - Organized by feature and role
- **MVC-inspired Structure** - Separation of concerns
- **Database Normalization** - Efficient data storage
- **CORS-enabled** - Cross-origin requests support

---

## 💻 System Requirements

### Server Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server with `.htaccess` support
- 256MB minimum RAM
- 100MB disk space minimum

### Client Requirements

- Modern web browser (Chrome, Firefox, Edge, Safari)
- JavaScript enabled
- Cookies enabled
- Responsive screen (desktop, tablet, mobile)

---

## 📦 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/Driver-License-School.git
cd Driver-License-School
```

### Step 2: Create Database

```sql
mysql -u root -p < server/database/schema.sql
```

For XAMPP and phpMyAdmin on Windows, do this instead:

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin` in your browser.
3. Click **Databases**.
4. Create a new database named `driver_license_school` with collation `utf8mb4_unicode_ci`.
5. Open the `driver_license_school` database.
6. Click **Import**.
7. Choose [server/database/schema.sql](server/database/schema.sql).
8. Click **Import** to load the tables and seed data.

Note: `schema.sql` already contains `CREATE DATABASE IF NOT EXISTS driver_license_school`, so if you import it directly in phpMyAdmin it can create the database for you as well.

### Step 3: Install Server Dependencies

```bash
cd server
npm install
```

### Step 4: Configure Environment Variables

Create a `.env` file in the `server` directory (or configure in `config/db.php`):

```env
DB_HOST=localhost
DB_NAME=driver_license_school
DB_USER=root
DB_PASS=your_password
JWT_SECRET=your_secret_key_here
```

### Step 5: Start the Application

**Option A: Using PHP built-in server**

```bash
cd server
npm start
# Server runs on http://localhost:8000
```

**Option B: Using Apache/Nginx**

- Point web root to project's `client` directory
- Ensure `server/api` is accessible

### Step 6: Access the Application

Open your browser and navigate to:

```
http://localhost:8000
```

---

## ⚙️ Configuration

### Database Configuration (`server/config/db.php`)

```php
$db_host = getenv('DB_HOST') ?: "localhost";
$db_name = getenv('DB_NAME') ?: "driver_license_school";
$db_user = getenv('DB_USER') ?: "root";
$db_pass = getenv('DB_PASS') ?: "";
```

### JWT Configuration (`server/config/jwt.php`)

Configure token expiration and secret key:

```php
define('JWT_SECRET', getenv('JWT_SECRET'));
define('JWT_EXPIRATION', 86400); // 24 hours
```

### CORS Configuration (`server/config/db.php`)

Headers are automatically configured for cross-origin requests:

```php
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
```

---

## 📁 Project Structure

```
Driver-License-School/
├── client/                          # Frontend Application
│   ├── assets/
│   │   ├── css/                    # Stylesheets (dark mode, responsive)
│   │   ├── js/                     # JavaScript modules
│   │   └── images/                 # Images and icons
│   ├── student-portal/             # Student UI
│   ├── instructor-portal/          # Instructor UI
│   ├── supervisor-portal/          # Supervisor UI
│   ├── manager-portal/             # Manager UI
│   ├── index.html                  # Landing page
│   ├── login.html                  # Login page
│   ├── register.html               # Registration page
│   └── [other pages]               # Additional pages
│
├── server/                          # Backend Application
│   ├── api/                        # REST API Endpoints
│   │   ├── auth/                   # Authentication endpoints
│   │   │   ├── login.php
│   │   │   └── register.php
│   │   ├── students/               # Student endpoints
│   │   │   ├── profile.php
│   │   │   ├── schedule.php
│   │   │   ├── progress.php
│   │   │   ├── exams.php
│   │   │   ├── certificate.php
│   │   │   └── upload.php
│   │   ├── instructors/            # Instructor endpoints
│   │   │   ├── dashboard.php
│   │   │   ├── students.php
│   │   │   ├── lessons.php
│   │   │   └── profile.php
│   │   ├── supervisor/             # Supervisor endpoints
│   │   │   ├── dashboard.php
│   │   │   ├── monitor.php
│   │   │   ├── assign.php
│   │   │   ├── exams.php
│   │   │   └── reports.php
│   │   ├── manager/                # Manager endpoints
│   │   │   ├── dashboard.php
│   │   │   ├── users/
│   │   │   ├── enrollment/
│   │   │   ├── exams/
│   │   │   ├── programs/
│   │   │   ├── certificates/
│   │   │   └── reports/
│   │   └── [other endpoints]
│   ├── config/                     # Configuration files
│   │   ├── db.php                  # Database connection
│   │   └── jwt.php                 # JWT authentication
│   ├── models/                     # Data models
│   │   └── User.php
│   ├── middleware/                 # Middleware
│   ├── database/
│   │   └── schema.sql              # Database schema
│   ├── package.json
│   └── .htaccess                   # Apache routing rules
│
├── scratch/                        # Temporary files
├── LICENSE                         # License file
└── README.md                       # This file
```

---

## 👥 User Roles & Workflows

### 1. **Student / Learner**

- **Permissions**: View own profile, attend lessons, take exams, view results
- **Workflow**:
  1. Register in system
  2. Wait for manager approval
  3. Attend assigned theory lessons
  4. Take theory exam
  5. Upon passing → attend practical lessons
  6. Take final practical exam
  7. Receive certificate upon graduation

**Portal**: `client/student-portal/`

### 2. **Instructor**

- **Permissions**: Manage assigned students, record lessons, evaluate performance, recommend exams
- **Responsibilities**:
  - Schedule and conduct lessons
  - Record attendance
  - Provide performance feedback
  - Recommend students for exams
  - Update progress status

**Portal**: `client/instructor-portal/`

### 3. **Supervisor**

- **Permissions**: Monitor activities, assign instructors, approve exam readiness, handle complaints
- **Responsibilities**:
  - Assign instructors to students
  - Monitor training quality
  - Review performance reports
  - Approve exam readiness
  - Handle complaints and issues
  - Adjust schedules as needed

**Portal**: `client/supervisor-portal/`

### 4. **Manager**

- **Permissions**: Full system access, user management, approvals, reporting
- **Responsibilities**:
  - Approve/reject student registrations
  - Create and manage training programs
  - Assign students to programs
  - Schedule exams
  - Approve final results
  - Issue certificates
  - Generate reports
  - Manage all user accounts

**Portal**: `client/manager-portal/`

### End-to-End Workflow Example

```
1. Student registers
         ↓
2. Manager approves registration & assigns program
         ↓
3. Supervisor assigns instructor
         ↓
4. Student attends theory classes (Instructor records attendance)
         ↓
5. Student takes theory exam
         ↓
6. Supervisor approves exam readiness → Manager approves result
         ↓
7. If PASSED → Student attends practical lessons
   If FAILED → Student retakes theory exam
         ↓
8. Instructor evaluates practical performance
         ↓
9. Student takes final practical exam
         ↓
10. Supervisor validates results → Manager approves final result
         ↓
11. Manager approves graduation
         ↓
12. System generates certificate
         ↓
13. Student receives & can verify certificate
```

---

## 🗄️ Database Schema

### Core Tables

#### `users`

Stores all user accounts with role-based access.

```
id, first_name, last_name, email, phone, password_hash,
role (student|instructor|supervisor|manager|admin),
status (pending|active|inactive|blocked), created_at, updated_at
```

#### `student_details`

Extended information for students.

```
id, user_id, national_id, date_of_birth, region, city, address,
license_class, experience_level, enrollment_status, created_at, updated_at
```

#### `training_programs`

Available training programs (vehicle types).

```
id, name, theory_hours, practical_hours, fee, created_by, created_at, updated_at
```

#### `enrollments`

Student enrollments in programs.

```
id, student_user_id, program_id, assigned_instructor_id, assigned_supervisor_id,
start_date, enrollment_date, status, progress_percentage, created_at, updated_at
```

#### `lessons`

Individual lesson records.

```
id, enrollment_id, instructor_id, session_date, lesson_type (theory|practical),
duration_minutes, attendance, performance_score, notes, created_at, updated_at
```

#### `exams`

Exam records for students.

```
id, student_user_id, exam_type (theory|practical), scheduled_date,
status (scheduled|passed|failed|cancelled), score, result_date, conducted_by,
approved, created_at, updated_at
```

#### `certificates`

Certificate/graduation records.

```
id, student_user_id, program_id, certificate_number, issue_date, issued_by, created_at
```

#### `notifications`

System notifications.

```
id, user_id, title, message, is_read, created_at
```

#### `documents`

Uploaded documents (ID, photos, etc.).

```
id, user_id, document_type, file_path, created_at
```

See `server/database/schema.sql` for complete schema with all relationships.

---

## 🔌 API Documentation

### Authentication Endpoints

**POST** `/api/auth/login.php`

```json
Request:
{
  "email": "user@example.com",
  "password": "password123"
}

Response:
{
  "success": true,
  "token": "eyJhbGc...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "role": "student"
  }
}
```

**POST** `/api/auth/register.php`

```json
Request:
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+251911234567",
  "password": "securePass123"
}

Response:
{
  "success": true,
  "message": "Registration successful",
  "user_id": 1
}
```

### Student Endpoints

**GET** `/api/students/profile.php`

- Get student profile information
- Requires: JWT token

**GET** `/api/students/schedule.php`

- Get assigned lessons
- Query params: `limit`, `offset`

**GET** `/api/students/progress.php`

- Get training progress
- Returns: progress percentage, completed modules

**POST** `/api/students/exams.php`

- Register for exam
- Requires: exam_type (theory|practical)

**GET** `/api/students/certificate.php`

- Get certificate information
- Returns: certificate details and download link

**POST** `/api/students/upload.php`

- Upload documents (ID, photos)
- Form data: `document_type`, `file`

### Instructor Endpoints

**GET** `/api/instructors/dashboard.php`

- Get instructor dashboard data

**GET** `/api/instructors/students.php`

- Get assigned students list

**POST** `/api/instructors/lessons.php`

- Create/update lesson records
- Requires: enrollment_id, session_date, lesson_type

**POST** `/api/evaluation.php`

- Evaluate student performance
- Requires: lesson_id, performance_score, notes

### Supervisor Endpoints

**GET** `/api/supervisor/dashboard.php`

- Get supervisor dashboard

**POST** `/api/supervisor/assign.php`

- Assign instructor to student
- Requires: enrollment_id, instructor_id

**GET** `/api/supervisor/monitor.php`

- Monitor instructor activities

**GET** `/api/supervisor/reports.php`

- Generate performance reports

### Manager Endpoints

**GET** `/api/manager/dashboard.php`

- Get manager dashboard with statistics

**POST** `/api/manager/users/updateStatus.php`

- Update user status
- Requires: user_id, status

**POST** `/api/manager/programs/create.php`

- Create training program
- Requires: name, theory_hours, practical_hours, fee

**POST** `/api/manager/enrollment/approveStudent.php`

- Approve student registration
- Requires: enrollment_id

**GET** `/api/manager/reports/students.php`

- Generate student enrollment report

**GET** `/api/manager/reports/exams.php`

- Generate exam statistics report

**POST** `/api/manager/certificates/issue.php`

- Issue certificate to student
- Requires: student_user_id, program_id

For complete API documentation, refer to individual endpoint files in `server/api/`.

---

## 🚀 Getting Started

### First-time Setup

1. **Create Admin Account** (optional):
   - First registered manager user becomes admin
   - Login with manager account to manage other users

2. **Create Training Programs**:
   - Navigate to Manager Portal
   - Create programs (Level 1-5 already pre-seeded)
   - Define theory hours, practical hours, and fees

3. **Register Instructors**:
   - Manager approves instructor registrations
   - Assign instructor details and license information

4. **Register Students**:
   - Students self-register
   - Manager approves and assigns to programs

5. **Start Training Process**:
   - Supervisor assigns instructors to students
   - Instructors schedule and conduct lessons
   - Begin tracking progress

### Testing the System

**Test Credentials** (after setup):

- Manager: manager@example.com / password
- Instructor: instructor@example.com / password
- Student: student@example.com / password
- Supervisor: supervisor@example.com / password

See application's login page for actual test accounts.

---

## 🔧 Development

### Running Development Server

```bash
cd server
npm start
```

Server runs on `http://localhost:8000`

### Project Development Structure

- **Frontend**: Located in `client/` directory
  - Static HTML files
  - CSS in `assets/css/`
  - JavaScript in `assets/js/`

- **Backend**: Located in `server/` directory
  - PHP API endpoints
  - Database configuration
  - Models and middleware

### Making API Calls

Frontend uses standardized API client in `client/assets/js/api.js`:

```javascript
// Example API call
const response = await api.get("/api/students/profile.php");
const data = await response.json();
```

### Authentication in Frontend

JWT token stored in localStorage:

```javascript
// Login
const token = await auth.login(email, password);

// Use in requests
const headers = {
  Authorization: `Bearer ${token}`,
};
```

### Database Migrations

To apply schema changes:

```bash
mysql -u root -p driver_license_school < server/database/schema.sql
```

---

## 📊 Key Features in Detail

### Dark Mode

Automatic theme detection with manual override available. Toggle in settings.

### Responsive Design

Mobile-first design adapts to all screen sizes:

- Mobile (< 768px)
- Tablet (768px - 1024px)
- Desktop (> 1024px)

### QR Code Certificate Verification

Certificates include embedded QR codes for verification:

```
QR Code → Verify Certificate → Check Authenticity
```

### Progress Tracking

Students can view:

- Overall progress percentage
- Completed lessons count
- Exam status
- Remaining requirements

### Performance Metrics

Instructors and supervisors can view:

- Student attendance rate
- Performance scores
- Progress trends
- Recommendation readiness

---

## 🐛 Troubleshooting

### Database Connection Error

```
Check credentials in server/config/db.php
Ensure MySQL server is running
Verify database exists: driver_license_school
```

### JWT Token Errors

```
Clear localStorage cookies
Re-login to generate new token
Check token expiration (default: 24 hours)
```

### API Not Responding

```
Ensure server is running (npm start)
Check CORS headers in server/config/db.php
Verify API endpoint paths
Check browser console for errors
```

### Frontend Not Loading

```
Clear browser cache
Disable extensions
Check JavaScript console for errors
Verify asset files exist in client/assets/
```

---

## 📄 License

This project is licensed under the ISC License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💼 Support & Contributing

### Getting Help

For issues, questions, or feature requests:

1. Check existing documentation
2. Review API endpoint files
3. Check browser console for errors
4. Review database schema for data structure

### Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

### Development Standards

- Follow PSR-12 for PHP code
- Use semantic HTML5
- Follow JavaScript conventions
- Write meaningful commit messages
- Comment complex logic

---

## 📞 Contact

**Project Name**: Driver License School Management System  
**Version**: 1.0.0  
**Status**: Production Ready

For inquiries or support, please refer to your project documentation or contact your development team.

---

## 🎯 Roadmap

### Future Enhancements

- [ ] Integration with national licensing authority
- [ ] Online theory exams with automated grading
- [ ] Mobile application (iOS/Android)
- [ ] GPS tracking for driving practice
- [ ] Payment integration (fees, billing)
- [ ] SMS notifications
- [ ] Email automation
- [ ] Advanced analytics dashboard
- [ ] Multi-branch support
- [ ] API rate limiting

---

**Last Updated**: May 2026  
**Maintained By**: Development Team

---

_This README covers the complete Driver License School Management System. For more detailed information, please refer to specific documentation files in the project directories._
"# -Driver-License-School-Management-System"
