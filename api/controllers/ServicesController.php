<?php
/**
 * Services Controller
 * 
 * API for managing donation services (souvenirs)
 */

class ServicesController
{
    private $db;

    public function __construct()
    {
        $this->db = require_once __DIR__ . '/../../config/database.php';
    }

    /**
     * Handle API requests
     */
    public function handle($method, $id = null, $action = null)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->show($id);
                }
                return $this->index();
            default:
                http_response_code(405);
                return ['success' => false, 'error' => ['message' => 'Method not allowed']];
        }
    }

    /**
     * Get all services (souvenirs)
     */
    public function index(): array
    {
        try {
            // Get query parameters
            $type = $_GET['type'] ?? 'all'; // 'service' or 'service_donat' or 'all'
            $active = $_GET['active'] ?? '1';

            $services = [];

            // Get from 'service' table (souvenir)
            if ($type === 'all' || $type === 'service') {
                $stmt = $this->db->prepare("SELECT * FROM `service` ORDER BY id DESC");
                $stmt->execute();
                $souvenirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($souvenirs as &$item) {
                    $item['type'] = 'souvenir';
                    $item['image_url'] = $this->getImageUrl($item);
                }
                $services = array_merge($services, $souvenirs);
            }

            // Get from 'service_donat' table
            if ($type === 'all' || $type === 'service_donat') {
                $stmt = $this->db->prepare("SELECT * FROM `service_donat` ORDER BY id DESC");
                $stmt->execute();
                $donatServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($donatServices as &$item) {
                    $item['type'] = 'service_donat';
                    $item['image_url'] = $this->getServiceDonatImageUrl($item);
                }
                $services = array_merge($services, $donatServices);
            }

            return [
                'success' => true,
                'data' => $services,
                'meta' => [
                    'total' => count($services)
                ]
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return [
                'success' => false,
                'error' => ['message' => 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Get single service by ID
     */
    public function show($id): array
    {
        try {
            $type = $_GET['type'] ?? 'service';
            $table = $type === 'service_donat' ? 'service_donat' : 'service';

            $stmt = $this->db->prepare("SELECT * FROM `$table` WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$service) {
                http_response_code(404);
                return [
                    'success' => false,
                    'error' => ['message' => 'ไม่พบข้อมูล']
                ];
            }

            $service['type'] = $type === 'service_donat' ? 'service_donat' : 'souvenir';
            $service['image_url'] = $type === 'service_donat'
                ? $this->getServiceDonatImageUrl($service)
                : $this->getImageUrl($service);

            return [
                'success' => true,
                'data' => $service
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return [
                'success' => false,
                'error' => ['message' => 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Get image URL for souvenir
     */
    private function getImageUrl($item): string
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';

        if (!empty($item['img_file'])) {
            return $basePath . '/assets/images/products/' . $item['img_file'];
        }

        // Default image based on ID (cycling 1-7)
        $imageNumber = (($item['id'] - 1) % 7) + 1;
        return $basePath . '/assets/images/products/' . $imageNumber . '.jpg';
    }

    /**
     * Get image URL for service_donat
     */
    private function getServiceDonatImageUrl($item): string
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';

        if (!empty($item['img_file'])) {
            return $basePath . '/assets/images/products/' . $item['img_file'];
        }

        return $basePath . '/assets/images/products/default.jpg';
    }
}
