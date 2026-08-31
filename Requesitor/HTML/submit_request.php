<?php
ob_start();
header('Content-Type: application/json');

while (ob_get_level() > 0) {
    ob_end_clean();
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();

$response = ["success" => false, "message" => "Unknown error"];

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    $response["message"] = "Session expired. Please log in again.";
    echo json_encode($response);
    exit;
}

$conn = new mysqli("localhost", "root", "", "ze_electronic");

if ($conn->connect_error) {
    $response["message"] = "Database connection failed";
    echo json_encode($response);
    exit;
}

require_once '../../Admin/PHP/log_activity.php';

require_once __DIR__ . '/../PHP/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHP/PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHP/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$userId = (int) $_SESSION['user_id'];
$requestorName = $_SESSION['fullname'] ?? 'Unknown User';
$requestorEmail = $_SESSION['email'] ?? '';

// POST data
$itemName = trim($_POST['itemName'] ?? '');
$manufacturer = trim($_POST['manufacturer'] ?? '');
$quantity = (int) ($_POST['quantity'] ?? 0);
$category = trim($_POST['category'] ?? '');
$subcategory = trim($_POST['subcategory'] ?? '');   // ← NEW
$currency = $_POST['currency'] ?? 'USD';
$reason = trim($_POST['reason'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$urgency = $_POST['urgency'] ?? 'Normal';
$requiredDate = !empty($_POST['requiredDate']) ? $_POST['requiredDate'] : null;
$distributor = trim($_POST['distributor'] ?? 'Digi-Key');
$selectedDistributor = trim($_POST['selected_distributor'] ?? $_POST['selectedDistributor'] ?? 'N/A');

// JS sends "unit_price" as a raw USD float
$unitPrice = (float) ($_POST['unit_price'] ?? $_POST['unitPrice'] ?? 0);
$totalAmount = $unitPrice * $quantity;

error_log("submit_request.php — unit_price={$unitPrice}, qty={$quantity}, total={$totalAmount}, category={$category}, subcategory={$subcategory}");

// Validation
if (empty($itemName) || $quantity < 1 || empty($reason) || empty($category)) {
    $response["message"] = "Missing required fields";
    echo json_encode($response);
    $conn->close();
    exit;
}

// Generate PR number
$currentYear = date("Y");
$likePattern = "PR-$currentYear-%";

$stmtCount = $conn->prepare("SELECT COUNT(*) as cnt FROM purchase_requests WHERE pr_number LIKE ?");
$stmtCount->bind_param("s", $likePattern);
$stmtCount->execute();
$row = $stmtCount->get_result()->fetch_assoc();
$existingCount = (int) ($row['cnt'] ?? 0);
$prNumber = "PR-" . $currentYear . "-" . str_pad($existingCount + 1, 4, "0", STR_PAD_LEFT);
$stmtCount->close();

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO purchase_requests
            (pr_number, user_id, requestor_name, request_date,
             category, subcategory,
             mpn, manufacturer,
             quantity, unit_price, total_amount, currency,
             reason, notes, urgency, required_by,
             distributor, selected_distributor_text, status)
        VALUES
            (?, ?, ?, CURDATE(),
             ?, ?,
             ?, ?,
             ?, ?, ?, ?,
             ?, ?, ?, ?,
             ?, ?, 'PENDING')
    ");

    // s i s | s s | s s | i d d s | s s s s | s s
    $stmt->bind_param(
        "sissssissdsssssss",
        $prNumber,            // s
        $userId,              // i
        $requestorName,       // s
        $category,            // s
        $subcategory,         // s  ← NEW
        $itemName,            // s
        $manufacturer,        // s
        $quantity,            // i
        $unitPrice,           // s (DECIMAL stored as string is fine)
        $totalAmount,         // d
        $currency,            // s
        $reason,              // s
        $notes,               // s
        $urgency,             // s
        $requiredDate,        // s
        $distributor,         // s
        $selectedDistributor  // s
    );

    $stmt->execute();
    $stmt->close();

    // Log activity
    $formattedTotal = $currency . ' ' . number_format($totalAmount, 2);
    logActivity(
        $conn,
        'request_created',
        $requestorName,
        "New purchase request submitted for {$quantity} {$category}",
        "MPN: {$itemName}, Manufacturer: {$manufacturer}, Subcategory: {$subcategory}, Total: {$formattedTotal}, Distributor: {$selectedDistributor}, Urgency: {$urgency}",
        $userId,
        $prNumber
    );

    // Email notification
    if (!empty($requestorEmail)) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'dane.rohan1112@gmail.com';
            $mail->Password = 'qtaeffszjnlqexhc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('no-reply@procurement-system.com', 'Procurement System');
            $mail->addAddress($requestorEmail, $requestorName);

            $mail->isHTML(true);
            $mail->Subject = "Your Purchase Request $prNumber has been submitted";
            $mail->Body = "
                <h2>Purchase Request Submitted</h2>
                <p>Dear $requestorName,</p>
                <p>Your purchase request <strong>$prNumber</strong> has been successfully submitted.</p>
                <p>Status: <strong>Pending Approval</strong></p>
                <p>We will notify you when it is approved or needs changes.</p>
                <p>Thank you,<br>Electronics Purchasing Team</p>
            ";
            $mail->AltBody = strip_tags($mail->Body);
            $mail->send();
            error_log("Email sent to $requestorEmail for PR $prNumber");
        } catch (Exception $e) {
            error_log("Email failed for PR $prNumber: " . $mail->ErrorInfo);
        }
    }

    $conn->commit();

    $response = [
        "success" => true,
        "message" => "Request submitted successfully",
        "pr_number" => $prNumber,
        "unit_price" => $unitPrice,
        "total" => $totalAmount,
    ];

} catch (Exception $e) {
    $conn->rollback();
    $response["message"] = "Error submitting request: " . $e->getMessage();
    error_log("INSERT ERROR: " . $e->getMessage());
}

$conn->close();
ob_end_clean();
echo json_encode($response);
ob_end_flush();
?>