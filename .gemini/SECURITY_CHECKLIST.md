# eDonation API - Quick Security Checklist
**สำหรับนักพัฒนา PHP Backend**

---

## ✅ การตรวจสอบความปลอดภัยก่อน Commit Code

### 1. SQL Queries ✓

#### ❌ ห้าม:
```php
// SQL Injection Vulnerability!
$table = $_GET['table'];
$sql = "SELECT * FROM $table WHERE id = $id";

// หรือ
$sql = "SELECT * FROM users WHERE email = '" . $_POST['email'] . "'";
```

#### ✅ ต้องทำ:
```php
// ใช้ Prepared Statements ALWAYS
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
```

---

### 2. SELECT * Queries ✓

#### ❌ ห้าม:
```php
$stmt = $pdo->prepare("SELECT * FROM edonation_members WHERE id = :id");
```

#### ✅ ต้องทำ:
```php
// ระบุ Column ที่ต้องการชัดเจน
$stmt = $pdo->prepare("
    SELECT id, name, email, phone, created_at 
    FROM edonation_members 
    WHERE id = :id
");
```

**เหตุผล:**
- ป้องกันการ expose sensitive data (password, tokens)
- ประหยัดหน่วยความจำ
- เพิ่มประสิทธิภาพ

---

### 3. Error Handling ✓

#### ❌ ห้าม:
```php
try {
    // ...
} catch (Exception $e) {
    return ['error' => $e->getMessage()]; // Expose internal details!
}
```

#### ✅ ต้องทำ:
```php
try {
    // ...
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage()); // Log server-side
    return Response::error('DATABASE_ERROR', 'เกิดข้อผิดพลาดในระบบ', 500); // Generic message
}
```

---

### 4. Input Validation ✓

#### ❌ ห้าม:
```php
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
```

#### ✅ ต้องทำ:
```php
$id = $_GET['id'] ?? '';

// Validate BEFORE query
if (!ctype_digit($id) || (int)$id <= 0) {
    return Response::error('VALIDATION_ERROR', 'Invalid ID format', 400);
}

$stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
```

**Validation Examples:**
```php
// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return Response::error('VALIDATION_ERROR', 'Invalid email', 400);
}

// Enum/Whitelist
private const ALLOWED_ROLES = ['admin', 'editor', 'viewer'];
if (!in_array($role, self::ALLOWED_ROLES, true)) {
    return Response::error('VALIDATION_ERROR', 'Invalid role', 400);
}

// Numeric ID
private function isValidId(string $id): bool {
    return ctype_digit($id) && (int)$id > 0;
}
```

---

### 5. Strict Typing ✓

#### ✅ ทุกไฟล์ควรมี:
```php
<?php

declare(strict_types=1);

class MyController {
    // Type hints everywhere
    public function handle(string $method, ?string $id = null): array
    {
        // ...
    }
}
```

---

### 6. Constants for Configuration ✓

#### ❌ ห้าม:
```php
if ($role === 'admin' || $role === 'super_admin' || $role === 'editor') { ... }
```

#### ✅ ต้องทำ:
```php
private const ALLOWED_ROLES = ['super_admin', 'admin', 'editor', 'viewer'];

if (in_array($role, self::ALLOWED_ROLES, true)) { ... }
```

---

## 🛡️ Security Checklist สำหรับ Code Review

- [ ] ทุก SQL Query ใช้ Prepared Statements
- [ ] ไม่มี `SELECT *` (ยกเว้นกรณีจำเป็นจริงๆ)
- [ ] Validate ทุก User Input ก่อน Query
- [ ] Error Messages ไม่ leak database structure
- [ ] ใช้ `declare(strict_types=1)`
- [ ] Type hints ครบทุก method
- [ ] Whitelist validation สำหรับ enum values
- [ ] HTTP Status codes ถูกต้อง (400, 404, 409, 500)
- [ ] Sensitive data ไม่ถูก log หรือ return

---

## 📋 Example: Perfect Controller Method

```php
<?php

declare(strict_types=1);

class UserController
{
    private const ALLOWED_ROLES = ['admin', 'editor', 'viewer'];
    
    private const USER_COLUMNS = [
        'id',
        'email',
        'name',
        'role',
        'status',
        'created_at'
    ];

    private PDO $pdo;

    public function show(string $id): array
    {
        // 1. Validate Input
        if (!$this->isValidId($id)) {
            return Response::error('VALIDATION_ERROR', 'Invalid ID format', 400);
        }

        try {
            // 2. Explicit Column Selection
            $columns = implode(', ', self::USER_COLUMNS);
            $stmt = $this->pdo->prepare("
                SELECT {$columns}
                FROM users 
                WHERE id = :id
            ");
            
            // 3. Prepared Statement
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4. Handle Not Found
            if (!$user) {
                return Response::notFound('User not found');
            }

            return Response::success($user);
            
        } catch (PDOException $e) {
            // 5. Log Error Internally, Return Generic Message
            error_log("UserController::show Error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'Internal server error', 500);
        }
    }

    private function isValidId(string $id): bool
    {
        return ctype_digit($id) && (int)$id > 0;
    }
}
```

---

## 🚨 Common Vulnerabilities to Avoid

### 1. Dynamic Table/Column Names
```php
// ❌ DANGER
$table = $_GET['table'];
$sql = "SELECT * FROM `$table`";

// ✅ SAFE
$allowedTables = ['users', 'posts', 'comments'];
if (!in_array($table, $allowedTables, true)) {
    return Response::error('INVALID_TABLE', 'Invalid table', 400);
}

$sql = match ($table) {
    'users' => "SELECT id, name FROM users",
    'posts' => "SELECT id, title FROM posts",
    'comments' => "SELECT id, content FROM comments",
};
```

### 2. Dynamic WHERE Clauses
```php
// ❌ DANGER
$whereClause = "";
if ($_GET['status']) {
    $whereClause .= " AND status = '" . $_GET['status'] . "'";
}

// ✅ SAFE
$params = [];
$conditions = ["1=1"]; // Base condition

if (!empty($_GET['status']) && in_array($_GET['status'], ['active', 'inactive'], true)) {
    $conditions[] = "status = :status";
    $params[':status'] = $_GET['status'];
}

$whereClause = implode(' AND ', $conditions);
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE {$whereClause}");
$stmt->execute($params);
```

---

## 📚 Resources

- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [OWASP SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
- [PSR-12 Coding Style](https://www.php-fig.org/psr/psr-12/)

---

**Last Updated:** 2026-01-28  
**Security Standard:** OWASP Top 10 Compliance
