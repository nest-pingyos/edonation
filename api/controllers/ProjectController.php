<?php
/**
 * Project Controller
 * 
 * Endpoints:
 * GET    /projects              - รายการโครงการ
 * GET    /projects/:id          - รายละเอียดโครงการ
 * POST   /projects              - สร้างโครงการ (Admin)
 * POST   /projects/upload-image - อัปโหลดรูปภาพโครงการ (Admin)
 * PUT    /projects/:id          - แก้ไขโครงการ (Admin)
 * DELETE /projects/:id          - ลบโครงการ (Admin)
 */

class ProjectController
{
    const VERSION = '2.2';
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const UPLOAD_PATH = __DIR__ . '/../../assets/images/projects';

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        switch ($method) {
            case 'GET':
                return $id ? $this->show($id) : $this->index();
            case 'POST':
                AuthMiddleware::requireAdmin();
                // Check for upload-image action
                if ($id === 'upload-image') {
                    return $this->uploadImage();
                }
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
     * GET /projects
     * รายการโครงการทั้งหมด
     */
    private function index(): array
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(500, max(1, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;
        $sort = strtolower($_GET['sort'] ?? 'desc');
        $orderDir = ($sort === 'asc') ? 'ASC' : 'DESC';

        $whereClauses = [];
        $params = [':limit' => $limit, ':offset' => $offset];

        if ($status && $status !== 'all') {
            $whereClauses[] = "LOWER(status) = LOWER(:status)";
            $params[':status'] = $status;
        }

        if ($search) {
            $whereClauses[] = "(project_name LIKE :search OR project_number LIKE :search2)";
            $params[':search'] = "%$search%";
            $params[':search2'] = "%$search%";
        }

        $whereClause = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

        $sql = "SELECT id, project_number, project_name, project_receipt_name, description, image_url, status, created_at 
                FROM edonation_projects $whereClause 
                ORDER BY id $orderDir LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total for pagination
        $countSql = "SELECT COUNT(*) FROM edonation_projects $whereClause";
        $countStmt = $this->pdo->prepare($countSql);
        $countParams = array_filter($params, fn($k) => $k !== ':limit' && $k !== ':offset', ARRAY_FILTER_USE_KEY);
        $countStmt->execute($countParams);
        $total = (int) $countStmt->fetchColumn();

        // Add image URL fallback
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
        foreach ($results as &$item) {
            if (empty($item['image_url'])) {
                $imgNum = ($item['id'] % 3) + 1;
                $item['image_url'] = "{$basePath}/assets/images/projects/pro-{$imgNum}.jpg";
            } elseif (strpos($item['image_url'], 'http') !== 0) {
                $item['image_url'] = "{$basePath}/assets/images/projects/" . basename($item['image_url']);
            }
        }

        return Response::success($results, null, [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]);
    }

    /**
     * GET /projects/:id
     * รายละเอียดโครงการ
     */
    private function show(string $id): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, project_number, project_name, project_receipt_name, description, image_url, status, created_at 
             FROM edonation_projects 
             WHERE id = :id OR project_number = :pn LIMIT 1"
        );
        $stmt->execute([':id' => $id, ':pn' => $id]);
        $project = $stmt->fetch();

        if (!$project) {
            return Response::notFound('ไม่พบโครงการ');
        }

        // Add image URL fallback
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
        if (empty($project['image_url'])) {
            $imgNum = ($project['id'] % 3) + 1;
            $project['image_url'] = "{$basePath}/assets/images/projects/pro-{$imgNum}.jpg";
        } elseif (strpos($project['image_url'], 'http') !== 0) {
            $project['image_url'] = "{$basePath}/assets/images/projects/" . basename($project['image_url']);
        }

        return Response::success($project);
    }

    /**
     * POST /projects
     * สร้างโครงการใหม่ (Admin)
     */
    private function create(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $v = new Validator($data);
        $v->required('project_number')->required('project_name');

        if (!$v->passes()) {
            return Response::validation($v->errors());
        }

        $sql = "INSERT INTO edonation_projects (project_number, project_name, project_receipt_name, description, image_url, status) 
                VALUES (:number, :name, :receipt_name, :description, :image_url, :status)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':number' => $data['project_number'],
            ':name' => $data['project_name'],
            ':receipt_name' => $data['project_receipt_name'] ?? $data['project_name'],
            ':description' => $data['description'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':status' => $data['status'] ?? 'active'
        ]);

        return Response::success(['id' => $this->pdo->lastInsertId()], 'สร้างโครงการสำเร็จ');
    }

    /**
     * PUT /projects/:id
     * แก้ไขโครงการ (Admin)
     */
    private function update(string $id): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $allowedFields = ['project_name', 'project_receipt_name', 'description', 'image_url', 'status'];
        $fields = [];
        $params = [':id' => $id];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return Response::error('NO_DATA', 'ไม่มีข้อมูลที่จะอัปเดต');
        }

        $sql = "UPDATE edonation_projects SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            return Response::notFound('ไม่พบโครงการ');
        }

        return Response::success(null, 'อัปเดตสำเร็จ');
    }

    /**
     * DELETE /projects/:id
     * ลบโครงการ (Admin)
     */
    private function delete(string $id): array
    {
        $stmt = $this->pdo->prepare("DELETE FROM edonation_projects WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            return Response::notFound('ไม่พบโครงการ');
        }

        return Response::success(null, 'ลบโครงการสำเร็จ');
    }

    /**
     * POST /projects/upload-image
     * อัปโหลดรูปภาพโครงการ (Admin)
     */
    private function uploadImage(): array
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['image']['error'] ?? 'NO_FILE';
            return Response::error('UPLOAD_ERROR', 'การอัปโหลดไฟล์ล้มเหลว: ' . $this->getUploadErrorMessage($errorCode));
        }

        $file = $_FILES['image'];

        // Validate file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return Response::error('FILE_TOO_LARGE', 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 5MB)');
        }

        // Validate file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            return Response::error('INVALID_TYPE', 'ประเภทไฟล์ไม่ถูกต้อง (รองรับ: JPG, PNG, GIF, WebP)');
        }

        // Generate unique filename
        $extension = $this->getExtensionFromMime($mimeType);
        $filename = 'project_' . uniqid() . '_' . time() . '.' . $extension;
        $targetPath = self::UPLOAD_PATH . '/' . $filename;

        // Ensure upload directory exists
        if (!is_dir(self::UPLOAD_PATH)) {
            mkdir(self::UPLOAD_PATH, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return Response::error('UPLOAD_FAILED', 'ไม่สามารถบันทึกไฟล์ได้');
        }

        // Optimize image if too large
        $this->optimizeImage($targetPath, 1200, 800);

        // Return the relative path for storing in database
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
        $imageUrl = $basePath . '/assets/images/projects/' . $filename;

        return Response::success([
            'filename' => $filename,
            'url' => $imageUrl,
            'size' => filesize($targetPath),
            'type' => $mimeType
        ], 'อัปโหลดรูปภาพสำเร็จ');
    }

    /**
     * Optimize and resize image
     */
    private function optimizeImage(string $path, int $maxWidth, int $maxHeight): void
    {
        if (!extension_loaded('gd')) {
            return;
        }

        $info = getimagesize($path);
        if (!$info) {
            return;
        }

        list($width, $height) = $info;
        $mime = $info['mime'];

        // Only resize if larger than max dimensions
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);

        // Create image based on type
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $source = imagecreatefrompng($path);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($path);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($path);
                break;
            default:
                return;
        }

        if (!$source) {
            return;
        }

        // Create resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($resized, $path, 85);
                break;
            case 'image/png':
                imagepng($resized, $path, 8);
                break;
            case 'image/gif':
                imagegif($resized, $path);
                break;
            case 'image/webp':
                imagewebp($resized, $path, 85);
                break;
        }

        imagedestroy($source);
        imagedestroy($resized);
    }

    /**
     * Get file extension from MIME type
     */
    private function getExtensionFromMime(string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        return $map[$mime] ?? 'jpg';
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($code): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'ไฟล์ใหญ่เกินกำหนดของ server',
            UPLOAD_ERR_FORM_SIZE => 'ไฟล์ใหญ่เกินกำหนดของฟอร์ม',
            UPLOAD_ERR_PARTIAL => 'อัปโหลดไฟล์ไม่สมบูรณ์',
            UPLOAD_ERR_NO_FILE => 'ไม่พบไฟล์ที่อัปโหลด',
            UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ชั่วคราว',
            UPLOAD_ERR_CANT_WRITE => 'ไม่สามารถเขียนไฟล์ได้',
            UPLOAD_ERR_EXTENSION => 'ส่วนขยาย PHP หยุดการอัปโหลด'
        ];
        return $messages[$code] ?? 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ';
    }
}
