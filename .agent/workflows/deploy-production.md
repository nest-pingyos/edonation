---
description: เตรียมโปรเจ็คสำหรับ deploy ขึ้น production
---

# Production Deployment

## Production URL
- Web: https://app.nurse.cmu.ac.th/edonation
- API: https://app.nurse.cmu.ac.th/edonation/api

## 1. ตั้งค่า Environment
สร้างไฟล์ `.env` บน server:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.nurse.cmu.ac.th/edonation
BASE_PATH=/edonation

DB_HOST=localhost
DB_NAME=edonation
DB_USER=<production_user>
DB_PASS=<production_password>

JWT_SECRET=<strong_random_secret_key>
```

## 2. Upload Files
Upload folders:
- `web/` → `/edonation/`
- `api/` → `/edonation/api/`
- `.env` → `/edonation/.env`

**ข้อควรระวัง:**
- ไม่ upload folder `vendor/` (ติดตั้งบน server)
- ไม่ upload `.env.example`, `.env.production`

## 3. ติดตั้ง Dependencies
บน server:
```bash
cd /path/to/edonation/web
composer install --no-dev --optimize-autoloader
```

## 4. ตั้งค่า Permissions
```bash
chmod 755 -R /path/to/edonation
chmod 777 /path/to/edonation/web/donat/qrcodepayment
chmod 777 /path/to/edonation/web/logs
```

## 5. ตรวจสอบ .htaccess
ตรวจสอบว่าไฟล์ `.htaccess` ทำงานถูกต้อง

## 6. ทดสอบ
- https://app.nurse.cmu.ac.th/edonation/
- https://app.nurse.cmu.ac.th/edonation/api/
