# 📋 Implementation Plan: eDonation Project Restructure

## 🎯 วัตถุประสงค์
ปรับโครงสร้างโปรเจ็ค eDonation จากเดิมที่เป็น Monolithic PHP เป็นโครงสร้างแบบ 3-Tier Architecture:

1. **Web** - หน้าเว็บสาธารณะสำหรับผู้บริจาค
2. **API** - REST API สำหรับจัดการข้อมูล
3. **Admin** - Admin Panel สำหรับเจ้าหน้าที่

---

## 📁 โครงสร้างโปรเจ็คใหม่

```
c:\xampp\htdocs\appdev\edonation\
├── web/                          # Public Website
│   ├── index.php                 # หน้าแรก
│   ├── donate/                   # หน้าบริจาค
│   │   ├── index.php             # แบบฟอร์มบริจาค
│   │   ├── qr-payment.php        # QR Code Payment
│   │   └── thank-you.php         # หน้าขอบคุณ
│   ├── receipt/                  # ใบเสร็จ
│   │   ├── search.php            # ค้นหาใบเสร็จ
│   │   └── view.php              # ดูใบเสร็จ PDF
│   ├── assets/                   # Static files
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── templates/                # UI Templates
│       ├── header.php
│       ├── footer.php
│       └── components/
│
├── api/                          # REST API
│   ├── index.php                 # API Router
│   ├── .htaccess                 # URL Rewriting
│   ├── config/
│   │   ├── database.php          # Database connection
│   │   └── cors.php              # CORS settings
│   ├── controllers/              # API Controllers
│   │   ├── DonationController.php
│   │   ├── ReceiptController.php
│   │   ├── ProjectController.php
│   │   └── WebhookController.php
│   ├── models/                   # Data Models
│   │   ├── Donation.php
│   │   ├── Receipt.php
│   │   └── Project.php
│   ├── middleware/               # Middleware
│   │   ├── AuthMiddleware.php
│   │   ├── RateLimitMiddleware.php
│   │   └── CorsMiddleware.php
│   ├── services/                 # Business Logic
│   │   ├── PaymentService.php
│   │   ├── PdfService.php
│   │   ├── EmailService.php
│   │   └── LineService.php
│   └── utils/                    # Utilities
│       ├── Response.php
│       ├── Validator.php
│       └── QrGenerator.php
│
├── admin/                        # Admin Panel
│   ├── index.php                 # Dashboard
│   ├── login.php                 # Login page
│   ├── logout.php                # Logout
│   ├── donations/                # จัดการบริจาค
│   │   ├── index.php             # รายการบริจาค
│   │   ├── view.php              # ดูรายละเอียด
│   │   └── edit.php              # แก้ไข
│   ├── receipts/                 # จัดการใบเสร็จ
│   │   ├── index.php             # รายการใบเสร็จ
│   │   ├── generate.php          # ออกใบเสร็จ
│   │   └── cancel.php            # ยกเลิกใบเสร็จ
│   ├── projects/                 # จัดการโครงการ
│   │   ├── index.php
│   │   ├── create.php
│   │   └── edit.php
│   ├── reports/                  # รายงาน
│   │   ├── daily.php
│   │   ├── monthly.php
│   │   └── export.php
│   ├── assets/                   # Admin assets
│   ├── templates/                # Admin templates
│   └── includes/                 # Admin includes
│       ├── session.php
│       ├── auth.php
│       └── sidebar.php
│
├── shared/                       # Shared Components
│   ├── config/
│   │   ├── app.php               # App configuration
│   │   └── database.php          # Database config
│   ├── libs/
│   │   ├── TCPDF/                # PDF Library
│   │   └── phpqrcode/            # QR Code Library
│   └── helpers/
│       ├── functions.php
│       └── security.php          # Security helpers
│
├── storage/                      # File Storage
│   ├── logs/                     # Log files
│   ├── cache/                    # Cache files
│   └── uploads/                  # User uploads
│
├── .env                          # Environment variables
├── .env.example                  # Environment example
├── .htaccess                     # Main rewrite rules
├── composer.json                 # PHP dependencies
└── README.md                     # Documentation
```

---

## 🔧 Phase 1: Setup Foundation (วันที่ 1-2)

### Task 1.1: สร้างโครงสร้างโฟลเดอร์ใหม่
- [ ] สร้าง folders: `web/`, `api/`, `admin/`, `shared/`, `storage/`
- [ ] ย้าย TCPDF และ phpqrcode ไป `shared/libs/`
- [ ] สร้าง `.env` file และ `.env.example`

### Task 1.2: Setup Shared Configuration
- [ ] สร้าง `shared/config/app.php` - Application settings
- [ ] สร้าง `shared/config/database.php` - PDO connection with .env
- [ ] สร้าง `shared/helpers/security.php` - Security functions

### Task 1.3: Setup Environment Variables
```php
// .env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/appdev/edonation

DB_HOST=localhost
DB_NAME=edonation
DB_USER=edonation
DB_PASS=your_secure_password

LINE_TOKEN=your_line_token
GMAIL_USER=your_email
GMAIL_PASS=your_app_password
```

---

## 🌐 Phase 2: Build API Layer (วันที่ 3-5)

### Task 2.1: API Router & Core
- [ ] สร้าง `api/index.php` - Main router
- [ ] สร้าง `api/.htaccess` - URL rewriting
- [ ] สร้าง `api/config/cors.php` - CORS configuration
- [ ] สร้าง `api/utils/Response.php` - JSON response helper

### Task 2.2: API Endpoints Design
```
# Donations
GET    /api/donations           # รายการบริจาคทั้งหมด
GET    /api/donations/:id       # รายละเอียดบริจาค
POST   /api/donations           # สร้างรายการบริจาค
PUT    /api/donations/:id       # อัปเดตบริจาค
DELETE /api/donations/:id       # ลบบริจาค

# Receipts
GET    /api/receipts            # รายการใบเสร็จ
GET    /api/receipts/:id        # รายละเอียดใบเสร็จ
POST   /api/receipts/generate   # ออกใบเสร็จ
POST   /api/receipts/:id/cancel # ยกเลิกใบเสร็จ
GET    /api/receipts/:id/pdf    # ดาวน์โหลด PDF

# Projects
GET    /api/projects            # รายการโครงการ
GET    /api/projects/:id        # รายละเอียดโครงการ
POST   /api/projects            # สร้างโครงการ
PUT    /api/projects/:id        # อัปเดตโครงการ

# Webhook (for payment callback)
POST   /api/webhook/payment     # รับ callback จากธนาคาร

# Auth (for admin)
POST   /api/auth/login          # เข้าสู่ระบบ
POST   /api/auth/logout         # ออกจากระบบ
GET    /api/auth/me             # ข้อมูลผู้ใช้ปัจจุบัน
```

### Task 2.3: Build Controllers
- [ ] `DonationController.php` - CRUD donations
- [ ] `ReceiptController.php` - Receipt management
- [ ] `ProjectController.php` - Project management
- [ ] `WebhookController.php` - Payment webhook
- [ ] `AuthController.php` - Authentication

### Task 2.4: Build Services
- [ ] `PaymentService.php` - QR payment logic
- [ ] `PdfService.php` - PDF generation (wrap TCPDF)
- [ ] `EmailService.php` - Email sending
- [ ] `LineService.php` - LINE notification

### Task 2.5: Middleware
- [ ] `AuthMiddleware.php` - JWT/Session authentication
- [ ] `RateLimitMiddleware.php` - Rate limiting
- [ ] `CorsMiddleware.php` - CORS handling

---

## 🖥️ Phase 3: Build Web Frontend (วันที่ 6-8)

### Task 3.1: Base Templates
- [ ] `web/templates/header.php` - Common header
- [ ] `web/templates/footer.php` - Common footer
- [ ] `web/assets/css/style.css` - Modern CSS

### Task 3.2: Public Pages
- [ ] `web/index.php` - Landing page
- [ ] `web/donate/index.php` - Donation form
- [ ] `web/donate/qr-payment.php` - QR code display
- [ ] `web/donate/thank-you.php` - Thank you page
- [ ] `web/receipt/search.php` - Search receipts
- [ ] `web/receipt/view.php` - View receipt

### Task 3.3: JavaScript Integration
- [ ] สร้าง `web/assets/js/api-client.js` - API client
- [ ] สร้าง `web/assets/js/donation-form.js` - Form handling
- [ ] สร้าง `web/assets/js/qr-payment.js` - QR polling

---

## 👨‍💼 Phase 4: Build Admin Panel (วันที่ 9-12)

### Task 4.1: Authentication
- [ ] `admin/login.php` - OAuth/CMU Account login
- [ ] `admin/logout.php` - Logout
- [ ] `admin/includes/auth.php` - Auth middleware

### Task 4.2: Dashboard
- [ ] `admin/index.php` - Dashboard with stats

### Task 4.3: Donation Management
- [ ] `admin/donations/index.php` - List with filters
- [ ] `admin/donations/view.php` - Detail view
- [ ] `admin/donations/edit.php` - Edit form

### Task 4.4: Receipt Management
- [ ] `admin/receipts/index.php` - Receipt list
- [ ] `admin/receipts/generate.php` - Generate receipt
- [ ] `admin/receipts/cancel.php` - Cancel receipt

### Task 4.5: Reports
- [ ] `admin/reports/daily.php` - Daily report
- [ ] `admin/reports/monthly.php` - Monthly summary
- [ ] `admin/reports/export.php` - Export to Excel

---

## 🔒 Phase 5: Security Hardening (วันที่ 13-14)

### Task 5.1: Input Validation
- [ ] ทุก input ต้องผ่าน validation
- [ ] ใช้ prepared statements เท่านั้น
- [ ] Sanitize output ด้วย htmlspecialchars()

### Task 5.2: Authentication & Authorization
- [ ] Implement JWT for API
- [ ] Session management for Admin
- [ ] Role-based access control

### Task 5.3: Security Headers
- [ ] CSRF tokens ทุกฟอร์ม
- [ ] HTTP Security Headers
- [ ] Rate limiting

### Task 5.4: Logging & Monitoring
- [ ] Request logging
- [ ] Error logging
- [ ] Security event logging

---

## 🧪 Phase 6: Testing & Deployment (วันที่ 15-16)

### Task 6.1: Testing
- [ ] Test all API endpoints
- [ ] Test payment flow
- [ ] Test PDF generation
- [ ] Security testing

### Task 6.2: Documentation
- [ ] API documentation
- [ ] Admin guide
- [ ] Deployment guide

### Task 6.3: Migration
- [ ] Backup existing data
- [ ] Test migration scripts
- [ ] Deploy to production

---

## 📊 Progress Tracking

| Phase | Description | Status | Start Date | End Date |
|-------|-------------|--------|------------|----------|
| 1 | Setup Foundation | ⬜ Not Started | - | - |
| 2 | Build API Layer | ⬜ Not Started | - | - |
| 3 | Build Web Frontend | ⬜ Not Started | - | - |
| 4 | Build Admin Panel | ⬜ Not Started | - | - |
| 5 | Security Hardening | ⬜ Not Started | - | - |
| 6 | Testing & Deployment | ⬜ Not Started | - | - |

---

## 🚀 เริ่มต้น

พร้อมที่จะเริ่ม Phase 1 หรือต้องการปรับแผนก่อน?
