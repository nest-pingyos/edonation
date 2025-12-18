<?php
include('../config/connect_pdf.php');
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
        $ret .=  $satang . "สตางค์";
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
    if ($number == 0) return $ret;
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
        list($thb, $ths) = explode('.', $thb);
        $ths = substr($ths . '00', 0, 2);
        $thb = $this->engFormat(intval($thb)) . ' Baht';
        if (intval($ths) > 0) {
            $thb .= ' and ' . $this->engFormat(intval($ths)) . ' Satang';
        }
        return $thb;
    }

    private function engFormat($number)
    {
        $suffix = ''; // Initialize suffix
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
                    if ($number == 18) {
                        $suffix = "een";
                    } else {
                        $suffix = "teen";
                    }
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
                    if ($number % pow(10, 2)) {
                        $suffix = " " . $this->engFormat($number % pow(10, 2));
                    }
                    $string = $prefix . $suffix;
                    break;
                case $number < pow(10, 6):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 3)))) . " thousand";
                    if ($number % pow(10, 3)) {
                        $suffix = $this->engFormat($number % pow(10, 3));
                    }
                    $string = $prefix . " " . $suffix;
                    break;
                case $number < pow(10, 9):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 6)))) . " million";
                    if ($number % pow(10, 6)) {
                        $suffix = $this->engFormat($number % pow(10, 6));
                    }
                    $string = $prefix . " " . $suffix;
                    break;
                case $number < pow(10, 12):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 9)))) . " billion";
                    if ($number % pow(10, 9)) {
                        $suffix = $this->engFormat($number % pow(10, 9));
                    }
                    $string = $prefix . " " . $suffix;
                    break;
                case $number < pow(10, 15):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 12)))) . " trillion";
                    if ($number % pow(10, 12)) {
                        $suffix = $this->engFormat($number % pow(10, 12));
                    }
                    $string = $prefix . " " . $suffix;
                    break;
                case $number < pow(10, 18):
                    $prefix = $this->engFormat(intval(floor($number / pow(10, 15)))) . " quadrillion";
                    if ($number % pow(10, 15)) {
                        $suffix = $this->engFormat($number % pow(10, 15));
                    }
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

$id = $_GET['id'];
$inv_mst_query = "SELECT T1.id, T1.billPaymentRef2, T1.payerAccountName, T1.billPaymentRef1, T1.amount, T1.address, T1.province, T1.amphure, T1.district, T1.zip_code, T1.project_name, T1.project_number, T1.created_at FROM donat T1 WHERE T1.id='" . $id . "'";
$inv_mst_results = mysqli_query($con, $inv_mst_query);
$count = mysqli_num_rows($inv_mst_results);
if ($count > 0) {
    $inv_mst_data_row = mysqli_fetch_array($inv_mst_results, MYSQLI_ASSOC);

    // Process dates and amounts
    $created_at = $inv_mst_data_row['created_at'];
    $rec_day = date("d", strtotime($created_at));
    $rec_month = $thai_months[date("m", strtotime($created_at))];
    $rec_monen = $english_months[date("m", strtotime($created_at))];
    $rec_yearen = date("Y", strtotime($created_at));
    $rec_yearth = $rec_yearen + 543;

    $number = $inv_mst_data_row['amount'];
    $EngBaht = convertToEnglish($number);

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
    $cellWidth = 194;  // กำหนดความกว้างของเซลล์
    $imageWidth = 25;  // กำหนดความกว้างของรูปภาพ

    // คำนวณตำแหน่ง X ให้รูปภาพอยู่ตรงกลางของเซลล์
    $x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
    // คำนวณตำแหน่ง Y ให้รูปภาพอยู่ด้านบนของเซลล์
    $y = $pdf->GetY() - 4;

    $pdf->Image($img, $x, $y, $imageWidth, 25, '', '', '', false, 300, '', false, false, 0, false, false, false);

    //

    // logo nurse
    $img = 'TCPDF/nurselogo.png';
    $cellWidth = 194;  // กำหนดความกว้างของเซลล์
    $imageWidth = 30;  // กำหนดความกว้างของรูปภาพ

    // คำนวณตำแหน่ง X ให้รูปภาพอยู่ตรงกลางของเซลล์
    $x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
    // คำนวณตำแหน่ง Y ให้รูปภาพอยู่ด้านบนของเซลล์ โดยเพิ่มค่า Y ที่ได้จากบรรทัดก่อนหน้านี้
    $y += 145;

    $pdf->Image($img, $x, $y, $imageWidth, 30, '', '', '', false, 300, '', false, false, 0, false, false, false);

    // 
    // ลายเช็ตคณบดี
    $img = 'TCPDF/signature_C.png';
    $cellWidth = 195;  // กำหนดความกว้างของเซลล์
    $imageWidth = 50;  // กำหนดความกว้างของรูปภาพ

    // คำนวณตำแหน่ง X ให้รูปภาพอยู่ตรงกลางของเซลล์
    $x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
    // คำนวณตำแหน่ง Y ให้รูปภาพอยู่ด้านบนของเซลล์
    $y = $pdf->GetY() + 245;

    $pdf->Image($img, $x, $y, $imageWidth, 10, '', '', '', false, 300, '', false, false, 0, false, false, false);
    //

    // ลายเช็นพี่เจี๊ยบ
    $img = 'TCPDF/signature.png';
    $cellWidth = 355;  // กำหนดความกว้างของเซลล์
    $imageWidth = 20;  // กำหนดความกว้างของรูปภาพ

    // คำนวณตำแหน่ง X ให้รูปภาพอยู่ตรงกลางของเซลล์
    $x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
    // คำนวณตำแหน่ง Y ให้รูปภาพอยู่ด้านบนของเซลล์
    $y = $pdf->GetY() + 90;

    $pdf->Image($img, $x, $y, $imageWidth, 15, '', '', '', false, 300, '', false, false, 0, false, false, false);
    // 

    // ลายน้ำ
    $img = 'TCPDF/cmulogo20.png';
    $cellWidth = 196;  // กำหนดความกว้างของเซลล์
    $imageWidth = 150;  // กำหนดความกว้างของรูปภาพ

    // คำนวณตำแหน่ง X ให้รูปภาพอยู่ตรงกลางของเซลล์
    $x = $pdf->GetX() + ($cellWidth - $imageWidth) / 2;
    // คำนวณตำแหน่ง Y ให้รูปภาพอยู่ด้านบนของเซลล์
    $y = $pdf->GetY() - 10;

    $pdf->Image($img, $x, $y, $imageWidth, 150, '', '', '', false, 300, '', false, false, 0, false, false, false);
    // 

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
            <td>ชื่อ/Name : ' . htmlspecialchars($inv_mst_data_row['payerAccountName']) . '</td>
            <td align="right">เลขที่ใบเสร็จ/Receipt No. 2567-E' . str_pad(htmlspecialchars($inv_mst_data_row['id']), 4, '0', STR_PAD_LEFT) . '</td>
        </tr>
        <tr>
            <td>ที่อยู่/Address : ' . htmlspecialchars($inv_mst_data_row['district']) . ' ' . htmlspecialchars($inv_mst_data_row['amphure']) . ' ' . htmlspecialchars($inv_mst_data_row['province']) . ' ' . htmlspecialchars($inv_mst_data_row['zip_code']) . '</td>
        </tr>
        <tr>
            <td>รายการ/Description</td>
		    <td align="right">วันที่/Date : ' . $rec_day . ' ' . $rec_month . ' ' . $rec_yearth . ' / ' . $rec_day . ' ' . $rec_monen . ' ' . $rec_yearen . '</td>
        </tr>
        <tr>
            <td colspan="2">' . htmlspecialchars($inv_mst_data_row['project_number']) . ' ' . htmlspecialchars($inv_mst_data_row['project_name']) . '</td>
        </tr>
        <tr>
            <td align="right" colspan="2">จำนวนเงิน/Amount : ' . number_format($inv_mst_data_row['amount'], 2) . ' บาท</td>
        </tr>
        <tr>
			<td style="text-align: right;">จำนวนเงินรวม/Total</td>
			<td align="right">' . number_format($inv_mst_data_row['amount'], 2) . ' บาท</td>
		</tr>

        <br>

        <tr>
            <td colspan="2">รวมทั้งหมด : ' . number_format($inv_mst_data_row['amount'], 2) . ' บาท (' . Convert($inv_mst_data_row['amount']) . ')</td>
        </tr>
        <tr>
            <td colspan="2">Total Amount Received : ' . number_format($inv_mst_data_row['amount'], 2) . ' Baht (' . convertToEnglish($inv_mst_data_row['amount']) . ')</td>
        </tr>
        <tr>
            <td>ชำระโดย/Pay by : </td>
        </tr>
        <tr>
			<td align="right" colspan="2">(นางสาวชนิดา ต้นพิพัฒน์)<br>เจ้าหน้าที่ผู้รับเงิน/Collector<br>วันที่ : ' . $rec_day . ' ' . $rec_month . ' ' . $rec_yearth . '</td>
		</tr>
        <tr>
			<td colspan="2" >หมายเหตุ : ใบเสร็จรับเงินจะมีผลสมบูรณ์ต่อเมื่อได้รับชำระเงินเรียบร้อยแล้วและมีลายเซ็นของผู้รับเงินครบถ้วน<br>The receipt will be valid with payment and the signature of the collector</td>
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
			<td colspan="2" style="text-align: center;">ได้รับเงินบริจาคเป็นจำนวนเงิน : ' . number_format($inv_mst_data_row['amount'], 2) . ' บาท (' . Convert($inv_mst_data_row['amount']) . ')</td>
		</tr>
        <tr>
            <td>จาก : ' . htmlspecialchars($inv_mst_data_row['payerAccountName']) . '</td>
        </tr>
        <tr>
            <td colspan="2">วัตถุประสงค์ : ' . htmlspecialchars($inv_mst_data_row['project_name']) . '</td>
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
			<td colspan="2" style="text-align: center;"><b>(ผู้ช่วยศาสตราจารย์ ดร.ธานี แก้วธรรมานุกูล)<br>คณบดีคณะพยาบาลศาสตร์</b></td>
		</tr>
        <tr>
            <td>เลขที่ใบเสร็จ : 2567-' . htmlspecialchars($inv_mst_data_row['project_number']) . '-E' . str_pad(htmlspecialchars($inv_mst_data_row['id']), 4, '0', STR_PAD_LEFT) . '</td>
		    <td align="right">ลำดับเอกสาร : 67' . str_pad(htmlspecialchars($inv_mst_data_row['id']), 4, '0', STR_PAD_LEFT) . '</td>
        </tr>
    </table>
';

    // Write the content to the PDF
    $pdf->writeHTML($content);


    // Output the PDF
    $pdf->Output('Receipt_' . ($inv_mst_data_row['id']) . '.pdf', 'I');
}

ob_end_flush();
