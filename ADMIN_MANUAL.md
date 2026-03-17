# Admin Manual - Grade Management System
## Institut Formation Management Console

This manual covers all administrative functions and operations for system administrators.

---

## Table of Contents
1. [Dashboard Overview](#dashboard-overview)
2. [User Management](#user-management)
3. [Grade Management](#grade-management)
4. [Reporting](#reporting)
5. [System Configuration](#system-configuration)
6. [Backup & Recovery](#backup--recovery)
7. [Troubleshooting](#troubleshooting)

---

## Dashboard Overview

### Admin Landing Page

After login, the admin dashboard displays:

**Quick Statistics:**
- Total Students: 261
- Total Grades Entered: (count)
- Pending Approvals: (count)
- System Health: (OK/WARNING)

**Key Sections:**
1. **Student Overview** - Search, filter, bulk actions
2. **Grade Entry Status** - By teacher, by module, by cohort
3. **Alert Center** - Eliminating grades, incomplete profiles
4. **Recent Activity** - Last 10 actions in system

---

## User Management

### Create New User Account

**Path:** Admin Dashboard → User Management → Add User

**Steps:**
1. Click "Add User" button
2. Fill in form:
   - **Username:** (required, unique)
   - **Email:** (required, valid email format)
   - **Role:** Select from dropdown
     - Admin
     - Coordinator
     - Teacher
     - Student
   - **Password:** (auto-generate or custom)
   - **Active:** Toggle on/off

3. Click "Create User"

**System generates:**
- Username and password for login
- Email notification (if configured)
- Audit log entry

**Example:**
```
Username: prof_math_2025
Email: jean.dupont@iftl.ma
Role: Teacher
Password: (auto-generated: X7k9@mP2)
```

### Edit User Account

**Path:** Admin Dashboard → User Management → Users List

**Steps:**
1. Find user in list or search
2. Click username or "Edit" button
3. Modify fields:
   - Email address
   - Role (if needed)
   - Status (active/inactive)
   - Password reset

4. Click "Save Changes"

### Deactivate User

**Path:** Admin Dashboard → User Management → Users List → [User] → Deactivate

**Effect:**
- User cannot login
- Grades/data remain in system
- Audit trail maintained
- Can be reactivated later

**Process:**
1. Find user in list
2. Click "Deactivate" button
3. Confirm action
4. User receives notification (if email enabled)

### Assign Modules to Teacher

**Path:** Admin Dashboard → Teachers → [Teacher Name] → Assign Modules

**Steps:**
1. Select teacher from list
2. Available modules shown on left
3. Select modules to assign:
   - Click module code
   - System shows coefficient and students
   - Confirm assignment

4. Save assignments

**Teacher can then:**
- Enter grades for assigned modules only
- See only students in their assigned classes
- Generate reports for their modules

---

## Grade Management

### Enter Grades Manually

**Path:** Grades → Manual Entry

**Steps:**
1. Select module from dropdown
2. Select class/group
3. Choose entry method:
   - **Single Grade:** Enter one student
   - **Batch Entry:** Enter all students at once
   - **Excel Import:** Upload spreadsheet

4. For manual entry:
   - Student code displays
   - Enter score (0-20)
   - System flags eliminating grades (≤5) in red
   - Press Enter or Tab to move to next

5. Click "Save All Grades"

**System checks:**
- All fields filled
- Scores valid (0-20)
- No duplicate entries
- Flags any eliminating grades

### Import Grades from Excel

**Path:** Grades → Import from Excel

**File Format Required:**
```
Column A: Student Code (e.g., TS00001)
Column B: Module Code (e.g., M201)
Column C: Score (e.g., 15.5)
```

**Example Excel Content:**
```
TS00001    M201    15.50
TS00001    M202    12.00
TS00002    M201    18.75
```

**Steps:**
1. Prepare Excel file with above format
2. Go to Grades → Import from Excel
3. Click "Choose File"
4. Select Excel file
5. Click "Preview Import"
6. System shows:
   - Number of grades to import
   - Any errors detected
   - Eliminating grades highlighted

7. Click "Confirm Import"
8. System inserts grades into database

**Supported Formats:**
- .xlsx (Excel 2007+)
- .xls (Excel 97-2003)
- .csv (comma-separated values)

**Error Handling:**
If errors detected:
- System shows error line numbers
- Fix Excel file
- Re-upload for retry

### Recalculate Grades for Student

**Path:** Grades → Student Details → [Student] → Recalculate

**When Needed:**
- After grade corrections
- When module coefficients change
- For missing calculation records

**Process:**
1. Find student
2. Click "Recalculate NGR"
3. System:
   - Fetches all grades for student
   - Applies current formula
   - Updates calculations table
   - Shows new NGR and decision

4. Confirm if changes look correct

### Bulk Grade Operations

**Path:** Grades → Bulk Actions

**Available Operations:**
1. **Lock Grades** - Prevent teacher edits
   - Select grades
   - Click "Lock"
   - Reason (optional)

2. **Approve Grades** - Mark as reviewed
   - Select grades
   - Click "Approve"
   - Email notification sent

3. **Delete Grades** - Remove entries
   - Select grades
   - Click "Delete"
   - Confirm action
   - Audit log entry created

4. **Duplicate Grades** - Copy to another cohort
   - Select source grades
   - Select destination cohort
   - Click "Duplicate"

### Manage Eliminating Grades

**Path:** Alerts → Eliminating Grades

**Display Shows:**
- All grades ≤ 5
- Student code and module
- Entry date and teacher
- Current decision status

**Actions Available:**
1. **Review** - View full student context
2. **Flag for Decision** - Mark for committee
3. **Notify Teacher** - Send message
4. **Override** - Change score and recalculate

**Example Alert:**
```
⚠️ Eliminating Grade Detected
Student: TS00042
Module: M205
Score: 4.50
Teacher: prof_smith
Entry Date: 2025-03-15
Decision Impact: Fails class
```

---

## Reporting

### Generate PV de Délibération

**Path:** Reports → PV de Délibération

**Steps:**
1. Select cohort (e.g., "2024-2025")
2. Select group (optional, for single class)
3. Click "Generate Report"
4. System creates PDF with:
   - Title and date
   - Student list (code only)
   - NGR and decision for each
   - Summary statistics
   - Signature lines

5. Download or print
6. PDF includes:
   - Institution name and logo
   - Official letterhead
   - Proper formatting for committee

### Generate Student Transcripts

**Path:** Reports → Student Transcripts

**Steps:**
1. Select student from list
2. Click "Generate Transcript"
3. System creates PDF with:
   - Student info (anonymized)
   - All grades by module
   - Module coefficients
   - Calculated averages
   - Final NGR and decision
   - Date signed

4. Email to student or print

**Transcript Includes:**
- Module codes and names
- Individual scores
- Coefficient weights
- Weighted contributions
- Total NGR formula shown
- Official decision

### Export Data

**Path:** Reports → Export

**Available Formats:**

**1. Excel Export**
- Select data type (grades, students, reports)
- Select filters (cohort, program, etc.)
- Click "Export"
- Downloads .xlsx file with multiple sheets

**2. JSON Export**
- Full data backup
- Anonymization options
- Click "Export to JSON"
- Downloads timestamped file

**3. CSV Export**
- Raw data for external analysis
- Open in any spreadsheet app
- Preserves data integrity

**Example CSV:**
```
student_code,module_code,score,coefficient,avg_1a,avg_2a,stages,eff,ngr_final,decision
TS00001,M201,15.50,1.0,14.2,16.8,18.0,15.5,16.15,ADMIS
TS00001,M202,12.00,1.0,14.2,16.8,18.0,15.5,16.15,ADMIS
```

### Dashboard Reports

**Path:** Reports → Dashboard

**Available Charts:**
1. **Grade Distribution** - Histogram of all NGR scores
2. **Decision Summary** - Count of ADMIS/AJOURNÉ/RATTRAPAGE
3. **Program Comparison** - Performance by program
4. **Eliminating Grades Trend** - Count over time
5. **Completion Rate** - % of grades entered by module

**Export Options:**
- Print charts
- Download as image
- Include in reports

---

## System Configuration

### Program Management

**Path:** Settings → Programs

**View Programs:**
- OTM-A: Techniques d'Optimisation Logistique
- OTM-B: Techniques d'Optimisation Logistique
- OFLP: Opérations Frêt et Logistique Portuaire
- AEL: Achat et Économie de la Logistique
- ECOM: E-Commerce et Distribution
- ADEE: Approvisionnement et Distribution d'Énergie

**Edit Program:**
1. Click program name
2. Update:
   - Description
   - Codes and modules
   - Decision thresholds (if different)

3. Save changes

### Module Management

**Path:** Settings → Modules

**View Modules:**
- Lists all modules
- Shows coefficient
- Shows assigned students
- Shows grades entered

**Edit Module:**
1. Click module code
2. Update:
   - Name
   - Coefficient
   - Max score
   - Program assignment

3. Click "Update"

**Add Module:**
1. Click "Add Module"
2. Fill in:
   - Code (e.g., M225)
   - Name
   - Program
   - Coefficient
   - Max score

3. Click "Create"

### Grade Calculation Settings

**Path:** Settings → Calculation Formula

**Current Formula:**
```
NGR = (Moy1A × 20%) + (Moy2A × 30%) + (Stages × 30%) + (EFF × 20%)
```

**View:**
- All weight percentages
- Total (should = 100%)
- Applied to: All programs

**Modify (if needed):**
1. ⚠️ Change weights carefully!
2. Update percentage values
3. Verify total = 100%
4. Click "Save"
5. Choose: Apply to new calculations only OR recalculate all

**Thresholds:**
- Passing NGR: 10 (configurable)
- Eliminating Grade: ≤5 (configurable)

---

## Backup & Recovery

### Automatic Backups

**Status:** Enabled by default

**Schedule:**
- Frequency: Daily
- Time: 2:00 AM server time
- Retention: 90 days
- Location: cPanel backup storage

**View Backups:**
1. cPanel → Backup Wizard
2. Check "Full Backups" section
3. List shows date and size

### Manual Backup

**Create Backup:**

```bash
# Via SSH/Terminal
mysqldump -u iftl_user -p notes_iftl_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Via Admin Dashboard
Backups → Create Backup → Confirm
```

**File Generated:**
- Format: `backup_20250315_143022.sql`
- Contains: All tables and data
- Size: ~50 MB typical

### Export JSON Backup

**Path:** Reports → Export → JSON Backup

**Creates:**
- Anonymized student data
- All grades and calculations
- System configuration
- Formatted as JSON

**Use Cases:**
- Long-term archival
- External analysis
- System migration
- Data exchange with other systems

**Steps:**
1. Click "Export to JSON"
2. Choose anonymization level:
   - Full (remove all names)
   - Partial (keep student codes)
   - None (keep all data)

3. Download file

### Restore from Backup

**⚠️ WARNING: This will overwrite current data!**

**Process:**

```bash
# Via SSH/Terminal
mysql -u iftl_user -p notes_iftl_db < backup_20250315.sql
```

**Verification After Restore:**
1. Check student count: `SELECT COUNT(*) FROM students;`
2. Check grades: `SELECT COUNT(*) FROM grades;`
3. Test login with known account
4. Verify recent grades are present

---

## Audit Logging

### View Audit Log

**Path:** Settings → Audit Log

**Displays:**
- User who performed action
- Action type (LOGIN, GRADE_ENTRY, EXPORT, etc.)
- Record type (grades, users, etc.)
- Timestamp
- Details

**Example Entries:**
```
2025-03-15 10:30:45  admin      GRADE_ENTRY     Student TS00042   Module M205, Score: 4.5
2025-03-15 10:25:12  prof_math  LOGOUT          User prof_math
2025-03-15 10:20:00  admin      USER_CREATED    User prof_bio
```

### Filter Audit Log

**Available Filters:**
- Date range
- User
- Action type
- Record type
- Record ID

**Example Query:**
```
Show all GRADE_ENTRY actions by prof_math between March 1-15, 2025
```

### Export Audit Log

**Path:** Audit Log → Export

**Formats:**
- CSV - Open in Excel
- JSON - Machine readable
- PDF - For compliance

---

## System Monitoring

### Check System Health

**Path:** Dashboard → System Health

**Displays:**
- ✓ Database connection
- ✓ File permissions
- ✓ Disk space available
- ✓ PHP version
- ✓ MySQL version
- ✓ SSL certificate status

**Issues Shown:**
- ⚠️ Disk space low
- ⚠️ SSL certificate expiring
- ❌ Database connection failed
- ❌ Missing file permissions

### Database Maintenance

**Path:** Settings → Database Maintenance

**Available Operations:**

1. **Check Tables**
   - Verifies table integrity
   - Reports errors
   - Shows status

2. **Optimize Tables**
   - Reduces database size
   - Improves query performance
   - Takes 5-10 minutes

3. **Repair Tables**
   - Fixes corrupted tables
   - ⚠️ Use only if needed
   - Create backup first!

4. **View Table Stats**
   - Rows in each table
   - Size on disk
   - Index information

---

## Troubleshooting

### Login Issues

**Problem:** Admin cannot login
**Solution:**
1. Verify account is active: `SELECT active FROM users WHERE username='admin';`
2. Reset password:
   ```bash
   mysql -u iftl_user -p notes_iftl_db
   UPDATE users SET password_hash=PASSWORD('newpassword') WHERE username='admin';
   ```
3. Clear browser cache
4. Try different browser

### Grade Calculation Wrong

**Problem:** NGR calculation doesn't match expected
**Solution:**
1. Verify all required grades entered
2. Check module coefficients: `SELECT * FROM modules WHERE program_id=1;`
3. Verify formula weights sum to 100%
4. Recalculate for specific student
5. Check audit log for recent changes

### Database Errors

**Problem:** "Database connection failed"
**Solution:**
1. Check MySQL is running: Contact hosting provider
2. Verify credentials in index.php
3. Test connection:
   ```bash
   mysql -u iftl_user -p notes_iftl_db -e "SELECT 1"
   ```
4. Check cPanel MySQL status
5. Review error.log: `/logs/error.log`

### Performance Issues

**Problem:** System is slow
**Solution:**
1. Check database size: `SELECT table_name, size FROM tables;`
2. Run optimize tables
3. Review error log for slow queries
4. Check server CPU/memory usage
5. Reduce concurrent users
6. Archive old data

### Backup Failed

**Problem:** Backup not created
**Solution:**
1. Check disk space: `df -h`
2. Verify backup directory permissions
3. Check backup logs in cPanel
4. Manual backup:
   ```bash
   mysqldump -u iftl_user -p notes_iftl_db > /tmp/backup.sql
   ```

---

## Best Practices

### Security
- ✓ Change default passwords immediately
- ✓ Use strong passwords (12+ characters)
- ✓ Rotate admin passwords monthly
- ✓ Enable SSL certificate auto-renewal
- ✓ Review audit log weekly

### Data Management
- ✓ Backup daily (automatic)
- ✓ Export JSON monthly
- ✓ Test restore quarterly
- ✓ Archive old cohorts annually
- ✓ Maintain data integrity checks

### Performance
- ✓ Optimize database monthly
- ✓ Monitor disk space weekly
- ✓ Limit concurrent users
- ✓ Cache frequently accessed data
- ✓ Archive logs older than 1 year

### Compliance
- ✓ Maintain audit trail
- ✓ Document all changes
- ✓ Verify all grades quarterly
- ✓ Review access logs regularly
- ✓ Keep disaster recovery plan updated

---

## Quick Reference

| Task | Path | Command |
|------|------|---------|
| Create user | User Mgmt → Add User | See form |
| Enter grade | Grades → Manual Entry | Enter scores |
| Import grades | Grades → Import Excel | Upload file |
| Generate PV | Reports → PV | Select cohort |
| Export data | Reports → Export | Choose format |
| Backup | Settings → Backup | Click button |
| View logs | Settings → Audit Log | Filter as needed |
| Recalculate | Grades → Recalculate | Select student |

---

**Manual Version:** 1.0
**Last Updated:** 2026-03-17
**For Support:** See README_SYSTEM.md
