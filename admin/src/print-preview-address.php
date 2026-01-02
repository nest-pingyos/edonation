<?php
require_once 'services/session.php';
requireAuth();
$pdo = DatabaseService::getInstance();

// Parameters
$selected_tables = $_GET['tables'] ?? [];
$min_amount = floatval($_GET['min_amount'] ?? 0);
$filter_address = isset($_GET['has_address']);
$paper_size = $_GET['paper_size'] ?? 'A4'; // A4, A5
$orientation = $_GET['orientation'] ?? 'portrait'; // portrait, landscape
$text_orientation = $_GET['text_orientation'] ?? 'horizontal'; // horizontal, vertical
$report_mode = $_GET['report_mode'] ?? 'address'; // address, summary

// Custom Size Params (CM)
$custom_w = floatval($_GET['custom_width'] ?? 10);
$custom_h = floatval($_GET['custom_height'] ?? 15);

// Offset Params (CM)
$off_x = floatval($_GET['offset_x'] ?? 0);
$off_y = floatval($_GET['offset_y'] ?? 0);
$transform_offset = "translate({$off_x}cm, {$off_y}cm)";

// Allowed tables whitelist
$allowed_tables = ['edonation_receipts'];

$data = [];
$errors = [];

// Calculate dimensions
if ($report_mode === 'summary') {
    $width = '210mm';
    $height = '297mm';
} elseif ($paper_size === 'custom') {
    // Treat input as base dimensions in CM
    if ($orientation === 'portrait') {
        $width_val = $custom_w;
        $height_val = $custom_h;
    } else {
        $width_val = $custom_h;
        $height_val = $custom_w;
    }
    $width = $width_val . 'cm';
    $height = $height_val . 'cm';
} else {
    $dimensions = [
        'A4' => ['portrait' => ['w' => '210mm', 'h' => '297mm'], 'landscape' => ['w' => '297mm', 'h' => '210mm']],
        'A5' => ['portrait' => ['w' => '148mm', 'h' => '210mm'], 'landscape' => ['w' => '210mm', 'h' => '148mm']]
    ];
    $width = $dimensions[$paper_size][$orientation]['w'];
    $height = $dimensions[$paper_size][$orientation]['h'];
}

// Determine orientation/layout based on paper size if needed
// A4 defaults to portrait, A5 to landscape? Or user just sets size.

if (empty($selected_tables) || !is_array($selected_tables)) {
    $errors[] = "ไม่ได้เลือกข้อมูล (ตาราง)";
} else {
    foreach ($selected_tables as $table) {
        if (!in_array($table, $allowed_tables))
            continue;

        try {
            if ($table === 'edonation_receipts') {
                $sql = "SELECT r.*, du.first_name, du.last_name, du.id_card,
                               du.address_line, du.province, du.amphure, du.district, du.zip_code,
                               du.phone, du.need_receipt
                        FROM edonation_receipts r
                        LEFT JOIN edonation_donat_user du ON r.donation_id = du.id";
            } else {
                $sql = "SELECT * FROM `$table`";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                // normalize amount
                $amount = floatval($row['amount'] ?? 0);
                if ($min_amount > 0 && $amount < $min_amount) {
                    continue;
                }

                // normalize name
                $name = $row['payer_name'] ?? $row['name'] ?? $row['payerAccountName'] ?? '';
                if (empty($name) || trim($name) === '') {
                    $first = $row['first_name'] ?? '';
                    $last = $row['last_name'] ?? '';
                    $name = trim("$first $last");
                }

                if (empty($name))
                    $name = 'ไม่ระบุชื่อ';
                $name = trim($name);

                // normalize address
                $address = '';

                // Attempt to construct from specific components first (User preference: address, province, amphure, district, zip_code)
                $street = $row['address'] ?? $row['address_line'] ?? ''; // 'address' acts as street when components exist

                // Map columns based on user request (district usually = Tambon, amphure = Amphure in this context)
                $subdist = $row['district'] ?? $row['subdistrict'] ?? $row['tambon'] ?? '';
                $dist_val = $row['amphure'] ?? $row['amphur'] ?? '';
                $prov = $row['province'] ?? '';
                $zip = $row['zip_code'] ?? $row['postcode'] ?? '';

                // If we have at least one administrative component, we construct
                if (!empty($prov) || !empty($dist_val) || !empty($subdist)) {
                    $parts = [];

                    if (!empty($street) && trim($street) !== '') {
                        $parts[] = trim($street);
                    }

                    if (!empty($subdist)) {
                        $s = trim($subdist);
                        if (!str_contains($s, 'ต.') && !str_contains($s, 'แขวง'))
                            $s = 'ต.' . $s;
                        $parts[] = $s;
                    }

                    if (!empty($dist_val)) {
                        $d = trim($dist_val);
                        if (!str_contains($d, 'อ.') && !str_contains($d, 'เขต'))
                            $d = 'อ.' . $d;
                        $parts[] = $d;
                    }

                    if (!empty($prov)) {
                        $p = trim($prov);
                        if (!str_contains($p, 'จ.') && !str_contains($p, 'กรุงเทพ'))
                            $p = 'จ.' . $p;
                        $parts[] = $p;
                    }

                    if (!empty($zip)) {
                        $parts[] = trim($zip);
                    }

                    if (!empty($parts)) {
                        $address = implode(' ', $parts);
                    }
                }

                // Fallback: if construction failed (no components), try single blob columns
                if (empty($address)) {
                    $address = $row['address'] ?? $row['receipt_address'] ?? '';
                }

                // If address is still empty and we have donation_id, maybe we should query edonation_donat_user?
                // But for now, let's assume the archive table has it.
                // If the user specific requirements says "Filter has address", we skip if empty.

                if ($filter_address && (empty($address) || trim($address) === '' || trim($address) === '-')) {
                    continue;
                }

                $data[] = [
                    'source' => $table,
                    'receipt_no' => $row['receipt_no'] ?? '-',
                    'name' => $name,
                    'address' => $address,
                    'amount' => $amount,
                    'date' => $row['created_at'] ?? $row['issued_at'] ?? $row['receipt_date'] ?? date('Y-m-d')
                ];
            }

        } catch (PDOException $e) {
            // $errors[] = "ไม่สามารถอ่านข้อมูลจากตาราง $table ได้: " . $e->getMessage();
            // Ignore error, maybe table doesn't exist yet
            continue;
        }
    }
}

// Sort by date or receipt no?
// usort($data, function($a, $b) { return strcmp($a['receipt_no'], $b['receipt_no']); });

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>พิมพ์ชื่อ-ที่อยู่ผู้บริจาค (<?php echo htmlspecialchars($paper_size . ' - ' . ucfirst($orientation)); ?>)
    </title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --paper-width:
                <?php echo $width; ?>
            ;
            --paper-height:
                <?php echo $height; ?>
            ;
            --label-height: 35mm;
            /* Approx height per entry */
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: #eee;
            margin: 0;
            padding: 20px;
            -webkit-print-color-adjust: exact;
        }

        .page {
            width: var(--paper-width);
            min-height: var(--paper-height);
            background: white;
            margin: 0 auto 20px auto;
            padding: 10mm;
            /* Margin for printer */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;

            /* Logic for A5/Custom Center Layout */
            <?php if ($paper_size === 'A5' || $paper_size === 'custom'): ?>
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                page-break-after: always;
                /* Each page div is one sheet */
                padding:
                    <?php echo $paper_size === 'custom' ? '5mm' : '20mm'; ?>
                ;
            <?php endif; ?>
        }

        /* Label Style (Address List) */
        .address-item {
            <?php if ($paper_size === 'A4'): ?>
                border-bottom: 1px dashed #ccc;
                padding: 10px 0;
                page-break-inside: avoid;
                /* List view rotation usually not needed but supported */
                <?php if ($text_orientation === 'vertical' || $off_x != 0 || $off_y != 0): ?>
                    transform:
                        <?php echo $transform_offset; ?>
                        <?php echo $text_orientation === 'vertical' ? 'rotate(-90deg)' : ''; ?>
                    ;
                    transform-origin: center;
                    overflow: visible;
                <?php endif; ?>

            <?php else: ?>
                /* A5/Custom Style */
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                <?php if ($text_orientation === 'vertical' || $off_x != 0 || $off_y != 0): ?>
                    transform:
                        <?php echo $transform_offset; ?>
                        <?php echo $text_orientation === 'vertical' ? 'rotate(-90deg)' : ''; ?>
                    ;
                <?php endif; ?>
            <?php endif; ?>
        }

        <?php if ($paper_size === 'A4'): ?>
            .address-item:last-child {
                border-bottom: none;
            }

        <?php endif; ?>

        .donor-name {
            font-size:
                <?php echo ($paper_size === 'A5' || $paper_size === 'custom') ? '18px' : '14px'; ?>
            ;
            font-weight: 400;
            color: #000;
            margin-bottom:
                <?php echo ($paper_size === 'A5' || $paper_size === 'custom') ? '10px' : '0'; ?>
            ;
        }

        .donor-address {
            font-size:
                <?php echo ($paper_size === 'A5' || $paper_size === 'custom') ? '16px' : '12px'; ?>
            ;
            color: #000;
            margin-top: 4px;
            line-height: 1.5;
        }

        .meta-info {
            font-size: 11px;
            color: #666;
            margin-top:
                <?php echo ($paper_size === 'A5' || $paper_size === 'custom') ? '20px' : '4px'; ?>
            ;
            display: flex;
            gap: 10px;
            <?php if ($paper_size === 'A5' || $paper_size === 'custom')
                echo 'justify-content: center;'; ?>
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            <?php if ($paper_size === 'A5' || $paper_size === 'custom')
                echo 'display: none;'; ?>
            /* Hide checklist header on A5 labels */
        }

        .btn-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
            z-index: 1000;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            background: #0b5ed7;
        }

        /* Summary Table Styles */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 13px;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        .summary-table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .summary-table .col-no {
            width: 40px;
            text-align: center;
        }

        .summary-table .col-receipt {
            width: 120px;
        }

        .summary-table .col-name {
            width: 220px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .page {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: auto;
                /* Allow natural height expansion */
                page-break-after: auto;
                /* Default for summary, manually handled if needed */
            }

            /* Address/Label mode still needs page breaks */
            .page-address,
            .page-label {
                min-height: 100vh;
                page-break-after: always;
            }

            .btn-print {
                display: none;
            }

            @page {
                size:
                    <?php echo $report_mode === 'summary' ? 'A4 portrait' : ($paper_size . ' ' . $orientation); ?>
                ;
                margin: 10mm;
                /* Browser default margins */
            }

            /* Summary Pagination */
            .summary-table thead {
                display: table-header-group;
            }

            .summary-table tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    <?php if (!empty($errors)): ?>
        <div style="text-align: center; padding: 50px;">
            <h3 style="color: red;">เกิดข้อผิดพลาด</h3>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>

        <?php if ($report_mode === 'summary'): ?>
            <!-- Summary List Mode: Table View -->
            <div class="page page-summary">
                <div class="header">
                    <h2>ใบสรุปรายชื่อการส่งเอกสาร/ใบเสร็จ</h2>
                    <div style="font-size: 14px;">
                        แหล่งข้อมูล: <?php echo implode(', ', $selected_tables); ?> |
                        ยอดขั้นต่ำ: <?php echo number_format($min_amount); ?> บาท |
                        รวม: <?php echo count($data); ?> รายการ
                    </div>
                </div>

                <?php if (count($data) > 0): ?>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th class="col-no">ลำดับ</th>
                                <th class="col-receipt">เลขที่ใบเสร็จ</th>
                                <th class="col-name">ชื่อ-นามสกุล</th>
                                <th>ที่อยู่</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $index => $item): ?>
                                <tr>
                                    <td class="col-no"><?php echo $index + 1; ?></td>
                                    <td class="col-receipt"><?php echo htmlspecialchars($item['receipt_no']); ?></td>
                                    <td class="col-name"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['address']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #777;">
                        ไม่พบข้อมูลตามเงื่อนไข
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($paper_size === 'A5' || $paper_size === 'custom'): ?>
            <!-- A5/Custom Mode: 1 Item per Page -->
            <?php if (count($data) > 0): ?>
                <?php foreach ($data as $index => $item): ?>
                    <div class="page page-label">
                        <div class="address-item">
                            <div class="donor-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="donor-address"><?php echo htmlspecialchars($item['address']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="page" style="display: flex; justify-content: center; align-items: center;">
                    <div style="color: #777;">ไม่พบข้อมูลตามเงื่อนไข</div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- A4 Mode: List View (Labels) -->
            <div class="page page-address">
                <div class="header">
                    <h2>รายการที่อยู่ผู้บริจาค (จ่าหน้า)</h2>
                    <div style="font-size: 14px;">
                        แหล่งข้อมูล: <?php echo implode(', ', $selected_tables); ?> |
                        ยอดขั้นต่ำ: <?php echo number_format($min_amount); ?> บาท |
                        รวม: <?php echo count($data); ?> รายการ
                    </div>
                </div>

                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $item): ?>
                        <div class="address-item">
                            <div class="donor-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="donor-address"><?php echo htmlspecialchars($item['address']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #777;">
                        ไม่พบข้อมูลตามเงื่อนไข
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            สั่งพิมพ์
        </button>

    <?php endif; ?>

</body>

</html>