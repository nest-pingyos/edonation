<?php

declare(strict_types=1);

/**
 * Services Controller
 *
 * API for managing donation services (souvenirs)
 *
 * @package eDonation\API\Controllers
 * @version 2.0.0 - Refactored for PSR-12, Security & Performance
 */

class ServicesController
{
    private const ALLOWED_TABLES = [
        'service' => 'service',
        'service_donat' => 'service_donat'
    ];

    private const SERVICE_COLUMNS = [
        'id',
        'name',
        'description',
        'price',
        'img_file',
        'is_active',
        'created_at',
        'updated_at'
    ];

    private const SERVICE_DONAT_COLUMNS = [
        'id',
        'name',
        'description',
        'amount',
        'img_file',
        'status',
        'created_at'
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = require_once __DIR__ . '/../../config/database.php';
    }

    /**
     * Handle API requests
     */
    public function handle(string $method, ?string $id = null, ?string $action = null): array
    {
        return match ($method) {
            'GET' => $id ? $this->show($id) : $this->index(),
            default => $this->methodNotAllowed()
        };
    }

    /**
     * Get all services (souvenirs)
     */
    public function index(): array
    {
        try {
            $type = $this->sanitizeType($_GET['type'] ?? 'all');
            $services = [];

            if ($type === 'all' || $type === 'service') {
                $services = array_merge(
                    $services,
                    $this->fetchServices('service')
                );
            }

            if ($type === 'all' || $type === 'service_donat') {
                $services = array_merge(
                    $services,
                    $this->fetchServices('service_donat')
                );
            }

            return $this->successResponse($services, [
                'total' => count($services)
            ]);
        } catch (PDOException $e) {
            error_log("Services Index Error: " . $e->getMessage());
            return $this->errorResponse('ไม่สามารถดึงข้อมูลได้', 500);
        } catch (Exception $e) {
            error_log("Services Index Unexpected Error: " . $e->getMessage());
            return $this->errorResponse('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Get single service by ID
     */
    public function show(string $id): array
    {
        try {
            if (!$this->isValidId($id)) {
                return $this->errorResponse('รูปแบบ ID ไม่ถูกต้อง', 400);
            }

            $type = $this->sanitizeType($_GET['type'] ?? 'service');
            $tableName = self::ALLOWED_TABLES[$type];
            $columns = $this->getColumnsForTable($tableName);

            // SECURITY: Use whitelisted table name, never interpolate user input
            $columnsList = implode(', ', $columns);
            $sql = match ($tableName) {
                'service' => "SELECT {$columnsList} FROM `service` WHERE id = :id LIMIT 1",
                'service_donat' => "SELECT {$columnsList} FROM `service_donat` WHERE id = :id LIMIT 1",
            };

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$service) {
                return $this->errorResponse('ไม่พบข้อมูล', 404);
            }

            $service['type'] = ($type === 'service_donat') ? 'service_donat' : 'souvenir';
            $service['image_url'] = ($type === 'service_donat')
                ? $this->getServiceDonatImageUrl($service)
                : $this->getImageUrl($service);

            return $this->successResponse($service);
        } catch (PDOException $e) {
            error_log("Services Show Error: " . $e->getMessage());
            return $this->errorResponse('ไม่สามารถดึงข้อมูลได้', 500);
        } catch (Exception $e) {
            error_log("Services Show Unexpected Error: " . $e->getMessage());
            return $this->errorResponse('เกิดข้อผิดพลาดภายในระบบ', 500);
        }
    }

    /**
     * Fetch services from specific table
     */
    private function fetchServices(string $type): array
    {
        $tableName = self::ALLOWED_TABLES[$type];
        $columns = $this->getColumnsForTable($tableName);
        $columnsList = implode(', ', $columns);

        $sql = match ($tableName) {
            'service' => "SELECT {$columnsList} FROM `service` ORDER BY id DESC",
            'service_donat' => "SELECT {$columnsList} FROM `service_donat` ORDER BY id DESC",
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $item['type'] = ($type === 'service_donat') ? 'service_donat' : 'souvenir';
            $item['image_url'] = ($type === 'service_donat')
                ? $this->getServiceDonatImageUrl($item)
                : $this->getImageUrl($item);
        }

        return $items;
    }

    /**
     * Get columns for specific table
     */
    private function getColumnsForTable(string $tableName): array
    {
        return match ($tableName) {
            'service' => self::SERVICE_COLUMNS,
            'service_donat' => self::SERVICE_DONAT_COLUMNS,
            default => []
        };
    }

    /**
     * Sanitize and validate type parameter
     */
    private function sanitizeType(string $type): string
    {
        $allowedTypes = ['all', 'service', 'service_donat'];
        return in_array($type, $allowedTypes, true) ? $type : 'service';
    }

    /**
     * Validate ID format
     */
    private function isValidId(string $id): bool
    {
        return ctype_digit($id) && (int) $id > 0;
    }

    /**
     * Get image URL for souvenir
     */
    private function getImageUrl(array $item): string
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';

        if (!empty($item['img_file'])) {
            return $basePath . '/assets/images/products/' . basename($item['img_file']);
        }

        $imageNumber = ((int) $item['id'] - 1) % 7 + 1;
        return $basePath . '/assets/images/products/' . $imageNumber . '.jpg';
    }

    /**
     * Get image URL for service_donat
     */
    private function getServiceDonatImageUrl(array $item): string
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';

        if (!empty($item['img_file'])) {
            return $basePath . '/assets/images/products/' . basename($item['img_file']);
        }

        return $basePath . '/assets/images/products/default.jpg';
    }

    /**
     * Success response helper
     */
    private function successResponse(array|object $data, ?array $meta = null): array
    {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return $response;
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $code = 400): array
    {
        http_response_code($code);
        return [
            'success' => false,
            'error' => ['message' => $message]
        ];
    }

    /**
     * Method not allowed response
     */
    private function methodNotAllowed(): array
    {
        return $this->errorResponse('Method not allowed', 405);
    }
}
