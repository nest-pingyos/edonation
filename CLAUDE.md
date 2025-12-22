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
│   ├── config/             
│   │   ├── env.php         # Environment loader
│   │   ├── database.php    # DB connection
│   │   └── head.php        # HTML head template
│   └── assets/             # CSS, JS, Images
│
├── api/                    # REST API (Pure PHP)
│   ├── controllers/        # Request Handlers
│   ├── config/             
│   │   ├── env.php         # Environment loader
│   │   ├── database.php    # DB connection
│   │   └── scb.php         # SCB config
│   ├── helpers/            # Response & Validator
│   ├── middleware/         # Auth Middleware
│   └── services/           # Wrappers for shared services
│
├── shared/                 # ✨ Shared code (web & api)
│   └── services/
│       └── SCBPaymentService.php
│
├── admin/                  # Admin UI (Coming Soon)
│   ├── README.md
│   └── CLAUDE.md
│
└── .agent/workflows/       # Development workflows
    ├── setup-dev.md
    ├── deploy-production.md
    └── create-admin-ui.md
```

## Independence of Modules

**✅ web, api, admin แยกกันสมบูรณ์**

| Module | Depends On | Description |
|--------|------------|-------------|
| `web` | `shared/`, `.env` | Frontend website |
| `api` | `shared/`, `.env` | REST API backend |
| `admin` | `api` (via HTTP) | Admin UI (Coming Soon) |
| `shared` | `.env` only | Shared services |

## URLs

| Environment | Web | API |
|-------------|-----|-----|
| **Production** | https://app.nurse.cmu.ac.th/edonation | https://app.nurse.cmu.ac.th/edonation/api |
| **Development** | http://localhost/appdev/edonation | http://localhost/appdev/edonation/api |

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

## API Endpoints (v1)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/projects` | รายการโครงการ |
| GET | `/api/v1/donations` | รายการบริจาค |
| POST | `/api/v1/donations` | สร้างการบริจาค |
| GET | `/api/v1/receipts/{id}` | ใบเสร็จ |
| POST | `/api/v1/auth/login` | เข้าสู่ระบบ |
| POST | `/api/v1/payments/callback` | PromptPay callback |

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
```

## Important Notes

- Production `BASE_PATH`: `/edonation`
- Development `BASE_PATH`: `/appdev/edonation`
- All file paths must be absolute
- Thai UTF-8 encoding throughout
