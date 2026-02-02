# eDonation API Documentation

**Base URL:** `/api/v1`  
**Version:** 3.0  
**Last Updated:** 2026-01-02

---

## Table of Contents

1. [Authentication](#authentication)
2. [Donations API](#donations-api)
3. [Receipts API](#receipts-api)
4. [Members API](#members-api)
5. [Projects API](#projects-api)
6. [Payments API](#payments-api)
7. [Reports API](#reports-api)
8. [Response Format](#response-format)
9. [Error Codes](#error-codes)

---

## Authentication

### Admin Authentication
Admin endpoints require `X-Admin-Token` header or session-based authentication.

```
X-Admin-Token: your-admin-token
```

### Member Authentication (Future)
Member endpoints will use OTP-based token authentication.

---

## Donations API

### Create Donation
```
POST /donations
```

สร้างรายการบริจาคใหม่ (สำหรับ Web Form)

**Request Body:**
```json
{
  "project_number": "PJ001",
  "phone": "0812345678",
  "amount": 1000,
  "type": "individual",
  "needReceipt": true,
  "firstName": "ทดสอบ",
  "lastName": "ระบบ",
  "idCard": "1234567890123",
  "receiptAddress": "123/45 ถ.สุเทพ",
  "shippingAddress": "123/45 ถ.สุเทพ"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "billPaymentRef1": "256812345678901",
    "amount": 1000,
    "status": "pending",
    "qr_url": "/api/v1/donations/123/qr",
    "expires_at": "2026-01-02T23:30:00+07:00"
  }
}
```

---

### Create Donation (Admin)
```
POST /donations/admin
```
🔐 **Requires Admin Auth**

สร้างรายการบริจาคจาก Admin (สร้างใบเสร็จอัตโนมัติ)

**Request Body:**
```json
{
  "first_name": "ทดสอบ",
  "last_name": "ระบบ",
  "id_card": "1234567890123",
  "address": "123/45 ถ.สุเทพ อ.เมือง จ.เชียงใหม่",
  "project_number": "PJ001",
  "amount": 1000,
  "payment_method": "เงินสด",
  "donation_date": "2026-01-02"
}
```

---

### Get Donation QR Code
```
GET /donations/:id/qr
```

ดึงข้อมูลสำหรับสร้าง QR Code

---

### Check Donation Status
```
GET /donations/:id/status
```

ตรวจสอบสถานะการชำระเงิน

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "123",
    "status": "completed",
    "payer_name": "นายทดสอบ ระบบ",
    "receipt_id": 456,
    "receipt_no": "2569-E0001",
    "pdf_url": "/edonation/web/receipts/pdf_completed.php?id=456&token=xxx",
    "amount": 1000
  }
}
```

---

### List Donations (Admin)
```
GET /donations
```
🔐 **Requires Admin Auth**

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | หน้าที่ต้องการ (default: 1) |
| limit | int | จำนวนต่อหน้า (default: 20, max: 100) |
| search | string | ค้นหาชื่อ, เลขบัตร, Ref1 |
| status | string | CONFIRMED, PENDING, CANCELLED |
| project | string | รหัสโครงการ |
| from | date | วันที่เริ่มต้น (YYYY-MM-DD) |
| to | date | วันที่สิ้นสุด (YYYY-MM-DD) |

---

### Get Donation Detail
```
GET /donations/:id
```
🔐 **Requires Admin Auth**

---

### Update Donation
```
PUT /donations/:id
```
🔐 **Requires Admin Auth**

---

### Delete Donation
```
DELETE /donations/:id
```
🔐 **Requires Admin Auth**

---

## Receipts API

### List Receipts
```
GET /receipts
```
🔐 **Requires Admin Auth**

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | หน้าที่ต้องการ (default: 1) |
| limit | int | จำนวนต่อหน้า (default: 20, max: 100) |
| search | string | ค้นหาเลขใบเสร็จ, ชื่อ, เลขบัตร |
| status | string | issued, cancelled |
| year | int | ปี พ.ศ. (เช่น 2569) |
| project | string | รหัสโครงการ |
| from | date | วันที่เริ่มต้น |
| to | date | วันที่สิ้นสุด |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "receipt_no": "2569-E0001",
      "payer_name": "นายทดสอบ ระบบ",
      "amount": 1000,
      "issued_at": "2026-01-02 10:30:00",
      "id_card": "1234567890123",
      "id_members": "0123456789",
      "project_name": "กองทุนพัฒนาคณะ"
    }
  ],
  "meta": {
    "total": 100,
    "page": 1,
    "limit": 20,
    "total_pages": 5,
    "total_amount": 13442358.95
  }
}
```

---

### Get Receipt Detail
```
GET /receipts/:id
```

---

### Get Receipt Details (Full)
```
GET /receipts/:id/details
```

ดึงข้อมูลใบเสร็จแบบละเอียด (สำหรับสร้าง PDF)

---

### Generate Receipt (Admin)
```
POST /receipts/generate
```
🔐 **Requires Admin Auth**

**Request Body:**
```json
{
  "first_name": "ทดสอบ",
  "last_name": "ระบบ",
  "id_card": "1234567890123",
  "address": "123/45 ถ.สุเทพ",
  "project_number": "PJ001",
  "amount": 1000,
  "donation_id": 123
}
```

---

### Cancel Receipt
```
POST /receipts/:id/cancel
```
🔐 **Requires Admin Auth**

---

### Get Receipt PDF (Public with Token)
```
GET /receipts/:id/pdf?id_card=xxx
```

ขอ URL สำหรับดาวน์โหลด PDF (ต้องยืนยันเลขบัตร)

---

### Get Receipt PDF (Admin)
```
GET /receipts/:id/admin_pdf
```
🔐 **Requires Admin Auth**

ขอ URL สำหรับดาวน์โหลด PDF (ไม่ต้องยืนยัน)

---

## Members API

### List Members
```
GET /members
```

รายการสมาชิกทั้งหมด (จัดกลุ่มตาม id_members)

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | หน้าที่ต้องการ |
| limit | int | จำนวนต่อหน้า (default: 20) |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id_members": "0123456789",
      "id_card": "1234567890123",
      "id_card_formatted": "1-2345-67890-12-3",
      "name": "นายทดสอบ ระบบ",
      "receipt_count": 5,
      "total_amount": 150000,
      "last_donation_date": "2026-01-02",
      "phone": "0812345678"
    }
  ],
  "meta": {
    "total": 500,
    "page": 1,
    "limit": 20,
    "total_pages": 25
  }
}
```

---

### Lookup by ID Card
```
GET /members/lookup?id_card=1234567890123
```

ค้นหาสมาชิกด้วยเลขบัตรประชาชน

---

### Lookup by Member ID
```
GET /members/by-member-id?id=0123456789
```

ค้นหาสมาชิกด้วยรหัสสมาชิก 10 หลัก

---

### Search Members
```
GET /members/search?q=ทดสอบ
```

ค้นหาสมาชิกด้วยชื่อ, เลขบัตร, หรือรหัสสมาชิก

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| q | string | คำค้นหา |
| limit | int | จำนวนผลลัพธ์ (default: 20, max: 50) |

---

### Get Member Profile
```
GET /members/:id_members
```

ดึงข้อมูลโปรไฟล์สมาชิกแบบเต็ม

**Response:**
```json
{
  "success": true,
  "data": {
    "id_members": "0123456789",
    "id_card": "1234567890123",
    "id_card_formatted": "1-2345-67890-12-3",
    "name": "นายทดสอบ ระบบ",
    "first_name": "ทดสอบ",
    "last_name": "ระบบ",
    "phone": "0812345678",
    "address": {
      "full": "123/45 ถ.สุเทพ ต.สุเทพ อ.เมือง จ.เชียงใหม่ 50200",
      "address_line": "123/45 ถ.สุเทพ",
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
    },
    "top_projects": [
      {
        "project_name": "กองทุนพัฒนาคณะ",
        "project_number": "PJ001",
        "count": 3,
        "total": 100000
      }
    ]
  }
}
```

---

### Get Member Donations
```
GET /members/:id_members/donations
```

รายการบริจาคของสมาชิก

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | หน้าที่ต้องการ |
| limit | int | จำนวนต่อหน้า |

---

### Get Member Receipts
```
GET /members/:id_members/receipts
```

รายการใบเสร็จของสมาชิก

---

### Get Member Summary
```
GET /members/:id_members/summary
```

สรุปยอดบริจาคของสมาชิก (ตามปี, ตามโครงการ)

**Response:**
```json
{
  "success": true,
  "data": {
    "id_members": "0123456789",
    "total_amount": 150000,
    "total_receipts": 5,
    "first_donation_date": "2024-01-15",
    "last_donation_date": "2026-01-02",
    "by_fiscal_year": [
      {"year": 2569, "amount": 50000, "count": 2},
      {"year": 2568, "amount": 100000, "count": 3}
    ],
    "by_project": [
      {"project_name": "กองทุนพัฒนาคณะ", "amount": 100000, "count": 3},
      {"project_name": "ทุนการศึกษา", "amount": 50000, "count": 2}
    ],
    "benefactor_level": {
      "level": 7,
      "name": "ขั้นที่ 7 เหรียญเงินดิเรกคุณาภรณ์"
    }
  }
}
```

---

## Projects API

### List Projects
```
GET /projects
```

รายการโครงการทั้งหมด

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | หน้าที่ต้องการ (default: 1) |
| limit | int | จำนวนต่อหน้า (default: 20, max: 500) |
| status | string | active, inactive, completed |
| search | string | ค้นหาชื่อโครงการ, รหัสโครงการ |

---

### Get Project Detail
```
GET /projects/:id
```

---

### Create Project
```
POST /projects
```
🔐 **Requires Admin Auth**

**Request Body:**
```json
{
  "project_number": "PJ001",
  "project_name": "โครงการทดสอบ",
  "project_receipt_name": "กองทุนทดสอบ",
  "description": "รายละเอียดโครงการ",
  "image_url": "/edonation/assets/images/projects/project_xxx.jpg",
  "status": "active"
}
```

---

### Update Project
```
PUT /projects/:id
```
🔐 **Requires Admin Auth**

**Request Body:**
```json
{
  "project_name": "ชื่อใหม่",
  "project_receipt_name": "ชื่อใบเสร็จใหม่",
  "description": "รายละเอียดใหม่",
  "image_url": "/edonation/assets/images/projects/project_xxx.jpg",
  "status": "active"
}
```

---

### Delete Project
```
DELETE /projects/:id
```
🔐 **Requires Admin Auth**

---

### Upload Project Image
```
POST /projects/upload-image
```
🔐 **Requires Admin Auth**

**Content-Type:** `multipart/form-data`

**Form Fields:**
| Field | Type | Description |
|-------|------|-------------|
| image | file | ไฟล์รูปภาพ (JPG, PNG, GIF, WebP) |

**Constraints:**
- Maximum file size: 5MB
- Supported formats: JPEG, PNG, GIF, WebP
- Images larger than 1200x800 will be automatically resized

**Response:**
```json
{
  "success": true,
  "data": {
    "filename": "project_65a1b2c3d4e5f_1706831400.jpg",
    "url": "/edonation/assets/images/projects/project_65a1b2c3d4e5f_1706831400.jpg",
    "size": 245678,
    "type": "image/jpeg"
  },
  "message": "อัปโหลดรูปภาพสำเร็จ"
}
```

**Error Responses:**
| Code | Message |
|------|---------|
| UPLOAD_ERROR | การอัปโหลดไฟล์ล้มเหลว |
| FILE_TOO_LARGE | ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 5MB) |
| INVALID_TYPE | ประเภทไฟล์ไม่ถูกต้อง |

---

## Payments API

### Payment Callback
```
POST /payments/callback
```

รับ Callback จากธนาคาร (PromptPay)

**Request Body:**
```json
{
  "payeeProxyId": "099400258783792",
  "payeeProxyType": "BILLERID",
  "payerAccountName": "นายทดสอบ ระบบ",
  "sendingBankCode": "014",
  "amount": "1000.00",
  "transactionId": "xxx",
  "transactionDateandTime": "2026-01-02T10:30:00+07:00",
  "billPaymentRef1": "256812345678901",
  "billPaymentRef2": "1234567890123"
}
```

**Response:**
```json
{
  "resCode": "00",
  "resDesc": "success",
  "transactionId": "xxx",
  "confirmId": "CNF20260102103012345"
}
```

---

## Reports API

### Dashboard Stats
```
GET /reports/dashboard
```
🔐 **Requires Admin Auth**

---

### Income Report by Period
```
GET /reports/income?period=monthly&year=2569
```
🔐 **Requires Admin Auth**

---

### Top Donors Report
```
GET /reports/top-donors?limit=10
```
🔐 **Requires Admin Auth**

---

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful",
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 100
  }
}
```

### Error Response
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

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | ข้อมูลไม่ถูกต้อง |
| NOT_FOUND | 404 | ไม่พบข้อมูล |
| UNAUTHORIZED | 401 | ไม่ได้รับอนุญาต |
| FORBIDDEN | 403 | ไม่มีสิทธิ์เข้าถึง |
| METHOD_NOT_ALLOWED | 405 | Method ไม่รองรับ |
| DATABASE_ERROR | 500 | เกิดข้อผิดพลาดฐานข้อมูล |

---

## Database Schema (Key Tables)

### edonation_receipts
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| donation_id | INT | FK to edonation_donat_user |
| bank_transaction_id | INT | FK to edonation_bank_transactions |
| receipt_no | VARCHAR | เลขที่ใบเสร็จ (YYYY-EXXXX) |
| payer_name | VARCHAR | ชื่อผู้บริจาค |
| amount | DECIMAL | จำนวนเงิน |
| issued_at | DATETIME | วันที่ออกใบเสร็จ |
| **id_card** | VARCHAR(13) | เลขบัตรประชาชน |
| **id_members** | VARCHAR(10) | รหัสสมาชิก (สุ่มอัตโนมัติ) |

### edonation_donat_user
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| billPaymentRef1 | VARCHAR | Reference สำหรับ QR |
| first_name | VARCHAR | ชื่อ |
| last_name | VARCHAR | นามสกุล |
| id_card | VARCHAR | เลขบัตรประชาชน |
| receipt_address | TEXT | ที่อยู่เต็ม |
| province | VARCHAR | จังหวัด |
| amphure | VARCHAR | อำเภอ |
| district | VARCHAR | ตำบล |
| zip_code | VARCHAR | รหัสไปรษณีย์ |

---

## Changelog

### Version 3.1 (2026-02-01)
- เพิ่ม Endpoint `POST /projects/upload-image` สำหรับอัปโหลดรูปภาพโครงการ
- เพิ่มฟิลด์ `image_url` ใน Create/Update Project
- รองรับไฟล์รูปภาพ JPG, PNG, GIF, WebP (สูงสุด 5MB)
- Auto-resize รูปภาพที่ใหญ่เกิน 1200x800

### Version 3.0 (2026-01-02)
- เพิ่มฟิลด์ `id_card` และ `id_members` ในตาราง `edonation_receipts`
- ปรับปรุง Members API ให้ใช้ `id_members` เป็นตัวระบุหลัก
- เพิ่ม Endpoint `GET /members` สำหรับดูรายการสมาชิกทั้งหมด
- เพิ่ม Endpoint `GET /members/:id_members` สำหรับดูโปรไฟล์สมาชิก
- เพิ่มข้อมูลที่อยู่ใน Member Profile

### Version 2.0 (2025-12-28)
- ปรับปรุงระบบ Access Token สำหรับ PDF
- เพิ่ม Safari Popup Blocker Handling

### Version 1.0 (2025-12-01)
- Initial API Release
