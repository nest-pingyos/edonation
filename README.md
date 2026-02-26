# eDonation Project

ระบบจัดการการบริจาค (e-Donation) สำหรับคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่

## การติดตั้งและรันด้วย Docker

โปรเจคนี้รองรับการรันผ่าน Docker เพื่อความสะดวกในการย้ายเครื่องหรือติดตั้งในที่ต่างๆ

### สิ่งที่ต้องมี
1. [Docker](https://www.docker.com/products/docker-desktop/)
2. [Docker Compose](https://docs.docker.com/compose/install/)

### ขั้นตอนการเริ่มใช้งานครั้งแรก

1. **คัดลอกไฟล์ Environment**
   ```bash
   cp .env.example .env
   ```

2. **รัน Docker Compose**
   ```bash
   docker-compose up -d --build
   ```

3. **ตรวจสอบการทำงาน**
   - หน้าเว็บหลัก: [http://localhost:8080/edonation](http://localhost:8080/edonation)
   - หน้าจัดการ Admin: [http://localhost:8080/edonation/admin](http://localhost:8080/edonation/admin)
   - API Endpoint: [http://localhost:8080/edonation/api/v1](http://localhost:8080/edonation/api/v1)

### รายละเอียดโครงสร้าง Docker
- **App Service**: รันด้วย PHP 8.2 + Apache (Port 8080)
- **DB Service**: รันด้วย MariaDB 10.4 (Port 3306)
- **Database Initialization**: ข้อมูลจะถูกโหลดเข้า Database โดยอัตโนมัติจากไฟล์ `database/donation.sql` ในการรันครั้งแรก

### ปัญหาที่พบบ่อย (Tips)
- **สิทธิการเข้าถึงไฟล์ (Permissions)**: หากพบว่าไม่สามารถอัปโหลดรูปภาพได้ ให้ตรวจสอบ permission ของโฟลเดอร์ `assets/images/news/` โดยการเข้าไปใน container:
  ```bash
  docker exec -it edonation_app chown -R www-data:www-data assets/images/news
  ```
- **การแก้ไขข้อมูล Database**: ไฟล์ SQL จะถูก Import เฉพาะตอนรันครั้งแรก (Volume DB ยังไม่ถูกสร้าง) หากต้องการ Import ใหม่ต้องลบ Volume เดิมออกก่อน:
  ```bash
  docker-compose down -v
  ```

---
พัฒนาโดย ทีมพัฒนาคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่
