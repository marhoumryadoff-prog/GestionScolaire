# GestionScolaire - System Overview & Architecture

## 🏗 System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     USER BROWSER / CLIENT                       │
├─────────────────────────────────────────────────────────────────┤
│                     APACHE WEB SERVER                           │
│                  (Serves PHP files, assets)                     │
├─────────────────────────────────────────────────────────────────┤
│                      PHP 8.2.12 ENGINE                          │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              config.php (Protection Layer)              │  │
│  │  - Session check                                        │  │
│  │  - Login redirect                                       │  │
│  │  - Global variables                                     │  │
│  │  - Helper functions                                     │  │
│  └──────────────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │            connexion_base.php (Database)               │  │
│  │  - PDO connection                                       │  │
│  │  - Prepared statements                                  │  │
│  │  - Secure queries                                       │  │
│  └──────────────────────────────────────────────────────────┘  │
├─────────────────────────────────────────────────────────────────┤
│            MARIADB 10.4.32 DATABASE SERVER                      │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │          gestion_scolaire_tp Database                   │  │
│  │  ┌────────────────────────────────────────────────────┐ │  │
│  │  │ user (login accounts)                              │ │  │
│  │  │ etudiants (72 students)                            │ │  │
│  │  │ filières (7 programs)                              │ │  │
│  │  │ modules (17 courses)                               │ │  │
│  │  │ notes (194 grades)                                 │ │  │
│  │  │ nationalites / sports / enseignants (reference)   │ │  │
│  │  └────────────────────────────────────────────────────┘ │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 User Flow Diagram

### Authentication Flow
```
┌─────────────────────────────────────────────────────────┐
│ User Access Any Page                                    │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
         ┌───────────────────┐
         │ Page Includes     │
         │ config.php        │
         └────────┬──────────┘
                  │
                  ▼
         ┌──────────────────────┐     NO   ┌──────────────┐
         │ $_SESSION['user_id'] ├─────────►│ Redirect to  │
         │ Set?                 │         │ login.php    │
         └────────┬─────────────┘         └──────────────┘
                  │
                 YES
                  │
                  ▼
         ┌──────────────────────────┐
         │ Load Global Variables:   │
         │ - $user_id              │
         │ - $user_email           │
         │ - $user_role            │
         │ - $is_admin / $is_user  │
         └────────┬─────────────────┘
                  │
                  ▼
         ┌──────────────────────────┐
         │ Execute Page Logic       │
         │ (Protected from here)    │
         └──────────────────────────┘
```

### Login Process
```
┌──────────────────┐
│ Visit login.php  │
└────────┬─────────┘
         │
         ▼
┌────────────────────────┐
│ Enter Email & Password │
└────────┬───────────────┘
         │
         ▼
┌──────────────────────────┐     NO    ┌───────────────────┐
│ Check in user table      ├──────────►│ Show Error        │
│ password_verify()        │          │ "User Inexistant" │
└────────┬─────────────────┘          └───────────────────┘
         │
        YES
         │
         ▼
┌─────────────────────────────┐
│ Create Session Variables:   │
│ $_SESSION['user_id']        │
│ $_SESSION['user_email']     │
│ $_SESSION['user_role']      │
└────────┬────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ Redirect to menu_principal   │
│ (based on role)              │
└──────────────────────────────┘
```

### Role-Based Access
```
┌─────────────────────┐
│ After Login         │
└────────┬────────────┘
         │
         ▼
    ┌────────────┐
    │ Check Role │
    └────┬───┬───┘
         │   │
       ADMIN USER
         │   │
         │   └─────────────────────────┐
         │                             │
         ▼                             ▼
    ┌──────────────────┐    ┌──────────────────┐
    │ Admin Menu:      │    │ User Menu:       │
    │ - Dashboard      │    │ - Mon Bulletin   │
    │ - Students       │    │ - Home           │
    │ - Grades         │    │ - Logout         │
    │ - Teachers       │    └──────────────────┘
    │ - Reports        │
    │ - User Mgmt      │
    │ - Statistics     │
    └──────────────────┘
```

---

## 📊 Data Model (ER Diagram Simplified)

```
┌─────────────────────┐
│      user           │
├─────────────────────┤
│ id (PK)             │
│ email (UNIQUE)      │
│ mdp (hashed)        │
│ role (Admin/User)   │
└─────────────────────┘

┌─────────────────────────┐
│    etudiants            │◄─────┐
├─────────────────────────┤      │
│ id_etudiant (PK)        │      │
│ numero_etudiant         │      │ 1:N
│ nom_etudiant            │      │
│ prenom_etudiant         │      │
│ FilièreId (FK) ─────────┼──────┼─────┐
│ id_nationalite (FK)     │      │     │
│ photo                   │      │     │
│ ...more fields...       │      │     │
└─────────────────────────┘      │     │
                                 │     │
                    ┌────────────┘     │
                    │                  │
                    ▼                  ▼
         ┌──────────────────┐  ┌──────────────────┐
         │    filières      │  │    modules       │
         ├──────────────────┤  ├──────────────────┤
         │ Id (PK)          │  │ CodeModule (PK)  │
         │ CodeFilière      │  │ DésignationModule│
         │ Désignation      │  │ Coefficient      │
         └────────┬─────────┘  └────────┬─────────┘
                  │                     │
                  │                     │
                  └──────────┬──────────┘
                             │
                    ┌────────▼──────────┐
                    │      notes        │
                    ├───────────────────┤
                    │ id_note (PK)      │
                    │ Num_Etudiant (FK) │
                    │ Code_Module (FK)  │
                    │ Note (13.40-19.85)│
                    └───────────────────┘

(Additional tables: nationalites, sports, etudiant_sports, enseignants)
```

---

## 🗂 File Dependency Map

```
PUBLIC FILES (No Authentication Required)
├── login.php ◄─── index.php, inscription.php, logout.php
├── inscription.php
├── logout.php
└── index.php

PROTECTED FILES (All require config.php → redirect if not logged in)
├── config.php (Included first to check auth)
│   ├── Checks $_SESSION['user_id']
│   ├── Redirects to login.php if missing
│   └── Sets global variables
│
├── menu_principal.php (All authenticated users)
│   ├── Displays Admin menu if $is_admin
│   ├── Displays User menu if $is_user
│   └── Includes quick access cards
│
├── student_bulletin.php (User role only)
│   ├── Check: if (!$is_user) redirect
│   ├── Uses connexion_base for DB access
│   └── Displays grades with calculation
│
└── Admin Pages (All check: if (!$is_admin) redirect)
    ├── bulletin_etudiant.php
    ├── liste_etudiants.php
    ├── liste_note.php
    ├── formulaire_principal.php
    ├── frmEnseignants.php
    ├── frmModules.php
    ├── frmBulletins.php
    ├── frmFilières.php
    ├── gestion_nationalites.php
    ├── gestion_sports.php
    ├── gestion_users.php
    ├── statistiques.php
    ├── pv_global.php
    └── pv_global2.php

UTILITY
├── connexion_base.php (Database class - included by config.php)
└── uploads/ (Directory for student photos)
```

---

## 🔐 Security Layers

```
LAYER 1: Web Server Level
├── Apache serves only public files
└── .htaccess (optional mod_rewrite rules)

LAYER 2: PHP Execution Level
├── config.php checks
│   ├── Session status
│   ├── Login requirement
│   └── Sets user variables
└── Page-level access checks
    ├── if (!$is_admin) redirect
    └── if (!$is_user) redirect

LAYER 3: Database Level
├── Prepared statements (no string concat)
├── Parameter binding (PDO)
└── SQL injection impossible

LAYER 4: Password Security
├── BCrypt hashing (password_hash)
├── Password verification (password_verify)
└── No plain text storage

LAYER 5: Input Validation
├── Email format check
├── Required field check
├── htmlspecialchars() for XSS
└── Input trimming/sanitization
```

---

## 📈 Grade Calculation Process

```
User Input
    │
    ▼
┌─────────────────────────────┐
│ Search by Student Number    │
│ (1-72 valid range)          │
└────────┬────────────────────┘
         │
         ▼
┌──────────────────────────────────────────┐
│ Query: SELECT student info + all grades  │
│ FROM notes JOIN modules ON ...           │
│ WHERE Num_Etudiant = ?                   │
└────────┬─────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────┐
│ For Each Grade:                          │
│ Calculate: Note × Coefficient            │
└────────┬─────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────┐
│ Sum all: (Note × Coeff) / Sum of Coeff  │
│ Example: 108.60 / 7 = 15.51 Average     │
└────────┬─────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────┐
│ Compare with threshold (10.0)            │
├──────┬──────────────────────────┬────────┤
│      │                          │        │
▼      ▼                          ▼        ▼
≥10:   10.0-19.85              <10:      
✅     ✅ Pass (Green)          ❌       
Réussi Show in green            À améliorer
       in bulletin               Show in red
                                in bulletin
```

---

## 🎯 Page Flow by Role

### Admin User Flow
```
login.php
    │
    ├─ Email: admin@scolaire.com
    ├─ Password: admin123
    │
    ▼
menu_principal.php (ADMIN VERSION)
    │
    ├─ Quick Access Cards (8 options)
    │   ├─ Voir Étudiants
    │   ├─ Gestion Notes
    │   ├─ Ajouter Étudiant
    │   ├─ Gestion Utilisateurs
    │   ├─ Enseignants
    │   ├─ Statistiques
    │   ├─ PV Global
    │   └─ Filières
    │
    ├─ Navigation Menu
    │   ├─ Gestion des Données
    │   │   ├─ formulaire_principal.php (Add Student)
    │   │   ├─ frmEnseignants.php (Teachers)
    │   │   ├─ frmModules.php (Modules)
    │   │   └─ frmBulletins.php (Grades)
    │   │
    │   ├─ Tables de Référence
    │   │   ├─ gestion_nationalites.php
    │   │   ├─ gestion_sports.php
    │   │   └─ frmFilières.php
    │   │
    │   ├─ Listes & Rapports
    │   │   ├─ liste_etudiants.php (72 students)
    │   │   └─ liste_note.php (194 grades)
    │   │
    │   └─ Administration
    │       ├─ gestion_users.php (User Mgmt)
    │       └─ statistiques.php (Stats)
    │
    └─ Logout (destroy session → login.php)
```

### Regular User Flow
```
login.php
    │
    ├─ Email: user@scolaire.com
    ├─ Password: user123
    │
    ▼
menu_principal.php (USER VERSION)
    │
    ├─ Quick Access Cards (3 options)
    │   ├─ Mon Bulletin
    │   ├─ Accueil
    │   └─ Déconnexion
    │
    └─ Navigation Menu
        └─ Mon Bulletin (single option)
            │
            ▼
        student_bulletin.php
            │
            ├─ Enter Student Number (1-72)
            │
            ├─ View Results:
            │   ├─ Personal Grades
            │   ├─ Weighted Average
            │   ├─ Pass/Fail Status
            │   └─ Print Button
            │
            └─ Logout (destroy session → login.php)
```

---

## 📦 Deployment Stack

```
Production Server
├── Operating System (Linux/Windows)
│
├── Web Server
│   └── Apache 2.4.x
│       ├── mod_rewrite enabled
│       ├── mod_ssl for HTTPS
│       └── Proper vhost configuration
│
├── PHP Runtime
│   └── PHP 8.2.12+
│       ├── PDO enabled
│       ├── mysqli disabled (use PDO only)
│       ├── Error logging configured
│       └── Memory limit: 128MB+
│
├── Database Server
│   └── MariaDB 10.4+
│       ├── User with restricted privileges
│       ├── Strong password set
│       ├── Automated backups
│       ├── Proper indexing
│       └── Regular optimization
│
├── File System
│   ├── /var/www/html/GestionScolaire/
│   │   ├── Proper file permissions (644)
│   │   ├── Directory permissions (755)
│   │   └── uploads/ writeable (775)
│   │
│   └── /var/log/apache2/
│       ├── Error logs
│       └── Access logs
│
└── Security
    ├── SSL/TLS certificate
    ├── Firewall rules
    ├── Regular backups
    ├── Security updates
    └── Access monitoring
```

---

## ⚡ Performance Optimization

```
Current Optimizations
├── Database
│   ├── Prepared statements (prevent full table scans)
│   ├── Proper indexing on foreign keys
│   ├── Efficient JOIN queries
│   └── Query result caching in session
│
├── PHP
│   ├── Minimal includes (no unnecessary files)
│   ├── Single database connection (reused)
│   ├── Session-based caching
│   └── Minimal global variables
│
├── Network
│   ├── No external API calls
│   ├── All assets local
│   └── No CDN dependencies
│
└── Code
    ├── DRY principle (config.php reused)
    ├── No code duplication
    ├── Efficient loops and conditions
    └── Proper memory management
```

---

## 🔄 Request/Response Cycle

```
1. USER REQUEST
   ├─ http://localhost/GestionScolaire/menu_principal.php
   └─ Browser sends GET/POST to Apache

2. APACHE ROUTING
   ├─ Matches request to PHP file
   └─ Passes control to PHP-CGI

3. PHP EXECUTION (config.php is always first)
   ├─ Session check
   ├─ Login redirect if needed
   ├─ Load global variables
   ├─ Include connexion_base.php
   └─ Continue with page logic

4. DATABASE QUERIES (if needed)
   ├─ Prepare statement
   ├─ Bind parameters
   ├─ Execute query
   ├─ Fetch results
   └─ Process data

5. HTML RENDERING
   ├─ Output HTML
   ├─ Render forms
   ├─ Display data tables
   └─ Include CSS/JS

6. RESPONSE
   ├─ Browser receives HTML
   ├─ Renders page
   ├─ Executes any JavaScript
   └─ User sees interface

7. USER INTERACTION
   ├─ User clicks button/link
   ├─ Form submission
   └─ Back to step 1
```

---

## 📊 System Load Estimation

```
72 Students
7 Programs (Filières)
17 Modules
194 Grades
Unlimited user accounts

Per Request Overhead
├─ Session check: 1-2ms
├─ Database query: 5-20ms (depending on complexity)
├─ Page rendering: 10-50ms
└─ Total average: 20-80ms per request

Concurrent Users
├─ Low usage (< 10 concurrent): < 50MB memory
├─ Medium usage (10-50 concurrent): 50-200MB memory
├─ High usage (50-100 concurrent): 200-500MB memory
└─ Peak (100+ concurrent): Requires optimization/caching
```

---

**Architecture Version:** 1.0  
**Last Updated:** November 12, 2025  
**Status:** Production Ready ✅
