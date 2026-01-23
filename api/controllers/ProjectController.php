<?php
/**
 * Project Controller
 * 
 * Endpoints:
 * GET    /projects          - รายการโครงการ
 * GET    /projects/:id      - รายละเอียดโครงการ
 * POST   /projects          - สร้างโครงการ (Admin)
 * PUT    /projects/:id      - แก้ไขโครงการ (Admin)
 * DELETE /projects/:id      - ลบโครงการ (Admin)
 */

class ProjectController
{
    const VERSION = '2.1';
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

        $sql = "INSERT INTO edonation_projects (project_number, project_name, project_receipt_name, description, status) 
                VALUES (:number, :name, :receipt_name, :description, :status)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':number' => $data['project_number'],
            ':name' => $data['project_name'],
            ':receipt_name' => $data['project_receipt_name'] ?? $data['project_name'],
            ':description' => $data['description'] ?? null,
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

        $allowedFields = ['project_name', 'project_receipt_name', 'description', 'status'];
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
}
