# 🔌 API Design Document - eDonation

## 📋 Overview

REST API สำหรับระบบ e-Donation คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่

**Base URL:** `https://app.nurse.cmu.ac.th/edonation/api/v1`

**Response Format:** JSON

**Authentication:** JWT Token (สำหรับ Admin endpoints)

---

## 🗃️ Database Tables (วิเคราะห์จากโค้ด)

| Table | Description |
|-------|-------------|
| `donat` | รายการบริจาคหลัก |
| `donat_user` | ข้อมูลผู้บริจาคชั่วคราว (ก่อนชำระเงิน) |
| `json_confirm` | ข้อมูล callback จากธนาคาร |
| `project` | โครงการรับบริจาค |
| `users` | ผู้ใช้ระบบ |
| `provinces` | จังหวัด |
| `amphures` | อำเภอ |
| `districts` | ตำบล |
| `receipt_2566-2569` | ใบเสร็จแยกตามปี |

---

## 🎯 API Endpoints

### 1. 🏷️ Projects API (โครงการ)

#### GET /projects
รายการโครงการทั้งหมดที่เปิดรับบริจาค

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | `active`, `inactive`, `all` (default: `active`) |
| `page` | int | No | หน้าที่ต้องการ (default: 1) |
| `limit` | int | No | จำนวนต่อหน้า (default: 20, max: 100) |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "project_number": "P001",
      "project_name": "กองทุนพัฒนาการศึกษา",
      "project_tex": "สนับสนุนทุนการศึกษาแก่นักศึกษา",
      "status": "active",
      "total_donated": 150000.00,
      "donor_count": 45,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_items": 100,
    "per_page": 20
  }
}
```

---

#### GET /projects/:project_number
รายละเอียดโครงการ

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "project_number": "P001",
    "project_name": "กองทุนพัฒนาการศึกษา",
    "project_tex": "สนับสนุนทุนการศึกษาแก่นักศึกษา...",
    "image_url": "https://...",
    "status": "active",
    "statistics": {
      "total_donated": 150000.00,
      "donor_count": 45,
      "this_month": 25000.00
    }
  }
}
```

---

#### POST /projects (Admin Only)
สร้างโครงการใหม่

**Headers:**
```
Authorization: Bearer <jwt_token>
```

**Request Body:**
```json
{
  "project_number": "P002",
  "project_name": "กองทุนช่วยเหลือนักศึกษา",
  "project_tex": "รายละเอียดโครงการ...",
  "status": "active"
}
```

**Response:**
```json
{
  "success": true,
  "message": "สร้างโครงการสำเร็จ",
  "data": {
    "id": 2,
    "project_number": "P002",
    "project_name": "กองทุนช่วยเหลือนักศึกษา"
  }
}
```

---

#### PUT /projects/:id (Admin Only)
แก้ไขโครงการ

---

#### DELETE /projects/:id (Admin Only)
ลบโครงการ (soft delete)

---

### 2. 💰 Donations API (การบริจาค)

#### POST /donations
สร้างรายการบริจาคใหม่ (เริ่มกระบวนการบริจาค)

**Request Body:**
```json
{
  "project_number": "P001",
  "type": "บุคคลทั่วไป",
  "email": "donor@example.com",
  "phone": "0812345678",
  "amount": 1000.00
}
```

**Response:**
```json
{
  "success": true,
  "message": "สร้างรายการบริจาคสำเร็จ",
  "data": {
    "id": 123,
    "billPaymentRef1": "ED20241215001234",
    "amount": 1000.00,
    "status": "pending",
    "qr_code_url": "/api/v1/donations/123/qr",
    "expires_at": "2024-12-15T12:00:00Z"
  }
}
```

---

#### GET /donations/:id/qr
สร้าง QR Code สำหรับชำระเงิน

**Response:** 
- Content-Type: `image/png` (QR Code Image)
- หรือ JSON พร้อม base64

```json
{
  "success": true,
  "data": {
    "qr_image": "data:image/png;base64,...",
    "qr_string": "00020101021230...",
    "amount": 1000.00,
    "expires_at": "2024-12-15T12:00:00Z"
  }
}
```

---

#### GET /donations/:id/status
ตรวจสอบสถานะการชำระเงิน (สำหรับ polling)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "status": "completed",  // pending, completed, expired, cancelled
    "paid_at": "2024-12-15T10:35:00Z",
    "receipt_url": "/api/v1/receipts/456/pdf"
  }
}
```

---

#### GET /donations (Admin Only)
รายการบริจาคทั้งหมด

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | `pending`, `completed`, `cancelled`, `all` |
| `project` | string | No | Filter by project_number |
| `from_date` | date | No | วันที่เริ่มต้น (YYYY-MM-DD) |
| `to_date` | date | No | วันที่สิ้นสุด |
| `search` | string | No | ค้นหาชื่อ, เลขบัตร, เบอร์โทร |
| `fiscal_year` | int | No | ปีงบประมาณ (2566, 2567, ...) |
| `page` | int | No | หน้า |
| `limit` | int | No | จำนวนต่อหน้า |
| `sort` | string | No | `date_desc`, `date_asc`, `amount_desc` |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "transaction_id": "TXN123456",
      "billPaymentRef1": "ED20241215001234",
      "billPaymentRef2": "1234567890123",
      "payer_name": "นายสมชาย ใจดี",
      "type": "บุคคลทั่วไป",
      "email": "donor@example.com",
      "phone": "0812345678",
      "amount": 1000.00,
      "project_number": "P001",
      "project_name": "กองทุนพัฒนาการศึกษา",
      "status": "completed",
      "pay_by": "QR PromptPay",
      "receipt_no": "E0001",
      "fiscal_year": 2568,
      "receipt_date": "2024-12-15",
      "created_at": "2024-12-15T10:30:00Z"
    }
  ],
  "pagination": {...},
  "summary": {
    "total_amount": 1500000.00,
    "total_count": 150
  }
}
```

---

#### GET /donations/:id (Admin Only)
รายละเอียดการบริจาค

---

#### PUT /donations/:id (Admin Only)
แก้ไขข้อมูลการบริจาค

**Request Body:**
```json
{
  "payer_name": "นายสมชาย ใจดี",
  "address": "123 ถนนห้วยแก้ว",
  "province": "เชียงใหม่",
  "amphure": "เมือง",
  "district": "สุเทพ",
  "zip_code": "50200",
  "comment": "หมายเหตุเพิ่มเติม"
}
```

---

### 3. 🧾 Receipts API (ใบเสร็จ)

#### GET /receipts/search
ค้นหาใบเสร็จ (สำหรับผู้บริจาค)

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id_card` | string | No* | เลขบัตรประชาชน 13 หลัก |
| `phone` | string | No* | เบอร์โทรศัพท์ |
| `receipt_no` | string | No* | เลขที่ใบเสร็จ |

*ต้องระบุอย่างน้อย 1 ตัว

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 456,
      "receipt_no": "2568-E0001",
      "payer_name": "นายสมชาย ใจดี",
      "amount": 1000.00,
      "project_name": "กองทุนพัฒนาการศึกษา",
      "receipt_date": "2024-12-15",
      "fiscal_year": 2568,
      "status": "issued",
      "pdf_url": "/api/v1/receipts/456/pdf"
    }
  ]
}
```

---

#### GET /receipts/:id/pdf
ดาวน์โหลดใบเสร็จ PDF

**Response:**
- Content-Type: `application/pdf`
- Content-Disposition: `inline; filename="Receipt_2568-E0001.pdf"`

---

#### GET /receipts (Admin Only)
รายการใบเสร็จทั้งหมด

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | `issued`, `cancelled`, `all` |
| `fiscal_year` | int | No | ปีงบประมาณ |
| `from_date` | date | No | วันที่เริ่มต้น |
| `to_date` | date | No | วันที่สิ้นสุด |
| `search` | string | No | ค้นหา |
| `page` | int | No | หน้า |
| `limit` | int | No | จำนวนต่อหน้า |

---

#### POST /receipts/:id/cancel (Admin Only)
ยกเลิกใบเสร็จ

**Request Body:**
```json
{
  "reason": "ข้อมูลไม่ถูกต้อง"
}
```

**Response:**
```json
{
  "success": true,
  "message": "ยกเลิกใบเสร็จเรียบร้อย",
  "data": {
    "id": 456,
    "receipt_no": "2568-E0001",
    "status": "cancelled",
    "cancelled_at": "2024-12-15T15:00:00Z",
    "cancelled_pdf_url": "/api/v1/receipts/456/cancelled-pdf"
  }
}
```

---

#### POST /receipts/generate (Admin Only)
ออกใบเสร็จแบบ manual

**Request Body:**
```json
{
  "payer_name": "นายสมชาย ใจดี",
  "id_card": "1234567890123",
  "email": "donor@example.com",
  "phone": "0812345678",
  "address": "123 ถนนห้วยแก้ว",
  "province": "เชียงใหม่",
  "amphure": "เมือง",
  "district": "สุเทพ",
  "zip_code": "50200",
  "amount": 5000.00,
  "project_number": "P001",
  "pay_by": "เงินสด",
  "receipt_date": "2024-12-15",
  "fiscal_year": 2568
}
```

---

### 4. 🔔 Webhook API (Payment Callback)

#### POST /webhooks/payment
รับ callback จากธนาคารเมื่อมีการชำระเงิน

**Request Body (จากธนาคาร):**
```json
{
  "payeeProxyId": "0994000423179",
  "payeeProxyType": "NATID",
  "payeeAccountNumber": "123456789",
  "payeeName": "คณะพยาบาลศาสตร์ มช.",
  "payerAccountNumber": "987654321",
  "payerAccountName": "นายสมชาย ใจดี",
  "payerName": "นายสมชาย ใจดี",
  "sendingBankCode": "004",
  "receivingBankCode": "014",
  "amount": "1000.00",
  "transactionId": "TXN123456789",
  "transactionDateandTime": "2024-12-15T10:35:00+07:00",
  "billPaymentRef1": "ED20241215001234",
  "billPaymentRef2": "1234567890123",
  "currencyCode": "THB",
  "channelCode": "PMT",
  "transactionType": "PAYMENT"
}
```

**Response:**
```json
{
  "resCode": "00",
  "resDesc": "success",
  "transactionId": "TXN123456789",
  "confirmId": "789"
}
```

---

### 5. 👤 Auth API (Authentication)

#### POST /auth/login
เข้าสู่ระบบ Admin

**Request Body:**
```json
{
  "username": "admin@cmu.ac.th",
  "password": "********"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@cmu.ac.th",
      "role": "admin"
    }
  }
}
```

---

#### POST /auth/oauth/cmu
Login ด้วย CMU OAuth

**Request Body:**
```json
{
  "code": "oauth_authorization_code"
}
```

---

#### POST /auth/logout
ออกจากระบบ

---

#### GET /auth/me
ข้อมูลผู้ใช้ปัจจุบัน

---

### 6. 📊 Reports API (Admin Only)

#### GET /reports/summary
ภาพรวมสถิติ

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `fiscal_year` | int | ปีงบประมาณ |

**Response:**
```json
{
  "success": true,
  "data": {
    "fiscal_year": 2568,
    "total_donations": 5000000.00,
    "total_donors": 450,
    "total_receipts": 445,
    "cancelled_receipts": 5,
    "by_project": [
      {
        "project_number": "P001",
        "project_name": "กองทุนพัฒนาการศึกษา",
        "total_amount": 2500000.00,
        "count": 200
      }
    ],
    "by_month": [
      {"month": "2024-01", "amount": 450000.00, "count": 40},
      {"month": "2024-02", "amount": 520000.00, "count": 48}
    ],
    "by_type": [
      {"type": "บุคคลทั่วไป", "amount": 3000000.00, "count": 280},
      {"type": "ศิษย์เก่า", "amount": 1500000.00, "count": 150},
      {"type": "บุคลากร", "amount": 500000.00, "count": 20}
    ]
  }
}
```

---

#### GET /reports/daily
รายงานประจำวัน

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `date` | date | วันที่ (default: today) |

---

#### GET /reports/monthly
รายงานประจำเดือน

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `year` | int | ปี |
| `month` | int | เดือน (1-12) |

---

#### GET /reports/export
ดาวน์โหลดรายงาน Excel

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | `donations`, `receipts`, `summary` |
| `format` | string | `xlsx`, `csv` |
| `from_date` | date | วันที่เริ่มต้น |
| `to_date` | date | วันที่สิ้นสุด |
| `fiscal_year` | int | ปีงบประมาณ |

---

### 7. 📍 Address API (ที่อยู่)

#### GET /address/provinces
รายการจังหวัด

```json
{
  "success": true,
  "data": [
    {"id": 1, "name_th": "กรุงเทพมหานคร", "name_en": "Bangkok"}
  ]
}
```

---

#### GET /address/amphures/:province_id
รายการอำเภอในจังหวัด

---

#### GET /address/districts/:amphure_id
รายการตำบลในอำเภอ

---

### 8. 📧 Notifications API (Admin Only)

#### POST /notifications/send-receipt
ส่งใบเสร็จทางอีเมล

**Request Body:**
```json
{
  "receipt_id": 456,
  "email": "donor@example.com"
}
```

---

#### POST /notifications/line
ส่งแจ้งเตือน LINE

**Request Body:**
```json
{
  "message": "ข้อความ..."
}
```

---

## 🔐 Authentication & Security

### JWT Token Structure
```json
{
  "sub": "user_id",
  "name": "Admin User",
  "email": "admin@cmu.ac.th",
  "role": "admin",
  "iat": 1702620000,
  "exp": 1702706400
}
```

### Roles & Permissions
| Role | Permissions |
|------|-------------|
| `admin` | Full access (CRUD all resources) |
| `staff` | View, Create receipts, Cannot delete |
| `viewer` | Read-only access |

### Rate Limiting
| Endpoint Type | Limit |
|---------------|-------|
| Public API | 60 requests/minute |
| Auth API | 10 requests/minute |
| Admin API | 120 requests/minute |
| Webhook | No limit (IP whitelist) |

---

## ❌ Error Response Format

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "ข้อมูลไม่ถูกต้อง",
    "details": [
      {"field": "email", "message": "รูปแบบอีเมลไม่ถูกต้อง"},
      {"field": "amount", "message": "จำนวนเงินต้องมากกว่า 0"}
    ]
  }
}
```

### Error Codes
| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 400 | ข้อมูลไม่ถูกต้อง |
| `UNAUTHORIZED` | 401 | ไม่ได้ login หรือ token หมดอายุ |
| `FORBIDDEN` | 403 | ไม่มีสิทธิ์เข้าถึง |
| `NOT_FOUND` | 404 | ไม่พบข้อมูล |
| `CONFLICT` | 409 | ข้อมูลซ้ำ |
| `RATE_LIMITED` | 429 | เกิน rate limit |
| `SERVER_ERROR` | 500 | เกิดข้อผิดพลาดภายใน |

---

## 🔄 API Versioning

- Current version: `v1`
- Version in URL: `/api/v1/...`
- Deprecated endpoints จะมี header: `X-API-Deprecated: true`

---

## 📝 Implementation Priority

### Phase 1 (Core - สัปดาห์ที่ 1)
1. ✅ POST /donations (สร้างรายการบริจาค)
2. ✅ GET /donations/:id/qr (QR Code)
3. ✅ GET /donations/:id/status (ตรวจสอบสถานะ)
4. ✅ POST /webhooks/payment (รับ callback)
5. ✅ GET /projects (รายการโครงการ)

### Phase 2 (Receipts - สัปดาห์ที่ 2)
1. GET /receipts/search
2. GET /receipts/:id/pdf
3. POST /receipts/:id/cancel
4. POST /receipts/generate

### Phase 3 (Admin - สัปดาห์ที่ 3)
1. Auth endpoints
2. GET /donations (list)
3. PUT /donations/:id
4. CRUD Projects

### Phase 4 (Reports - สัปดาห์ที่ 4)
1. Reports endpoints
2. Export functionality
3. Dashboard stats

---

## 🚀 พร้อมเริ่มพัฒนาหรือต้องการปรับแก้ไขอะไรเพิ่มเติม?
