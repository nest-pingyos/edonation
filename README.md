# eDonation NurseCMU

<div align="center">

![Version](https://img.shields.io/badge/version-3.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)

**ระบบบริจาคออนไลน์สำหรับคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่**

[Demo](https://app.nurse.cmu.ac.th/edonation) · [API Docs](#-api-documentation) · [Report Bug](../../issues)

</div>

---

## 📁 Project Structure

```
edonation/
├── web/              # Frontend Website (Public)
├── api/              # REST API Backend
│   ├── controllers/  # API Controllers
│   ├── middleware/   # Authentication Middleware
│   ├── core/         # Core Classes (Router, Response, Database)
│   └── API_DOCS.md   # Full API Documentation
├── admin/            # Admin Dashboard (Vue.js-like SPA)
├── shared/           # Shared Services
├── .env              # Environment Configuration
└── README.md         # This file
```

## 🌐 URLs

| Environment | Web | API | Admin |
|-------------|-----|-----|-------|
| **Production** | https://app.nurse.cmu.ac.th/edonation | /api/v1 | /admin |
| **Development** | http://localhost/edonation | /api/v1 | /admin |

---

## 🚀 Getting Started

### Prerequisites

- XAMPP (PHP 8.0+, MySQL 8.0+)
- Modern Web Browser (Chrome, Firefox, Safari)
- Git

### Quick Start

```bash
# 1. Clone repository
git clone <repository-url>
cd edonation

# 2. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 3. Import database
mysql -u root -p donation < database/schema.sql

# 4. Start XAMPP (Apache + MySQL)
# Access: http://localhost/edonation/
```

---

## 📚 API Documentation

### Base URL
```
Production: https://app.nurse.cmu.ac.th/edonation/api/v1
Development: http://localhost/edonation/api/v1
```

### Authentication
Admin endpoints require `X-Admin-Token` header or session authentication.

```http
X-Admin-Token: your-admin-token
```

---

## 📋 API Endpoints

### 💰 Donations

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/donations` | สร้างรายการบริจาค | - |
| `POST` | `/donations/admin` | สร้างรายการจาก Admin | 🔐 |
| `GET` | `/donations/:id/qr` | ดึงข้อมูล QR Code | - |
| `GET` | `/donations/:id/status` | ตรวจสอบสถานะ | - |
| `GET` | `/donations` | รายการทั้งหมด | 🔐 |
| `GET` | `/donations/:id` | รายละเอียด | 🔐 |
| `PUT` | `/donations/:id` | แก้ไข | 🔐 |
| `DELETE` | `/donations/:id` | ลบ | 🔐 |

<details>
<summary><b>📝 Request/Response Examples</b></summary>

#### Create Donation
```http
POST /api/v1/donations
Content-Type: application/json

{
  "project_number": "PJ001",
  "phone": "0812345678",
  "amount": 1000,
  "type": "individual",
  "needReceipt": true,
  "firstName": "ทดสอบ",
  "lastName": "ระบบ",
  "idCard": "1234567890123",
  "receiptAddress": "123/45 ถ.สุเทพ อ.เมือง จ.เชียงใหม่"
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "id": 123,
    "billPaymentRef1": "256812345678901",
    "amount": 1000,
    "status": "pending",
    "qr_url": "/api/v1/donations/123/qr"
  }
}
```
</details>

---

### 🧾 Receipts

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/receipts` | รายการใบเสร็จ | 🔐 |
| `GET` | `/receipts/:id` | รายละเอียดใบเสร็จ | - |
| `GET` | `/receipts/:id/details` | ข้อมูลสำหรับ PDF | - |
| `GET` | `/receipts/:id/pdf` | ขอ URL ดาวน์โหลด PDF | Token |
| `GET` | `/receipts/:id/admin_pdf` | ดาวน์โหลด PDF (Admin) | 🔐 |
| `POST` | `/receipts/generate` | ออกใบเสร็จ | 🔐 |
| `POST` | `/receipts/:id/cancel` | ยกเลิกใบเสร็จ | 🔐 |

<details>
<summary><b>📝 Query Parameters</b></summary>

```http
GET /api/v1/receipts?page=1&limit=20&search=ทดสอบ&year=2569&project=PJ001
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | int | หน้าที่ต้องการ (default: 1) |
| `limit` | int | จำนวนต่อหน้า (max: 100) |
| `search` | string | ค้นหาเลขใบเสร็จ, ชื่อ |
| `year` | int | ปี พ.ศ. |
| `project` | string | รหัสโครงการ |
| `from` | date | วันที่เริ่มต้น (YYYY-MM-DD) |
| `to` | date | วันที่สิ้นสุด |
</details>

---

### 👤 Members (v3.0 - Updated!)

> **หมายเหตุ:** API Members ใช้ `id_members` (10 หลัก) เป็นตัวระบุหลักแทน `id_card`

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/members` | รายการสมาชิกทั้งหมด | - |
| `GET` | `/members/lookup?id_card=xxx` | ค้นหาด้วยเลขบัตร | - |
| `GET` | `/members/by-member-id?id=xxx` | ค้นหาด้วยรหัสสมาชิก | - |
| `GET` | `/members/search?q=xxx` | ค้นหาทั่วไป | - |
| `GET` | `/members/:id_members` | **โปรไฟล์สมาชิก** | - |
| `GET` | `/members/:id_members/donations` | ประวัติบริจาค | - |
| `GET` | `/members/:id_members/receipts` | รายการใบเสร็จ | - |
| `GET` | `/members/:id_members/summary` | สรุปยอดบริจาค | - |

<details>
<summary><b>📝 Member Profile Response</b></summary>

```json
{
  "success": true,
  "data": {
    "id_members": "0123456789",
    "id_card": "1234567890123",
    "id_card_formatted": "1-2345-67890-12-3",
    "name": "นายทดสอบ ระบบ",
    "phone": "0812345678",
    "address": {
      "full": "123/45 ถ.สุเทพ ต.สุเทพ อ.เมือง จ.เชียงใหม่ 50200",
      "province": "เชียงใหม่",
      "amphure": "เมือง",
      "district": "สุเทพ",
      "zip_code": "50200"
    },
    "statistics": {
      "receipt_count": 5,
      "total_amount": 150000,
      "first_donation_date": "2024-01-15",
      "last_donation_date": "2026-01-02"
    },
    "benefactor_level": {
      "level": 7,
      "name": "ขั้นที่ 7 เหรียญเงินดิเรกคุณาภรณ์",
      "min_amount": 100000
    }
  }
}
```
</details>

---

### 🏛️ Projects

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/projects` | รายการโครงการทั้งหมด | - |
| `GET` | `/projects/:id` | รายละเอียดโครงการ | - |
| `POST` | `/projects` | สร้างโครงการใหม่ | 🔐 |
| `PUT` | `/projects/:id` | แก้ไขโครงการ | 🔐 |

---

### 💳 Payments

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/payments/callback` | รับ Callback จากธนาคาร | - |

<details>
<summary><b>📝 Bank Callback Format</b></summary>

```json
{
  "payeeProxyId": "099400258783792",
  "payerAccountName": "นายทดสอบ ระบบ",
  "amount": "1000.00",
  "transactionId": "xxx",
  "billPaymentRef1": "256812345678901",
  "billPaymentRef2": "1234567890123"
}
```

#### Response
```json
{
  "resCode": "00",
  "resDesc": "success",
  "confirmId": "CNF20260102103012345"
}
```
</details>

---

### 📊 Reports

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET` | `/reports/dashboard` | สถิติ Dashboard | 🔐 |
| `GET` | `/reports/income?period=monthly` | รายงานรายได้ | 🔐 |
| `GET` | `/reports/top-donors?limit=10` | Top ผู้บริจาค | 🔐 |

---

### 🔔 Notifications

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/notifications/send` | ส่งการแจ้งเตือน | 🔐 |
| `POST` | `/notifications/email` | ส่งใบเสร็จทางอีเมล | 🔐 |
| `POST` | `/notifications/line` | ส่งข้อความ LINE | 🔐 |

---

## 📦 Response Format

### Success
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful",
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

### Error
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "กรุณากรอกข้อมูลให้ครบถ้วน"
  }
}
```

---

## ⚠️ Error Codes

| Code | HTTP | Description |
|------|------|-------------|
| `VALIDATION_ERROR` | 400 | ข้อมูลไม่ถูกต้อง |
| `NOT_FOUND` | 404 | ไม่พบข้อมูล |
| `UNAUTHORIZED` | 401 | ไม่ได้รับอนุญาต |
| `FORBIDDEN` | 403 | ไม่มีสิทธิ์เข้าถึง |
| `METHOD_NOT_ALLOWED` | 405 | Method ไม่รองรับ |
| `DATABASE_ERROR` | 500 | ข้อผิดพลาดฐานข้อมูล |

---

## 🗄️ Database Schema

### Key Tables

```
edonation_receipts
├── id (PK)
├── donation_id (FK → edonation_donat_user)
├── receipt_no (YYYY-EXXXX)
├── payer_name
├── amount
├── issued_at
├── id_card (13 digits)      ← NEW
└── id_members (10 digits)   ← NEW

edonation_donat_user
├── id (PK)
├── billPaymentRef1
├── first_name, last_name
├── id_card
├── receipt_address
├── province, amphure, district, zip_code
└── status_donat (pending/completed/cancelled)
```

---

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | development / production | production |
| `APP_URL` | Base URL | - |
| `BASE_PATH` | Base path for URLs | /edonation |
| `DB_HOST` | Database host | localhost |
| `DB_NAME` | Database name | donation |
| `DB_USER` | Database user | root |
| `DB_PASS` | Database password | - |
| `LINE_TOKEN` | LINE Notify token | - |

---

## 🛡️ Security

- ✅ PDO Prepared Statements (SQL Injection Prevention)
- ✅ Input Sanitization with `filter_input()`
- ✅ Session-based Authentication
- ✅ Admin Token Authentication
- ✅ PDF Access Token (Time-limited)
- ✅ HTTPS in Production

---

## 📝 Changelog

### v3.0.0 (2026-01-02)
- ✨ เพิ่มฟิลด์ `id_card` และ `id_members` ใน `edonation_receipts`
- ✨ ปรับปรุง Members API ให้ใช้ `id_members` เป็นตัวระบุหลัก
- ✨ เพิ่ม Member Profile พร้อมข้อมูลที่อยู่
- ✨ เพิ่ม Endpoint `GET /members` สำหรับรายการสมาชิก
- 📝 อัปเดต API Documentation

### v2.0.0 (2025-12-28)
- ✨ ปรับปรุงระบบ Access Token สำหรับ PDF
- 🐛 แก้ไข Safari Popup Blocker

### v1.0.0 (2025-12-01)
- 🎉 Initial Release

---

## 📄 License

Copyright © 2025-2026 Faculty of Nursing, Chiang Mai University. All rights reserved.

---

<div align="center">

Made with ❤️ by Faculty of Nursing CMU

</div>
