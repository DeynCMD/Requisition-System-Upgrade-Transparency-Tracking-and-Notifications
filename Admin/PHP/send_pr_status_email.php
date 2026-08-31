<?php
// Admin/PHP/send_pr_status_email.php
// Reusable function to send PR status update emails to the requestor

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files (adjust path if needed)
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

require_once __DIR__ . '/env_loader.php';
loadEnv(__DIR__ . '/../.env');

function sendPRStatusEmail($conn, $pr_number, $new_status, $requestor_email, $requestor_name = '')
{
    if (empty($requestor_email)) {
        error_log("No email found for PR $pr_number - cannot send notification");
        return false;
    }

    // Normalize status to lowercase for easier comparison
    $status = strtolower(trim($new_status));

    $mail = new PHPMailer(true);

    try {
        // SMTP settings - loaded from .env via env_loader.php
        $mail->isSMTP();
        $mail->Host = getenv('EMAIL_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('EMAIL_USER') ?: '';
        $mail->Password = getenv('EMAIL_PASS') ?: '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)(getenv('EMAIL_PORT') ?: 587);

        // Disable debugging in production (set to 0)
        $mail->SMTPDebug = 0;           // 0 = off, 2 = debug (use only for testing!)
        $mail->Timeout = 30;

        // Sender & recipient
        $mail->setFrom(getenv('EMAIL_FROM') ?: 'no-reply@procurement-system.com', 'Procurement System');
        $mail->addAddress($requestor_email, $requestor_name ?: 'Requestor');

        $subject = '';
        $body = '';

        switch ($status) {
            case 'submitted':
            case 'pending':
                $subject = "Your Purchase Request $pr_number has been submitted";
                $body = "
                    <h2>Purchase Request Submitted</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>Your purchase request <strong>{$pr_number}</strong> has been successfully submitted.</p>
                    <p>Current status: <strong>Pending Approval</strong></p>
                    <p>We will notify you when it is reviewed.</p>
                    <p>Thank you,<br>Electronics Purchasing Team</p>
                ";
                break;

            case 'approved_by_approver':
            case 'approved_approver':
                $subject = "Your Purchase Request $pr_number has been approved";
                $body = "
                    <h2>Approved by Admin</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>Your purchase request <strong>{$pr_number}</strong> has been <strong>approved</strong> by the administrator.</p>
                    <p>It is now waiting for Finance approval.</p>
                    <p>You will be notified again when Finance acts.</p>
                    <p>Thank you,<br>Procurement Team</p>
                ";
                break;

            case 'awaiting_finance':
                $subject = "Your Purchase Request $pr_number is now in Finance review";
                $body = "
                    <h2>Finance Review Started</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>A winning supplier bid has been selected for your purchase request <strong>{$pr_number}</strong>.</p>
                    <p>It is now with the Finance team for final review and budget approval.</p>
                    <p>You will be notified when Finance acts on it.</p>
                    <p>Thank you,<br>Procurement Team</p>
                ";
                break;

            case 'finance_rejected':
                $subject = "❌ Your Purchase Request $pr_number was REJECTED by Finance";
                $body = "
                    <h2 style='color: #f87171;'>❌ Finance Rejection Notice</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>We regret to inform you that your purchase request <strong>{$pr_number}</strong> has been <strong>rejected by Finance</strong> after supplier bidding.</p>
                    <p>Please contact the Finance department for more details about the rejection reason.</p>
                    <p>You may submit a new request if needed.</p>
                    <p>Thank you,<br>Procurement Team</p>
                ";
                break;

            case 'approved_by_finance':
            case 'approved_finance':
                $subject = "✅ Your Purchase Request $pr_number has been APPROVED by Finance";
                $body = "
                    <h2 style='color: #22c55e;'>✅ Finance Approval Received</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>Great news! Your purchase request <strong>{$pr_number}</strong> has been <strong>approved by Finance</strong>.</p>
                    <p>Your request has been successfully processed and is now in the purchasing queue.</p>
                    <p>You will be notified when the items are purchased.</p>
                    <p>Thank you,<br>Electronics Purchasing Team</p>
                ";
                break;

            case 'rejected_by_finance':
            case 'rejected_finance':
                $subject = "❌ Your Purchase Request $pr_number has been REJECTED by Finance";
                $body = "
                    <h2 style='color: #f87171;'>❌ Finance Rejection Notice</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>We regret to inform you that your purchase request <strong>{$pr_number}</strong> has been <strong>rejected by Finance</strong>.</p>
                    <p>Please contact the Finance department for more details about the rejection reason.</p>
                    <p>You may submit a new request if needed.</p>
                    <p>Thank you,<br>Electronics Purchasing Team</p>
                ";
                break;

            case 'purchased':
            case 'bought':
                $subject = "✅ Your Purchase Request $pr_number has been PURCHASED";
                $body = "
                    <h2 style='color: #22c55e;'>✅ Your Items Have Been Purchased!</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>Great news! Your purchase request <strong>{$pr_number}</strong> has been <strong>purchased</strong> by the buyer team.</p>
                    <p>The items are now ordered and on the way. We'll notify you again when they arrive or if there are any updates.</p>
                    <p>Thank you for your request,<br>Electronics Purchasing Team</p>
                ";
                break;

            default:
                $subject = "Update on Purchase Request $pr_number";
                $body = "
                    <h2>Status Update</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>Your request <strong>{$pr_number}</strong> status changed to: <strong>" . ucfirst($status) . "</strong>.</p>
                    <p>Thank you,<br>Electronics Purchasing Team</p>
                ";
                break;
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        // Send email
        $sent = $mail->send();

        if ($sent) {
            error_log("Status email sent to {$requestor_email} for PR {$pr_number} - {$status}");
            return true;
        } else {
            error_log("Email send returned false for PR {$pr_number}: " . $mail->ErrorInfo);
            return false;
        }

    } catch (Exception $e) {
        error_log("Status email failed for PR {$pr_number}: " . $e->getMessage());
        return false;
    }
}