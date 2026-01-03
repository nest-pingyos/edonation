<?php
/**
 * Donation Helper Functions
 * This file is deprecated - functions moved to API services
 * 
 * @deprecated Use API controllers instead
 */

// These functions are now in:
// - api/controllers/DonationController.php
// - api/controllers/ReceiptController.php

/**
 * @deprecated Use DonationController->create() via API
 */
function isDuplicate($pdo, $billPaymentRef1) {
    trigger_error('isDuplicate() is deprecated. Use API instead.', E_USER_DEPRECATED);
    $query = "SELECT COUNT(*) FROM donat WHERE billPaymentRef1 = :billPaymentRef1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':billPaymentRef1' => $billPaymentRef1]);
    return $stmt->fetchColumn() > 0;
}

/**
 * @deprecated Use POST /api/v1/donations instead
 */
function insertDonation($pdo, $data) {
    trigger_error('insertDonation() is deprecated. Use API instead.', E_USER_DEPRECATED);
    $query = "INSERT INTO donat (payerAccountName, billPaymentRef2, billPaymentRef1, amount) VALUES (:payerAccountName, :billPaymentRef2, :billPaymentRef1, :amount)";
    $stmt = $pdo->prepare($query);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

/**
 * @deprecated Use API services instead
 */
function updateDonationDetails($pdo, $billPaymentRef1) {
    trigger_error('updateDonationDetails() is deprecated. Use API instead.', E_USER_DEPRECATED);
    // Keeping for backward compatibility but should not be used
}

/**
 * @deprecated Use POST /api/v1/receipts/generate instead
 */
function updateReceiptDetails($pdo, $id) {
    trigger_error('updateReceiptDetails() is deprecated. Use API instead.', E_USER_DEPRECATED);
    // Keeping for backward compatibility but should not be used
}
