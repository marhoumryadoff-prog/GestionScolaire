# 🔐 LOGIN PROTECTION - QUICK IMPLEMENTATION

## THE SIMPLEST SOLUTION ✅

Add **ONE LINE** at the very top of each PHP file you want to protect:

```php
<?php
require_once 'config.php';
// ... rest of your code ...
?>
```

That's it! The `config.php` file will:
- ✅ Start session automatically
- ✅ Connect to database
- ✅ Check if user is logged in
- ✅ Redirect to login if not authenticated
- ✅ Provide helpful variables ($user_id, $user_email, $user_role, etc.)

---

## FILES TO UPDATE (Add 1 line to each)

### PROTECTED FILES (Add the line):
```
menu_principal.php
bulletin_etudiant.php
student_bulletin.php
liste_etudiants.php
liste_note.php
pv_global.php
pv_global2.php
frmBulletins.php
frmEnseignants.php
frmFilières.php
frmModules.php
gestion_nationalites.php
gestion_sports.php
gestion_users.php
statistiques.php
traitement_etudiant.php
traitement_nationalites.php
traitement_sports.php
formulaire_principal.php
user_bulletin.php
modules.php
test_images.php
```

### PUBLIC FILES (NO changes needed):
```
login.php
logout.php
inscription.php
index.php (already updated)
```

---

## BEFORE & AFTER EXAMPLE

### BEFORE (menu_principal.php):
```php
<?php
session_start();
require_once 'connexion_base.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role'] ?? 'User';
// ... rest of code ...
?>
```

### AFTER (menu_principal.php):
```php
<?php
require_once 'config.php';  // ← ADD THIS ONE LINE

// NO need for session_start()
// NO need for require connexion_base.php
// NO need for login check
// $user_role is already available!

// ... rest of code (same as before) ...
?>
```

---

## AVAILABLE GLOBAL VARIABLES

After including `config.php`, you have access to:

```php
$user_id          // User's ID from database
$user_email       // User's email
$user_role        // 'Admin' or 'User'
$is_admin         // true if Admin, false otherwise
$is_user          // true if User, false otherwise
$is_logged_in     // true if session exists
```

### Usage Example:
```php
<?php
require_once 'config.php';

if ($is_admin) {
    echo "Welcome Admin: " . $user_email;
} else {
    echo "Welcome User: " . $user_email;
}
?>
```

---

## AVAILABLE HELPER FUNCTIONS

You also have helper functions:

```php
requireAdmin()        // Exit if not admin
requireUser()         // Exit if not logged in
redirectToLogin()     // Force redirect to login
redirectToMenu()      // Force redirect to menu
isLoggedIn()          // Check if logged in (returns bool)
getUserRole()         // Get user's role
getUserEmail()        // Get user's email
```

### Usage Example:
```php
<?php
require_once 'config.php';

requireAdmin();  // This page only for admins!

// If not admin, user is redirected automatically
echo "Admin panel";
?>
```

---

## STEP-BY-STEP IMPLEMENTATION

### Step 1: Test Current State
1. Clear browser cookies
2. Try to access: `http://localhost/GestionScolaire/menu_principal.php`
3. Currently: Should show login form (already has manual check)

### Step 2: Update ONE FILE (menu_principal.php)

**Current top (lines 1-8):**
```php
<?php
session_start();

// Check if user is logged in, otherwise redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? 'Unknown';
?>
```

**Change to:**
```php
<?php
require_once 'config.php';
// All variables are now available: $user_role, $user_email, etc.
?>
```

**Save and test:**
- Clear cookies
- Try: `http://localhost/GestionScolaire/menu_principal.php`
- ✅ Should redirect to login.php

### Step 3: Update Remaining Files
For each file in the "PROTECTED FILES" list above:
1. Open the file
2. Replace the top PHP block with just: `<?php require_once 'config.php'; ?>`
3. Save

---

## TESTING

### Test 1: No Login (should redirect)
```
1. Clear cookies
2. Go to: http://localhost/GestionScolaire/menu_principal.php
3. ✅ Should redirect to login.php
```

### Test 2: With Login (should show)
```
1. Go to: http://localhost/GestionScolaire/
2. Login with credentials
3. Try: http://localhost/GestionScolaire/liste_etudiants.php
4. ✅ Should show the page
```

### Test 3: After Logout (should redirect)
```
1. Click Logout
2. Try: http://localhost/GestionScolaire/liste_etudiants.php
3. ✅ Should redirect to login.php
```

---

## VERIFICATION CHECKLIST

- [ ] Created `config.php` file
- [ ] Updated `menu_principal.php` (just 1 line at top)
- [ ] Tested: Access without login → Redirects to login
- [ ] Tested: Access after login → Shows page
- [ ] Updated remaining protected files (bulk update possible)
- [ ] All CSS/JS still loading (not affected)
- [ ] All images still loading (not affected)
- [ ] Login/logout working properly

---

## IF IT'S STILL NOT WORKING

### Check 1: Is config.php really being loaded?
Add this to test:
```php
<?php
require_once 'config.php';
echo "config.php loaded! User: " . $user_email;
?>
```

### Check 2: Is session working?
Add this to login.php to verify:
```php
<?php
// After successful login, before redirect
echo "Session set. User ID: " . $_SESSION['user_id'];
header("Location: menu_principal.php");
?>
```

### Check 3: Verify file permissions
Make sure all `.php` files are readable by Apache

### Check 4: Clear all caches
- Clear browser cache
- Clear cookies
- Restart browser

---

## QUICK UPDATE SCRIPT

To update ALL protected files at once using a text editor:

**Find & Replace (in your editor):**
- **Find:** `<?php\nsession_start();\nrequire_once 'connexion_base.php';\n\nif (!isset($_SESSION['user_id'])) {\n    header("Location: login.php");\n    exit();\n}`
- **Replace:** `<?php\nrequire_once 'config.php';`

Or manually update each file by replacing the initial PHP block with:
```php
<?php
require_once 'config.php';
?>
```

---

## FILES STATUS

✅ **Created:**
- `config.php` - Main protection file
- `.htaccess` - (simplified, for future use)
- `check_session.php` - (alternative method)

✅ **Updated:**
- `index.php` - Checks login before menu redirect

⏳ **Need Update (add 1 line):**
- `menu_principal.php` - Ready to update
- `bulletin_etudiant.php`
- `student_bulletin.php`
- And 17 other files...

---

## RECOMMENDED APPROACH

Use `config.php` method because:
1. ✅ Works on all servers (no Apache mods needed)
2. ✅ Simplest implementation (1 line per file)
3. ✅ Provides database connection automatically
4. ✅ Provides helper variables and functions
5. ✅ Easy to debug if issues occur
6. ✅ Professional and scalable

---

**Status**: ✅ Infrastructure complete, ready to deploy  
**Date**: November 12, 2025  
**Next Action**: Update protected files with `require_once 'config.php';`
