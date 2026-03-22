# InfinityFree Deployment - Quick Reference Checklist

## 🚀 Quick Steps Overview

```
1. Prepare config files locally (5 min)
2. Create InfinityFree account (5 min)
3. Create database (5 min)
4. Upload files (15 min)
5. Import database (5 min)
6. Test application (10 min)
═══════════════════════════════════════
TOTAL TIME: ~45 minutes
```

---

## 📝 Step-by-Step Quick Reference

### Phase 1: LOCAL CONFIGURATION (Do on your computer)

#### [ ] 1.1 Update `config/settings.php`

```
Line 11:  APP_ENV = 'production'
Line 17:  APP_URL = 'https://YOURDOMAIN.epizy.com/wellnest'
```

#### [ ] 1.2 Update `config/database.php`

```
HOST:     localhost
DB_NAME:  [from InfinityFree panel]
USERNAME: [from InfinityFree panel]
PASSWORD: [from InfinityFree panel]
```

#### [ ] 1.3 Update `index.php`

Replace all:
```
/Wellnest_Sim_Web_Application/
```

With:
```
<?= APP_URL ?>
```

#### [ ] 1.4 Create `.htaccess`

Copy content from deployment guide and save as `.htaccess` in root folder

#### [ ] 1.5 Create ZIP File

```
Exclude:
- .git/
- node_modules/
- .vscode/

Include everything else ✓
```

---

### Phase 2: INFINITYFREE SETUP

#### [ ] 2.1 Login to Panel

- URL: https://panel.infinityfree.com
- Account ready? YES / NO

#### [ ] 2.2 Get Your Domain

- Domain: _________________________ (write it down!)
- Format: yourname.epizy.com

#### [ ] 2.3 Create Database

Panel → Databases → Create Database
```
Host:     _________________________ (usually localhost)
Database: _________________________ 
Username: _________________________ 
Password: _________________________ 
```

**SAVE THESE! →** They go in your `config/database.php`

---

### Phase 3: UPLOAD & EXTRACT

#### [ ] 3.1 Open File Manager

Panel → File Manager → Open File Manager

#### [ ] 3.2 Create Folder

Location: `/public_html/`
Folder name: `wellnest`

New location: `/public_html/wellnest/` ✓

#### [ ] 3.3 Upload ZIP

Click Upload → Select your ZIP file → Wait for completion

#### [ ] 3.4 Extract ZIP

Right-click ZIP → Extract → Wait

#### [ ] 3.5 Create Required Folders

- `/public_html/wellnest/logs/` → chmod 755
- `/public_html/wellnest/cache/` → chmod 755

---

### Phase 4: DATABASE SETUP

#### [ ] 4.1 Open phpMyAdmin

Panel → Databases → Manage [your database]

#### [ ] 4.2 Import Schema

Import tab → Browse → Select `database/schema.sql` → Go

**Status:** Complete when page shows success message

#### [ ] 4.3 Verify Tables

Click your database → Should see 9 tables:
- [ ] roles
- [ ] users
- [ ] sections
- [ ] assessments
- [ ] assessment_scores
- [ ] assessment_answers
- [ ] therapeutic_games
- [ ] game_sessions
- [ ] audit_logs

#### [ ] 4.4 Create Admin User

Insert new user in `users` table:
```
Email:        admin@wellnest.local
Password:     [bcrypt hash of Test@123456]
First Name:   Admin
Last Name:    User
Role ID:      3 (admin)
is_active:    1
```

---

### Phase 5: CONFIGURE SERVER FILES

#### [ ] 5.1 Edit settings.php on Server

File Manager → `/public_html/wellnest/config/settings.php`
- Change APP_ENV to 'production'
- Change APP_URL to your domain

#### [ ] 5.2 Edit database.php on Server

File Manager → `/public_html/wellnest/config/database.php`
- Update database credentials

---

### Phase 6: TEST

#### [ ] 6.1 Check Homepage

URL: `https://YOURDOMAIN.epizy.com/wellnest/`
Result: Griffins' WellNest logo appears ✓

#### [ ] 6.2 Test Login

Email: `admin@wellnest.local`
Password: `Test@123456`
Result: Dashboard loads ✓

#### [ ] 6.3 Test Registration

Click Register → Fill form → Submit
Result: Can create student account ✓

#### [ ] 6.4 Test HTTPS

Visit: `http://YOURDOMAIN.epizy.com/wellnest/`
Result: Auto-redirects to HTTPS ✓

#### [ ] 6.5 Create Test Accounts

Student:
```
Email:    student@test.local
Password: Test@123456
```

Counselor (via admin panel):
```
Email:    counselor@test.local
Password: TestCounselor@123
Role:     Counselor
```

---

### Phase 7: FEATURE VERIFICATION

#### As Student [ ]
- [ ] Dashboard loads
- [ ] Can view assessments
- [ ] Can start assessment
- [ ] Can complete assessment
- [ ] Can see results
- [ ] Can play game
- [ ] Can view notifications

#### As Counselor [ ]
- [ ] Dashboard loads
- [ ] Can view students
- [ ] Can see student details
- [ ] Can view assessment history
- [ ] Can add notes

#### As Admin [ ]
- [ ] Dashboard loads
- [ ] Can view users
- [ ] Can add user
- [ ] Can edit user
- [ ] Can delete user
- [ ] Can view settings

#### Mobile Test [ ]
- [ ] Responsive on phone
- [ ] Buttons clickable
- [ ] Forms usable

---

## 🔑 Important Credentials to Save

| Purpose | Value |
|---------|-------|
| Domain | _________________________ |
| Database Host | _________________________ |
| Database Name | _________________________ |
| Database User | _________________________ |
| Database Pass | _________________________ |
| Admin Email | _________________________ |
| Admin Password | _________________________ |

---

## ⚠️ Common Mistakes to Avoid

❌ **Don't upload `node_modules/` folder** - Too large!

❌ **Don't leave APP_DEBUG = true** - Shows errors to users

❌ **Don't use HTTP (only HTTPS)** - Security issue

❌ **Don't modify database structure** - Keep schema intact

❌ **Don't share credentials** - Keep them private!

---

## 🆘 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| Database error | Check credentials in `config/database.php` |
| 404 error | Check folder is `/public_html/wellnest/` |
| Blank page | Check `logs/error.log` file |
| Login fails | Verify admin user in phpMyAdmin |
| CSS not loading | Check `/css/output.css` exists |
| HTTPS not redirecting | Check `.htaccess` file exists |

---

## ✅ Final Deployment Verification

Before declaring "Success":

```
□ Domain accessible (https://domain/wellnest/)
□ Admin login works
□ Student registration works
□ Counselor account works
□ All 9 database tables created
□ No major errors in logs
□ HTTPS redirect working
□ Responsive design works
□ Error log doesn't show critical issues
□ Test accounts created
```

---

## 📞 Resources

- **Deployment Guide:** `INFINITYFREE_FILEMANAGER_DEPLOYMENT.md`
- **InfinityFree Panel:** https://panel.infinityfree.com/
- **phpMyAdmin:** Access via panel → Databases
- **Error Logs:** `/public_html/wellnest/logs/error.log`

---

**Good luck! You've got this! 🚀**

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Live URL:** https://_____________________

