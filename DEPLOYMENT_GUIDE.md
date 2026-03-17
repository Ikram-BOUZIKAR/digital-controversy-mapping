# Grade Management System - cPanel Deployment Guide
## Institut Formation - notes.iftl.ma

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Step 1: cPanel Setup](#step-1-cpanel-setup)
3. [Step 2: Database Configuration](#step-2-database-configuration)
4. [Step 3: File Upload](#step-3-file-upload)
5. [Step 4: Initialization](#step-4-initialization)
6. [Step 5: Verification](#step-5-verification)
7. [Troubleshooting](#troubleshooting)
8. [Admin Operations](#admin-operations)

---

## Prerequisites

✓ cPanel account with PHP 7.4+ and MySQL support
✓ Domain: iftl.ma with subdomain capability
✓ SSL certificate (usually auto-provisioned)
✓ 500 MB disk space minimum
✓ FTP or cPanel File Manager access

**Estimated time:** 30-45 minutes

---

## Step 1: cPanel Setup

### 1.1 Create Subdomain

1. Login to cPanel (cp.iftl.ma or your host)
2. Navigate to **Addon Domains** or **Subdomains**
3. Create subdomain `notes`
   - Domain: `notes.iftl.ma`
   - Document root: `public_html/notes`
4. Verify SSL certificate is active (should auto-provision)

### 1.2 Verify Subdomain

```
$ nslookup notes.iftl.ma
$ curl https://notes.iftl.ma/
```

Should return a blank page or directory listing (expected).

---

## Step 2: Database Configuration

### 2.1 Create Database

1. In cPanel, go to **MySQL Databases**
2. Create new database:
   - **Database Name:** `notes_iftl_db`
   - Click "Create Database"

### 2.2 Create Database User

1. In cPanel **MySQL Databases**, scroll to "MySQL Users"
2. Create new user:
   - **Username:** `iftl_user`
   - **Password:** Generate strong password (use cPanel generator)
   - **Click "Create User"**

**Save this password somewhere secure!**

### 2.3 Grant Privileges

1. In "Add User to Database" section:
   - Select user `iftl_user`
   - Select database `notes_iftl_db`
   - Click "Add"
2. Grant **ALL PRIVILEGES** (or at least: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP)

---

## Step 3: File Upload

### 3.1 Update Database Credentials

**Before uploading**, edit `index.php`:

```php
// Line 6-9: Update these with your actual credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'iftl_user');      // Your MySQL username
define('DB_PASS', 'YOUR_STRONG_PASSWORD');  // Your MySQL password
define('DB_NAME', 'notes_iftl_db');
```

### 3.2 Upload Files via cPanel File Manager

1. Login to cPanel
2. Open **File Manager**
3. Navigate to `/public_html/notes/`
4. Upload the following files:
   - `index.php` ⭐ (main application)
   - `init-db.php` (database setup)
   - `seed-data.php` (populate test data)

5. Create folder structure:
   ```
   /public_html/notes/
   ├── assets/
   │   ├── css/
   │   │   └── style.css
   │   ├── js/
   │   │   └── app.js
   │   └── logos/
   ├── logs/
   └── uploads/
   ```

6. Upload assets:
   - Upload `style.css` to `assets/css/`
   - Upload `app.js` to `assets/js/`

### 3.3 Set File Permissions

1. In File Manager:
   - Right-click `index.php` → Properties → Set to `644`
   - Right-click `logs/` → Properties → Set to `755`
   - Right-click `uploads/` → Properties → Set to `755`

---

## Step 4: Initialization

### 4.1 Access cPanel Terminal (or SSH)

1. In cPanel, find **Terminal** or **SSH** access
2. Navigate to your domain directory:
   ```bash
   cd ~/public_html/notes/
   ```

### 4.2 Run Database Initialization

```bash
php init-db.php
```

**Expected output:**
```
Creating tables...
✓ Users table created
✓ Students table created
...
✓ Database initialized successfully
```

### 4.3 Populate Seed Data

```bash
php seed-data.php
```

**Expected output:**
```
Seeding 261 students...
✓ Inserted 50 students
✓ Inserted 100 students
...
✓ All 261 students created successfully
✓ Sample grades generated
```

### 4.4 Verify Database

```bash
mysql -u iftl_user -p notes_iftl_db
```

Then check:
```sql
SHOW TABLES;
SELECT COUNT(*) FROM students;
SELECT COUNT(*) FROM users;
```

---

## Step 5: Verification

### 5.1 Access the Application

Open browser and go to: **https://notes.iftl.ma/**

You should see:
- ✓ Institut Formation logo and branding
- ✓ Login form
- ✓ SSL certificate active (green lock)

### 5.2 Test Login

**Default accounts created:**

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Coordinator | coordinator | coord123 |
| Teacher | teacher_demo | teacher123 |
| Student | student_1 | student123 |

**⚠️ IMPORTANT: Change these passwords immediately after first login!**

### 5.3 Quick Test Workflow

1. **Login as Admin:**
   ```
   Username: admin
   Password: admin123
   ```

2. **Verify Dashboard:**
   - Should see summary statistics
   - 261 students loaded
   - Programs and modules visible

3. **Login as Teacher:**
   ```
   Username: teacher_demo
   Password: teacher123
   ```
   - Should see module dropdown
   - Can enter grades for assigned classes

4. **Login as Student:**
   ```
   Username: student_1
   Password: student123
   ```
   - Should see own grades
   - Can complete profile

### 5.4 Performance Check

In browser console (F12):
- Page load time: Should be < 2 seconds
- No JavaScript errors
- Responsive on mobile

---

## Troubleshooting

### "Database connection failed"

**Solution:**
1. Verify credentials in `index.php` match cPanel database
2. Ensure database user has ALL PRIVILEGES
3. Check MySQL is running in cPanel

```bash
mysql -u iftl_user -pYOUR_PASSWORD notes_iftl_db -e "SELECT 1"
```

### "404 Not Found" on login

**Solution:**
1. Verify subdomain points to `/public_html/notes/`
2. Check `.htaccess` if routing issues
3. Ensure `index.php` is in `/public_html/notes/`

### "Permission denied" on logs/

**Solution:**
```bash
chmod 755 ~/public_html/notes/logs/
chmod 755 ~/public_html/notes/uploads/
chmod 644 ~/public_html/notes/index.php
```

### "SSL certificate not active"

**Solution:**
1. cPanel → SSL/TLS Status
2. Install AutoSSL for `notes.iftl.ma`
3. Wait 15 minutes for issuance
4. Access via `https://` only

### Students not appearing

**Solution:**
```bash
php seed-data.php
```

Re-run seed data script to ensure all 261 students are imported.

---

## Admin Operations

### Change Admin Password

1. Login as admin
2. Go to Settings → User Management
3. Click your username → Edit
4. Set new password (minimum 12 characters recommended)

### Create New Teacher Account

1. Admin Dashboard → User Management
2. Click "Add User"
3. Fill in:
   - Username: (e.g., `prof_math`)
   - Email: professor@iftl.ma
   - Role: Teacher
   - Password: (auto-generate strong password)
4. Assign modules in Teacher Details
5. Send username/password to teacher securely

### Backup Database

**Weekly automatic backup:**

cPanel → Backup Wizard → Automatic Backups

**Manual backup:**
```bash
mysqldump -u iftl_user -p notes_iftl_db > notes_backup_$(date +%Y%m%d).sql
```

### Export Grades as JSON

1. Admin Dashboard → Reports
2. Click "Export to JSON"
3. File downloads automatically
4. Store in secure location for archival

### Monitor System Health

1. cPanel → Resource Usage
2. Check:
   - Disk usage (should remain < 200 GB of 300 GB)
   - MySQL database size (monitor monthly)
   - Bandwidth (should be low usage)

---

## Security Recommendations

### 1. Change All Default Passwords ⭐

```bash
# Don't forget to update these in the database:
mysql -u iftl_user -p notes_iftl_db
UPDATE users SET password_hash=PASSWORD('newpassword') WHERE username='admin';
```

### 2. Enable IP Whitelisting (Optional)

In cPanel: Security → IP Whitelist (restrict admin access to known IPs)

### 3. Regular Backups

- Daily automatic backups in cPanel
- Export JSON dumps monthly to external storage
- Test restore process quarterly

### 4. Monitor Audit Log

1. Admin Dashboard → System → Audit Log
2. Review suspicious activity weekly
3. Check failed login attempts

### 5. SSL Certificate

- Verify auto-renewal is enabled
- cPanel → SSL/TLS Status → AutoSSL

### 6. Disable Default Accounts (After Setup)

Once all accounts are created:
```
Admin Dashboard → User Management → Deactivate default admin/teacher/student accounts
```

---

## Maintenance Schedule

| Task | Frequency | Who |
|------|-----------|-----|
| Check disk space | Weekly | Admin |
| Review audit logs | Weekly | Admin |
| Backup database | Daily (automatic) | System |
| Export JSON backup | Monthly | Admin |
| Update teacher passwords | Quarterly | Admin |
| SSL certificate renewal check | Quarterly | Admin |
| Performance review | Monthly | Admin |

---

## Support & Documentation

- **Admin Manual:** `/ADMIN_MANUAL.md`
- **API Reference:** `/API_REFERENCE.md`
- **Database Schema:** See `init-db.php`
- **Error Logs:** `/public_html/notes/logs/error.log`

---

## Quick Commands Reference

```bash
# SSH into server
ssh iftl@your-server.com

# Navigate to app
cd ~/public_html/notes/

# Initialize database
php init-db.php

# Seed sample data
php seed-data.php

# Check database
mysql -u iftl_user -p notes_iftl_db

# View error log
tail -f logs/error.log

# Set permissions
chmod 755 logs/ uploads/
chmod 644 index.php
```

---

**Deployment Date:** ________________

**Completed By:** ________________

**Notes:** ________________________________________________________

---

*Last Updated: 2026-03-17*
*Version: 1.0.0*
