# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

e-Donation NurseCMU is a Thai language donation management system built with PHP, MySQL, and vanilla JavaScript. It supports online donations via PromptPay QR codes, receipt generation, and administrative dashboards. The system integrates with Microsoft Entra ID (Azure AD) for authentication.

## Development Environment

This project runs on XAMPP:
- **Database**: MySQL/MariaDB (database name: `edonation`)
- **Web Server**: Apache via XAMPP
- **PHP Version**: Compatible with PDO
- **Root Directory**: `C:\xampp\htdocs\appdev\edonation`

To access the application locally, navigate to `http://localhost/appdev/edonation/` in your browser, which redirects to `/home/`.

## Key Architecture

### Directory Structure

- **`config/`** - Database connection (`connect.php`, `connect_pdf.php`) and shared components (header, footer, session management)
- **`home/`** - Public-facing homepage displaying donation projects
- **`donat/`** - Donation workflow (form submission, QR code generation, payment verification)
- **`office/`** - Administrative dashboard with authentication (requires Microsoft Entra ID login)
- **`list/`** - Donation records search and receipt PDF generation (uses TCPDF)
- **`member/`** - Member rewards/points system (cart, orders, redemption)
- **`service/`** - Service-related pages
- **`contact/`** - Contact page
- **`recieve.php`** - Webhook endpoint for PromptPay payment confirmations (JSON API)

### Database Connection

All database connections use PDO with UTF-8 charset. The central connection file is `config/connect.php`:
```php
$pdo = new PDO("mysql:host=localhost;dbname=edonation;charset=utf8", "root", "");
```

### Key Database Tables

- **`donat`** / **`donat_user`** - Stores donation records with fields like `billPaymentRef1`, `amount`, `project_number`, `fiscal_year`, `receipt_no`, `status_payment`, `payerAccountName`
- **`project`** - Project information including `project_number`, `project_name`, `project_name_web`, `project_description`, `img_file`
- **`json_confirm`** - Stores payment confirmation data from PromptPay webhook
- **`user_permissions`** - Administrative user access control (linked to `cmu_account` from Azure AD)

### Donation Flow

1. **User selects project** (`home/index.php`) and clicks "บริจาค" (Donate)
2. **Donation form** (`donat/index.php`) captures donor info (type, email, phone, amount, project)
3. **Form submission** (`donat/donat_db.php`):
   - Validates input and inserts record into `donat_user` table
   - Generates `billPaymentRef1` = fiscal_year + project_number + padded ID (15 digits total)
   - Creates unique `transactionId` with `uniqid('TXN_')`
   - Sets `status_payment = 'activation'` and `status_donat = 'online'`
   - Redirects to QR generator with URL parameters (billPaymentRef1, id, amount, transactionId, phone, created_at)
4. **QR Code generation** (`donat/qrgenerator.php`):
   - Retrieves donation record by ID from `donat_user`
   - Generates PromptPay QR code with embedded amount and `billPaymentRef1`
   - Uses `phpqrcode/qrlib.php` library and custom CRC16 checksum (via `lib-crc16.inc.php`)
   - Creates PNG file in `donat/qrcodepayment/` directory
   - Uses GD Library to generate downloadable image with Thai text overlay (NotoSansThai font)
   - Displays QR code with bank icons and warning about tax deduction requirements
   - Auto-starts polling via JavaScript to check payment status
5. **Payment verification** (`donat/data_check.php`):
   - Frontend polls every 5 seconds (max 100 loops = ~8 minutes)
   - Checks if `json_confirm` table has matching `billPaymentRef1`, `amount`, and `created_at` date
   - When match found: copies data from `donat_user` to `donat` table
   - Generates receipt number format: `E{padded_id}` (e.g., E0001)
   - Updates with `payerAccountName`, `billPaymentRef2`, and PDF URL
   - Triggers LINE and email notifications (non-blocking)
   - Returns JSON success response to frontend
6. **Webhook endpoint** (`recieve.php`):
   - Receives POST requests with payment confirmation JSON from PromptPay gateway
   - Validates 17 required fields (payeeProxyId, amount, transactionId, billPaymentRef1, etc.)
   - Validates amount is numeric and > 0
   - Inserts into `json_confirm` table using PDO transaction
   - Returns JSON response with resCode ("00" = success)

### Authentication (Office Section)

The `office/` directory uses Microsoft Entra ID (Azure AD) authentication:
- **Session management**: `office/partials/session.php`
- **Login page**: `office/auth-login.php`
- **Permission check**: Real-time validation against `user_permissions` table using `cmu_account` (CMU email)
- **User data**: Retrieved from `$_SESSION['login_info']` containing Azure AD profile
- **Access control**: `requireAuth()` function checks login status and permissions, redirects to login if unauthorized
- **Permission lookup**: Searches multiple account format variations (lowercase, with/without @cmu.ac.th) with `status = 'active'`
- **User display**: Prioritizes displayName → English name → Thai name → email username → CMU account for display
- **Profile images**: Checks multiple Azure AD fields (profileImageUrl, picture, photo) before falling back to default avatar

### PDF Generation

Receipt PDFs use TCPDF library (located in `list/TCPDF/`):
- **`list/pdf_receipt_maker.php`** - Generate official receipts
- **`list/pdf_receipt_cancel.php`** - Generate cancellation receipts
- **`list/pdf_maker.php`** - Alternative PDF format
- **Font support**: Thai language support via NotoSansThai fonts in `donat/font/`

### Dashboard (Office)

The dashboard has evolved with two distinct implementations:

#### Current Dashboard (`office/index.php`)
- **Filter system**: Year (พ.ศ.), month, date range via URL parameters
- **Statistics cards**: Total donations, donor count, monthly stats, average donation
- **Goal tracking**: 10M THB annual target with dynamic targets (yearly/monthly/date range)
- **Project breakdown**: Per-project donation amounts with colored progress bars
- **Top 10 donors**: Table with ranking icons and full donation details
- **Data flow**: PHP directly queries database with WHERE clause filtering based on URL parameters
- **Year conversion**: Converts Buddhist Era (BE) to Gregorian (CE) for database queries (BE - 543)

#### Enhanced Dashboard (`office/index-improved.php`)
- **Comparison mode**: Side-by-side year comparison with toggle button
- **AJAX-based**: Uses `dashboard-api.php` to fetch JSON data dynamically
- **Chart.js visualizations**: Monthly trend line chart, donut chart, comparison bar charts
- **Real-time updates**: No page reload needed when switching years or modes
- **Primary/Compare selectors**: Button-based year selection interface
- **Loading states**: Shows loading overlay during data fetch

## Common Commands

### Starting the Development Server
Since this uses XAMPP, ensure XAMPP is running with Apache and MySQL services started. No build commands are needed.

### Accessing the Application
- **Public site**: `http://localhost/appdev/edonation/home/`
- **Admin dashboard**: `http://localhost/appdev/edonation/office/` (requires authentication)
- **Donation search**: `http://localhost/appdev/edonation/list/`

### Database Access
Use phpMyAdmin or MySQL CLI:
```bash
mysql -u root -p
use edonation;
```

## Important Implementation Details

### PromptPay QR Code Format
The system generates QR codes following Thai PromptPay standard (`donat/qrgenerator.php`):
- Payee PromptPay ID: `099400258783792`
- Amount: Padded to 10 digits with 2 decimal places (e.g., "0000100.00")
- Reference: `billPaymentRef1` (15 characters: fiscal_year + project_number + padded_id)
- QR code structure: `000201` + `010212` + `30{...}` + `5303764` + `54{amount}` + `5802TH` + `62100706SCB001` + `6304` + CRC16
- CRC16 checksum appended via `lib-crc16.inc.php`
- PNG files saved to `donat/qrcodepayment/` directory with MD5-based filename
- GD Library creates downloadable image with Thai text overlay using NotoSansThai-Regular.ttf font

### Fiscal Year and Receipt Numbering
- `fiscal_year` stored as Buddhist Era (e.g., "2568" for 2025 CE)
- Receipt format: `{fiscal_year}-{receipt_no}` (e.g., "2568-E0001")
- `billPaymentRef1` format: `{fiscal_year}{project_number}{padded_id}` (15 digits total)
- Receipt number generation (`donat/data_check.php`): `E{str_pad($lastId, 4, '0', STR_PAD_LEFT)}`
- PDF URL format: `https://app.nurse.cmu.ac.th/edonation/list/pdf_maker.php?id={id}&table=donat`

### Date Handling
- **Database**: Stores dates in `YYYY-MM-DD` format (Gregorian/CE)
- **Display**: Converts to Thai Buddhist Era (BE = CE + 543) in `thai_date.php`
- **Filter parameters**: Year filters expect Buddhist Era (พ.ศ.)

### Security Considerations
- All database queries use PDO prepared statements
- Input sanitization with `filter_input()` and `FILTER_SANITIZE_STRING`, `FILTER_VALIDATE_EMAIL`, `FILTER_VALIDATE_FLOAT`
- Session-based authentication with real-time permission checking
- HTML output escaping with `htmlspecialchars()` and `ENT_QUOTES, 'UTF-8'`

### Frontend Libraries
- **jQuery 3.5.1** - DOM manipulation and AJAX
- **SweetAlert2** - Modal alerts and confirmations
- **Chart.js** - Dashboard visualizations (loaded via CDN in office dashboard)
- **Slick Carousel** - Homepage sliders
- **No build process** - All assets served directly

## Code Style

- **Language**: PHP 7.4+ features, procedural and some OOP (PDO)
- **Database**: PDO with named parameters (`:param`)
- **HTML**: Mixed PHP templates (not separated views)
- **JavaScript**: ES6+ features (arrow functions, fetch API, template literals)
- **Thai language**: UTF-8 encoding throughout, all user-facing text in Thai

## Testing Payment Flow

To test the donation workflow locally:
1. Navigate to `home/` and select a project
2. Fill the donation form in `donat/` with test data
3. View generated QR code in `donat/qrgenerator.php`
4. To simulate payment, manually insert a record into `json_confirm` table:
   ```sql
   INSERT INTO json_confirm (
       billPaymentRef1, amount, payerAccountName, billPaymentRef2,
       transactionId, transactionDateandTime, date,
       payeeProxyId, payeeProxyType, payeeAccountNumber, payeeName,
       payerAccountNumber, payerName, sendingBankCode, receivingBankCode,
       currencyCode, channelCode, transactionType
   ) VALUES (
       '{billPaymentRef1_from_donat_user}', {amount}, 'Test User', 'REF2',
       'TXN_test123', NOW(), CURDATE(),
       '099400258783792', '03', '1234567890', 'Payee Name',
       '0987654321', 'Payer Name', '014', '014',
       '764', 'MOBILE', 'TRANSFER'
   );
   ```
5. The polling script (max 100 loops × 5 seconds = ~8 minutes) will detect the payment
6. On success: data copied to `donat` table, receipt generated, notifications sent, redirects to home

## Vendor Dependencies

Dependencies managed via Composer (see `composer.json` and `composer.lock`):
- **PHPSpreadsheet** - Excel export functionality
- **TCPDF** - PDF generation (also vendored in `list/TCPDF/` and `office/TCPDF/`)
- **HTMLPurifier** - HTML sanitization
- **ZipStream** - Archive generation

To install dependencies:
```bash
composer install
```

## Email Integration

The system includes PHPMailer for email notifications:
- Located in `assets/php/PHPMailer/`
- Used in `donat/send_email.php` for donation confirmations
- Configuration in PHPMailer class files

## LINE Notify Integration

LINE notifications for donations via `donat/send_line.php` (if configured).

## Key Technical Patterns

### Error Handling
- **Database errors**: Use try-catch with PDOException, display SweetAlert2 modals to user
- **JSON APIs**: Set proper headers (`Content-Type: application/json`), use ob_clean() before output
- **Validation**: `filter_input()` with FILTER_SANITIZE_STRING, FILTER_VALIDATE_EMAIL, FILTER_VALIDATE_FLOAT
- **Non-blocking operations**: LINE/email notifications wrapped in try-catch to prevent workflow interruption

### Data Flow Patterns
- **Two-table donation system**: `donat_user` (temporary) → `donat` (confirmed after payment)
- **Payment verification**: Matching requires `billPaymentRef1` + `amount` + `created_at` date match
- **Receipt generation**: Only happens when payment confirmed, generates receipt_no and PDF URL
- **Session data**: Azure AD profile stored in `$_SESSION['login_info']`, permission checked on every page load

### File Paths
- **Absolute paths required**: All Read/Write tools need absolute paths (e.g., `C:\xampp\htdocs\appdev\edonation\...`)
- **Web URLs**: Production URLs point to `https://app.nurse.cmu.ac.th/edonation/`
- **QR code storage**: `donat/qrcodepayment/` directory (ensure write permissions)
- **Font files**: Thai fonts in `donat/font/` for QR code image generation

### Important Gotchas
- **Year conversion**: Dashboard filters use BE (Buddhist Era), database stores CE (Gregorian). Always convert: `$year - 543`
- **Polling timeout**: QR code page polls for ~8 minutes max (100 loops × 5 seconds)
- **CRC16 checksum**: Required for PromptPay QR codes, implemented in `lib-crc16.inc.php`
- **Permission checking**: Real-time validation on every request, checks `user_permissions.status = 'active'`
- **Receipt URL**: Hardcoded production URL in `data_check.php`, may need adjustment for dev/staging
