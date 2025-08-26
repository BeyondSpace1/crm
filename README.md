# CRM RBAC Project - Complete Implementation

## Project Structure
```
crm-rbac/
├── index.php
├── config.php
├── setup.sql
├── classes/
│   ├── Database.php
│   ├── Router.php
│   ├── AuthService.php
│   ├── Entities/
│   │   ├── User.php
│   │   └── Contact.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   └── ContactRepository.php
│   ├── Services/
│   │   ├── AuditLogger.php
│   │   └── ContactsCsvService.php
│   └── Controllers/
│       ├── AuthController.php
│       ├── ContactController.php
│       └── AuditController.php
├── views/
│   ├── layout.php
│   ├── login.php
│   ├── contacts/
│   │   ├── list.php
│   │   ├── form.php
│   │   ├── view.php
│   │   ├── import_form.php
│   │   └── import_results.php
│   └── audit/
│       └── list.php
└── public/
    └── assets/
        ├── css/
        │   └── custom.css
        └── js/
            ├── particles-config.js
            ├── three-sphere.js
            └── intro-tour.js
```

## Setup Instructions

### 1. Database Setup
Create a MySQL database named `crm` and run the setup.sql script:
```sql
mysql -u root -p
CREATE DATABASE crm;
USE crm;
SOURCE setup.sql;
```

### 2. Configuration
Update `config.php` with your database credentials.

### 3. WAMP Setup
1. Copy the `crm-rbac` folder to your `D:\wamp\www\` directory
2. Access via `http://localhost/crm-rbac`
3. Default admin login: `admin@example.com` / `Admin@123`

### 4. Features Overview

#### Authentication & RBAC
- **Roles**: admin, editor, viewer
- **Session-based authentication**
- **CSRF protection on all forms**
- **Role-based route guarding**

#### Contacts Management
- **CRUD operations** with validation
- **Search and pagination** (10 items per page)
- **Soft delete** functionality
- **JSON tags** support
- **Email uniqueness** validation

#### CSV Import/Export
- **Export filtered results** as CSV
- **Two-phase import**: dry-run then commit
- **Transaction safety**
- **Detailed import results**

#### Audit Logging
- **Automatic logging** of all mutations
- **Admin-only audit view**
- **Change tracking** with JSON diff
- **Paginated audit trail**

#### Modern UI Features
- **Bootstrap 5.3.3** responsive design
- **Particles.js** interactive background
- **Three.js** metallic sphere animation
- **SweetAlert2** for confirmations
- **Intro.js** guided tour for new users
- **Vertical sidebar** navigation

#### Security Features
- **PDO prepared statements**
- **CSRF token validation**
- **Output escaping** with htmlspecialchars
- **Role-based access control**
- **Transaction safety**

### 5. First Time User Guide
When you first login, you'll see an interactive tour powered by Intro.js that guides you through:
1. Navigation sidebar
2. Contact management features
3. Import/Export functionality
4. Audit log access (admin only)

### 6. CDN Libraries Used
- **Bootstrap 5.3.3**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css
- **Particles.js 2.0.0**: https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js
- **Three.js 179.1**: https://cdnjs.cloudflare.com/ajax/libs/three.js/0.179.1/three.min.js
- **SweetAlert2 11.22.4**: https://cdn.jsdelivr.net/npm/sweetalert2@11.22.4/dist/sweetalert2.min.js
- **Intro.js 7.2.0**: https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js

### 7. API Endpoints (GET/POST Actions)
- `login` / `logout`
- `contacts.list` / `contacts.view` / `contacts.create` / `contacts.update` / `contacts.delete`
- `contacts.exportCsv` / `contacts.importCsvForm` / `contacts.importCsvDryRun` / `contacts.importCsvCommit`
- `audit.list` (admin only)

### 8. Known Limitations
- File uploads limited to 2MB for CSV imports
- Email validation using PHP filter_var
- Session timeout after 24 hours of inactivity
- Pagination limited to showing first 1000 pages

### 9. Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### 10. Security Considerations
- Always use HTTPS in production
- Regular database backups
- Monitor audit logs for suspicious activity
- Update dependencies regularly

---

This implementation provides a complete, production-ready CRM system with modern UI, robust security, and comprehensive functionality as specified in the requirements.