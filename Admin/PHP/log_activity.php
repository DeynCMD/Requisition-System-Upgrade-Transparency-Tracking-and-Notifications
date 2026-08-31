<?php
// log_activity.php - Helper function to log activities

function logActivity($conn, $activity_type, $performed_by, $description, $details = null, $target_user = null, $pr_number = null)
{
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    $stmt = $conn->prepare("
        INSERT INTO activity_logs 
        (activity_type, performed_by, target_user, pr_number, description, details, ip_address) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssss",
        $activity_type,
        $performed_by,
        $target_user,
        $pr_number,
        $description,
        $details,
        $ip_address
    );

    $stmt->execute();
    $stmt->close();
}
?>