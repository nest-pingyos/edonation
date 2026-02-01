---
description: ขั้นตอนการทดสอบระบบบน Docker (Port 8080)
---

ใช้ไฟล์นี้เพื่อทดสอบระบบ eDonation ในสภาพแวดล้อมจำลอง (Docker) หลังจากมีการแก้ไขโค้ด

### 1. การเตรียมความพร้อม
- ตรวจสอบว่าได้ติดตั้ง Docker Desktop เรียบร้อยแล้ว
- ปิด Service อื่นๆ ที่อาจจะใช้พอร์ต 8080 อยู่

### 2. ขั้นตอนการ Build และ Run
รันคำสั่งต่อไปนี้ใน Terminal ที่โฟลเดอร์โปรเจกต์:

// turbo
```bash
# 1. Build และเริ่มระบบ (ใช้เวลาสักครู่ในการ Build และติดตั้ง PHP Extensions)
docker compose up -d --build
```

### 3. ตรวจสอบสถานะ
// turbo
```bash
# ตรวจสอบว่า Container รันขึ้นมาปกติหรือไม่
docker compose ps
```

### 4. การทดสอบในระบบ (Testing Flow)
เมื่อระบบรันขึ้นมาแล้ว ให้เปิด Browser และเข้าทดสอบตามลำดับดังนี้:

1. **หน้าหลักของผู้บริจาค**: 
   - [http://localhost:8080/](http://localhost:8080/)
   - ลองคลิก "ร่วมบริจาค" เพื่อตรวจสอบว่า URL โครงการถูกต้องหรือไม่

2. **หน้า Admin (Login)**:
   - [http://localhost:8080/admin/src/auth/login.php](http://localhost:8080/admin/src/auth/login.php)
   - ทดสอบใช้ **Developer Login** เพื่อเข้าสู่ระบบ (เนื่องจาก OAuth จริงต้องมีการตั้งค่า Redirect URI ที่ Microsoft)

3. **หน้าจัดการข่าวสาร (อัปโหลดรูปภาพ)**:
   - [http://localhost:8080/admin/src/news-list.php](http://localhost:8080/admin/src/news-list.php)
   - ลองสร้างข่าวใหม่และ **อัปโหลดรูปภาพ** เพื่อยืนยันว่า Permissions ของโฟลเดอร์ใน Docker ถูกต้อง

### 5. การปิดระบบ
// turbo
```bash
# เมื่อทดสอบเสร็จแล้ว สามารถสั่งปิดได้
docker compose down
```

> [!IMPORTANT]
> หากมีการแก้ไขโค้ด PHP ในเครื่อง และต้องการเห็นผลใน Docker ทันที ให้รัน `docker compose up -d --build` อีกครั้งเพื่อให้ระบบทำการ Copy ไฟล์ใหม่เข้าไปใน Container ครับ
