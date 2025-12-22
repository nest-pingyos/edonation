# eDonation Admin Panel

ระบบจัดการการบริจาค คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่

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

### Complete API Endpoints

#### Projects - โครงการ
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/projects` | รายการโครงการ |
| GET | `/api/v1/projects/:id` | รายละเอียดโครงการ |
| POST | `/api/v1/projects` | สร้างโครงการ |
| PUT | `/api/v1/projects/:id` | แก้ไขโครงการ |

#### Donations - การบริจาค
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/donations` | รายการบริจาค |
| GET | `/api/v1/donations/:id` | รายละเอียด |
| PUT | `/api/v1/donations/:id` | แก้ไข |

#### Receipts - ใบเสร็จ
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/receipts` | รายการใบเสร็จ |
| POST | `/api/v1/receipts/generate` | ออกใบเสร็จ manual |
| POST | `/api/v1/receipts/:id/cancel` | ยกเลิกใบเสร็จ |
| POST | `/api/v1/receipts/:id/resend` | ส่งใบเสร็จซ้ำ |

#### Members - สมาชิก
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/members/lookup` | ค้นหาสมาชิก |
| GET | `/api/v1/members/:id_card` | ข้อมูลสมาชิก |
| GET | `/api/v1/members/:id_card/donations` | รายการบริจาคของสมาชิก |
| GET | `/api/v1/members/:id_card/receipts` | รายการใบเสร็จของสมาชิก |
| GET | `/api/v1/members/:id_card/summary` | สรุปยอดบริจาค |

#### Benefits - ระดับผู้มีอุปการคุณ
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/benefits` | รายการระดับ |
| GET | `/api/v1/benefits/:id` | รายละเอียด |
| POST | `/api/v1/benefits` | เพิ่มระดับ |
| PUT | `/api/v1/benefits/:id` | แก้ไขระดับ |
| DELETE | `/api/v1/benefits/:id` | ลบระดับ |

#### News - ข่าวสาร
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/news` | รายการข่าว |
| GET | `/api/v1/news/:id` | รายละเอียด |
| POST | `/api/v1/news` | เพิ่มข่าว |
| PUT | `/api/v1/news/:id` | แก้ไขข่าว |
| DELETE | `/api/v1/news/:id` | ลบข่าว |
| POST | `/api/v1/news/upload` | อัพโหลดรูปภาพ |

#### Signatures - ลายเซ็น
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/signatures` | รายการลายเซ็น |
| GET | `/api/v1/signatures/:year` | ตามปีงบประมาณ |
| POST | `/api/v1/signatures` | เพิ่มลายเซ็น |
| PUT | `/api/v1/signatures/:year` | แก้ไขลายเซ็น |
| DELETE | `/api/v1/signatures/:year` | ลบลายเซ็น |

#### Notifications - การแจ้งเตือน
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/notifications/send` | ส่งแจ้งเตือนทั่วไป |
| POST | `/api/v1/notifications/email` | ส่งอีเมล |
| POST | `/api/v1/notifications/line` | ส่ง LINE |

## 🔒 Security

- ใช้ password_hash() สำหรับเก็บรหัสผ่าน
- CSRF Token protection
- Session timeout (1 ชั่วโมง)
- Prepared statements ป้องกัน SQL Injection
- JWT Token สำหรับ API Authentication

## 📝 License

© 2025 Chiang Mai University
