# 🗑️ Files Safe to Remove - Complete List

## ✅ SAFE TO DELETE (One-Time Setup/Test Scripts)

These files were created for setup, testing, or debugging and are no longer needed:

### Setup & Migration Scripts:
1. **`test_db.php`** - Database connection test
2. **`fix_passwords.php`** - Password hash fix script  
3. **`link_lance.php`** - One-time account linking script
4. **`setup_database.php`** - Database auto-setup script
5. **`migrate_user_status.php`** - User status migration (if already run)
6. **`fix_pending_status.php`** - Fix pending status script (if already run)
7. **`test_approval_access.php`** - Approval access test script

### Package Files:
8. **`package-lock.json`** - npm file (not needed for PHP project)

### Documentation (Optional):
9. **`FILES_TO_REMOVE.md`** - This cleanup guide (can remove after cleanup)
10. **`PROJECT_SUMMARY.md`** - Optional documentation
11. **`SETUP.md`** - Optional documentation (README.md has setup info)

## ❌ DO NOT DELETE (Core Application Files)

### Essential Application Files:
- ✅ All files in `api/` folder
- ✅ All files in `assets/` folder  
- ✅ All files in `config/` folder
- ✅ All files in `controllers/` folder
- ✅ All files in `includes/` folder
- ✅ All files in `models/` folder
- ✅ All files in `sql/` folder
- ✅ All main `.php` files in root (dashboard.php, production.php, etc.)
- ✅ `uploads/` folder
- ✅ `README.md` (recommended to keep)

## 📋 Quick Delete List

Copy and paste these filenames to delete:

```
test_db.php
fix_passwords.php
link_lance.php
setup_database.php
migrate_user_status.php
fix_pending_status.php
test_approval_access.php
package-lock.json
FILES_TO_REMOVE.md
PROJECT_SUMMARY.md
SETUP.md
```

## ⚠️ Before Deleting

1. ✅ Database is set up and working
2. ✅ All users can log in
3. ✅ Employee-user links are working
4. ✅ User approval system is working
5. ✅ You've backed up your database



