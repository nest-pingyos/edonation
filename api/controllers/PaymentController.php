<?php
/**
 * Payment Controller
 * จัดการ Callback การชำระเงินจากธนาคาร
 * 
 * Endpoints:
 * POST /payments/callback - รับ Callback จากธนาคาร
 */

class PaymentController
{
    const VERSION = '2.0';
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function handle(string $method, ?string $id, ?string $action): array
    {
        // URL: /payments/callback - "callback" comes as $id not $action
        if ($id === 'callback' && $method === 'POST') {
            return $this->callback();
        }

        return [
            'resCode' => '01',
            'resDesc' => 'Method not allowed',
            'transactionId' => null,
            'confirmId' => null
        ];
    }

    /**
     * POST /payments/callback
     * รับ Callback จากธนาคาร (ผ่าน recieve.php หรือเรียกตรง)
     * 
     * Expected JSON:
     * {
     *   "payeeProxyId": "099400258783792",
     *   "payeeProxyType": "BILLERID",
     *   "payeeAccountNumber": "5663044095",
     *   "payeeName": "FACULTY OF NURSING CMU",
     *   "payerAccountNumber": "xxx",
     *   "payerAccountName": "ชื่อผู้โอน",
     *   "payerName": "ชื่อผู้โอน",
     *   "sendingBankCode": "014",
     *   "receivingBankCode": "014",
     *   "amount": "2000.00",
     *   "transactionId": "xxx",
     *   "transactionDateandTime": "2025-11-04T09:39:56.597+07:00",
     *   "billPaymentRef1": "256812120700217",
     *   "billPaymentRef2": "1500701252395",
     *   "currencyCode": "764",
     *   "channelCode": "PMH",
     *   "transactionType": "Domestic Transfers"
     * }
     */
    private function callback(): array
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // Log incoming data
        error_log("Payment Callback API: " . json_encode($data, JSON_UNESCAPED_UNICODE));

        // Extract all fields
        $payeeProxyId = $data['payeeProxyId'] ?? '';
        $payeeProxyType = $data['payeeProxyType'] ?? '';
        $payeeAccountNumber = $data['payeeAccountNumber'] ?? '';
        $payeeName = $data['payeeName'] ?? '';
        $payerAccountNumber = $data['payerAccountNumber'] ?? '';
        $payerAccountName = $data['payerAccountName'] ?? '';
        $payerName = $data['payerName'] ?? '';
        $sendingBankCode = $data['sendingBankCode'] ?? '';
        $receivingBankCode = $data['receivingBankCode'] ?? '';
        $amount = $data['amount'] ?? '';
        $transactionId = $data['transactionId'] ?? null;
        $transactionDateandTime = $data['transactionDateandTime'] ?? '';
        $billPaymentRef1 = $data['billPaymentRef1'] ?? null;
        $billPaymentRef2 = $data['billPaymentRef2'] ?? '';
        $currencyCode = $data['currencyCode'] ?? '764';
        $channelCode = $data['channelCode'] ?? '';
        $transactionType = $data['transactionType'] ?? '';

        // Validate required fields
        if (empty($transactionId) || empty($billPaymentRef1) || empty($amount)) {
            return [
                'resCode' => '01',
                'resDesc' => 'Missing required fields',
                'transactionId' => $transactionId,
                'confirmId' => null
            ];
        }

        // Validate payeeProxyId (our biller ID)
        $expectedPayeeProxyId = '099400258783792';
        if (!empty($payeeProxyId) && $payeeProxyId !== $expectedPayeeProxyId) {
            error_log("Warning: PayeeProxyId mismatch - Expected: $expectedPayeeProxyId, Got: $payeeProxyId");
        }

        // Parse amount
        $amountValue = floatval($amount);
        if ($amountValue <= 0) {
            return [
                'resCode' => '01',
                'resDesc' => 'Invalid amount',
                'transactionId' => $transactionId,
                'confirmId' => null
            ];
        }

        // Parse transaction datetime
        $txnDateTime = null;
        if (!empty($transactionDateandTime)) {
            try {
                $dt = new DateTime($transactionDateandTime);
                $txnDateTime = $dt->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $txnDateTime = date('Y-m-d H:i:s');
            }
        } else {
            $txnDateTime = date('Y-m-d H:i:s');
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Check duplicate transactionId in bank_transactions
            $checkDuplicate = $this->pdo->prepare("SELECT id, confirmId FROM edonation_bank_transactions WHERE transactionId = :txnId LIMIT 1");
            $checkDuplicate->execute([':txnId' => $transactionId]);
            $existingTxn = $checkDuplicate->fetch();

            if ($existingTxn) {
                $this->pdo->rollBack();
                return [
                    'resCode' => '00',
                    'resDesc' => 'success',
                    'transactionId' => $transactionId,
                    'confirmId' => $existingTxn['confirmId']
                ];
            }

            // 2. Find donation by billPaymentRef1
            $findDonation = $this->pdo->prepare("
                SELECT id, amount, status_donat 
                FROM edonation_donat_user 
                WHERE billPaymentRef1 = :ref1 
                LIMIT 1
            ");
            $findDonation->execute([':ref1' => $billPaymentRef1]);
            $donation = $findDonation->fetch();

            if (!$donation) {
                $this->pdo->rollBack();
                return [
                    'resCode' => '02',
                    'resDesc' => 'Ref1 not found',
                    'transactionId' => $transactionId,
                    'confirmId' => null
                ];
            }

            // 3. Verify amount
            $expectedAmount = floatval($donation['amount']);
            if (abs($expectedAmount - $amountValue) > 0.01) {
                $this->pdo->rollBack();
                error_log("Amount mismatch - Expected: $expectedAmount, Got: $amountValue");
                return [
                    'resCode' => '03',
                    'resDesc' => 'Amount mismatch',
                    'transactionId' => $transactionId,
                    'confirmId' => null
                ];
            }

            // 4. Generate confirmId
            $confirmId = 'CNF' . date('YmdHis') . rand(1000, 9999);

            // 5. Insert INTO edonation_bank_transactions
            $insertTxn = $this->pdo->prepare("
                INSERT INTO edonation_bank_transactions (
                    payeeProxyId, payeeProxyType, payeeAccountNumber, payeeName,
                    payerAccountNumber, payerAccountName, payerName,
                    sendingBankCode, receivingBankCode, amount,
                    transactionId, transactionDateandTime, billPaymentRef1, billPaymentRef2,
                    currencyCode, channelCode, transactionType,
                    confirmId, processed
                ) VALUES (
                    :payeeProxyId, :payeeProxyType, :payeeAccountNumber, :payeeName,
                    :payerAccountNumber, :payerAccountName, :payerName,
                    :sendingBankCode, :receivingBankCode, :amount,
                    :transactionId, :transactionDateandTime, :billPaymentRef1, :billPaymentRef2,
                    :currencyCode, :channelCode, :transactionType,
                    :confirmId, 1
                )
            ");

            $insertTxn->execute([
                ':payeeProxyId' => $payeeProxyId,
                ':payeeProxyType' => $payeeProxyType,
                ':payeeAccountNumber' => $payeeAccountNumber,
                ':payeeName' => $payeeName,
                ':payerAccountNumber' => $payerAccountNumber,
                ':payerAccountName' => $payerAccountName,
                ':payerName' => $payerName,
                ':sendingBankCode' => $sendingBankCode,
                ':receivingBankCode' => $receivingBankCode,
                ':amount' => $amountValue,
                ':transactionId' => $transactionId,
                ':transactionDateandTime' => $txnDateTime,
                ':billPaymentRef1' => $billPaymentRef1,
                ':billPaymentRef2' => $billPaymentRef2,
                ':currencyCode' => $currencyCode,
                ':channelCode' => $channelCode,
                ':transactionType' => $transactionType,
                ':confirmId' => $confirmId
            ]);

            // ดึง bank_transaction_id ทันทีหลัง INSERT
            $bankTransactionId = $this->pdo->lastInsertId();

            // 6. UPDATE edonation_donat_user status (if not already completed)
            if ($donation['status_donat'] !== 'completed') {
                $updateDonation = $this->pdo->prepare("
                    UPDATE edonation_donat_user 
                    SET status_donat = 'completed',
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $updateDonation->execute([':id' => $donation['id']]);
            }

            // 7. สร้าง Receipt อัตโนมัติ (ถ้ายังไม่มี)

            $checkReceipt = $this->pdo->prepare("SELECT id FROM edonation_receipts WHERE donation_id = :did LIMIT 1");
            $checkReceipt->execute([':did' => $donation['id']]);

            if (!$checkReceipt->fetch()) {
                // สร้างเลขที่ใบเสร็จ Format: YYYY-EXXXX
                $fiscalYear = date('Y') + 543; // พ.ศ.
                $prefix = $fiscalYear . '-E';

                // Use MAX to get the highest receipt number (safer than COUNT)
                $maxStmt = $this->pdo->prepare("SELECT MAX(receipt_no) as max_no FROM edonation_receipts WHERE receipt_no LIKE :prefix FOR UPDATE");
                $maxStmt->execute([':prefix' => $prefix . '%']);
                $maxRow = $maxStmt->fetch();

                $nextNum = 1;
                if ($maxRow && $maxRow['max_no']) {
                    $numPart = preg_replace('/^\d{4}-E/', '', $maxRow['max_no']);
                    $nextNum = intval($numPart) + 1;
                }
                $receiptNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                // Check for duplicate receipt_no (safety check)
                $checkDupe = $this->pdo->prepare("SELECT COUNT(*) FROM edonation_receipts WHERE receipt_no = :rno");
                $checkDupe->execute([':rno' => $receiptNo]);
                if ($checkDupe->fetchColumn() > 0) {
                    // Find next available number
                    $safetyLoop = 0;
                    do {
                        $nextNum++;
                        $receiptNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                        $checkDupe->execute([':rno' => $receiptNo]);
                        $safetyLoop++;
                    } while ($checkDupe->fetchColumn() > 0 && $safetyLoop < 100);
                }

                // ดึงชื่อผู้บริจาคและเลขบัตรจาก donat_user
                $getDonorName = $this->pdo->prepare("SELECT first_name, last_name, id_card FROM edonation_donat_user WHERE id = :id");
                $getDonorName->execute([':id' => $donation['id']]);
                $donor = $getDonorName->fetch();
                $payerName = trim(($donor['first_name'] ?? '') . ' ' . ($donor['last_name'] ?? ''));

                // ถ้าไม่มีชื่อใน donat_user ให้ใช้จาก bank callback
                if (empty($payerName)) {
                    $payerName = $payerAccountName ?: $payerName ?: 'ไม่ระบุชื่อ';
                }

                // Manage id_members
                $idCardForReceipt = $donor['id_card'] ?? $billPaymentRef2;
                $idCardClean = preg_replace('/\D/', '', $idCardForReceipt);

                $checkMember = $this->pdo->prepare("SELECT id_members FROM edonation_receipts WHERE id_card = :id LIMIT 1");
                $checkMember->execute([':id' => $idCardClean]);
                $member = $checkMember->fetch();

                if ($member && !empty($member['id_members'])) {
                    $idMembers = $member['id_members'];
                } else {
                    $idMembers = '';
                    for ($i = 0; $i < 10; $i++) {
                        $idMembers .= rand(0, 9);
                    }
                }

                // Insert receipt พร้อม bank_transaction_id
                $insertReceipt = $this->pdo->prepare("
                    INSERT INTO edonation_receipts (donation_id, bank_transaction_id, receipt_no, payer_name, amount, issued_at, id_card, id_members)
                    VALUES (:donation_id, :bank_transaction_id, :receipt_no, :payer_name, :amount, NOW(), :id_card, :id_members)
                ");

                $insertReceipt->execute([
                    ':donation_id' => $donation['id'],
                    ':bank_transaction_id' => $bankTransactionId, // อ้างอิง bank_transactions.id
                    ':receipt_no' => $receiptNo,
                    ':payer_name' => $payerName,
                    ':amount' => $amountValue,
                    ':id_card' => $idCardClean,
                    ':id_members' => $idMembers
                ]);

                error_log("Receipt created automatically - ReceiptNo: $receiptNo, DonationId: " . $donation['id'] . ", BankTxnId: $bankTransactionId");
            }

            $this->pdo->commit();

            error_log("Payment processed successfully - TxnId: $transactionId, ConfirmId: $confirmId, DonationId: " . $donation['id']);

            // 8. ส่งแจ้งเตือน LINE OA (หลังจาก commit สำเร็จ)
            try {
                require_once __DIR__ . '/../services/LineNotificationService.php';
                $lineNotifier = new LineNotificationService();

                // ดึงชื่อโครงการ (ใช้ COLLATE เพื่อแก้ปัญหา collation mismatch)
                $getProject = $this->pdo->prepare("
                    SELECT p.project_name 
                    FROM edonation_donat_user d
                    JOIN edonation_projects p ON d.project_number COLLATE utf8mb4_unicode_ci = p.project_number COLLATE utf8mb4_unicode_ci
                    WHERE d.id = :id
                ");
                $getProject->execute([':id' => $donation['id']]);
                $project = $getProject->fetch();
                $projectName = $project['project_name'] ?? 'ไม่ระบุโครงการ';

                // ดึงชื่อผู้บริจาค
                $getDonor = $this->pdo->prepare("SELECT first_name, last_name FROM edonation_donat_user WHERE id = :id");
                $getDonor->execute([':id' => $donation['id']]);
                $donorInfo = $getDonor->fetch();
                $donorName = trim(($donorInfo['first_name'] ?? '') . ' ' . ($donorInfo['last_name'] ?? ''));

                // ส่งแจ้งเตือน
                $notifyResult = $lineNotifier->sendPaymentSuccessNotification(
                    (int) $donation['id'],
                    $amountValue,
                    $projectName,
                    $donorName
                );

                error_log("LINE Notification result: " . json_encode($notifyResult, JSON_UNESCAPED_UNICODE));
            } catch (Exception $notifyError) {
                // ไม่ให้ error การแจ้งเตือนมีผลกับ response
                error_log("LINE Notification Error: " . $notifyError->getMessage());
            }

            return [
                'resCode' => '00',
                'resDesc' => 'success',
                'transactionId' => $transactionId,
                'confirmId' => $confirmId
            ];

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Payment Callback DB Error: " . $e->getMessage());

            return [
                'resCode' => '99',
                'resDesc' => 'Database error',
                'transactionId' => $transactionId,
                'confirmId' => null
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Payment Callback Error: " . $e->getMessage());

            return [
                'resCode' => '99',
                'resDesc' => 'Internal error',
                'transactionId' => $transactionId,
                'confirmId' => null
            ];
        }
    }
}
