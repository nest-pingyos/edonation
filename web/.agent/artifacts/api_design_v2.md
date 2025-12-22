# 🔌 API Design - eDonation (v2)

**Base URL:** `https://app.nurse.cmu.ac.th/edonation/api/v1`

---

## 📊 Endpoints ทั้งหมด (5 หมวด, 24 endpoints)

| หมวด | จำนวน | Description |
|------|-------|-------------|
| Projects | 5 | จัดการโครงการ |
| Donations | 6 | การบริจาค + QR |
| Receipts | 6 | ใบเสร็จ + PDF |
| Auth | 4 | Authentication |
| Notifications | 2 | Email/LINE |
| **Webhook** | 1 | **อยู่ที่ `/edonation/recieve.php`** |

---

## 1️⃣ Projects API

### GET /projects
```
GET /api/v1/projects?status=active&page=1&limit=10
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "project_number": "P001",
      "project_name": "กองทุนพัฒนาการศึกษา",
      "project_tex": "สนับสนุนทุนการศึกษา",
      "status": "active"
    }
  ],
  "pagination": {"page": 1, "total": 10}
}
```

### GET /projects/:project_number
```
GET /api/v1/projects/P001
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "project_number": "P001",
    "project_name": "กองทุนพัฒนาการศึกษา",
    "project_tex": "รายละเอียดโครงการ...",
    "status": "active",
    "total_donated": 150000.00,
    "donor_count": 45
  }
}
```

### POST /projects (Admin)
```
POST /api/v1/projects
Authorization: Bearer <token>
Content-Type: application/json

{
  "project_number": "P002",
  "project_name": "กองทุนช่วยเหลือ",
  "project_tex": "รายละเอียด",
  "status": "active"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "สร้างโครงการสำเร็จ",
  "data": {"id": 2, "project_number": "P002"}
}
```

### PUT /projects/:id (Admin)
```
PUT /api/v1/projects/2
Authorization: Bearer <token>

{"project_name": "ชื่อใหม่", "status": "inactive"}
```

### DELETE /projects/:id (Admin)
```
DELETE /api/v1/projects/2
Authorization: Bearer <token>
```

---

## 2️⃣ Donations API

### POST /donations
สร้างรายการบริจาคใหม่
```
POST /api/v1/donations
Content-Type: application/json

{
  "project_number": "P001",
  "type": "บุคคลทั่วไป",
  "email": "test@example.com",
  "phone": "0812345678",
  "amount": 1000.00
}
```

**Response 201:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "billPaymentRef1": "ED20241215001234",
    "amount": 1000.00,
    "status": "pending",
    "qr_url": "/api/v1/donations/123/qr",
    "expires_at": "2024-12-15T12:00:00Z"
  }
}
```

### GET /donations/:id/qr
```
GET /api/v1/donations/123/qr
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "qr_image": "data:image/png;base64,iVBORw0KGgo...",
    "qr_string": "00020101021230...",
    "amount": 1000.00
  }
}
```

### GET /donations/:id/status
```
GET /api/v1/donations/123/status
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "status": "completed",
    "paid_at": "2024-12-15T10:35:00Z",
    "receipt_url": "/api/v1/receipts/456/pdf"
  }
}
```

### GET /donations (Admin)
```
GET /api/v1/donations?status=completed&from_date=2024-12-01&page=1
Authorization: Bearer <token>
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "payer_name": "นายสมชาย",
      "amount": 1000.00,
      "project_name": "กองทุนฯ",
      "status": "completed",
      "receipt_no": "2568-E0001",
      "created_at": "2024-12-15T10:30:00Z"
    }
  ],
  "summary": {"total_amount": 50000, "count": 25}
}
```

### GET /donations/:id (Admin)
```
GET /api/v1/donations/123
Authorization: Bearer <token>
```

### PUT /donations/:id (Admin)
```
PUT /api/v1/donations/123
Authorization: Bearer <token>

{
  "payer_name": "นายสมชาย ใจดี",
  "address": "123 ถนนห้วยแก้ว",
  "province": "เชียงใหม่",
  "comment": "หมายเหตุ"
}
```

---

## 3️⃣ Receipts API

### GET /receipts/search (Public)
```
GET /api/v1/receipts/search?phone=0812345678
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 456,
      "receipt_no": "2568-E0001",
      "payer_name": "นายสมชาย",
      "amount": 1000.00,
      "receipt_date": "2024-12-15",
      "pdf_url": "/api/v1/receipts/456/pdf"
    }
  ]
}
```

### GET /receipts/:id/pdf
```
GET /api/v1/receipts/456/pdf
```
**Response:** PDF file (application/pdf)

### GET /receipts (Admin)
```
GET /api/v1/receipts?fiscal_year=2568&status=issued
Authorization: Bearer <token>
```

### POST /receipts/generate (Admin)
```
POST /api/v1/receipts/generate
Authorization: Bearer <token>

{
  "payer_name": "นายสมชาย",
  "id_card": "1234567890123",
  "amount": 5000.00,
  "project_number": "P001",
  "pay_by": "เงินสด"
}
```

### POST /receipts/:id/cancel (Admin)
```
POST /api/v1/receipts/456/cancel
Authorization: Bearer <token>

{"reason": "ข้อมูลไม่ถูกต้อง"}
```

### POST /receipts/:id/resend (Admin)
```
POST /api/v1/receipts/456/resend
Authorization: Bearer <token>

{"email": "donor@example.com"}
```

---

## 4️⃣ Auth API

### POST /auth/login
```
POST /api/v1/auth/login

{"username": "admin@cmu.ac.th", "password": "****"}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 86400,
    "user": {"id": 1, "name": "Admin", "role": "admin"}
  }
}
```

### POST /auth/oauth/cmu
```
POST /api/v1/auth/oauth/cmu

{"code": "oauth_authorization_code"}
```

### POST /auth/logout
```
POST /api/v1/auth/logout
Authorization: Bearer <token>
```

### GET /auth/me
```
GET /api/v1/auth/me
Authorization: Bearer <token>
```

---

## 5️⃣ Notifications API (Admin)

### POST /notifications/email
```
POST /api/v1/notifications/email
Authorization: Bearer <token>

{"receipt_id": 456, "email": "donor@example.com"}
```

### POST /notifications/line
```
POST /api/v1/notifications/line
Authorization: Bearer <token>

{"message": "ข้อความแจ้งเตือน"}
```

---

## 🔔 Webhook - Payment Callback

**ตำแหน่ง:** `https://app.nurse.cmu.ac.th/edonation/recieve.php`

```
POST /edonation/recieve.php
Content-Type: application/json

{
  "payeeProxyId": "0994000423179",
  "payerAccountName": "นายสมชาย",
  "amount": "1000.00",
  "transactionId": "TXN123456",
  "transactionDateandTime": "2024-12-15T10:35:00+07:00",
  "billPaymentRef1": "ED20241215001234",
  "billPaymentRef2": "1234567890123"
}
```

**Response:**
```json
{"resCode": "00", "resDesc": "success", "transactionId": "TXN123456"}
```

---

## ❌ Error Responses

```json
{"success": false, "error": {"code": "VALIDATION_ERROR", "message": "..."}}
{"success": false, "error": {"code": "UNAUTHORIZED", "message": "..."}}
{"success": false, "error": {"code": "NOT_FOUND", "message": "..."}}
```
