# Grade Management System - Institut Formation
## notes.iftl.ma Platform

A comprehensive web-based grade management system designed for educational institutions to manage student grades, calculate academic averages, generate official reports, and maintain complete academic records.

---

## 🎯 Features

### Core Features
- ✅ **Multi-role Authentication** - Admin, Coordinator, Teacher, Student roles
- ✅ **Grade Entry** - Manual entry or batch Excel import
- ✅ **Automatic Calculations** - NGR (Grade Point Average) with weighted formula
- ✅ **Real-time Alerts** - Flags eliminating grades (≤5) with visual highlighting
- ✅ **PV de Délibération** - Official meeting minutes generation
- ✅ **Student Transcripts** - Individual grade slips (Relevé de Notes)
- ✅ **Data Export** - JSON, Excel, and PDF formats
- ✅ **Audit Logging** - Complete action tracking for compliance
- ✅ **Data Privacy** - Anonymization options for reports

### Grade Calculation Formula
```
NGR = (Moy 1A × 20%) + (Moy 2A × 30%) + (Stages × 30%) + (EFF × 20%)

Decision Rules:
- ADMIS (Passed): NGR ≥ 10 AND no eliminating grades
- AJOURNÉ (Failed): NGR < 10 OR has eliminating grade
```

---

## 🚀 Quick Start

### Installation
1. Follow `DEPLOYMENT_GUIDE.md` for cPanel setup
2. Run `php init-db.php` to initialize database
3. Run `php seed-data.php` to load sample data
4. Access at `https://notes.iftl.ma`

### Default Accounts
- **Admin:** admin / admin123
- **Coordinator:** coordinator / coord123
- **Teacher:** teacher_demo / teacher123

**⚠️ Change passwords immediately after first login!**

---

## 📋 Architecture

**Stack:** PHP 7.4+ | MySQL | Bootstrap 5
**Hosting:** cPanel shared hosting
**Users:** 100+ concurrent supported
**Students:** 261 anonymized records

---

## 👥 User Roles

| Role | Permissions | Key Actions |
|------|-------------|-------------|
| **Admin** | Full access | Manage users, configure modules, import data, view logs |
| **Coordinator** | Grade validation | Generate reports, PV, approve grades |
| **Teacher** | Grade entry | Enter/import grades for assigned modules |
| **Student** | Read-only | View own grades, download transcripts, complete profile |

---

## 📊 Grade Calculation

Formula with weights:
- **Moy 1A:** 20%
- **Moy 2A:** 30%
- **Stages:** 30%
- **EFF (Final Exam):** 20%

**Decision Threshold:** NGR ≥ 10 = ADMIS (with no eliminating grades)

---

## 🔒 Security

- CSRF tokens on all forms
- SQL injection prevention (prepared statements)
- Session timeout (30 minutes)
- SSL/TLS encryption (HTTPS)
- Audit logging of all actions
- Role-based access control

---

## 📁 File Structure

```
/public_html/notes/
├── index.php                 # Main application
├── init-db.php              # Database setup
├── seed-data.php            # Sample data
├── assets/css/style.css     # Styling
├── assets/js/app.js         # Frontend logic
└── logs/                    # Error logs
```

---

## 📈 Performance

- **Page Load:** < 2 seconds
- **Concurrent Users:** 100+
- **Database Queries:** < 5 per request
- **Report Generation:** < 3 seconds

---

## 📝 Maintenance

- **Daily:** Monitor disk usage
- **Weekly:** Review logs
- **Monthly:** Export JSON backup
- **Quarterly:** Test backup restoration

---

## 🐛 Troubleshooting

### Database Connection Failed
→ Check credentials in `index.php` and cPanel database settings

### Pages Not Loading
→ Verify SSL certificate is active, check file permissions

### Grade Calculations Wrong
→ Verify all students have required grades, check module coefficients

See `DEPLOYMENT_GUIDE.md` for detailed troubleshooting.

---

## 📞 Support

- **Deployment:** See `DEPLOYMENT_GUIDE.md`
- **Admin Operations:** See `ADMIN_MANUAL.md`
- **API Details:** See `API_REFERENCE.md`
- **Errors:** Check `/logs/error.log`

---

## 📋 Data Privacy

- All personal data treated as confidential
- Anonymization options for exports
- HTTPS encryption for all communications
- Audit trails maintained for compliance
- Regular backups for disaster recovery

---

## 📄 License

MIT License - Copyright © 2026 Institut Formation

---

**Version:** 1.0.0 | **Status:** Production Ready | **Last Updated:** 2026-03-17
