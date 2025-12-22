# eDonation Admin Panel

ระบบจัดการการบริจาค มหาวิทยาลัยเชียงใหม่

## 📋 ข้อกำหนดเบื้องต้น

- PHP 8.0+
- MySQL 5.7+
- Node.js 16+ (สำหรับ build assets)
- Apache with mod_rewrite

## 🚀 การติดตั้ง

### 1. ติดตั้ง Dependencies

```bash
cd admin
npm install
# หรือ
yarn install
```

### 2. สร้าง Admin User

เปิดเบราว์เซอร์และเข้าไปที่:
```
http://localhost/appdev/edonation/admin/src/setup.php
```

หรือรันผ่าน command line:
```bash
cd admin/src
php setup.php
```

### 3. ลบไฟล์ Setup

⚠️ **สำคัญ**: หลังจากตั้งค่าเสร็จ ให้ลบไฟล์ `setup.php` ทิ้ง

### 4. เข้าสู่ระบบ

```
URL: http://localhost/appdev/edonation/admin/src/
Email: admin@edonation.cmu.ac.th
Password: admin@123
```

## 📁 โครงสร้างไฟล์

```
admin/
├── src/
│   ├── assets/          # CSS, JS, Images
│   ├── config/          # Configuration files
│   │   ├── config.php   # Main configuration
│   │   └── admin_users.sql  # SQL for admin table
│   ├── partials/        # Template partials
│   │   ├── main.php     # Session initialization
│   │   ├── title-meta.php
│   │   ├── head-css.php
│   │   ├── topbar.php
│   │   ├── edonation-nav.php  # eDonation custom menu
│   │   └── footer.php
│   ├── services/        # Backend services
│   │   ├── database.php # MySQL connection
│   │   └── session.php  # Session management
│   ├── index.php        # Dashboard
│   ├── auth-signin.php  # Login page
│   ├── logout.php       # Logout handler
│   └── setup.php        # Initial setup (DELETE after use!)
├── package.json
├── gulpfile.js
└── README.md
```

## 🔧 การพัฒนา

### Build Assets

```bash
# Development mode with watch
npm run dev

# Production build
npm run build
```

### ใช้ Menu ของ eDonation

ในไฟล์ที่ต้องการใช้ menu:

```php
<?php include 'partials/edonation-nav.php'; ?>
```

แทนที่จะใช้ `main-nav.php` เดิม

## 🔐 บทบาทผู้ใช้

| Role | Permission |
|------|------------|
| `super_admin` | จัดการทุกอย่าง + ผู้ใช้ระบบ |
| `admin` | จัดการการบริจาค, โครงการ, ข่าว |
| `editor` | แก้ไขข่าว, โครงการ |
| `viewer` | ดูรายงานเท่านั้น |

## 📊 API Integration

Admin panel เชื่อมต่อกับ API ที่:
```
/api/v1/
```

### Available Endpoints:
- `/api/v1/members` - ข้อมูลสมาชิก
- `/api/v1/donations` - การบริจาค
- `/api/v1/projects` - โครงการ
- `/api/v1/receipts` - ใบเสร็จ
- `/api/v1/news` - ข่าวสาร

## 🔒 Security

- ใช้ password_hash() สำหรับเก็บรหัสผ่าน
- CSRF Token protection
- Session timeout (1 ชั่วโมง)
- Prepared statements ป้องกัน SQL Injection

## 📝 License

© 2024 Chiang Mai University
