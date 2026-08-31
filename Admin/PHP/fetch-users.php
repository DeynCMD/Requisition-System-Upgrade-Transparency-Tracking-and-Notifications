<?php
// Admin/PHP/fetch-users.php
header("Content-Type: application/json");
require_once "db.php";  // your connection file

// Include username in the SELECT
$sql = "SELECT id, firstname, lastname, middlename, username, email, role, gender 
        FROM users 
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(["error" => mysqli_error($conn)]);
    exit;
}

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = [
        "id"         => $row["id"],
        "firstname"  => $row["firstname"] ?? "",
        "lastname"   => $row["lastname"] ?? "",
        "middlename" => $row["middlename"] ?? "",
        "username"   => $row["username"] ?? "—",          // ← Added
        "email"      => $row["email"] ?? "",
        "role"       => $row["role"] ?? "REQUESTOR",
        "gender"     => $row["gender"] ?? "—"
    ];
}

echo json_encode($users);

mysqli_close($conn);
?>