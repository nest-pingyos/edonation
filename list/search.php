<?php
require_once '../config/connect.php';

// ตรวจสอบว่าได้ส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyword = $_POST['keyword'] ?? '';
    $year = $_POST['year'] ?? '';

    // ตรวจสอบว่ามีการกรอกข้อมูล
    if (empty($keyword)) {
        echo showAlert('กรุณากรอกข้อมูลให้ถูกต้อง', 'ชื่อ-สกุล หรือ เลขบัตรประชาชน หรือ เลขที่ใบเสร็จที่ต้องการค้นหา');
        exit;
    }

    // ตรวจสอบความยาวของ keyword
    if (strlen($keyword) < 3) {
        echo showAlert('กรุณากรอกหมายเลขผู้เสียภาษี', 'กรุณากรอกข้อมูลให้ครบถ้วนเพื่อให้ค้นหาผลลัพธ์ได้แม่นยำ');
        exit;
    }

    // เลือกตารางตามปีที่เลือก
    $table = getTableByYear($year);
    if (!$table) {
        echo showAlert('ปีที่เลือกไม่ถูกต้อง', 'กรุณาเลือกปีที่ถูกต้อง');
        exit;
    }

    // สร้างคำสั่ง SQL
    $query = "SELECT * FROM $table WHERE (billPaymentRef1 LIKE :keyword OR billPaymentRef2 LIKE :keyword OR payerAccountName LIKE :keyword) ORDER BY id DESC";
    $stmt = $pdo->prepare($query);
    $searchKeyword = "%$keyword%";
    $stmt->bindParam(':keyword', $searchKeyword, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // สร้างผลลัพธ์ในรูปแบบ HTML
    $output = generateResults($results, $table);
    echo $output;
}

// ฟังก์ชันแสดง Alert
function showAlert($title, $text)
{
    return "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: '$title',
                text: '$text',
                confirmButtonColor: '#ffaa00',
                showConfirmButton: false,
                timer: 3000
            }).then(function() {
                window.location.href = '../list/';
            });
        </script>";
}


// ฟังก์ชันเลือกตารางตามปี
function getTableByYear($year)
{
    switch ($year) {
        case '2569':
            return 'donat';
        case '2568':
            return 'receipt_2568';
        case '2567':
            return 'receipt_2567';
        case '2566':
            return 'receipt_2566';
        default:
            return null;
    }
}

// ฟังก์ชันสร้างผลลัพธ์ HTML
function generateResults($results, $table)
{
    if (!$results) {
        return showAlert('เกิดข้อผิดพลาด', 'ไม่พบใบเสร็จตามคำค้นหาที่ระบุ');
    }

    $output = "<h5></h5>";
    foreach ($results as $row) {
        $output .= "<div class='col-12' style='border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;'>";
        $output .= "<div class='col-12'>";
        $output .= "<p><strong>ชื่อ-สกุล :</strong> " . htmlspecialchars($row['payerAccountName']) . "</p>";
        $output .= "<p><strong>เลขที่ใบเสร็จ :</strong> " . htmlspecialchars($row['billPaymentRef1']) . "</p>";
        $output .= "<p><strong>โครงการ :</strong> " . htmlspecialchars($row['project_name']) . "</p>";

        // ตรวจสอบว่ามีข้อมูลวันที่ใน receiptDate
        if (!empty($row['receiptDate'])) {
            $dateTime = new DateTime($row['receiptDate']);
            $dateTime->setTimezone(new DateTimeZone('Asia/Bangkok'));
            $formatter = new IntlDateFormatter('th_TH', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Asia/Bangkok', IntlDateFormatter::GREGORIAN);
            $thai_date = $formatter->format($dateTime);
            $output .= "<p><strong>วันที่บริจาค :</strong> " . htmlspecialchars($thai_date) . "</p>";
        } else {
            $output .= "<p><strong>วันที่บริจาค :</strong> ไม่ทราบวันที่</p>";
        }

        // เพิ่มการส่งค่า ID ไปยัง JavaScript
        $output .= "<a href='#' class='btn btn__secondary btn__link' onclick='validateBillPaymentRef2(\"" . htmlspecialchars($row['billPaymentRef2']) . "\", \"" . htmlspecialchars($row['id']) . "\", \"$table\")'>";
        $output .= "<i class='icon-arrow-right icon-filled'></i><span>เปิดใบเสร็จ</span></a>";
        $output .= "</div>";
        $output .= "</div>";
    }

    return $output;
}

?>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
    function validateBillPaymentRef2(correctRef2, id, table) {
        // ขอให้ผู้ใช้กรอกหมายเลขผู้เสียภาษี
        Swal.fire({
            title: 'กรุณากรอกหมายเลขผู้เสียภาษี',
            input: 'text',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            showLoaderOnConfirm: true,
            preConfirm: async (inputRef2) => {
                if (inputRef2 === correctRef2) {
                    // หากกรอกหมายเลขถูกต้อง
                    window.open('pdf_maker.php?id=' + id + '&table=' + table, '_blank');
                    return true;
                } else {
                    // หากกรอกหมายเลขไม่ถูกต้อง
                    Swal.showValidationMessage('หมายเลขผู้เสียภาษีไม่ถูกต้อง');
                    return false;
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    }
</script>