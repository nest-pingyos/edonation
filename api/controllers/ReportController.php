<?php
/**
 * Report Controller
 * 
 * ใช้ edonation_receipts เป็นตารางหลัก
 * JOIN กับ edonation_donat_user (donation_id) และ edonation_bank_transactions (bank_transaction_id)
 * 
 * Endpoints:
 * GET    /reports/daily     - รายงานประจำวัน
 * GET    /reports/monthly   - รายงานประจำเดือน
 * GET    /reports/yearly    - รายงานประจำปี
 * GET    /reports/summary   - สรุปภาพรวม
 * 
 * @version 1.0
 */

class ReportController
{
    private PDO $pdo;

    const VERSION = '2.0';

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        // Only GET is supported
        if ($method !== 'GET') {
            return Response::error('METHOD_NOT_ALLOWED', 'Method not allowed', 405);
        }

        // Require admin auth
        AuthMiddleware::requireAdmin();

        switch ($id) {
            case 'daily':
                return $this->dailyReport();
            case 'monthly':
                return $this->monthlyReport();
            case 'yearly':
                return $this->yearlyReport();
            case 'summary':
                return $this->summary();
            default:
                return Response::error('NOT_FOUND', 'ไม่พบ endpoint ที่ระบุ', 404);
        }
    }

    /**
     * GET /reports/daily?date=2024-12-29
     */
    private function dailyReport(): array
    {
        $date = $_GET['date'] ?? date('Y-m-d');

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return Response::error('VALIDATION_ERROR', 'รูปแบบวันที่ไม่ถูกต้อง (YYYY-MM-DD)');
        }

        try {
            // Main query - receipts as primary table
            $sql = "SELECT 
                        r.id,
                        r.receipt_no,
                        r.payer_name,
                        r.amount,
                        r.issued_at,
                        r.donation_id,
                        r.bank_transaction_id,
                        du.project_name,
                        du.project_number,
                        du.title,
                        du.first_name,
                        du.last_name,
                        du.id_card,
                        du.payby,
                        du.status_donat,
                        du.address_line,
                        du.province,
                        du.amphure,
                        du.district,
                        du.zip_code,
                        du.phone,
                        bt.transactionId,
                        bt.transactionDateandTime,
                        bt.sendingBankCode,
                        bt.billPaymentRef1,
                        bt.billPaymentRef2
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                    WHERE DATE(r.issued_at) = :date
                    ORDER BY r.issued_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':date' => $date]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate statistics
            $stats = $this->calculateStats($results);

            // Group by hour for chart
            $hourlyData = array_fill(0, 24, 0);
            foreach ($results as $row) {
                $hour = (int) date('H', strtotime($row['issued_at']));
                $hourlyData[$hour] += floatval($row['amount']);
            }

            // Format results
            $formatted = array_map(function ($row) {
                return $this->formatReceiptRow($row);
            }, $results);

            return Response::success([
                'date' => $date,
                'receipts' => $formatted,
                'stats' => $stats,
                'hourly_data' => $hourlyData
            ], null, [
                'api_version' => self::VERSION,
                'source_table' => 'edonation_receipts'
            ]);

        } catch (PDOException $e) {
            error_log("Daily report error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /reports/monthly?month=12&year=2024
     * Note: year parameter is Fiscal Year (ปีงบประมาณ)
     * Fiscal Year = Oct(Y-1) - Sep(Y)
     */
    private function monthlyReport(): array
    {
        $month = intval($_GET['month'] ?? date('n'));
        $fiscalYear = intval($_GET['year'] ?? (date('n') >= 10 ? date('Y') + 1 : date('Y')));

        // Validate
        if ($month < 1 || $month > 12) {
            return Response::error('VALIDATION_ERROR', 'เดือนไม่ถูกต้อง');
        }
        if ($fiscalYear < 2020 || $fiscalYear > 2100) {
            return Response::error('VALIDATION_ERROR', 'ปีไม่ถูกต้อง');
        }

        // Calculate actual calendar year based on fiscal year
        // For months Oct-Dec (10-12), actual year = fiscalYear - 1
        // For months Jan-Sep (1-9), actual year = fiscalYear
        $actualYear = ($month >= 10) ? $fiscalYear - 1 : $fiscalYear;

        try {
            $startDate = sprintf('%04d-%02d-01', $actualYear, $month);
            $lastDay = date('t', strtotime($startDate));
            $endDate = sprintf('%04d-%02d-%02d', $actualYear, $month, $lastDay);

            // Main query
            $sql = "SELECT 
                        r.id,
                        r.receipt_no,
                        r.payer_name,
                        r.amount,
                        r.issued_at,
                        r.donation_id,
                        r.bank_transaction_id,
                        du.project_name,
                        du.project_number,
                        du.title,
                        du.first_name,
                        du.last_name,
                        du.id_card,
                        du.payby,
                        du.status_donat,
                        du.address_line,
                        du.province,
                        du.amphure,
                        du.district,
                        du.zip_code,
                        du.phone,
                        bt.transactionId,
                        bt.billPaymentRef1,
                        bt.billPaymentRef2
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                    WHERE DATE(r.issued_at) BETWEEN :start AND :end
                    ORDER BY r.issued_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':start' => $startDate, ':end' => $endDate]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate stats
            $stats = $this->calculateStats($results);

            // Group by day for chart
            $dailyData = array_fill(1, $lastDay, 0);
            foreach ($results as $row) {
                $day = (int) date('j', strtotime($row['issued_at']));
                $dailyData[$day] += floatval($row['amount']);
            }

            // Group by project
            $projectSummary = $this->groupByProject($results);

            // Find best day
            $bestDay = 0;
            $bestAmount = 0;
            foreach ($dailyData as $day => $amount) {
                if ($amount > $bestAmount) {
                    $bestAmount = $amount;
                    $bestDay = $day;
                }
            }

            // Format results
            $formatted = array_map(function ($row) {
                return $this->formatReceiptRow($row);
            }, $results);

            return Response::success([
                'month' => $month,
                'year' => $fiscalYear,
                'receipts' => $formatted,
                'stats' => array_merge($stats, [
                    'best_day' => $bestDay,
                    'best_day_amount' => $bestAmount
                ]),
                'daily_data' => array_values($dailyData),
                'project_summary' => $projectSummary
            ], null, [
                'api_version' => self::VERSION,
                'source_table' => 'edonation_receipts'
            ]);

        } catch (PDOException $e) {
            error_log("Monthly report error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /reports/yearly?year=2024
     */
    private function yearlyReport(): array
    {
        // current fiscal year: if month >= 10, fiscal year = current_year + 1
        $currentFiscalYear = intval(date('n')) >= 10 ? intval(date('Y')) + 1 : intval(date('Y'));
        $year = intval($_GET['year'] ?? $currentFiscalYear);

        if ($year < 2020 || $year > 2100) {
            return Response::error('VALIDATION_ERROR', 'ปีไม่ถูกต้อง');
        }

        try {
            // Fiscal Year Range: Oct (Y-1) to Sep (Y)
            $startDate = ($year - 1) . "-10-01";
            $endDate = $year . "-09-30";

            // Previous Fiscal Year Range for comparison
            $prevStartDate = ($year - 2) . "-10-01";
            $prevEndDate = ($year - 1) . "-09-30";

            // Current year query
            $sql = "SELECT 
                        r.id,
                        r.receipt_no,
                        r.payer_name,
                        r.amount,
                        r.issued_at,
                        r.donation_id,
                        du.project_name,
                        du.project_number,
                        du.title,
                        du.first_name,
                        du.last_name,
                        du.id_card,
                        du.payby,
                        du.status_donat,
                        du.address_line,
                        du.province,
                        du.amphure,
                        du.district,
                        du.zip_code,
                        du.phone,
                        bt.billPaymentRef1,
                        bt.billPaymentRef2
                    FROM edonation_receipts r
                    LEFT JOIN edonation_donat_user du ON r.donation_id = du.id
                    LEFT JOIN edonation_bank_transactions bt ON r.bank_transaction_id = bt.id
                    WHERE r.issued_at BETWEEN :start AND :end
                    ORDER BY r.issued_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':start' => $startDate, ':end' => $endDate]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Previous year total for comparison
            $prevSql = "SELECT SUM(amount) as total FROM edonation_receipts WHERE issued_at BETWEEN :start AND :end";
            $prevStmt = $this->pdo->prepare($prevSql);
            $prevStmt->execute([':start' => $prevStartDate, ':end' => $prevEndDate]);
            $prevTotal = floatval($prevStmt->fetchColumn() ?: 0);

            // Stats
            $stats = $this->calculateStats($results);

            // Group by month for chart (Oct - Sep order)
            $fiscalMonths = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            $monthlyData = [];
            foreach ($fiscalMonths as $m)
                $monthlyData[$m] = 0;

            foreach ($results as $row) {
                $month = (int) date('n', strtotime($row['issued_at']));
                $monthlyData[$month] += floatval($row['amount']);
            }

            // Previous year monthly data
            $prevMonthlyData = [];
            foreach ($fiscalMonths as $m)
                $prevMonthlyData[$m] = 0;

            $prevMonthSql = "SELECT MONTH(issued_at) as month, SUM(amount) as total 
                             FROM edonation_receipts 
                             WHERE issued_at BETWEEN :start AND :end 
                             GROUP BY MONTH(issued_at)";
            $prevMonthStmt = $this->pdo->prepare($prevMonthSql);
            $prevMonthStmt->execute([':start' => $prevStartDate, ':end' => $prevEndDate]);
            while ($row = $prevMonthStmt->fetch(PDO::FETCH_ASSOC)) {
                $prevMonthlyData[(int) $row['month']] = floatval($row['total']);
            }

            // Find best month
            $bestMonth = 0;
            $bestAmount = 0;
            foreach ($monthlyData as $month => $amount) {
                if ($amount > $bestAmount) {
                    $bestAmount = $amount;
                    $bestMonth = $month;
                }
            }

            // Calculate growth
            $growth = $prevTotal > 0 ? (($stats['total_amount'] - $prevTotal) / $prevTotal * 100) : 0;

            // Group by project
            $projectSummary = $this->groupByProject($results);

            // Format results
            $formatted = array_map(function ($row) {
                return $this->formatReceiptRow($row);
            }, $results);

            return Response::success([
                'year' => $year,
                'receipts' => $formatted,
                'stats' => array_merge($stats, [
                    'best_month' => $bestMonth,
                    'best_month_amount' => $bestAmount,
                    'prev_year_total' => $prevTotal,
                    'growth_percent' => round($growth, 2)
                ]),
                'monthly_data' => array_values($monthlyData),
                'prev_monthly_data' => array_values($prevMonthlyData),
                'project_summary' => $projectSummary
            ], null, [
                'api_version' => self::VERSION,
                'source_table' => 'edonation_receipts'
            ]);

        } catch (PDOException $e) {
            error_log("Yearly report error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /reports/summary
     * ภาพรวมทั้งหมด
     */
    private function summary(): array
    {
        try {
            // Fiscal type: 'thai' (Oct-Sep) or 'calendar' (Jan-Dec)
            $fiscalType = $_GET['fiscal_type'] ?? 'thai';

            // Calculate current fiscal year based on type
            if ($fiscalType === 'calendar') {
                $currentFiscalYear = intval(date('Y'));
            } else {
                $currentFiscalYear = intval(date('n')) >= 10 ? intval(date('Y')) + 1 : intval(date('Y'));
            }

            $year = intval($_GET['year'] ?? $currentFiscalYear);
            $today = date('Y-m-d');

            // Calculate date range based on fiscal type
            if ($fiscalType === 'calendar') {
                // Calendar Year: Jan 1 - Dec 31
                $startDate = $year . "-01-01";
                $endDate = $year . "-12-31";
                $fiscalMonths = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            } else {
                // Thai Fiscal Year: Oct 1 (Y-1) - Sep 30 (Y)
                $startDate = ($year - 1) . "-10-01";
                $endDate = $year . "-09-30";
                $fiscalMonths = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            }

            // Summary Stats for the selected fiscal year
            $yearStmt = $this->pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM edonation_receipts WHERE issued_at BETWEEN :start AND :end");
            $yearStmt->execute([':start' => $startDate, ':end' => $endDate]);
            $yearStats = $yearStmt->fetch(PDO::FETCH_ASSOC);

            // Today's stats
            $todayStmt = $this->pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM edonation_receipts WHERE DATE(issued_at) = :date");
            $todayStmt->execute([':date' => $today]);
            $todayStats = $todayStmt->fetch(PDO::FETCH_ASSOC);

            // All time stats
            $allStmt = $this->pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM edonation_receipts");
            $allStats = $allStmt->fetch(PDO::FETCH_ASSOC);

            // Unique donors count (All time)
            $memberStmt = $this->pdo->query("SELECT COUNT(DISTINCT id_card) FROM edonation_donat_user WHERE (status_donat = 'completed' OR status_donat = 'CONFIRMED') AND id_card IS NOT NULL AND id_card != ''");
            $memberCount = (int) $memberStmt->fetchColumn();

            // Monthly data for selected fiscal year
            $monthlyData = [];
            foreach ($fiscalMonths as $m)
                $monthlyData[$m] = 0;

            $monthlyStmt = $this->pdo->prepare("SELECT MONTH(issued_at) as month, SUM(amount) as total FROM edonation_receipts WHERE issued_at BETWEEN :start AND :end GROUP BY MONTH(issued_at)");
            $monthlyStmt->execute([':start' => $startDate, ':end' => $endDate]);
            while ($row = $monthlyStmt->fetch()) {
                $monthlyData[(int) $row['month']] = floatval($row['total']);
            }

            // Project distribution (Top 5) for selected fiscal year
            $projectStmt = $this->pdo->prepare("SELECT du.project_name, SUM(r.amount) as total FROM edonation_receipts r LEFT JOIN edonation_donat_user du ON r.donation_id = du.id WHERE r.issued_at BETWEEN :start AND :end GROUP BY du.project_name ORDER BY total DESC LIMIT 5");
            $projectStmt->execute([':start' => $startDate, ':end' => $endDate]);
            $projects = $projectStmt->fetchAll();

            // Payment method distribution for selected fiscal year
            $payStmt = $this->pdo->prepare("SELECT du.payby, COUNT(*) as count, SUM(r.amount) as total FROM edonation_receipts r LEFT JOIN edonation_donat_user du ON r.donation_id = du.id WHERE r.issued_at BETWEEN :start AND :end GROUP BY du.payby");
            $payStmt->execute([':start' => $startDate, ':end' => $endDate]);
            $payments = $payStmt->fetchAll();

            return Response::success([
                'today' => [
                    'count' => (int) $todayStats['count'],
                    'total' => floatval($todayStats['total'])
                ],
                'selected_year' => [
                    'year' => $year,
                    'count' => (int) $yearStats['count'],
                    'total' => floatval($yearStats['total'])
                ],
                'all_time' => [
                    'count' => (int) $allStats['count'],
                    'total' => floatval($allStats['total']),
                    'members' => $memberCount
                ],
                'charts' => [
                    'monthly' => array_values($monthlyData),
                    'projects' => $projects,
                    'payments' => $payments
                ]
            ], null, [
                'api_version' => self::VERSION,
                'source_table' => 'edonation_receipts'
            ]);

        } catch (PDOException $e) {
            error_log("Summary report error: " . $e->getMessage());
            return Response::error('DATABASE_ERROR', 'ไม่สามารถดึงข้อมูลได้: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate basic statistics from results
     */
    private function calculateStats(array $results): array
    {
        $count = count($results);
        $total = 0;
        $confirmed = 0;

        foreach ($results as $row) {
            $total += floatval($row['amount']);
            if (($row['status_donat'] ?? '') === 'completed') {
                $confirmed++;
            }
        }

        return [
            'count' => $count,
            'total_amount' => $total,
            'confirmed_count' => $confirmed,
            'average' => $count > 0 ? round($total / $count, 2) : 0
        ];
    }

    /**
     * Group results by project
     */
    private function groupByProject(array $results): array
    {
        $projects = [];
        $total = 0;

        foreach ($results as $row) {
            $key = $row['project_name'] ?: $row['project_number'] ?: 'ไม่ระบุโครงการ';
            if (!isset($projects[$key])) {
                $projects[$key] = ['count' => 0, 'amount' => 0];
            }
            $projects[$key]['count']++;
            $projects[$key]['amount'] += floatval($row['amount']);
            $total += floatval($row['amount']);
        }

        // Sort by amount descending
        uasort($projects, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        // Add percentage
        $result = [];
        foreach ($projects as $name => $data) {
            $result[] = [
                'project_name' => $name,
                'count' => $data['count'],
                'amount' => $data['amount'],
                'percent' => $total > 0 ? round($data['amount'] / $total * 100, 1) : 0
            ];
        }

        return $result;
    }

    /**
     * Format receipt row for API response
     */
    private function formatReceiptRow(array $row): array
    {
        // Build donor name
        $name = trim($row['payer_name'] ?? '');
        if (empty($name)) {
            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        }
        if (empty($name)) {
            $name = 'ไม่ระบุชื่อ';
        }

        return [
            'id' => (int) $row['id'],
            'receipt_no' => $row['receipt_no'],
            'donor_name' => $name,
            'amount' => floatval($row['amount']),
            'issued_at' => $row['issued_at'],
            'project_name' => $row['project_name'] ?? '',
            'project_number' => $row['project_number'] ?? '',
            'pay_by' => $row['payby'] ?? 'ไม่ระบุ',
            'status' => ($row['status_donat'] ?? 'pending') === 'completed' ? 'CONFIRMED' : 'PENDING',
            'donation_id' => (int) ($row['donation_id'] ?? 0),
            'bank_transaction_id' => $row['bank_transaction_id'] ? (int) $row['bank_transaction_id'] : null,
            'transaction_id' => $row['transactionId'] ?? null,
            'ref1' => $row['billPaymentRef1'] ?? null,
            'tax_id' => $row['billPaymentRef2'] ?? $row['id_card'] ?? null,

            // CVS-CMU fields
            'title' => $row['title'] ?? '',
            'first_name' => $row['first_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'address_line' => $row['address_line'] ?? '',
            'province' => $row['province'] ?? '',
            'amphure' => $row['amphure'] ?? '',
            'district' => $row['district'] ?? '',
            'zip_code' => $row['zip_code'] ?? '',
            'phone' => $row['phone'] ?? ''
        ];
    }
}
