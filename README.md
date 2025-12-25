# eDonation NurseCMU

ระบบบริจาคออนไลน์สำหรับคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่

## 📁 Project Structure

```
edonation/
├── web/              # Frontend Website (Public)
├── api/              # REST API Backend
├── admin/            # Admin Dashboard
├── shared/           # Shared Services
├── .env              # Environment Configuration (Main)
├── .env.example      # Environment Template
└── .env.production   # Production Config Template
```

## 🌐 URLs

| Environment | Web | API | Admin |
|-------------|-----|-----|-------|
| **Production** | https://app.nurse.cmu.ac.th/edonation | https://app.nurse.cmu.ac.th/edonation/api | https://app.nurse.cmu.ac.th/edonation/admin |
| **Development** | http://localhost/appdev/edonation | http://localhost/appdev/edonation/api | http://localhost/appdev/edonation/admin |

## 🚀 Getting Started

### Prerequisites

- XAMPP (PHP 8.0+, MySQL/MariaDB)
- Composer
- Modern Web Browser
- Node.js 16+ (for admin assets)

### Installation

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd edonation
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Install Dependencies (Web)**
   ```bash
   cd web
   composer install
   ```

4. **Setup Database**
   - Import database schema (see `web/database/`)
   - Configure database credentials in `.env`

5. **Start XAMPP**
   - Start Apache and MySQL services
   - Access: http://localhost/appdev/edonation/

## 📚 API Documentation

### Base URL
- **Production**: `https://app.nurse.cmu.ac.th/edonation/api/v1`
- **Development**: `http://localhost/appdev/edonation/api/v1`

### API Manager
เปิด API Manager เพื่อทดสอบ endpoints:
- `http://localhost/appdev/edonation/api/docs/`

---

## 📋 API Endpoints

### 🏛️ Projects - โครงการบริจาค

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/projects` | รายการโครงการทั้งหมด | - |
| `GET` | `/projects/:id` | รายละเอียดโครงการ | - |
| `POST` | `/projects` | สร้างโครงการใหม่ | Admin |
| `PUT` | `/projects/:id` | แก้ไขโครงการ | Admin |

---

### 💰 Donations - การบริจาค

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/donations` | สร้างรายการบริจาค (รองรับ split address, title) | - |
| `GET` | `/donations/:id/qr` | ดึง QR Code PromptPay | - |
| `GET` | `/donations/:id/status` | ตรวจสอบสถานะการชำระเงิน | - |
| `GET` | `/donations` | รายการบริจาคทั้งหมด | Admin |
| `GET` | `/donations/:id` | รายละเอียดการบริจาค | Admin |
| `PUT` | `/donations/:id` | แก้ไขการบริจาค | Admin |

---

### 🧾 Receipts - ใบเสร็จรับเงิน

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/receipts/search?keyword=xxx` | ค้นหาใบเสร็จด้วยเลขบัตรหรือเลขที่ใบเสร็จ | - |
| `GET` | `/receipts/:id` | รายละเอียดใบเสร็จ | - |
| `GET` | `/receipts/:id/verify?tax_id=xxx` | ยืนยันเลขประจำตัวผู้เสียภาษี | - |
| `GET` | `/receipts/:id/pdf` | ดาวน์โหลด PDF (ต้อง verify ก่อน) | Token |
| `GET` | `/receipts/:id/details` | ข้อมูลใบเสร็จสำหรับ PDF | - |
| `GET` | `/receipts` | รายการใบเสร็จทั้งหมด | Admin |
| `POST` | `/receipts/generate` | ออกใบเสร็จ manual | Admin |
| `POST` | `/receipts/:id/cancel` | ยกเลิกใบเสร็จ | Admin |
| `POST` | `/receipts/:id/resend` | ส่งใบเสร็จซ้ำ | Admin |

---

### 👤 Members - สมาชิก (ผู้บริจาค)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/members/lookup?id_card=xxx` | ค้นหาสมาชิกจากเลขบัตร | - |
| `GET` | `/members/:id_card` | ข้อมูลสมาชิก | - |
| `GET` | `/members/:id_card/donations` | รายการบริจาคของสมาชิก | - |
| `GET` | `/members/:id_card/receipts` | รายการใบเสร็จของสมาชิก | - |
| `GET` | `/members/:id_card/summary` | สรุปยอดบริจาค | - |

---

### 🔐 Auth - ระบบยืนยันตัวตน

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/auth/login` | เข้าสู่ระบบ | - |
| `POST` | `/auth/oauth/cmu` | CMU OAuth Login | - |
| `POST` | `/auth/logout` | ออกจากระบบ | - |
| `GET` | `/auth/me` | ข้อมูลผู้ใช้ปัจจุบัน | Bearer |

---

### 💳 Payments - การชำระเงิน

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/payments/callback` | รับ Callback จากธนาคาร (SCB) | - |

---

### 🏆 Benefits - ระดับผู้มีอุปการคุณ

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/benefits` | รายการระดับทั้งหมด | - |
| `GET` | `/benefits/:id` | รายละเอียดระดับ | - |
| `POST` | `/benefits` | เพิ่มระดับใหม่ | Admin |
| `PUT` | `/benefits/:id` | แก้ไขระดับ | Admin |
| `DELETE` | `/benefits/:id` | ลบระดับ | Admin |

---

### 📰 News - ข่าวสาร

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/news` | รายการข่าวทั้งหมด | - |
| `GET` | `/news/:id` | รายละเอียดข่าว | - |
| `POST` | `/news` | เพิ่มข่าวใหม่ | Admin |
| `PUT` | `/news/:id` | แก้ไขข่าว | Admin |
| `DELETE` | `/news/:id` | ลบข่าว | Admin |
| `POST` | `/news/upload` | อัพโหลดรูปภาพข่าว | Admin |

---

### ✍️ Signatures - ลายเซ็นใบเสร็จ

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/signatures` | รายการลายเซ็นทั้งหมด | - |
| `GET` | `/signatures/:year` | ลายเซ็นตามปีงบประมาณ (พ.ศ.) | - |
| `POST` | `/signatures` | เพิ่มลายเซ็นใหม่ | Admin |
| `PUT` | `/signatures/:year` | แก้ไขลายเซ็น | Admin |
| `DELETE` | `/signatures/:year` | ลบลายเซ็น | Admin |

---

### 🔔 Notifications - การแจ้งเตือน

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/notifications/send` | ส่งการแจ้งเตือนทั่วไป | Admin |
| `POST` | `/notifications/email` | ส่งใบเสร็จทางอีเมล | Admin |
| `POST` | `/notifications/line` | ส่งข้อความ LINE | Admin |

---

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Environment (development/production) | production |
| `APP_URL` | Base URL | https://app.nurse.cmu.ac.th/edonation |
| `BASE_PATH` | Base path for URLs | /edonation |
| `DB_HOST` | Database host | localhost |
| `DB_NAME` | Database name | edonation |
| `DB_USER` | Database user | root |
| `DB_PASS` | Database password | - |
| `JWT_SECRET` | Secret key for JWT | (generated) |
| `JWT_EXPIRE` | JWT expiration in seconds | 86400 |
| `LINE_TOKEN` | LINE Notify token | - |
| `SCB_API_KEY` | SCB API Key | - |
| `SCB_API_SECRET` | SCB API Secret | - |

### Web Sections

| Path | Description |
|------|-------------|
| `/` | หน้าแรก |
| `/donat/` | ฟอร์มบริจาค |
| `/list/` | ค้นหาใบเสร็จ |
| `/office/` | ระบบจัดการ (Admin Legacy) |

## 🛡️ Security

- All database queries use PDO prepared statements
- Input sanitization with `filter_input()`
- Session-based authentication with Azure AD
- JWT tokens for API authentication
- CSRF Token protection
- Password hashing with `password_hash()`

## 🔄 Authentication Flow

### JWT Authentication
```
POST /api/v1/auth/login
{
    "username": "admin@cmu.ac.th",
    "password": "password"
}

Response:
{
    "success": true,
    "data": {
        "access_token": "eyJ0eXAiOi...",
        "token_type": "Bearer",
        "expires_in": 86400,
        "user": { ... }
    }
}
```

### Using Token
```
Authorization: Bearer <access_token>
```

## 📝 License

Copyright © 2025 Faculty of Nursing, Chiang Mai University
