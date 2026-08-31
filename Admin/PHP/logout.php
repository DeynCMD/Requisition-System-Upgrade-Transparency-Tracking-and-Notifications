<?php
session_start();

// Prevent browser from caching protected pages — back button won't show stale content
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Clear all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../HTML/ZE-Electronics.php");
exit();
?>
