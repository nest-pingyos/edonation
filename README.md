# eDonation NurseCMU

ระบบบริจาคออนไลน์สำหรับคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่

## 📁 Project Structure

```
edonation/
├── web/              # Frontend Website (Public)
├── api/              # REST API Backend
├── admin/            # Admin Dashboard (Coming Soon)
├── .env              # Environment Configuration (Main)
├── .env.example      # Environment Template
└── .env.production   # Production Config Template
```

## 🌐 URLs

| Environment | Web | API |
|-------------|-----|-----|
| **Production** | https://app.nurse.cmu.ac.th/edonation | https://app.nurse.cmu.ac.th/edonation/api |
| **Development** | http://localhost/appdev/edonation | http://localhost/appdev/edonation/api |

## 🚀 Getting Started

### Prerequisites

- XAMPP (PHP 7.4+, MySQL/MariaDB)
- Composer
- Modern Web Browser

### Installation

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd edonation
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Install Dependencies (Web)**
   ```bash
   cd web
   composer install
   ```

4. **Setup Database**
   - Import database schema (see `web/database/`)
   - Configure database credentials in `.env`

5. **Start XAMPP**
   - Start Apache and MySQL services
   - Access: http://localhost/appdev/edonation/

## 📚 Documentation

### API Endpoints

Base URL: `/api/v1`

| Resource | Endpoint | Description |
|----------|----------|-------------|
| Projects | `/api/v1/projects` | จัดการโครงการบริจาค |
| Donations | `/api/v1/donations` | รายการบริจาค |
| Receipts | `/api/v1/receipts` | ใบเสร็จรับเงิน |
| Auth | `/api/v1/auth` | ระบบยืนยันตัวตน |
| Payments | `/api/v1/payments` | การชำระเงิน |
| Benefits | `/api/v1/benefits` | สิทธิประโยชน์ |
| News | `/api/v1/news` | ข่าวสาร |

### Web Sections

- `/` - หน้าแรก
- `/donat/` - ฟอร์มบริจาค
- `/list/` - ค้นหาใบเสร็จ
- `/office/` - ระบบจัดการ (Admin)

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Environment (development/production) | production |
| `APP_URL` | Base URL | https://app.nurse.cmu.ac.th/edonation |
| `DB_HOST` | Database host | localhost |
| `DB_NAME` | Database name | edonation |
| `JWT_SECRET` | Secret key for JWT | - |
| `LINE_TOKEN` | LINE Notify token | - |

## 🛡️ Security

- All database queries use PDO prepared statements
- Input sanitization with `filter_input()`
- Session-based authentication with Azure AD
- JWT tokens for API authentication

## 📝 License

Copyright © 2025 Faculty of Nursing, Chiang Mai University
