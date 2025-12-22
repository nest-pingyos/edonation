<?php
/**
 * Project Controller
 * 
 * Endpoints:
 * GET    /projects          - รายการโครงการ
 * GET    /projects/:id      - รายละเอียดโครงการ
 * POST   /projects          - สร้างโครงการ (Admin)
 * PUT    /projects/:id      - แก้ไขโครงการ (Admin)
 */

class ProjectController {
    private PDO $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance();
    }
    
    public function handle(string $method, ?string $id, ?string $action): array {
        switch ($method) {
            case 'GET':
                return $id ? $this->show($id) : $this->index();
            case 'POST':
                AuthMiddleware::requireAdmin();
                return $this->create();
            case 'PUT':
                AuthMiddleware::requireAdmin();
                return $this->update($id);
            default:
                return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
        }
    }
    
    // GET /projects
    private function index(): array {
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            
            // Simple query without status filter first
            $sql = "SELECT * FROM edonation_projects ORDER BY id DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // Add image URL - use BASE_PATH from config (assets are in web/ folder)
            $baseImageUrl = (defined('BASE_PATH') ? BASE_PATH : '/edonation') . '/web/assets/images/projects/';
            foreach ($results as &$item) {
                // Check if img_file exists (legacy) or just use default rotation based on ID
                if (!empty($item['img_file'])) {
                    $item['image_url'] = $baseImageUrl . $item['img_file'];
                } elseif (!empty($item['image_url'])) { // New schema support
                    $item['image_url'] = strpos($item['image_url'], 'http') === 0 
                        ? $item['image_url'] 
                        : $baseImageUrl . basename($item['image_url']);
                } else {
                    // Fallback to rotation of pro-1, pro-2, pro-3 based on ID
                    $imgNum = ($item['id'] % 3) + 1;
                    $item['image_url'] = $baseImageUrl . "pro-{$imgNum}.jpg"; 
                }
            }
            
            return Response::success($results);
        } catch (PDOException $e) {
            error_log("Projects index error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }
    
    // GET /projects/:id
    private function show(string $id): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM edonation_projects WHERE id = :id OR project_number = :pn LIMIT 1"
            );
            $stmt->execute([':id' => $id, ':pn' => $id]);
            $project = $stmt->fetch();
            
            if (!$project) return Response::notFound('ไม่พบโครงการ');
            
            // Add image URL logic - use BASE_PATH from config (assets are in web/ folder)
            $baseImageUrl = (defined('BASE_PATH') ? BASE_PATH : '/edonation') . '/web/assets/images/projects/';
            if (!empty($project['img_file'])) {
                $project['image_url'] = $baseImageUrl . $project['img_file'];
            } elseif (!empty($project['image_url'])) {
                $project['image_url'] = strpos($project['image_url'], 'http') === 0 
                    ? $project['image_url'] 
                    : $baseImageUrl . basename($project['image_url']);
            } else {
                $imgNum = ($project['id'] % 3) + 1;
                $project['image_url'] = $baseImageUrl . "pro-{$imgNum}.jpg";
            }
            
            return Response::success($project);
        } catch (PDOException $e) {
            error_log("Project show error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }
    
    // POST /projects
    private function create(): array {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $v = new Validator($data);
        $v->required('project_number')->required('project_name');
        
        if (!$v->passes()) return Response::validation($v->errors());
        
        try {
            // Check existing columns
            $columnsStmt = $this->pdo->query("SHOW COLUMNS FROM edonation_projects");
            $columns = array_column($columnsStmt->fetchAll(), 'Field');
            
            // Build dynamic insert based on available columns
            $insertCols = ['project_number', 'project_name'];
            $insertVals = [':number', ':name'];
            $params = [
                ':number' => $data['project_number'],
                ':name' => $data['project_name']
            ];
            
            if (in_array('project_tex', $columns) && isset($data['project_tex'])) {
                $insertCols[] = 'project_tex';
                $insertVals[] = ':tex';
                $params[':tex'] = $data['project_tex'];
            }
            
            if (in_array('status', $columns)) {
                $insertCols[] = 'status';
                $insertVals[] = ':status';
                $params[':status'] = $data['status'] ?? 'active';
            }
            
            $sql = "INSERT INTO edonation_projects (" . implode(', ', $insertCols) . ") VALUES (" . implode(', ', $insertVals) . ")";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return Response::success(['id' => $this->pdo->lastInsertId()], 'สร้างโครงการสำเร็จ');
        } catch (PDOException $e) {
            error_log("Create project error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถสร้างโครงการได้: ' . $e->getMessage(), 500);
        }
    }
    
    // PUT /projects/:id
    private function update(string $id): array {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $fields = [];
        $params = [':id' => $id];
        
        foreach (['project_name', 'project_tex', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        
        if (empty($fields)) return Response::error('NO_DATA', 'ไม่มีข้อมูลที่จะอัปเดต');
        
        $sql = "UPDATE edonation_projects SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return Response::success(null, 'อัปเดตสำเร็จ');
    }
}
