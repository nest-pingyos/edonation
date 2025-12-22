---
description: เริ่มต้นพัฒนา eDonation บน local
---

# Setup Development Environment

## 1. ตั้งค่า Environment
// turbo
```bash
cd c:\xampp\htdocs\appdev\edonation
copy .env.example .env
```

## 2. ตรวจสอบ .env
แก้ไขไฟล์ `.env` ตั้งค่าดังนี้:
- `APP_ENV=development`
- `APP_DEBUG=true`
- `APP_URL=http://localhost/appdev/edonation`
- `BASE_PATH=/appdev/edonation`
- Database credentials

## 3. ติดตั้ง Dependencies (Web)
```bash
cd c:\xampp\htdocs\appdev\edonation\web
composer install
```

## 4. เริ่ม XAMPP
- เปิด XAMPP Control Panel
- Start Apache
- Start MySQL

## 5. ทดสอบการเชื่อมต่อ
- Web: http://localhost/appdev/edonation/
- API: http://localhost/appdev/edonation/api/
