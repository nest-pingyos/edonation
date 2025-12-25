<?php
/**
 * PDF Maker - สร้างใบเสร็จ PDF
 * อัปเดต: 2024-12-17 - เรียกข้อมูลผ่าน API
 */

// Load environment configuration
require_once dirname(__DIR__) . '/config/env.php';

require('TCPDF/tcpdf.php');
ob_start();

// Thai month names
$thai_months = [
    "01" => "มกราคม",
    "02" => "กุมภาพันธ์",
    "03" => "มีนาคม",
    "04" => "เมษายน",
    "05" => "พฤษภาคม",
    "06" => "มิถุนายน",
    "07" => "กรกฎาคม",
    "08" => "สิงหาคม",
    "09" => "กันยายน",
    "10" => "ตุลาคม",
    "11" => "พฤศจิกายน",
    "12" => "ธันวาคม"
];

$english_months = [
    "01" => "January",
    "02" => "February",
    "03" => "March",
    "04" => "April",
    "05" => "May",
    "06" => "June",
    "07" => "July",
    "08" => "August",
    "09" => "September",
    "10" => "October",
    "11" => "November",
    "12" => "December"
];

function Convert($amount_number)
{
    $amount_number = number_format($amount_number, 2, ".", "");
    $pt = strpos($amount_number, ".");
    $number = $fraction = "";
    if ($pt === false)
        $number = $amount_number;
    else {
        $number = substr($amount_number, 0, $pt);
        $fraction = substr($amount_number, $pt + 1);
    }

    $ret = "";
    $baht = ReadNumber($number);
    if ($baht != "")
        $ret .= $baht . "บาท";

    $satang = ReadNumber($fraction);
    if ($satang != "")
        $ret .= $satang . "สตางค์";
    else
        $ret .= "ถ้วน";
    return $ret;
}

function ReadNumber($number)
{
    $position_call = array("แสน", "หมื่น", "พัน", "ร้อย", "สิบ", "");
    $number_call = array("", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า");
    $number = $number + 0;
    $ret = "";
    if ($number == 0)
        return $ret;
    if ($number > 1000000) {
        $ret .= ReadNumber(intval($number / 1000000)) . "ล้าน";
        $number = intval(fmod($number, 1000000));
    }

    $divider = 100000;
    $pos = 0;
    while ($number > 0) {
        $d = intval($number / $divider);
        $ret .= (($divider == 10) && ($d == 2)) ? "ยี่" : ((($divider == 10) && ($d == 1)) ? "" : ((($divider == 1) && ($d == 1) && ($ret != "")) ? "เอ็ด" : $number_call[$d]));
        $ret .= ($d ? $position_call[$pos] : "");
        $number = $number % $divider;
        $divider = $divider / 10;
        $pos++;
    }
    return $ret;
}

class Currency
{
    public function bahtEng($thb)
    {
        // แปลงเป็น string และตรวจสอบจุดทศนิยม
        $thb = number_format((float) $thb, 2, '.', '');
        $parts = explode('.', $thb);
        $baht = $parts[0] ?? '0';
        $satang = $parts[1] ?? '00';
        $satang = substr($satang . '00', 0, 2);

        $result = $this->engFormat(intval($baht)) . ' Baht';
        if (intval($satang) > 0) {
            $result .= ' and ' . $this->engFormat(intval($satang)) . ' Satang';
        }
        return $result;
    }

    private function engFormat($number)
    {
        $suffix = '';
        $max_size = pow(10, 18);
        if (!$number) {
            return "zero";
        }
        if (is_int($number) && $number < abs($max_size)) {
            switch ($number) {
                case $number < 0:
                    $prefix = "negative";
                    $suffix = $this->engFormat(-1 * $number);
                    $string = $prefix . " " . $suffix;
                    break;
                case 1:
                    $string = "one";
                    break;
                case 2:
                    $string = "two";
                    break;
                case 3:
                    $string = "three";
                    break;
                case 4:
                    $string = "four";
                    break;
                case 5:
                    $string = "five";
                    break;
                case 6:
                    $string = "six";
                    break;
                case 7:
                    $string = "seven";
                    break;
                case 8:
                    $string = "eight";
                    break;
                case 9:
                    $string = "nine";
                    break;
                case 10:
                    $string = "ten";
                    break;
                case 11:
                    $string = "eleven";
                    break;
                case 12:
                    $string = "twelve";
                    break;
                case 13:
                    $string = "thirteen";
                    break;
                case 15:
                    $string = "fifteen";
                    break;
                case $number < 20:
                    $string = $this->engFormat($number % 10);
                    $suffix = ($number == 18) ? "een" : "teen";
                    $string .= $suffix;
                    break;
                case 20:
                    $string = "twenty";
                    break;
                case 30:
                    $string = "thirty";
                    break;
                case 40:
                    $string = "forty";
                    break;
                case 50:
                    $string = "fifty";
                    break;
                case 60:
                    $string = "sixty";
                    break;
                case 70:
                    $string = "seventy";
                    break;
                case 80:
                    $string = "eighty";
                    break;
                case 90:
                    $string = "ninety";
                    break;
                case $number < 100:
                    $prefix = $this->engFormat($number - $number % 10);
                    $suffix = $this->engFormat($number % 10);
                    $string = $prefix . "-" . $suffix;
                    break;
                case $number < pow(10, 3):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 2)))) . " hundred";
                    if ($number % pow(10, 2))
                        $suffix = " " . $this->engFormat($number % pow(10, 2));
                    $string = $prefix . $suffix;
                    break;
                case $number < pow(10, 6):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 3)))) . " thousand";
                    if ($number % pow(10, 3))
                        $suffix = $this->engFormat($number % pow(10, 3));
                    $string = $prefix . " " . $suffix;
                    break;
                case $number < pow(10, 9):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 6)))) . " million";
                    if ($number % pow(10, 6))
                        $suffix = $this->engFormat($number % pow(10, 6));
                    $string = $prefix . " " . $suffix;
                    break;
                case $number < pow(10, 12):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 9)))) . " billion";
                    if ($number % pow(10, 9))
                        $suffix = $this->engFormat($number % pow(10, 9));
                    $string = $prefix . " " . $suffix;
                    break;
                case $number < pow(10, 15):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 12)))) . " trillion";
                    if ($number % pow(10, 12))
                        $suffix = $this->engFormat($number % pow(10, 12));
                    $string = $prefix . " " . $suffix;
                    break;
                case $number < pow(10, 18):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 15)))) . " quadrillion";
                    if ($number % pow(10, 15))
                        $suffix = $this->engFormat($number % pow(10, 15));
                    $string = $prefix . " " . $suffix;
                    break;
            }
        }
        return $string;
    }
}

function convertToEnglish($thb)
{
    $currency = new Currency();
    return $currency->bahtEng($thb);
}

/**
 * เรียกข้อมูลผ่าน API
 */
function fetchReceiptData($receiptId)
{
    // Use BASE_PATH from config
    $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
    $apiUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "{$basePath}/api/v1/receipts/{$receiptId}/details";

    // สร้าง context สำหรับ HTTP request
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'timeout' => 10
        ]
    ]);

    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if (!$data || !$data['success']) {
        return null;
    }

    return $data['data'];
}

/**
 * ตรวจสอบ access token
 */
function validateAccessToken($receiptId, $token)
{
    if (empty($token)) {
        return false;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $storedToken = $_SESSION['pdf_access_tokens'][$receiptId] ?? null;

    if (!$storedToken) {
        return false;
    }

    if ($storedToken['token'] !== $token) {
        return false;
    }

    if ($storedToken['expire_at'] < time()) {
        unset($_SESSION['pdf_access_tokens'][$receiptId]);
        return false;
    }

    return true;
}

// รับค่า receipt ID และ token
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($id <= 0) {
    die("ไม่ได้ระบุ ID ใบเสร็จ");
}

// ตรวจสอบ access token
if (!validateAccessToken($id, $token)) {
    // Use BASE_PATH from config
    $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
    // แสดงหน้า error สวยๆ
    echo '<!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ไม่สามารถเข้าถึงได้</title>
        <style>
            body { font-family: "Sarabun", sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
            .error-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
            .error-icon { font-size: 60px; margin-bottom: 20px; }
            h1 { color: #333; margin: 0 0 10px 0; }
            p { color: #666; margin: 0 0 20px 0; }
            a { display: inline-block; padding: 10px 30px; background: #00a651; color: white; text-decoration: none; border-radius: 5px; }
            a:hover { background: #008c44; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <div class="error-icon">🔒</div>
            <h1>ไม่สามารถเข้าถึงได้</h1>
            <p>กรุณายืนยันตัวตนด้วยเลขประจำตัวผู้เสียภาษีก่อนเปิดใบเสร็จ</p>
            <a href="' . $basePath . '/receipts/">กลับไปหน้าค้นหา</a>
        </div>
    </body>
    </html>';
    exit;
}

// เรียกข้อมูลผ่าน API
$receiptData = fetchReceiptData($id);

if (!$receiptData) {
    // Fallback: ใช้ SQL query ถ้า API ไม่ทำงาน
    include('../config/connect_pdf.php');

    $table = isset($_GET['table']) ? $_GET['table'] : 'donat_user';
    $valid_tables = ['donat', 'donat_user', 'receipt_2568', 'receipt_2567', 'receipt_2566'];

    if (!in_array($table, $valid_tables)) {
        die("ตารางไม่ถูกต้อง");
    }

    $stmt = $con->prepare("SELECT T1.id, T1.billPaymentRef2, T1.payerAccountName, T1.billPaymentRef1, 
                                  T1.amount, T1.address, T1.province, T1.amphure, T1.district, T1.zip_code, 
                                  T1.project_name, T1.project_number, T1.receiptDate, T1.fiscal_year, 
                                  T1.receipt_no, T1.payby 
                           FROM {$table} T1 WHERE T1.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("ไม่พบข้อมูลใบเสร็จ");
    }

    $receiptData = $result->fetch_assoc();
}

// Map field names (API อาจใช้ชื่อ field ต่างจาก SQL)
// Map field names (API อาจใช้ชื่อ field ต่างจาก SQL)
$data = [
    'id' => $receiptData['id'] ?? $id,
    'payerAccountName' => $receiptData['payer_name'] ?? $receiptData['payerAccountName'] ?? '',
    'amount' => $receiptData['amount'] ?? 0,
    'address' => $receiptData['address'] ?? '',
    'address_line' => $receiptData['address_line'] ?? '',
    'province' => $receiptData['province'] ?? '',
    'amphure' => $receiptData['amphure'] ?? '',
    'district' => $receiptData['district'] ?? '',
    'zip_code' => $receiptData['zip_code'] ?? '',
    'project_name' => $receiptData['project_name'] ?? '',
    'project_number' => $receiptData['project_number'] ?? '',
    'receiptDate' => $receiptData['receipt_date'] ?? $receiptData['receiptDate'] ?? date('Y-m-d'),
    'fiscal_year' => $receiptData['fiscal_year'] ?? (date('Y') + 543),
    'receipt_no' => $receiptData['receipt_no'] ?? '',
    'payby' => $receiptData['pay_by'] ?? $receiptData['payby'] ?? 'QR PromptPay'
];

// Construct Full Address
$fullAddress = $data['address']; // Default to legacy full address

if (!empty($data['address_line'])) {
    $parts = [];
    $parts[] = trim($data['address_line']);

    if (!empty($data['district'])) {
        $d = trim($data['district']);
        $parts[] = (strpos($d, 'ต.') === 0 || strpos($d, 'แขวง') === 0) ? $d : 'ต.' . $d;
    }

    if (!empty($data['amphure'])) {
        $a = trim($data['amphure']);
        $parts[] = (strpos($a, 'อ.') === 0 || strpos($a, 'เขต') === 0) ? $a : 'อ.' . $a;
    }

    if (!empty($data['province'])) {
        $p = trim($data['province']);
        $parts[] = (strpos($p, 'จ.') === 0 || strpos($p, 'กรุงเทพ') === 0) ? $p : 'จ.' . $p;
    }

    if (!empty($data['zip_code'])) {
        $parts[] = trim($data['zip_code']);
    }

    $fullAddress = implode(' ', $parts);
}

// Process dates and amounts
$receiptDate = $data['receiptDate'];
$rec_day = date("d", strtotime($receiptDate));
$rec_month = $thai_months[date("m", strtotime($receiptDate))];
$rec_monen = $english_months[date("m", strtotime($receiptDate))];
$rec_yearen = date("Y", strtotime($receiptDate));
$rec_yearth = $rec_yearen + 543;

$number = $data['amount'];
$EngBaht = convertToEnglish($number);

// =============================================
// ดึงลายเซ็นจาก API (หรือ fallback ไป hardcoded)
// =============================================
$fiscalYear = intval($data['fiscal_year']);

/**
 * ดึง config ลายเซ็นจาก API
 */
function fetchSignatureConfig($year)
{
    // Use BASE_PATH from config
    $basePath = defined('BASE_PATH') ? BASE_PATH : '/edonation';
    $apiUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "{$basePath}/api/v1/signatures/{$year}";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'timeout' => 5
        ]
    ]);

    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if (!$data || !$data['success']) {
        return null;
    }

    return $data['data'];
}

// ดึงจาก API
$signatureData = fetchSignatureConfig($fiscalYear);

// ถ้าดึงจาก API ไม่ได้ ให้ใช้ค่า default
if ($signatureData) {
    $currentConfig = [
        'dean_signature' => $signatureData['dean_signature'],
        'dean_name' => $signatureData['dean_name'],
        'collector_signature' => $signatureData['collector_signature'],
        'collector_name' => $signatureData['collector_name'],
    ];
} else {
    // Fallback: ค่า default
    $currentConfig = [
        'dean_signature' => 'TCPDF/signature/Suparat_2568.png',
        'dean_name' => '(ผู้ช่วยศาสตราจารย์ ดร.สุภารัตน์ วังศรีคูณ)',
        'collector_signature' => 'TCPDF/signature/signature_collector.png',
        'collector_name' => '(นางสาวชนิดา ต้นพิพัฒน์)',
    ];
}

// Initialize PDF
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
$pdf->SetDefaultMonospacedFont('thsarabunnew');
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetMargins(PDF_MARGIN_LEFT, '1', PDF_MARGIN_RIGHT);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('thsarabunnew', '', 13);
$pdf->SetMargins(8, 10, 8);
$pdf->SetAutoPageBreak(true, 2);

// Add a new page
$pdf->AddPage();

// logo cmu
$img = 'TCPDF/cmulogo.png';
$cellWidth = 194;
$imageWidth = 25;
$x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
$y = $pdf->GetY() - 4;
$pdf->Image($img, $x, $y, $imageWidth, 25, '', '', '', false, 300, '', false, false, 0, false, false, false);

// logo nurse
$img = 'TCPDF/nurselogo.png';
$cellWidth = 194;
$imageWidth = 30;
$x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
$y += 155;
$pdf->Image($img, $x, $y, $imageWidth, 30, '', '', '', false, 300, '', false, false, 0, false, false, false);

// ลายเช็นคณบดี (ใช้จาก config ตามปี)
$deanSignature = $currentConfig['dean_signature'];
if (file_exists($deanSignature)) {
    $img = $deanSignature;
} else {
    $img = 'TCPDF/signature_C.png'; // fallback
}
$cellWidth = 195;
$imageWidth = 50;
$x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
$y = $pdf->GetY() + 255;
$pdf->Image($img, $x, $y, $imageWidth, 10, '', '', '', false, 300, '', false, false, 0, false, false, false);

// ลายเช็นผู้รับเงิน (ใช้จาก config ตามปี)
$collectorSignature = $currentConfig['collector_signature'];
if (file_exists($collectorSignature)) {
    $img = $collectorSignature;
} else {
    $img = 'TCPDF/signature.png'; // fallback
}
$cellWidth = 355;
$imageWidth = 20;
$x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
$y = $pdf->GetY() + 100;
$pdf->Image($img, $x, $y, $imageWidth, 15, '', '', '', false, 300, '', false, false, 0, false, false, false);

// ลายน้ำ
$img = 'TCPDF/cmulogo20.png';
$cellWidth = 196;
$imageWidth = 150;
$x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
$y = $pdf->GetY() - 10;
$pdf->Image($img, $x, $y, $imageWidth, 150, '', '', '', false, 300, '', false, false, 0, false, false, false);

// Prepare content
$content = '
<table>
    <tr>
        <td>มหาวิทยาลัยเชียงใหม่</td>
        <td align="right">ใบเสร็จรับเงิน/Receipt</td>
    </tr>
    <tr>
        <td>Chiang Mai University</td>
        <td align="right">ต้นฉบับ/Original</td>
    </tr>
    <tr>
        <td>239 ถนนห้วยแก้ว ต.สุเทพ อ.เมือง จ.เชียงใหม่ 50200</td>
        <td align="right">คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่</td>
    </tr>
    <tr>
        <td>239 Huaykaew Road, Muang District, Chiang Mai, 50200</td>
        <td align="right">Faculty of Nursing, CMU</td>
    </tr>
    <tr>
        <td>เบอร์โทร 053-943130</td>
        <td align="right">110/406 ถนนอินทวโรรส ต.สุเทพ อ.เมือง จ.เชียงใหม่ 50200</td>
    </tr>
    <tr>
        <td>เลขประจำตัวผู้เสียภาษีอากร/Taxpayer identification Number </td>
        <td align="right">110/406 Inthawaroros Road, Suthep, Chiang Mai 50200</td>
    </tr>
    <tr>
        <td>099 4 00042317 9</td>
        <td align="right">โทรศัพท์/Tel 053-949075</td>
    </tr>
   
    <br>
  
    <tr>
        <td width="60%"><b>ชื่อ/Name : </b>' . htmlspecialchars($data['payerAccountName']) . '</td>
        <td width="40%" align="right"><b>เลขที่ใบเสร็จ/Receipt No. </b>' . htmlspecialchars($data['receipt_no']) . '</td>
    </tr>
    <tr>
        <td colspan="2"><b>ที่อยู่/Address :</b> ' . htmlspecialchars($fullAddress) . '</td>
    </tr>
    
    <br>

    <tr>
        <td><b>รายการ/Description</b></td>
        <td align="right"><b>วันที่/Date : </b>' . $rec_day . ' ' . $rec_month . ' ' . $rec_yearth . ' / ' . $rec_day . ' ' . $rec_monen . ' ' . $rec_yearen . '</td>
    </tr>
    <tr>
        <td colspan="2">' . htmlspecialchars($data['project_number']) . ' ' . htmlspecialchars($data['project_name']) . '</td>
    </tr>
    <tr>
        <td align="right" colspan="2"><b>จำนวนเงิน/Amount : </b>' . number_format($data['amount'], 2) . ' บาท</td>
    </tr>
    <br>
    <tr>
        <td style="text-align: right;"><b>จำนวนเงินรวม/Total</b></td>
        <td align="right">' . number_format($data['amount'], 2) . ' บาท</td>
    </tr>

    <br>

    <tr>
        <td colspan="2"><b>รวมทั้งหมด : ' . number_format($data['amount'], 2) . ' บาท (' . Convert($data['amount']) . ')</b></td>
    </tr>
    <tr>
        <td colspan="2"><b>Total Amount Received : ' . number_format($data['amount'], 2) . ' Baht (' . convertToEnglish($data['amount']) . ')</b></td>
    </tr>
    <tr>
        <td><b>ชำระโดย/Pay by : ' . htmlspecialchars($data['payby']) . '</b></td>
    </tr>
    <tr>
        <td align="right" colspan="2">' . htmlspecialchars($currentConfig['collector_name']) . '<br>เจ้าหน้าที่ผู้รับเงิน/Collector<br>วันที่ : ' . $rec_day . ' ' . $rec_month . ' ' . $rec_yearth . '</td>
    </tr>
    <tr>
        <td colspan="2" ><b>หมายเหตุ : ใบเสร็จรับเงินจะมีผลสมบูรณ์ต่อเมื่อได้รับชำระเงินเรียบร้อยแล้วและมีลายเซ็นของผู้รับเงินครบถ้วน<br>The receipt will be valid with payment and the signature of the collector</b></td>
    </tr>
    <tr>
        <td colspan="2" style="border-bottom: solid black 1px;"></td>
    </tr>

    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <tr>
        <td colspan="2" style="text-align: center; font-size: 18px;"><b>อนุโมทนาบัตร</b></td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center;"><b>คณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่</b></td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center;">ได้รับเงินบริจาคเป็นจำนวนเงิน : ' . number_format($data['amount'], 2) . ' บาท (' . Convert($data['amount']) . ')</td>
    </tr>
    <tr>
        <td><b>จาก : </b>' . htmlspecialchars($data['payerAccountName']) . '</td>
    </tr>
    <tr>
        <td colspan="2"><b>วัตถุประสงค์ : </b>' . htmlspecialchars($data['project_name']) . '</td>
    </tr>

    <br>

    <tr>
        <td colspan="2" style="text-align: center;">ขอให้กุศลผลบุญจากการบริจาคของท่านในครั้งนี้<br>โปรดดลบันดาลให้ท่านประสบแต่ความสุขสวัสดี ปราศจากทุกข์โศกโรคภัย<br>ปราถนาสิ่งใดให้สำเร็จสมดังประสงค์ทุกประการ<br>ให้ไว้ ณ วันที่  ' . $rec_day . ' ' . $rec_month . ' ' . $rec_yearth . '</td>
    </tr>

    <br>
    <br>
    <br>
    <br>

    <tr>
        <td colspan="2" style="text-align: center;"><b>' . htmlspecialchars($currentConfig['dean_name']) . '<br>คณบดีคณะพยาบาลศาสตร์</b></td>
    </tr>
    <tr>
        <td><b>เลขที่ใบเสร็จ : </b>' . htmlspecialchars($data['receipt_no']) . '</td>
        <td align="right"><b>ลำดับเอกสาร : </b>69' . str_pad(htmlspecialchars($data['id']), 4, '0', STR_PAD_LEFT) . '</td>
    </tr>
</table>
';

// Write the content to the PDF
$pdf->writeHTML($content);

// Output the PDF
$pdf->Output('Receipt_' . ($data['id']) . '.pdf', 'I');

ob_end_flush();
