# CLAUDE.md - eDonation Project

เอกสารนี้เป็นคู่มือสำหรับ AI Assistants ในการทำงานกับโปรเจ็ค eDonation

## Project Structure

```
edonation/
├── .env                    # Main environment config (shared)
├── .env.example            # Template for developers
├── .env.production         # Production template
├── .gitignore              # Git ignore rules
├── README.md               # Project documentation
│
├── web/                    # Frontend Website (PHP + jQuery)
│   ├── home/               # หน้าแรก
│   ├── donat/              # ระบบบริจาค
│   ├── list/               # ค้นหาใบเสร็จ
│   ├── office/             # Admin Dashboard (Legacy)
│   ├── receipts/           # PDF Generation (TCPDF)
│   ├── config/             
│   │   ├── env.php         # Environment loader
│   │   ├── database.php    # DB connection
│   │   └── head.php        # HTML head template
│   └── assets/             # CSS, JS, Images
│
├── api/                    # REST API (Pure PHP)
│   ├── controllers/        # Request Handlers (11 controllers)
│   │   ├── AuthController.php
│   │   ├── BenefitsController.php
│   │   ├── DonationController.php
│   │   ├── MemberController.php
│   │   ├── NewsController.php
│   │   ├── NotificationsController.php
│   │   ├── PaymentController.php
│   │   ├── ProjectController.php
│   │   ├── ReceiptController.php
│   │   └── SignatureController.php
│   ├── config/             
│   │   ├── bootstrap.php   # App bootstrap
│   │   ├── env.php         # Environment loader
│   │   ├── database.php    # DB connection
│   │   └── scb.php         # SCB config
│   ├── docs/               # API Manager (Interactive Docs)
│   ├── helpers/            # Response & Validator
│   ├── middleware/         # Auth Middleware
│   └── services/           # Wrappers for shared services
│
├── shared/                 # ✨ Shared code (web & api)
│   └── services/
│       └── SCBPaymentService.php
│
├── admin/                  # Admin UI
│   ├── src/                # PHP source files
│   │   ├── assets/         # CSS, JS, Images
│   │   ├── config/         # Configuration
│   │   ├── partials/       # Template partials
│   │   └── services/       # Backend services
│   └── README.md
│
└── .agent/workflows/       # Development workflows
    ├── setup-dev.md
    ├── deploy-production.md
    └── create-admin-ui.md
```

## Independence of Modules

**✅ web, api, admin แยกกันสมบูรณ์ - เชื่อมต่อผ่าน HTTP API เท่านั้น**

| Module | Location | Depends On | Description |
|--------|----------|------------|-------------|
| `web` | `/web/` | `.env`, `shared/` | Frontend website (PHP) |
| `api` | `/api/` | `.env` | REST API (Pure PHP, ไม่ต้องใช้ shared) |
| `admin` | `/admin/` | **API via HTTP** | Admin Dashboard |
| `shared` | `/shared/` | `.env` | Utilities (AutoProvince, SCB) |

### การเชื่อมต่อระหว่าง Modules

```
┌─────────────┐     HTTP/HTTPS      ┌─────────────┐
│   Web/Admin │ ◄────────────────► │    API      │
│ (Same Domain)│     JSON REST      │(Same/Other) │
└─────────────┘                     └─────────────┘
      │                                   │
      ▼                                   ▼
   ┌──────┐                          ┌──────┐
   │ .env │                          │ .env │
   └──────┘                          └──────┘
```

## Domain Configuration

### ⚙️ รองรับ 2 รูปแบบ:

**1. Same Domain (Default)**
```
Web:   https://app.nurse.cmu.ac.th/edonation
Admin: https://app.nurse.cmu.ac.th/edonation/admin
API:   https://app.nurse.cmu.ac.th/edonation/api
```

**2. Separate API Domain**
```
Web:   https://app.nurse.cmu.ac.th/edonation
Admin: https://app.nurse.cmu.ac.th/edonation/admin
API:   https://api.nurse.cmu.ac.th/api  (แยก domain)
```

### Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_DOMAIN` | Domain สำหรับ Web/Admin | `https://app.nurse.cmu.ac.th` |
| `API_DOMAIN` | Domain สำหรับ API | Same as APP_DOMAIN หรือแยก |
| `BASE_PATH` | Path หลัง domain | `/edonation` |
| `API_BASE_PATH` | Path ของ API | `/edonation/api` |
| `CORS_ALLOWED_ORIGINS` | Domain ที่อนุญาต CORS | `https://app.nurse.cmu.ac.th` |

## URLs


| Environment | Web | API | Admin |
|-------------|-----|-----|-------|
| **Production** | https://app.nurse.cmu.ac.th/edonation | https://app.nurse.cmu.ac.th/edonation/api | https://app.nurse.cmu.ac.th/edonation/admin |
| **Development** | http://localhost/appdev/edonation | http://localhost/appdev/edonation/api | http://localhost/appdev/edonation/admin |

## Environment Configuration

ไฟล์ `.env` ที่ root level ถูกโหลดโดย:
- `web/config/env.php`
- `api/config/env.php`

```env
# Key settings
APP_ENV=development|production
APP_URL=https://app.nurse.cmu.ac.th/edonation
BASE_PATH=/edonation  # Production
# BASE_PATH=/appdev/edonation  # Development
```

## API Endpoints (v1) - Complete List

### Projects - โครงการบริจาค
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/projects` | รายการโครงการ | - |
| GET | `/api/v1/projects/:id` | รายละเอียดโครงการ | - |
| POST | `/api/v1/projects` | สร้างโครงการ | Admin |
| PUT | `/api/v1/projects/:id` | แก้ไขโครงการ | Admin |

### Donations - การบริจาค
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/donations` | สร้างการบริจาค | - |
| GET | `/api/v1/donations/:id/qr` | QR Code | - |
| GET | `/api/v1/donations/:id/status` | สถานะการชำระ | - |
| GET | `/api/v1/donations` | รายการทั้งหมด | Admin |
| GET | `/api/v1/donations/:id` | รายละเอียด | Admin |
| PUT | `/api/v1/donations/:id` | แก้ไข | Admin |

### Receipts - ใบเสร็จ
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/receipts/search` | ค้นหาใบเสร็จ | - |
| GET | `/api/v1/receipts/:id/verify` | ยืนยัน Tax ID | - |
| GET | `/api/v1/receipts/:id/pdf` | ดาวน์โหลด PDF | Token |
| GET | `/api/v1/receipts/:id/details` | รายละเอียดสำหรับ PDF | - |
| GET | `/api/v1/receipts/:id` | ดูใบเสร็จ | - |
| GET | `/api/v1/receipts` | รายการทั้งหมด | Admin |
| POST | `/api/v1/receipts/generate` | ออกใบเสร็จ manual | Admin |
| POST | `/api/v1/receipts/:id/cancel` | ยกเลิกใบเสร็จ | Admin |
| POST | `/api/v1/receipts/:id/resend` | ส่งใบเสร็จซ้ำ | Admin |

### Members - สมาชิก
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/members/lookup` | ค้นหาสมาชิก | - |
| GET | `/api/v1/members/:id_card` | ข้อมูลสมาชิก | - |
| GET | `/api/v1/members/:id_card/donations` | รายการบริจาค | - |
| GET | `/api/v1/members/:id_card/receipts` | รายการใบเสร็จ | - |
| GET | `/api/v1/members/:id_card/summary` | สรุปยอด | - |

### Auth - ยืนยันตัวตน
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/auth/login` | เข้าสู่ระบบ | - |
| POST | `/api/v1/auth/oauth/cmu` | CMU OAuth | - |
| POST | `/api/v1/auth/logout` | ออกจากระบบ | - |
| GET | `/api/v1/auth/me` | ข้อมูลผู้ใช้ปัจจุบัน | Bearer |

### Payments - การชำระเงิน
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/payments/callback` | PromptPay callback | - |

### Benefits - ระดับผู้มีอุปการคุณ
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/benefits` | รายการระดับ | - |
| GET | `/api/v1/benefits/:id` | รายละเอียด | - |
| POST | `/api/v1/benefits` | เพิ่มระดับ | Admin |
| PUT | `/api/v1/benefits/:id` | แก้ไขระดับ | Admin |
| DELETE | `/api/v1/benefits/:id` | ลบระดับ | Admin |

### News - ข่าวสาร
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/news` | รายการข่าว | - |
| GET | `/api/v1/news/:id` | รายละเอียด | - |
| POST | `/api/v1/news` | เพิ่มข่าว | Admin |
| PUT | `/api/v1/news/:id` | แก้ไขข่าว | Admin |
| DELETE | `/api/v1/news/:id` | ลบข่าว | Admin |
| POST | `/api/v1/news/upload` | อัพโหลดรูป | Admin |

### Signatures - ลายเซ็น
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/signatures` | รายการทั้งหมด | - |
| GET | `/api/v1/signatures/:year` | ตามปีงบประมาณ | - |
| POST | `/api/v1/signatures` | เพิ่มลายเซ็น | Admin |
| PUT | `/api/v1/signatures/:year` | แก้ไข | Admin |
| DELETE | `/api/v1/signatures/:year` | ลบ | Admin |

### Notifications - การแจ้งเตือน
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/notifications/send` | ส่งแจ้งเตือนทั่วไป | Admin |
| POST | `/api/v1/notifications/email` | ส่งอีเมล | Admin |
| POST | `/api/v1/notifications/line` | ส่ง LINE | Admin |

## JavaScript API Configuration

ใน web pages, ใช้ meta tags:
```html
<meta name="base-path" content="/edonation">
<meta name="api-base" content="/edonation/api/v1">
```

JavaScript:
```javascript
// จาก config.js
const API_BASE = window.API_BASE || document.querySelector('meta[name="api-base"]').content;
fetch(API_BASE + '/projects');
```

## Key Patterns

1. **Environment Loading**: ทุก module โหลด `.env` จาก root
2. **Shared Services**: SCBPaymentService อยู่ใน `shared/`
3. **Fiscal Year**: Buddhist Era (BE = CE + 543)
4. **Authentication**: Azure AD for office/, JWT for API
5. **Response Format**: 
   ```json
   {
     "success": true|false,
     "data": {...},
     "message": "...",
     "meta": {...}
   }
   ```

## Workflows

- `/setup-dev` - ตั้งค่า local development
- `/deploy-production` - เตรียม deploy ขึ้น production
- `/create-admin-ui` - สร้าง Admin Dashboard ใหม่

## Development

```bash
# Start XAMPP (Apache + MySQL)
# Access: http://localhost/appdev/edonation/

# Install dependencies
cd web && composer install

# API Docs
http://localhost/appdev/edonation/api/docs/
```

## Important Notes

- Production `BASE_PATH`: `/edonation`
- Development `BASE_PATH`: `/appdev/edonation`
- All file paths must be absolute
- Thai UTF-8 encoding throughout
- PHP 8.0+ required for typed properties
