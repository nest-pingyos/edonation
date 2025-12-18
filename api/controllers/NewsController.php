<?php
/**
 * News Controller
 * API สำหรับจัดการข่าวสาร
 * 
 * Endpoints:
 * GET    /news              - รายการข่าวทั้งหมด (Public)
 * GET    /news/:id          - รายละเอียดข่าว
 * POST   /news              - เพิ่มข่าวใหม่ (Admin)
 * PUT    /news/:id          - แก้ไขข่าว (Admin)
 * DELETE /news/:id          - ลบข่าว (Admin)
 */

class NewsController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        switch ($method) {
            case 'GET':
                if ($id)
                    return $this->show($id);
                return $this->index();
            case 'POST':
                // Check if it's an upload request: POST /news/upload
                if ($id === 'upload') {
                    AuthMiddleware::requireAdmin();
                    return $this->uploadImage();
                }
                AuthMiddleware::requireAdmin();
                return $this->create();
            case 'PUT':
                AuthMiddleware::requireAdmin();
                if (!$id)
                    return Response::error('VALIDATION_ERROR', 'กรุณาระบุ ID');
                return $this->update($id);
            case 'DELETE':
                AuthMiddleware::requireAdmin();
                if (!$id)
                    return Response::error('VALIDATION_ERROR', 'กรุณาระบุ ID');
                return $this->delete($id);
            default:
                return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
        }
    }

    /**
     * GET /news
     * รายการข่าวทั้งหมด
     */
    private function index(): array
    {
        $activeOnly = isset($_GET['active']) ? $_GET['active'] === '1' : true;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;

        // Validate limit
        if ($limit < 1)
            $limit = 10;
        if ($limit > 100)
            $limit = 100;

        $sql = "SELECT id, title, excerpt, content, img_file, category, 
                       author, published_at, is_featured, is_active, 
                       view_count, created_at, updated_at 
                FROM news";

        $where = [];
        $params = [];

        if ($activeOnly) {
            $where[] = "is_active = 1";
        }

        if ($category) {
            $where[] = "category = :category";
            $params[':category'] = $category;
        }

        // Search in title and excerpt
        if ($search) {
            $where[] = "(title LIKE :search OR excerpt LIKE :search2)";
            $params[':search'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY is_featured DESC, published_at DESC";
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $countSql = "SELECT COUNT(*) FROM news";
        if (!empty($where)) {
            $countSql .= " WHERE " . implode(' AND ', $where);
        }
        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        // Add image URLs
        $baseImageUrl = '/appdev/edonation/assets/images/news/';
        foreach ($results as &$item) {
            $item['image_url'] = $item['img_file']
                ? $baseImageUrl . $item['img_file']
                : $baseImageUrl . 'default.jpg';

            // Format published date
            if ($item['published_at']) {
                $item['published_at_formatted'] = $this->formatThaiDate($item['published_at']);
            }
        }

        return Response::success($results, null, [
            'count' => count($results),
            'total' => (int) $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * GET /news/:id
     * รายละเอียดข่าว
     */
    private function show(string $id): array
    {
        $sql = "SELECT id, title, excerpt, content, img_file, category,
                       author, published_at, is_featured, is_active, 
                       view_count, created_at, updated_at 
                FROM news WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return Response::notFound('ไม่พบข่าวนี้');
        }

        // Increment view count
        $updateSql = "UPDATE news SET view_count = view_count + 1 WHERE id = :id";
        $updateStmt = $this->pdo->prepare($updateSql);
        $updateStmt->execute([':id' => $id]);
        $result['view_count']++;

        // Add image URL
        $baseImageUrl = '/appdev/edonation/assets/images/news/';
        $result['image_url'] = $result['img_file']
            ? $baseImageUrl . $result['img_file']
            : $baseImageUrl . 'default.jpg';

        // Format published date
        if ($result['published_at']) {
            $result['published_at_formatted'] = $this->formatThaiDate($result['published_at']);
        }

        return Response::success($result);
    }

    /**
     * POST /news
     * เพิ่มข่าวใหม่ (Admin)
     */
    private function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($data);
        $v->required('title');

        if (!$v->passes())
            return Response::validation($v->errors());

        $sql = "INSERT INTO news (title, excerpt, content, img_file, category, 
                                  author, published_at, is_featured, is_active)
                VALUES (:title, :excerpt, :content, :img_file, :category,
                        :author, :published_at, :is_featured, :is_active)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':excerpt' => $data['excerpt'] ?? null,
            ':content' => $data['content'] ?? null,
            ':img_file' => $data['img_file'] ?? null,
            ':category' => $data['category'] ?? 'general',
            ':author' => $data['author'] ?? null,
            ':published_at' => $data['published_at'] ?? date('Y-m-d H:i:s'),
            ':is_featured' => $data['is_featured'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);

        $id = $this->pdo->lastInsertId();

        return Response::success([
            'id' => (int) $id
        ], 'เพิ่มข่าวใหม่สำเร็จ');
    }

    /**
     * PUT /news/:id
     * แก้ไขข่าว (Admin)
     */
    private function update(string $id): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Check if news exists
        $checkSql = "SELECT id FROM news WHERE id = :id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':id' => $id]);

        if (!$checkStmt->fetch()) {
            return Response::notFound('ไม่พบข่าวนี้');
        }

        // Build dynamic SET clause
        $updates = [];
        $params = [':id' => $id];

        $allowedFields = [
            'title',
            'excerpt',
            'content',
            'img_file',
            'category',
            'author',
            'published_at',
            'is_featured',
            'is_active'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($updates)) {
            return Response::error('VALIDATION_ERROR', 'ไม่มีข้อมูลที่ต้องอัปเดต');
        }

        // Add updated_at
        $updates[] = "updated_at = NOW()";

        $sql = "UPDATE news SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return Response::success([
            'id' => (int) $id
        ], 'อัปเดตข่าวสำเร็จ');
    }

    /**
     * DELETE /news/:id
     * ลบข่าว (Admin)
     */
    private function delete(string $id): array
    {
        // Check if news exists
        $checkSql = "SELECT id FROM news WHERE id = :id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':id' => $id]);

        if (!$checkStmt->fetch()) {
            return Response::notFound('ไม่พบข่าวนี้');
        }

        $sql = "DELETE FROM news WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        return Response::success([
            'id' => (int) $id
        ], 'ลบข่าวสำเร็จ');
    }

    /**
     * Format date to Thai format
     */
    private function formatThaiDate(string $date): string
    {
        $thaiMonths = [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม'
        ];

        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = (int) date('n', $timestamp);
        $year = (int) date('Y', $timestamp) + 543; // Convert to Buddhist Era

        return "{$day} {$thaiMonths[$month]} {$year}";
    }

    /**
     * POST /news/upload
     * อัพโหลดรูปภาพข่าว (Admin)
     * 
     * การตั้งชื่อไฟล์: news_YYYYMMDD_HHMMSS_RANDOM.ext
     * ตัวอย่าง: news_20241218_103500_a1b2c3.jpg
     */
    private function uploadImage(): array
    {
        // Check if file was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'ไฟล์มีขนาดใหญ่เกินกำหนด (PHP ini)',
                UPLOAD_ERR_FORM_SIZE => 'ไฟล์มีขนาดใหญ่เกินกำหนด (Form)',
                UPLOAD_ERR_PARTIAL => 'อัพโหลดไฟล์ไม่สมบูรณ์',
                UPLOAD_ERR_NO_FILE => 'ไม่พบไฟล์ที่อัพโหลด',
                UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ temp',
                UPLOAD_ERR_CANT_WRITE => 'ไม่สามารถเขียนไฟล์ได้',
                UPLOAD_ERR_EXTENSION => 'การอัพโหลดถูกหยุดโดย extension'
            ];

            $errorCode = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
            $errorMsg = $errorMessages[$errorCode] ?? 'เกิดข้อผิดพลาดในการอัพโหลด';

            return Response::error('UPLOAD_ERROR', $errorMsg);
        }

        $file = $_FILES['image'];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            return Response::error('VALIDATION_ERROR', 'รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, GIF, WebP)');
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return Response::error('VALIDATION_ERROR', 'ไฟล์มีขนาดใหญ่เกิน 5MB');
        }

        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        // Generate new filename: news_YYYYMMDD_HHMMSS_RANDOM.ext
        $timestamp = date('Ymd_His');
        $randomStr = substr(md5(uniqid(mt_rand(), true)), 0, 6);
        $newFilename = "news_{$timestamp}_{$randomStr}.{$extension}";

        // Upload directory
        $uploadDir = dirname(__DIR__, 2) . '/assets/images/news/';

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . $newFilename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return Response::error('UPLOAD_ERROR', 'ไม่สามารถบันทึกไฟล์ได้');
        }

        // Return success with file info
        $baseImageUrl = '/appdev/edonation/assets/images/news/';

        return Response::success([
            'filename' => $newFilename,
            'original_name' => $file['name'],
            'image_url' => $baseImageUrl . $newFilename,
            'size' => $file['size'],
            'mime_type' => $mimeType
        ], 'อัพโหลดรูปภาพสำเร็จ');
    }
}
