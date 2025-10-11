<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Ensure audit log table exists
ensure_audit_log_table($conn);

echo "<h2>Audit Log Data Check</h2>";

// Check current audit log entries
$result = $conn->query("SELECT COUNT(*) as total FROM audit_log");
$count = $result->fetch_assoc()['total'];
echo "<p><strong>Total audit log entries:</strong> $count</p>";

if ($count > 0) {
    echo "<h3>Recent Audit Log Entries:</h3>";
    $result = $conn->query("SELECT * FROM audit_log ORDER BY timestamp DESC LIMIT 10");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>User</th><th>Action</th><th>Table</th><th>Record ID</th><th>Timestamp</th><th>IP</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_role']) . "</td>";
        echo "<td>" . htmlspecialchars($row['action']) . "</td>";
        echo "<td>" . htmlspecialchars($row['table_name']) . "</td>";
        echo "<td>" . ($row['record_id'] ? htmlspecialchars($row['record_id']) : 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No audit log entries found. The system may not be logging actions properly.</p>";
}

echo "<br><a href='admin_audit_log.php' style='background:#1976d2;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>View Full Audit Log</a>";
echo "<br><br><a href='test_actions.php' style='background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Test Audit Logging</a>";
?>
