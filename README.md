# Griffins' WellNest - Mental Health Support System

![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)
![Version](https://img.shields.io/badge/Version-1.0.0-blue)
![License](https://img.shields.io/badge/License-MIT-green)

**A comprehensive web-based mental health support system for schools and educational institutions, featuring therapeutic games, mental health assessments, and counselor dashboards.**

> Empowering students with mental health awareness and providing counselors with data-driven insights for better student support.

---

## 📋 Table of Contents

1. [Project Overview](#-project-overview)
2. [Key Features](#-key-features)
3. [Technology Stack](#-technology-stack)
4. [System Architecture](#-system-architecture)
5. [Installation Guide](#-installation-guide)
6. [Configuration](#-configuration)
7. [Running the Application](#-running-the-application)
8. [Project Structure](#-project-structure)
9. [User Roles & Features](#-user-roles--features)
10. [Database Schema](#-database-schema)
11. [API Documentation](#-api-documentation)
12. [Deployment Guide](#-deployment-guide)
13. [Security Considerations](#-security-considerations)
14. [Development Guide](#-development-guide)
15. [Troubleshooting](#-troubleshooting)
16. [Support & Contact](#-support--contact)

---

## 🎯 Project Overview

**Griffins' WellNest** is a mental health support system designed for educational institutions, enabling:

- **Students** to access mental health assessments, therapeutic games, and track their wellness journey
- **Counselors** to monitor student mental health trends, identify at-risk individuals, and provide targeted interventions
- **Administrators** to manage users, configure system settings, and oversee platform operations

### System Philosophy

The system follows a **holistic wellness approach** combining:
- Evidence-based mental health assessments
- Gamification for engagement and therapeutic benefit
- Data privacy and confidentiality
- Accessible, user-friendly interface
- Professional counselor tools for intervention

### Current Version
- **Version:** 1.0.0
- **Release Date:** March 20, 2026
- **Status:** Production Ready ✅

---

## ✨ Key Features

### 🎓 Student Features
- **Mental Health Assessments**
  - Stress, anxiety, depression evaluations
  - School experience assessments
  - Real-time risk level determination
  - Historical tracking of results
  - Privacy-protected responses

- **Therapeutic Games**
  - "Frogger Blocks Crossing" - Concentration & reflexes
  - Assessment prerequisite system (must complete assessment before playing)
  - Score tracking and leaderboards
  - Session history

- **Dashboard & Profile**
  - Personalized wellness dashboard
  - Assessment history with trend analysis
  - Game highscores
  - Mood check-in tracking
  - Notification center

- **Privacy & Security**
  - Confidential assessment responses
  - Counselor-only access to results
  - Session-based security
  - CSRF token protection

### 👨‍⚕️ Counselor Features
- **Student Management**
  - View all assigned students in sections
  - Filter by class/section
  - Quick risk level identification
  - Assessment history per student

- **Monitoring & Alerts**
  - High-risk student dashboard
  - Recent assessment overview
  - Risk level color-coding (Green/Yellow/Orange/Red)
  - Pending review tracking

- **Documentation**
  - Add counselor notes per student
  - Save treatment observations
  - Track intervention progress
  - Assessment review workflow

- **Data Export**
  - Generate reports
  - Track assessment trends
  - Identify patterns

### 🔐 Admin Features
- **User Management**
  - Add/edit/delete users
  - Assign roles (Student, Counselor, Admin)
  - Manage active/inactive status
  - Password reset functionality
  - Audit trail of user changes

- **System Configuration**
  - Application settings
  - Assessment configuration
  - Maintenance mode
  - Cache management
  - System information

- **Audit & Monitoring**
  - Complete audit log of all actions
  - User activity tracking
  - System health monitoring
  - Error logging

---

## 🛠 Technology Stack

### Backend
| Technology | Purpose | Version |
|-----------|---------|---------|
| **PHP** | Server-side logic | 7.4+ |
| **PDO** | Database abstraction | Built-in |
| **MySQL** | Relational database | 5.7+ |

### Frontend
| Technology | Purpose | Version |
|-----------|---------|---------|
| **HTML5** | Semantic markup | Latest |
| **CSS3** | Styling | Latest |
| **Tailwind CSS** | Utility-first CSS framework | 3.4.19 |
| **JavaScript** | Client-side interactivity | Vanilla (ES6+) |

### Development Tools
| Tool | Purpose | Version |
|------|---------|---------|
| **Node.js** | Build tooling | 14+ |
| **npm** | Package management | Latest |
| **PostCSS** | CSS processing | 8.5.8 |
| **Autoprefixer** | CSS vendor prefixes | 10.4.27 |

### Deployment
| Component | Purpose |
|-----------|---------|
| **Apache** | Web server |
| **XAMPP** | Local development (includes Apache, MySQL, PHP) |
| **InfinityFree** | Production hosting (future) |

---

## 🏗 System Architecture

### Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                     CLIENT LAYER (Browser)                       │
│  HTML5 | CSS3 (Tailwind) | JavaScript (Vanilla ES6+)            │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      │ HTTP Requests
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│              APPLICATION LAYER (PHP Web Server)                  │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐│
│  │  Views Layer     │  │  API Router      │  │  Auth Handler    ││
│  │ (45+ templates)  │  │  (src/api.php)   │  │  (src/auth.php)  ││
│  └──────────────────┘  └──────────────────┘  └──────────────────┘│
│          │                    │                      │            │
│          └────────────┬───────┴──────────────────────┘            │
│                       │                                            │
│  ┌──────────────────────────────────────────────────────────────┐│
│  │         Business Logic Layer (Helper Functions)             ││
│  │  • Session Management          • CSRF Protection            ││
│  │  • Input Validation            • Error Handling             ││
│  │  • Password Hashing (bcrypt)   • Logging                    ││
│  │  • Assessment Scoring          • Risk Determination         ││
│  └──────────────────────────────────────────────────────────────┘│
│                       │                                            │
│                       ▼                                            │
│  ┌──────────────────────────────────────────────────────────────┐│
│  │         Database Access Layer (PDO Database Class)          ││
│  │  • Prepared Statements        • SQL Injection Prevention     ││
│  │  • Connection Pooling (Singleton)    • Transaction Support  ││
│  └──────────────────────────────────────────────────────────────┘│
└────────┬─────────────────────────────────────────────────┬────────┘
         │                                                  │
         ▼ (Queries)                                  (Queries) ▼
┌──────────────────────────────────────────────────────────────────┐
│                    DATA LAYER (MySQL Database)                    │
├──────────────────────────────────────────────────────────────────┤
│  Users | Roles | Sections | Assessments | Assessment_Scores    │
│  Assessment_Answers | Therapeutic_Games | Game_Sessions         │
│  Audit_Logs | (9 tables total)                                  │
└──────────────────────────────────────────────────────────────────┘
```

### Component Breakdown

**1. Presentation Layer (Views)**
- 45+ server-side PHP templates
- Tailwind CSS for responsive design
- Vanilla JavaScript for interactivity
- Real-time form validation
- Dynamic content rendering

**2. Business Logic Layer**
- Authentication & Authorization (Role-Based Access Control)
- Session Management with security hardening
- CSRF Token Generation & Validation
- Input Validation & Sanitization
- Password Hashing (bcrypt with 12 rounds)
- Assessment Scoring & Risk Calculation
- Audit Logging

**3. API Layer**
- RESTful JSON API (`src/api.php`)
- Endpoints for assessments, games, counselor data
- CORS support for future mobile app
- Structured JSON responses

**4. Database Layer**
- PDO (PHP Data Objects) abstraction
- Singleton pattern for connection pooling
- Prepared statements for SQL injection prevention
- Transaction support for data integrity
- 9 normalized tables with proper relationships

---

## 📥 Installation Guide

### Prerequisites

Before installing Griffins' WellNest, ensure you have:

- **XAMPP 7.4+** (includes PHP 7.4+, Apache 2.4+, MySQL 5.7+)
  - Download: https://www.apachefriends.org/
- **Node.js 14+** (for build tools)
  - Download: https://nodejs.org/
- **Git** (for version control)
  - Download: https://git-scm.com/
- **Text Editor** (VS Code recommended)
  - Download: https://code.visualstudio.com/

### Step 1: Start XAMPP

1. Open XAMPP Control Panel
2. Start **Apache** server
3. Start **MySQL** database
4. Verify both show green indicators

### Step 2: Clone/Download Project

```bash
# Using Git
cd C:\xampp\htdocs
git clone https://github.com/HaruCy16/Griffins_Wellnest_Sim_Mental_Health_Web_Based_System.git
cd Wellnest_Sim_Web_Application

# OR manually download ZIP and extract to:
# C:\xampp\htdocs\Wellnest_Sim_Web_Application
```

### Step 3: Install Node Dependencies

```bash
# Navigate to project directory
cd C:\xampp\htdocs\Wellnest_Sim_Web_Application

# Install npm packages (Tailwind CSS, PostCSS, Autoprefixer)
npm install
```

### Step 4: Create Database

```bash
# Open phpMyAdmin
# URL: http://localhost/phpmyadmin

# Option A: Import SQL schema
1. Click "Import" tab
2. Select: database/schema.sql
3. Click "Go"

# Option B: Manual creation via Query
1. Click "SQL" tab
2. Copy contents from database/schema.sql
3. Paste and execute
```

### Step 5: Compile CSS (If needed)

```bash
# Build Tailwind CSS (already compiled in output.css)
npm run build

# OR watch for changes during development
npm run dev
```

### Step 6: Verify Installation

1. Open browser: `http://localhost/Wellnest_Sim_Web_Application/`
2. You should see the Griffins' WellNest landing page
3. Application is ready to use!

---

## ⚙️ Configuration

### Application Configuration

**File:** `config/settings.php`

```php
// Environment
define('APP_ENV', 'development'); // 'development', 'staging', 'production'
define('APP_DEBUG', APP_ENV === 'development');

// Application Info
define('APP_NAME', "Griffins' WellNest");
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Wellnest_Sim_Web_Application');

// Timezone
date_default_timezone_set('Asia/Manila');

// Session
define('SESSION_LIFETIME', 3600); // 1 hour (in seconds)
```

### Database Configuration

**File:** `config/database.php`

```php
private const HOST = 'localhost';
private const DB_NAME = 'griffin_wellnest_db';
private const USERNAME = 'root';
private const PASSWORD = ''; // XAMPP default (empty)
private const CHARSET = 'utf8mb4';
```

### Constants Configuration

**File:** `config/constants.php`

```php
// Role IDs
define('ROLE_STUDENT', 1);
define('ROLE_COUNSELOR', 2);
define('ROLE_ADMIN', 3);

// Assessment Types
define('ASSESSMENT_SCHOOL_EXPERIENCE', 'school_experience');
define('ASSESSMENT_MENTAL_HEALTH', 'mental_health');
define('ASSESSMENT_HELP_SEEKING', 'help_seeking');

// Risk Levels
define('RISK_LOW', 'low');
define('RISK_MEDIUM', 'medium');
define('RISK_HIGH', 'high');
define('RISK_CRITICAL', 'critical');
```

### Environment-Specific Settings

**Development:**
```php
define('APP_ENV', 'development');
define('APP_DEBUG', true); // Show errors
define('APP_URL', 'http://localhost/Wellnest_Sim_Web_Application');
```

**Production (InfinityFree):**
```php
define('APP_ENV', 'production');
define('APP_DEBUG', false); // Hide errors
define('APP_URL', 'https://yourdomain.infinityfree.com');
```

---

## 🚀 Running the Application

### Local Development

1. **Start Services:**
   ```bash
   # XAMPP Control Panel
   - Start Apache
   - Start MySQL
   ```

2. **Run Application:**
   ```
   URL: http://localhost/Wellnest_Sim_Web_Application/
   ```

3. **Watch CSS Changes (Optional):**
   ```bash
   npm run dev
   ```

### Test Credentials

**Student Account**
```
Email: student@wellnest.edu
Password: Test@123456 (or use registration)
```

**Counselor Account**
```
Email: counselor@wellnest.edu
Password: TestCounselor@123
```

**Admin Account**
```
Email: admin@wellnest.edu
Password: TestAdmin@123
```

### Accessing Different Dashboards

| Role | URL | Features |
|------|-----|----------|
| **Student** | `/views/student/dashboard.php` | Assessments, Games, Profile |
| **Counselor** | `/views/counselor/dashboard.php` | Student Management, Reports |
| **Admin** | `/views/admin/dashboard.php` | User Management, Settings |

---

## 📁 Project Structure

```
Wellnest_Sim_Web_Application/
│
├── 📄 README.md                          Main documentation (this file)
├── 📄 index.php                          Landing/entry point
├── 📄 package.json                       npm dependencies config
├── 📄 tailwind.config.js                 Tailwind CSS configuration
├── 📄 postcss.config.js                  PostCSS configuration
│
├── 📁 config/                            Application configuration
│   ├── settings.php                      Environment & app settings
│   ├── database.php                      PDO Database class (Singleton)
│   └── constants.php                     Role & assessment constants
│
├── 📁 src/                               Core application logic
│   ├── functions.php                     Helper functions (300+ lines)
│   │   ├── Session management
│   │   ├── CSRF protection
│   │   ├── Authentication utilities
│   │   ├── Error handling
│   │   └── Logging functions
│   │
│   ├── auth.php                          Authentication handler
│   │   ├── handleLogin()
│   │   ├── handleRegister()
│   │   └── handleLogout()
│   │
│   ├── validators.php                    Input validation
│   │   ├── validateLogin()
│   │   ├── validateRegistration()
│   │   ├── validateEmail()
│   │   └── validatePassword()
│   │
│   ├── api.php                           API router & endpoints
│   │   ├── Authentication APIs
│   │   ├── Assessment APIs
│   │   ├── Counselor APIs
│   │   └── Game APIs
│   │
│   └── ai-recommendations.php            AI recommendation engine
│       └── generateRecommendations()
│
├── 📁 views/                             Presentation layer (45+ templates)
│   │
│   ├── 📁 layouts/
│   │   └── header.php                    Navigation bar (role-based)
│   │
│   ├── 📁 student/                       Student-only views
│   │   ├── dashboard.php                 Main student hub (2-column grid)
│   │   ├── assessments.php               Available assessments list
│   │   ├── assessment.php                Assessment questionnaire
│   │   ├── assessment-results.php        Results & recommendations
│   │   ├── profile.php                   Student profile
│   │   ├── notifications.php             Message center
│   │   └── 📁 games/
│   │       └── frogger.php               Frogger game implementation
│   │
│   ├── 📁 counselor/                     Counselor-only views
│   │   ├── dashboard.php                 Counselor overview
│   │   │   ├── Stats cards
│   │   │   ├── Quick action cards
│   │   │   ├── High-risk students section
│   │   │   └── Recent assessments table
│   │   │
│   │   └── students.php                  Student management
│   │       ├── Student list (with filters)
│   │       ├── Student detail view
│   │       ├── Assessment history
│   │       └── Counselor notes
│   │
│   ├── 📁 admin/                         Admin-only views
│   │   ├── dashboard.php                 Admin overview
│   │   │   ├── User statistics
│   │   │   ├── Quick actions
│   │   │   └── Audit log
│   │   │
│   │   ├── users.php                     User management (CRUD)
│   │   │   ├── User listing
│   │   │   ├── Add user modal
│   │   │   ├── Edit user form
│   │   │   └── Delete functionality
│   │   │
│   │   └── settings.php                  System configuration
│   │       ├── App settings
│   │       ├── Assessment config
│   │       └── System actions
│   │
│   ├── login.php                         Login form
│   └── register.php                      Registration form
│
├── 📁 database/
│   └── schema.sql                        Complete database schema (9 tables)
│
├── 📁 css/
│   ├── input.css                         Tailwind directives
│   └── output.css                        Compiled CSS (production-ready)
│
├── 📁 javascript/
│   └── (custom JS files as needed)       Client-side logic
│
├── 📁 assets/
│   ├── logo_griffin.png                  Application logo
│   └── (images, icons, etc.)
│
├── 📁 documentation/
│   ├── TESTING_REPORT_AND_DOCUMENTATION.md
│   ├── FROGGER_GAME_README.md
│   ├── FROGGER_TESTING_GUIDE.md
│   ├── FROGGER_SETUP_COMPLETE.md
│   └── API_JSON_Bodies.md
│
├── 📁 logs/
│   └── error.log                         Error log file (auto-created)
│
└── 📁 node_modules/                      npm packages (Tailwind, PostCSS, etc.)
```

---

## 👥 User Roles & Features

### 1. Student Role (ID: 1)

**Dashboard Features:**
- Quick start to games and assessments
- Mood check-in tracking
- 2-column grid layout (Games left, Assessments right)
- Recent activity summary

**Assessment Features:**
- View available assessments
- Complete self-assessments
- View historical results
- See risk level trends
- Privacy-protected responses

**Game Features:**
- Must complete assessment before playing
- Frogger Blocks Crossing game
- Score tracking and highscores
- Game session history

**Profile & Notifications:**
- View personal profile
- Edit profile information
- Receive counselor messages
- Notification center

### 2. Counselor Role (ID: 2)

**Dashboard Features:**
- Statistics overview (total students, high-risk count, pending reviews)
- Quick access cards for high-risk students and assessments
- High-risk students section with color-coding
- Recent assessments table

**Student Management:**
- View all assigned students
- Filter by section/class
- View individual student profiles
- Quick access to assessment history

**Assessment Tracking:**
- View student assessment results
- Track risk level changes
- See assessment attempt history
- Review assessment responses

**Documentation:**
- Add counselor notes per student
- Save intervention observations
- Track progress over time
- Document consultation history

**Reporting:**
- Generate assessment reports
- Track trends by section
- Identify at-risk patterns
- Export data for analysis

### 3. Admin Role (ID: 3)

**User Management:**
- Add new users (students, counselors, admins)
- Edit user information
- Toggle user active/inactive status
- Delete users (soft delete)
- Reset user passwords
- Bulk user operations

**System Settings:**
- Configure application settings (APP_NAME, support email)
- Assessment configuration
- Toggle maintenance mode
- Clear application cache
- View system information

**Audit & Monitoring:**
- View complete audit log of all actions
- Track user activities
- Monitor user login history
- System health monitoring
- Error log review

**Reporting:**
- User statistics dashboard
- User role distribution
- Activity reports
- System usage analytics

---

## 🗄 Database Schema

### Database Overview
- **Database Name:** `griffin_wellnest_db`
- **Total Tables:** 9
- **Character Set:** UTF-8 MB4 (supports emojis and special characters)
- **Engine:** InnoDB (ACID transactions support)

### Table Relationships

```
roles (1) ←→ (N) users
     │
     └─→ (Students/Counselors)

users (1) ←→ (N) sections
     │
     ├─→ (N) assessment_scores
     ├─→ (N) game_sessions
     └─→ (N) audit_logs

assessments (1) ←→ (N) assessment_scores
     │
     └─→ (N) assessment_answers

assessment_scores (1) ←→ (N) assessment_answers

therapeutic_games (1) ←→ (N) game_sessions
```

### Table Structures

**1. roles** - Role definitions
```
role_id (PK)
role_name (UNIQUE)         → 'student', 'counselor', 'admin'
description
permissions (JSON)
created_at (TIMESTAMP)
```

**2. users** - User accounts
```
user_id (PK)
email (UNIQUE)
password_hash              → bcrypt hashed
first_name
last_name
role_id (FK → roles)       → 1=student, 2=counselor, 3=admin
section_id (FK → sections) → for students
student_id (UNIQUE)        → school ID
is_active (BOOLEAN)
last_login (TIMESTAMP)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)     → soft delete support
```

**3. sections** - Student sections/classes
```
section_id (PK)
section_name (UNIQUE)      → 'Grade 7-A', 'Grade 10-B'
grade_level
academic_year              → '2024-2025'
section_head_id (FK → users) → counselor assigned
total_students
is_active (BOOLEAN)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

**4. assessments** - Assessment definitions
```
assessment_id (PK)
assessment_name (UNIQUE)
description
assessment_type            → 'stress', 'anxiety', 'depression', etc.
total_questions
estimated_time             → minutes
instructions
is_active (BOOLEAN)        → can students take it?
version
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

**5. assessment_scores** - Assessment results
```
assessment_score_id (PK)
user_id (FK → users)
assessment_id (FK → assessments)
total_score
percentage_score           → GENERATED (calculated)
risk_level                 → 'low', 'medium', 'high', 'critical'
completed_at (TIMESTAMP)
reviewed_by (FK → users)   → counselor who reviewed
counselor_notes (TEXT)
created_at (TIMESTAMP)
```

**6. assessment_answers** - Individual question responses
```
answer_id (PK)
assessment_score_id (FK → assessment_scores)
question_number
response_value             → answer/score
response_text              → descriptive answer
created_at (TIMESTAMP)
```

**7. therapeutic_games** - Game definitions
```
game_id (PK)
game_name                  → 'Frogger Blocks Crossing'
game_slug                  → 'frogger'
description
game_type                  → 'therapeutic', 'educational'
icon_url
is_active (BOOLEAN)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

**8. game_sessions** - Game play history
```
session_id (PK)
user_id (FK → users)
game_id (FK → therapeutic_games)
score
time_played                → seconds
is_completed (BOOLEAN)
started_at (TIMESTAMP)
ended_at (TIMESTAMP)
created_at (TIMESTAMP)
```

**9. audit_logs** - Admin action tracking
```
log_id (PK)
user_id (FK → users)       → who performed the action
action_type                → 'CREATE', 'UPDATE', 'DELETE'
entity_type                → 'USER', 'ASSESSMENT', etc.
entity_id
old_values (JSON)          → before changes
new_values (JSON)          → after changes
description (TEXT)
ip_address
user_agent
created_at (TIMESTAMP)
```

### Database Queries

**Get Student Assessment History:**
```sql
SELECT a.assessment_name, 
       ascore.total_score, 
       ascore.percentage_score,
       ascore.risk_level,
       ascore.completed_at
FROM assessment_scores ascore
JOIN assessments a ON ascore.assessment_id = a.assessment_id
WHERE ascore.user_id = ?
ORDER BY ascore.completed_at DESC;
```

**Get Counselor's High-Risk Students:**
```sql
SELECT DISTINCT u.user_id, 
       u.first_name, 
       u.last_name,
       ascore.risk_level,
       MAX(ascore.completed_at) as last_assessment
FROM users u
JOIN assessment_scores ascore ON u.user_id = ascore.user_id
WHERE u.section_id IN (
    SELECT section_id FROM sections WHERE section_head_id = ?
)
AND ascore.risk_level IN ('high', 'critical')
GROUP BY u.user_id
ORDER BY ascore.completed_at DESC;
```

---

## 🔌 API Documentation

### API Base URL
```
http://localhost/Wellnest_Sim_Web_Application/src/api.php
```

### API Response Format
```json
{
  "success": true,
  "message": "Description of result",
  "data": { /* response data */ },
  "timestamp": "2026-03-20T10:30:45Z"
}
```

### Authentication Endpoints

**1. Login**
```
POST /src/api.php?action=login
Content-Type: application/json

{
  "email": "student@wellnest.edu",
  "password": "Test@123456"
}

Response:
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user_id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "role": "student"
  }
}
```

**2. Logout**
```
POST /src/api.php?action=logout
Response: { "success": true, "message": "Logged out successfully" }
```

### Assessment Endpoints

**3. Get Assessments**
```
GET /src/api.php?action=get_assessments

Response:
{
  "success": true,
  "data": [
    {
      "assessment_id": 1,
      "assessment_name": "Stress Assessment",
      "assessment_type": "stress",
      "total_questions": 20,
      "estimated_time": 10
    }
  ]
}
```

**4. Get Assessment Questions**
```
GET /src/api.php?action=get_assessment_questions&assessment_id=1

Response:
{
  "success": true,
  "data": {
    "assessment_id": 1,
    "assessment_name": "Stress Assessment",
    "questions": [
      {
        "question_id": 1,
        "question_text": "How often do you feel stressed?",
        "question_type": "scale",
        "options": ["Never", "Rarely", "Sometimes", "Often", "Always"]
      }
    ]
  }
}
```

**5. Submit Assessment Response**
```
POST /src/api.php?action=submit_response
Content-Type: application/json

{
  "assessment_score_id": 1,
  "question_number": 1,
  "response_value": 3
}

Response: { "success": true, "message": "Response saved" }
```

**6. Complete Assessment**
```
POST /src/api.php?action=complete_assessment
Content-Type: application/json

{
  "assessment_id": 1,
  "responses": [
    { "question_number": 1, "response_value": 3 },
    { "question_number": 2, "response_value": 2 }
  ]
}

Response:
{
  "success": true,
  "data": {
    "total_score": 45,
    "percentage_score": 75,
    "risk_level": "high",
    "recommendations": ["Seek counselor support", ...]
  }
}
```

### Counselor Endpoints

**7. Get Section Students**
```
GET /src/api.php?action=get_section_students&section_id=1

Response:
{
  "success": true,
  "data": [
    {
      "user_id": 10,
      "first_name": "Jane",
      "last_name": "Smith",
      "email": "jane@school.edu",
      "latest_risk": "medium",
      "assessment_count": 3
    }
  ]
}
```

**8. Get Student Details**
```
GET /src/api.php?action=get_student_details&student_id=10

Response:
{
  "success": true,
  "data": {
    "user_id": 10,
    "first_name": "Jane",
    "email": "jane@school.edu",
    "section": "Grade 8-A",
    "assessment_history": [
      {
        "assessment_name": "Stress Assessment",
        "score": 65,
        "risk_level": "medium",
        "completed_at": "2026-03-20"
      }
    ]
  }
}
```

### Game Endpoints

**9. Get Games**
```
GET /src/api.php?action=get_games

Response:
{
  "success": true,
  "data": [
    {
      "game_id": 1,
      "game_name": "Frogger Blocks Crossing",
      "game_slug": "frogger",
      "icon_url": "/assets/frogger-icon.png"
    }
  ]
}
```

**10. Save Game Progress**
```
POST /src/api.php?action=save_game_progress
Content-Type: application/json

{
  "game_id": 1,
  "score": 1500,
  "time_played": 120,
  "is_completed": true
}

Response: { "success": true, "message": "Game progress saved" }
```

---

## 🚀 Deployment Guide

### Local Development (XAMPP)

See **Running the Application** section above.

### InfinityFree Deployment (Future)

InfinityFree is a free hosting service suitable for educational projects. Here's how to deploy Griffins' WellNest:

#### Step 1: Create InfinityFree Account
1. Visit https://www.infinityfree.net/
2. Create free account
3. Activate account via email

#### Step 2: Setup Hosting
1. In cpanel, create new website
2. Note your FTP credentials
3. Note MySQL database credentials
4. Note your domain/subdomain

#### Step 3: Connect via FTP
```bash
# Using FileZilla or similar FTP client
FTP Host: ftp.yourdomain.com
Username: (from control panel)
Password: (from control panel)
Port: 21
```

#### Step 4: Upload Application Files
1. Download project as ZIP
2. Extract locally
3. Connect via FTP
4. Upload all files to public_html/
5. Create logs/ directory (chmod 755)

#### Step 5: Configure Database
```bash
# SSH or cpanel terminal
1. Create MySQL database
2. Create MySQL user
3. Grant privileges to user
4. Note credentials
```

#### Step 6: Update Configuration
Edit `config/database.php`:
```php
private const HOST = 'localhost'; // InfinityFree is localhost
private const DB_NAME = 'your_db_name';
private const USERNAME = 'your_db_user';
private const PASSWORD = 'your_db_pass';
```

Edit `config/settings.php`:
```php
define('APP_ENV', 'production');
define('APP_URL', 'https://yourdomain.infinityfree.com');
```

#### Step 7: Import Database
1. In cpanel, open phpMyAdmin
2. Select your database
3. Import database/schema.sql
4. Verify tables created

#### Step 8: Verify Installation
1. Visit https://yourdomain.infinityfree.com
2. Test login with admin credentials
3. Verify all features work

#### InfinityFree Considerations
- **Free SSL:** InfinityFree provides free SSL/TLS
- **Database:** MySQL 5.7+ available
- **PHP:** PHP 7.4+ usually available
- **Storage:** Limited storage (use wisely)
- **Bandwidth:** Limited bandwidth
- **Email:** Better to use external SMTP
- **Performance:** May be slower than paid hosts

#### Production Checklist
- [ ] Set `APP_ENV` to 'production'
- [ ] Set `APP_DEBUG` to false
- [ ] Enable HTTPS/SSL
- [ ] Set strong admin passwords
- [ ] Configure email settings
- [ ] Setup error logging
- [ ] Backup database regularly
- [ ] Monitor disk usage
- [ ] Monitor bandwidth usage

### Custom VPS/Dedicated Server

For production deployments on VPS:

**Requirements:**
- Ubuntu 20.04+ OS
- Nginx or Apache web server
- PHP 7.4+ with extensions (PDO, JSON, Sessions)
- MySQL 5.7+ or MariaDB
- SSL certificate (Let's Encrypt)
- Git for deployments
- Composer for package management

**Deployment Process:**
```bash
# 1. Connect to VPS
ssh root@yourvps.com

# 2. Install dependencies
apt update && apt upgrade
apt install -y php7.4 php7.4-mysql php7.4-fpm nginx mysql-server

# 3. Clone project
cd /var/www
git clone https://github.com/HaruCy16/...

# 4. Setup permissions
chown -R www-data:www-data /var/www/wellnest
chmod -R 755 /var/www/wellnest
chmod -R 777 /var/www/wellnest/logs

# 5. Configure web server
# (Create nginx config file)

# 6. Setup SSL
# (Use Let's Encrypt certbot)

# 7. Create database
mysql -u root -p < database/schema.sql

# 8. Start services
systemctl restart nginx
systemctl restart php7.4-fpm
systemctl restart mysql
```

---

## 🔐 Security Considerations

### 1. Authentication Security ✅

**Password Hashing:**
- bcrypt hashing with 12 rounds (industry standard)
- Passwords never stored in plain text
- Salt generated automatically

```php
$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
```

**Session Security:**
- Session ID regeneration on login
- HTTPOnly cookies (JavaScript cannot access)
- Secure flag on HTTPS (production)
- SameSite attribute to prevent CSRF
- Session timeout (1 hour default)

### 2. Authorization (RBAC) ✅

**Role-Based Access Control:**
- 3 user roles (Student, Counselor, Admin)
- Page-level authorization checks
- Function: `requireRole(ROLE_CONSTANT)`
- Unauthorized access redirected to login

```php
requireRole(ROLE_COUNSELOR); // Only counselors can access
```

### 3. CSRF Protection ✅

**Cross-Site Request Forgery Prevention:**
- Unique token per session
- Token regenerates after 30 minutes
- Token validated on all POST/PUT/DELETE requests
- Function: `verifyCsrfToken()`

```php
if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    die('CSRF token invalid');
}
```

### 4. SQL Injection Prevention ✅

**Prepared Statements:**
- All queries use PDO prepared statements
- Parameters are parameterized
- Never concatenate user input into queries

```php
// Good (prepared statement)
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// Dangerous (never do this)
$query = "SELECT * FROM users WHERE email = '$email'"; // NEVER!
```

### 5. XSS (Cross-Site Scripting) Prevention ✅

**Output Escaping:**
- All output escaped with `e()` function
- Uses `htmlspecialchars()` with ENT_QUOTES
- Prevents malicious JavaScript execution

```php
// Safe
<?= e($user['first_name']) ?>

// Dangerous (never do this)
<?= $user['first_name'] ?> <!-- NEVER! -->
```

### 6. Data Privacy ✅

**Confidentiality:**
- Assessment responses only visible to counselors
- Students cannot see each other's data
- Soft delete preserves data for audits

**Data Protection:**
- No sensitive data in logs (passwords, API keys)
- Audit trail of all data modifications
- Encrypted session storage

### 7. Environment Security

**Production Checklist:**
- [ ] APP_ENV set to 'production'
- [ ] APP_DEBUG set to false
- [ ] Error messages don't expose system details
- [ ] Database credentials not in version control
- [ ] Use environment variables for secrets
- [ ] Enable HTTPS/SSL
- [ ] Restrict admin access (IP whitelisting)
- [ ] Regular security updates (PHP, MySQL, OS)
- [ ] File permissions (logs directory writable)
- [ ] Disable directory listing
- [ ] Disable file upload access (if applicable)

### 8. Regular Security Maintenance

**Recommended Actions:**
- Keep PHP/MySQL updated
- Monitor audit logs regularly
- Review user access logs
- Backup database daily
- Test security with vulnerability scanners
- Conduct code reviews
- Implement rate limiting (prevent brute force)
- Use strong admin passwords (20+ characters)

---

## 👨‍💻 Development Guide

### Setting Up Development Environment

**1. Clone Repository:**
```bash
git clone https://github.com/HaruCy16/Griffins_Wellnest_Sim_Mental_Health_Web_Based_System.git
cd Wellnest_Sim_Web_Application
```

**2. Install Dependencies:**
```bash
npm install
```

**3. Start Services:**
- XAMPP: Apache + MySQL
- CSS Watcher: `npm run dev`

### Code Structure

**PHP Files Organization:**
```
src/
├── functions.php       # Utility functions (300+ lines)
├── auth.php           # Authentication logic
├── validators.php     # Input validation
├── api.php            # REST API endpoints
└── ai-recommendations.php  # AI recommendation engine
```

### Coding Standards

**1. Naming Conventions:**
```php
// Functions: camelCase
function validateUserInput() {}

// Classes: PascalCase
class Database {}

// Constants: UPPER_SNAKE_CASE
define('ROLE_STUDENT', 1);

// Variables: snake_case
$user_id = 1;
```

**2. PHP Style:**
```php
// Classes/Namespaces
use function validateEmail;

// Type hints
function getUserById(int $id): ?array {}

// Return types
public function getName(): string {}

// Null coalescing
$email = $_POST['email'] ?? null;
```

**3. Database Query Pattern:**
```php
try {
    $user = Database::fetchOne(
        "SELECT * FROM users WHERE email = ? AND is_active = TRUE",
        [$email]
    );
} catch (Exception $e) {
    logError('Database error', ['error' => $e->getMessage()]);
    // Handle error gracefully
}
```

**4. Form Validation:**
```php
// Validate input
$validation = validateLogin(['email' => $email, 'password' => $password]);
if (!$validation->isValid) {
    $_SESSION['errors'] = $validation->getAllErrors();
    redirect('/login.php');
}
```

### Git Workflow

**1. Create Feature Branch:**
```bash
git checkout -b feature/student-dashboard-redesign
```

**2. Make Changes:**
```bash
git add .
git commit -m "feat: redesign student dashboard with grid layout"
```

**3. Push Branch:**
```bash
git push origin feature/student-dashboard-redesign
```

**4. Create Pull Request:**
- Push to GitHub
- Open PR for code review
- Address feedback
- Merge when approved

### Testing

**PHP Syntax Validation:**
```bash
php -l src/functions.php
php -l views/student/dashboard.php
```

**Manual Testing Checklist:**
- [ ] Login works for all roles
- [ ] All dashboards load correctly
- [ ] Assessments complete successfully
- [ ] Game launches properly
- [ ] Admin CRUD operations work
- [ ] Forms validate input correctly
- [ ] Privacy notices display
- [ ] Responsive design works on mobile

### Building Release

**1. Compile CSS:**
```bash
npm run build
```

**2. Update Version:**
```php
// config/settings.php
define('APP_VERSION', '1.1.0');
```

**3. Create Release Branch:**
```bash
git checkout -b release/v1.1.0
git tag -a v1.1.0 -m "Release version 1.1.0"
git push origin release/v1.1.0 --tags
```

---

## 🔧 Troubleshooting

### Common Issues

**1. "Database connection failed"**
```
Issue: Cannot connect to MySQL database

Solutions:
- Verify XAMPP MySQL is running
- Check hostname (usually 'localhost')
- Verify database name: 'griffin_wellnest_db'
- Check MySQL user has correct password
- Import database schema if not created

Test:
- Open phpMyAdmin: http://localhost/phpmyadmin
- Verify database and tables exist
```

**2. "Session not starting"**
```
Issue: $_SESSION is empty or not persisting

Solutions:
- Verify session.save_path is writable
- Check session_name() matches WELLNEST_SID
- Verify PHP sessions enabled in php.ini
- Clear browser cookies
- Check for session_destroy() being called

Test:
- Check php.ini settings: php_info()
- Verify /tmp or session directory is writable
```

**3. "CSS not loading (plain HTML styling)"**
```
Issue: Tailwind CSS stylesheet not loading

Solutions:
- Verify output.css exists in /css/
- Check CSS file path in <link> tag
- Run: npm run build (to compile CSS)
- Clear browser cache
- Check file permissions (755)

Test:
- Check Network tab in browser DevTools
- Inspect <link> tag in page source
- Verify CSS file size (should be ~50KB)
```

**4. "404 errors on page navigation"**
```
Issue: Pages not found when navigating

Solutions:
- Verify file actually exists in directory
- Check file paths are relative correctly
- Check route configuration
- Verify pretty URLs not enabled in Apache
- Check APP_URL in config is correct

Test:
- Direct URL to file: http://localhost/path/to/file.php
- Check views/ folder structure matches routes
```

**5. "Login fails for all users"**
```
Issue: Cannot log in even with correct credentials

Solutions:
- Verify user exists in database
- Check password_verify() function working
- Verify CSRF token generated and validated
- Check session_start() is called
- Verify redirect_with_message() function exists

Test:
- Query database: SELECT * FROM users;
- Test password: php -r "echo password_hash('test', PASSWORD_BCRYPT);"
- Check PHP error log for exceptions
```

**6. "Assessment shows no questions"**
```
Issue: Assessment page loads but no questions display

Solutions:
- Verify assessment exists in database
- Check assessment_answers table populated
- Verify database query fetching questions
- Check question text is not NULL

Test:
- Query database: SELECT * FROM assessment_answers;
- Trace database queries in PHP error log
- Check API response in browser DevTools
```

### Debug Mode

**Enable Debug Mode:**
```php
// config/settings.php
define('APP_DEBUG', true); // Shows full error messages
```

**View Error Logs:**
```bash
# Windows
type logs/error.log

# Linux/Mac
tail -f logs/error.log
```

**Check PHP Errors:**
```php
// Anywhere in code
echo '<pre>'; var_dump($variable); echo '</pre>';
```

### Performance Debugging

**Slow Pages:**
1. Check database query performance
2. Verify indexes on frequently queried columns
3. Check for N+1 queries
4. Monitor server CPU/memory
5. Enable query logging

```php
// Log slow queries
if (time() - $start_time > 1) {
    logError('Slow query', ['query' => $sql, 'time' => time() - $start_time]);
}
```

---

## 📞 Support & Contact

### Getting Help

**Documentation:**
- Main README: `README.md` (this file)
- Testing Report: `documentation/TESTING_REPORT_AND_DOCUMENTATION.md`
- Frogger Game: `documentation/FROGGER_GAME_README.md`
- API Documentation: `documentation/API_JSON_Bodies.md`

**GitHub Issues:**
- Create issue on GitHub repository
- Include error message, steps to reproduce
- Include system information (OS, PHP version, browser)

**Common Resolutions:**
1. Check documentation first
2. Review Testing Report for known issues
3. Clear cache and cookies
4. Restart MySQL and Apache
5. Check error logs

### System Requirements

|  |  |
|---|---|
| **Web Server** | Apache 2.4+ or Nginx |
| **PHP** | 7.4+ (with PDO, JSON, Session extensions) |
| **Database** | MySQL 5.7+ or MariaDB 10.0+ |
| **Browser** | Chrome 90+, Firefox 88+, Safari 14+, Edge 90+ |
| **RAM** | 512MB minimum (1GB recommended) |
| **Disk** | 100MB minimum (500MB with logs) |

### Server Requirements Verification

```bash
# Check PHP version
php -v

# Check MySQL version
mysql --version

# Check required PHP extensions
php -r "echo extension_loaded('pdo_mysql') ? 'PDO MySQL: OK' : 'MISSING';"

# Check file permissions
ls -la logs/
# Should show: drwxrwxrwx (777) or drwxr-xr-x (755)
```

### Contact Information

**Project Repository:**
- GitHub: https://github.com/HaruCy16/Griffins_Wellnest_Sim_Mental_Health_Web_Based_System

**Support Channels:**
- GitHub Issues: For bug reports and feature requests
- Documentation: See `documentation/` folder
- Code Comments: Well-documented in-file comments

---

## 📄 License

This project is licensed under the MIT License. See LICENSE file for details.

### Summary
- ✅ You can use this for commercial purposes
- ✅ You can modify the code
- ✅ You can distribute the software
- ❌ The software is provided "as-is" without warranty
- ℹ️ You must include the license notice

---

## 🙏 Acknowledgments

- **Tailwind CSS** - Utility CSS framework
- **PHP** - Server-side language
- **MySQL** - Database management
- **InfinityFree** - Future hosting platform

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| **Total PHP Files** | 20+ |
| **Total JavaScript Files** | 5+ |
| **Total CSS** | ~3000 lines (Tailwind compiled) |
| **Database Tables** | 9 |
| **API Endpoints** | 10+ |
| **Views/Templates** | 45+ |
| **Configuration Files** | 3 |
| **Helper Functions** | 50+ |
| **Lines of Code** | 5000+ |

---

## 🎯 Project Status & Roadmap

### ✅ Completed Features (v1.0)
- Core authentication system
- 3-role RBAC system
- Student assessments
- Frogger game integration
- Counselor student management
- Admin user management
- System settings
- Audit logging
- Privacy protection
- Responsive design

### 🔄 In Development
- Advanced reporting dashboard
- Email notifications
- Mobile app (React Native)
- Multi-language support

### 📅 Planned Features (v2.0)
- Video counseling integration
- Progress tracking visualizations
- Peer support groups
- More therapeutic games
- Interactive wellness modules
- Parent portal
- Integration with Learning Management System

---

## 📝 Final Notes

**Getting Started:**
1. Follow Installation Guide
2. Start XAMPP (Apache + MySQL)
3. Import database schema
4. Visit application at localhost
5. Log in and explore

**For Production Deployment:**
1. Follow Deployment Guide
2. Configure for InfinityFree or VPS
3. Update App URL and environment settings
4. Import database on production server
5. Test all features in production
6. Enable HTTPS/SSL
7. Monitor system regularly

**Need Help?**
- Check documentation folder
- Review code comments
- Check GitHub issues
- Consult security section

---

**Last Updated:** March 20, 2026  
**Version:** 1.0.0  
**Status:** Production Ready ✅

**Made with ❤️ for Student Mental Health Support**

