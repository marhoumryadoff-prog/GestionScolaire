# GestionScolaire - Quick Start Guide

## Login Credentials

### Test Accounts (If using provided SQL):
- **Admin:** 
  - Email: `admin@scolaire.com`
  - Password: `admin123`

- **User:**
  - Email: `user@scolaire.com`
  - Password: `user123`

> Note: If these don't exist, register new accounts via inscription.php

---

## Access the System

1. **Start XAMPP:**
   - Start Apache & MySQL/MariaDB

2. **Navigate to:**
   ```
   http://localhost/GestionScolaire/
   ```

3. **First Login:**
   - You'll be redirected to `login.php`
   - Enter email & password
   - Click "Se Connecter"

---

## Admin Features (After Login as Admin)

### Quick Access Cards:
1. **Voir Étudiants** → View all 72 students
2. **Gestion Notes** → Enter/edit grades
3. **Ajouter Étudiant** → Add new student
4. **Gestion Utilisateurs** → Manage admin accounts
5. **Enseignants** → Manage teachers
6. **Statistiques** → View system stats
7. **PV Global** → Generate reports
8. **Filières** → Manage study programs

### Navigation Menu:
- **📊 Gestion des Données:**
  - 👥 Étudiants (Add/manage students)
  - 👨‍🏫 Enseignants (Add/manage teachers)
  - 📚 Modules (Add/manage courses)
  - 📋 Bulletins de Notes (Grade management)

- **⚙️ Tables de Référence:**
  - 🌍 Nationalité (Nationality reference)
  - ⚽ Sports (Sports activities)
  - 🎓 Filières (Study programs)

- **📑 Liste des Étudiants:** Full student list
- **📈 Liste des Notes:** Grade report
- **📊 PV Global:** Comprehensive report

- **🔐 Administration:**
  - 👤 Gestion des Utilisateurs (User accounts)
  - 📈 Statistiques (System statistics)

---

## User Features (After Login as Regular User)

### Quick Access Cards:
1. **Mon Bulletin** → Search for personal grades
2. **Accueil** → Return to home
3. **Déconnexion** → Logout

### How to Search Your Bulletin:
1. Click "Mon Bulletin" (or from menu)
2. Enter your student number (1-72)
3. Click "🔎 Rechercher"
4. View your grades, weighted average, pass/fail status
5. Click "🖨️ Imprimer" to print bulletin

---

## System Information

### Student Data:
- **Total Students:** 72
- **Filières:** 7
  - Filière 1-2: 11 students each
  - Filière 3-4: 11 students each
  - Filière 5-6: 11 students each
  - Filière 7: 6 students

### Grades:
- **Total Grade Entries:** 194
- **Grade Range:** 13.40 - 19.85 /20
- **Filière-Specific Modules:**
  - Filières 1,2,5,6,7: MATH101, PHY101, INFO101
  - Filière 3: PROG201, ALGO201
  - Filière 4: BDD301, WEB401

### Average Calculation:
- Weighted by module coefficient
- Pass (Réussi): ≥ 10 /20
- Needs Improvement (À améliorer): < 10 /20

---

## File Organization

```
c:\xampp\htdocs\GestionScolaire\

Core Files:
├── config.php                    ← Auto-protection (include in all protected pages)
├── connexion_base.php           ← Database connection
├── login.php                    ← Login form
├── logout.php                   ← Logout handler
├── inscription.php              ← Registration form
├── index.php                    ← Entry point

Main Pages:
├── menu_principal.php           ← Main navigation hub
├── student_bulletin.php         ← User: View personal grades

Admin Pages:
├── bulletin_etudiant.php        ← Admin: Student bulletins
├── liste_etudiants.php          ← Admin: Student list (with photos)
├── liste_note.php               ← Admin: Grade report
├── formulaire_principal.php     ← Admin: Add student
├── frmEnseignants.php          ← Admin: Teacher management
├── frmModules.php              ← Admin: Module management
├── frmBulletins.php            ← Admin: Grade entry
├── frmFilières.php             ← Admin: Filière management
├── gestion_nationalites.php    ← Admin: Nationality reference
├── gestion_sports.php          ← Admin: Sports reference
├── gestion_users.php           ← Admin: User management
├── statistiques.php            ← Admin: Statistics
├── pv_global.php               ← Admin: Global report
├── pv_global2.php              ← Admin: Alternative report

Database:
├── add_random_notes_by_filiere.sql  ← Import to populate grades
├── base_etudiants_tp2_2025.sql      ← Database schema

Documentation:
├── FINAL_IMPLEMENTATION_SUMMARY.md  ← Full documentation (this)
└── README files (if any)

Resources:
└── uploads/                     ← Student photos directory
```

---

## Database Access

### Via phpMyAdmin:
1. Go to: `http://localhost/phpmyadmin`
2. Database: `gestion_scolaire_tp`
3. Username: `root`
4. Password: (leave empty)

### Key Tables:
- `user` - Login accounts (email, hashed password, role)
- `etudiants` - Student information (72 records)
- `filières` - Study programs (7 records)
- `modules` - Courses (specific to each filière)
- `notes` - Grades (194 records, filière-specific)
- `nationalites` - Nationality reference
- `sports` - Sports activities reference
- `etudiant_sports` - Student-sports relationships
- `enseignants` - Teacher information

---

## Common Tasks

### Add a New Admin User:
1. Database → phpMyAdmin
2. `user` table → Insert new row
3. Email: (new email)
4. Password: `password_hash('password', PASSWORD_DEFAULT)` in value
5. Role: Admin

### Change User Role:
1. Login as Admin
2. Go to Gestion des Utilisateurs
3. Edit user record
4. Change role: Admin ↔ User

### Add Student Grades:
1. Login as Admin
2. Go to Gestion des Données → Bulletins de Notes
3. Enter student number, module, grade
4. Save

### View Student List:
1. Login as Admin
2. Click "Liste des Étudiants" (quick access or menu)
3. View all students with:
   - Photos, names, numbers
   - Contact info, location
   - Nationality, filière, sports

### Search Student Grades (as User):
1. Login as regular User
2. Click "Mon Bulletin"
3. Enter your student number (1-72)
4. View your grades and average

---

## Troubleshooting

### "User Inexistant. Inscrivez vous"
- **Problem:** Email not found in database
- **Solution:** Register via inscription.php or admin creates account

### "Veuillez remplir tous les champs"
- **Problem:** Empty email or password field
- **Solution:** Enter both email and password before clicking login

### "Aucun étudiant trouvé"
- **Problem:** Student number doesn't exist (valid: 1-72)
- **Solution:** Check valid student numbers in liste_etudiants.php

### "Aucune note enregistrée"
- **Problem:** Student exists but no grades assigned
- **Solution:** Admin needs to add grades via frmBulletins.php

### Access Denied / Redirect to Login
- **Problem:** Trying to access protected page without login
- **Solution:** Login first via login.php

### Photo Not Showing
- **Problem:** Photo file missing from uploads folder
- **Solution:** Add student photo to uploads/filename.jpg

---

## Security Features

✅ **Password Security:**
- Stored as BCrypt hash (password_hash)
- Never stored in plain text
- Verified with password_verify

✅ **SQL Injection Protection:**
- All database queries use prepared statements
- PDO parameter binding
- No string concatenation in queries

✅ **Session Security:**
- Session-based authentication
- Automatic redirect to login if not authenticated
- Logout destroys session

✅ **Access Control:**
- Role-based menu visibility
- Page-level access restrictions
- Admin pages check user role
- User pages check is_user flag

✅ **Data Validation:**
- Email format validation
- Password confirmation in registration
- Input sanitization in forms

---

## Performance Tips

1. **Student List:** 
   - 72 students with photos may take 1-2 seconds
   - Fully responsive

2. **Grade Report:**
   - 194 entries organized by filière/module
   - Efficient database queries

3. **Session Management:**
   - Uses PHP native sessions
   - Consider implementing timeout in production

---

## API/Integration Notes

### To Integrate with External Systems:

**Get Student Grades via API:**
```php
// Would need to create API endpoint
// Returns JSON: {student: {id, name}, grades: [{module, note, coeff}], average: X}
```

**Update Grades Programmatically:**
```php
// Use frmBulletins.php form or direct database insert
// INSERT INTO notes (Num_Etudiant, Code_Module, Note) VALUES (?, ?, ?)
```

**Export Grades:**
```php
// Use liste_note.php to view, then browser print to PDF
// Or modify to output CSV/Excel format
```

---

## Next Steps / Enhancements

**Possible Future Features:**
- [ ] Dark mode theme
- [ ] Email notifications for grades
- [ ] Grade history tracking
- [ ] Attendance system
- [ ] Comments on grades
- [ ] Grade appeals process
- [ ] Export to PDF/Excel
- [ ] Mobile app
- [ ] SMS notifications
- [ ] Parent portal
- [ ] Document upload (transcripts, etc.)
- [ ] Multi-language support
- [ ] Session timeout
- [ ] 2FA (Two-factor authentication)

---

## Contact & Support

For issues or questions:
1. Check FINAL_IMPLEMENTATION_SUMMARY.md (full documentation)
2. Review database schema in phpMyAdmin
3. Check error messages in browser console (F12)
4. Review PHP error logs

---

**Last Updated:** November 12, 2025  
**System Version:** 1.0 Final  
**Status:** Production Ready  
**Theme:** Original styling (no CSS framework applied)
