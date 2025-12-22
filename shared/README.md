# Shared Services

โฟลเดอร์นี้เก็บ services และ utilities ที่ใช้ร่วมกันระหว่าง `web` และ `api`

## Structure

```
shared/
└── services/
    └── SCBPaymentService.php    # SCB Open Banking API Integration
```

## Usage

### From Web
```php
require_once __DIR__ . '/../config/env.php';
require_once dirname(__DIR__, 2) . '/shared/services/SCBPaymentService.php';

$scb = new SCBPaymentService();
$qr = $scb->createQRCode($amount, $ref1, $ref2);
```

### From API
```php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../../shared/services/SCBPaymentService.php';

$scb = new SCBPaymentService();
$qr = $scb->createQRCode($amount, $ref1, $ref2);
```

## Services

### SCBPaymentService

เชื่อมต่อกับ SCB Open Banking API สำหรับ:
- OAuth 2.0 Token Management
- Dynamic QR Code Generation
- Single-use QR with Expiry (30 นาที)
