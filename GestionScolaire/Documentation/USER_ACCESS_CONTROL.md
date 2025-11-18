# Contrôle d'Accès Utilisateur - Documentation 🔐

## Résumé des Restrictions

Le système a été configuré pour **limiter l'accès des utilisateurs réguliers** à leur propre bulletin uniquement.

---

## 1. Rôles et Permissions

### **Administrateur (Admin)** 👨‍💼
- ✅ Accès complet au système
- ✅ **Gestion des Données** (Étudiants, Enseignants, Modules, Bulletins)
- ✅ **Tables de Référence** (Nationalités, Sports, Filières)
- ✅ Consultation de **tous les bulletins**
- ✅ **Liste des Étudiants** - accès complet
- ✅ **Liste des Notes** - gestion complète
- ✅ **PV Global** - rapports complets
- ✅ **Administration** (Gestion utilisateurs, Statistiques)
- ✅ Génération de rapports et statistiques

### **Utilisateur (User)** 👤
- ❌ **PAS D'ACCÈS** à "Gestion des Données"
- ❌ **PAS D'ACCÈS** à "Tables de Référence"
- ❌ **PAS D'ACCÈS** à "Liste des Étudiants"
- ❌ **PAS D'ACCÈS** à "Liste des Notes"
- ❌ **PAS D'ACCÈS** à "PV Global"
- ❌ **PAS D'ACCÈS** à "Administration"
- ✅ **ACCÈS LIMITÉ À SON BULLETIN PERSONNEL UNIQUEMENT**
- ✅ Peut consulter et imprimer son propre bulletin
- ✅ Peut retourner à l'accueil
- ✅ Peut se déconnecter

---

## 2. Navigation Restreinte

### Menu Principal - Navigation Dynamique

#### **Éléments Admin Uniquement:**
- 📊 Gestion des Données
  - 👥 Étudiants
  - 👨‍🏫 Enseignants
  - 📚 Modules
  - 📋 Bulletins de Notes
- ⚙️ Tables de Référence
  - 🌍 Nationalité
  - ⚽ Sports
  - 🎓 Filières
- 📑 Liste des Étudiants
- 📈 Liste des Notes
- 📊 PV Global
- 🔐 Administration (Gestion Utilisateurs, Statistiques)

#### **Éléments User Uniquement:**
- 📋 Mon Bulletin (Seul accès au bulletin personnel)

#### **Éléments Communs:**
- 🏠 Accueil (toujours visible)

```php
<?php if ($user_role === 'Admin'): ?>
    <!-- Éléments Admin visibles -->
    - Gestion des Données (Étudiants, Enseignants, Modules, Bulletins)
    - Tables de Référence (Nationalité, Sports, Filières)
    - Liste des Étudiants
    - Liste des Notes
    - PV Global
    - Administration (Gestion Utilisateurs, Statistiques)
<?php else: ?>
    <!-- Éléments User uniquement -->
    - Mon Bulletin (bulletin personnel restreint)
<?php endif; ?>
```

### Quick Access Cards (Cartes d'Accès Rapide)

**Pour les Admins:**
1. 👥 Voir Étudiants
2. 📝 Gestion Notes
3. ✏️ Ajouter Étudiant
4. 🔐 Gestion Utilisateurs
5. 👨‍🏫 Enseignants
6. 📊 Statistiques
7. 📋 PV Global
8. 🎓 Filières

**Pour les Users:**
1. 📋 Mon Bulletin (Seul accès disponible)
2. 🏠 Accueil
3. 🚪 Déconnexion

---

## 3. Pages Accessibles

### Pages Publiques (Avec Check Session)
- `menu_principal.php` - Accueil avec navigation restreinte
- `logout.php` - Déconnexion

### Pages Admins Uniquement (Protégées)
- `liste_etudiants.php` - Liste de tous les étudiants
- `liste_note.php` - Gestion des notes
- `frmBulletins.php` - Modification des notes
- `pv_global.php` - Rapports globaux
- `gestion_users.php` - Gestion des comptes
- `statistiques.php` - Statistiques du système
- `frmEnseignants.php` - Gestion enseignants
- `frmFilières.php` - Gestion filières
- Etc.

### Pages Users Uniquement (Protégées)
- `student_bulletin.php` - **Bulletin personnel restreint**

---

## 4. Fichier: `student_bulletin.php` 🆕

Nouvelle page créée spécifiquement pour les utilisateurs réguliers.

### Caractéristiques:

✅ **Accès restreint**
```php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'User') {
    header("Location: menu_principal.php");
    exit();
}
```

✅ **Affiche uniquement le bulletin de l'étudiant actuellement connecté**
- Numéro étudiant
- Nom et prénom
- Filière et programme
- Liste des notes avec coefficients
- Moyenne générale calculée
- Statut (Réussi/À améliorer)

✅ **Fonctionnalités**
- 📋 Consultation du bulletin
- 🖨️ Impression directe
- 🔄 Navigation limitée
- 🚪 Bouton déconnexion rapide

---

## 5. Système de Vérification

### Session Check (à chaque chargement)

```php
// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier le rôle
if ($_SESSION['user_role'] !== 'User') {
    // Rediriger vers menu principal
    header("Location: menu_principal.php");
    exit();
}
```

### Variables de Session Utilisées
- `$_SESSION['user_id']` - ID unique de l'utilisateur
- `$_SESSION['user_email']` - Email affiché dans la barre info
- `$_SESSION['user_role']` - Rôle (Admin ou User)

---

## 6. Fonctionnement du Bulletin Étudiant

### Processus:

1. **Utilisateur se connecte** avec son compte
2. **Menu principal chargé** avec navigation restreinte
3. **Clic sur "Mon Bulletin"** → Redirection vers `student_bulletin.php`
4. **Page affiche son bulletin** personnel
5. **Peut imprimer** directement depuis le navigateur
6. **Peut se déconnecter** depuis n'importe quelle page

### Données Affichées:
```sql
SELECT e.numero_etudiant, e.nom_etudiant, e.prenom_etudiant, 
       e.civilite, f.CodeFilière, f.Désignation,
       m.DésignationModule, m.Coefficient, n.Note
FROM etudiants e
LEFT JOIN filières f ON e.FilièreId = f.Id
LEFT JOIN notes n ON n.Num_Etudiant = e.numero_etudiant
LEFT JOIN modules m ON n.Code_Module = m.CodeModule
WHERE e.numero_etudiant = (du user connecté)
```

---

## 7. Sécurité Mise en Place

| Mesure | Description |
|--------|-------------|
| **Vérification de session** | Redirige vers login si pas connecté |
| **Vérification de rôle** | Contrôle accès Admin/User |
| **Pas d'accès paramètre GET** | Impossible de modifier l'ID dans l'URL |
| **HTMLspecialchars()** | Protège contre XSS |
| **Redirection stricte** | Empêche l'accès direct à d'autres bulletins |

---

## 8. Exemple de Flux

### Utilisateur Régulier:
```
Login (login.php)
    ↓
Menu Principal (navigation restreinte)
    ↓
Mon Bulletin (student_bulletin.php) ← SEULE OPTION
    ↓
Imprimer / Retour à l'accueil
    ↓
Déconnexion
```

### Administrateur:
```
Login (login.php)
    ↓
Menu Principal (navigation complète)
    ↓
Multiples options (étudiants, notes, users, etc.)
    ↓
Accès complet au système
    ↓
Déconnexion
```

---

## 9. Tests Recommandés

### Test 1: Accès User
```bash
1. Se connecter avec: etudiant@isil.com / password
2. Vérifier que seul "Mon Bulletin" apparaît
3. Cliquer sur "Mon Bulletin"
4. Vérifier affichage du bulletin personnel
5. Tester impression
6. Déconnecter
```

### Test 2: Tentative d'Accès Non Autorisé
```bash
1. Connecté comme User
2. Tenter d'accéder à: liste_etudiants.php
3. Vérifier redirection vers menu_principal.php
4. Tenter d'accéder à: gestion_users.php
5. Vérifier redirection
```

### Test 3: Accès Admin
```bash
1. Se connecter avec: admin@isil.com / password
2. Vérifier accès complet à tous les éléments
3. Vérifier accès à gestion_users.php
4. Vérifier accès à statistiques.php
5. Vérifier accès à liste_etudiants.php
```

---

## 10. Fichiers Modifiés/Créés

| Fichier | Action | Description |
|---------|--------|-------------|
| `menu_principal.php` | ✏️ Modifié | Navigation dynamique selon rôle |
| `student_bulletin.php` | 🆕 Créé | Bulletin restreint pour users |
| `check_access.php` | ✓ Existant | Fonctions de vérification |
| `logout.php` | ✓ Existant | Déconnexion |

---

## 11. Prochaines Améliorations Possibles

- [ ] Ajouter un système de connexion student (matricule + PIN)
- [ ] Lier les comptes Users aux étudiants automatiquement
- [ ] Ajouter notifications de nouvelles notes
- [ ] Historique de consultation des bulletins
- [ ] Export PDF automatique
- [ ] Alertes si notes critiques
- [ ] Système de demande de correction

---

## 📞 Support

Pour toute question ou ajustement des permissions:
- Modifier les conditions `if ($user_role === 'Admin')` dans le code
- Ajouter des vérifications supplémentaires si nécessaire
- Contacter l'administrateur du système
