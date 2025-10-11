<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

echo "<h2>Clear Test Data</h2>";

// Clear test audit log entries (those with test IP addresses)
$result = $conn->query("DELETE FROM audit_log WHERE ip_address = '127.0.0.1' OR ip_address = 'Unknown'");
$deleted = $conn->affected_rows;

echo "<p>Cleared $deleted test audit log entries.</p>";

// Show remaining audit log count
$result = $conn->query("SELECT COUNT(*) as total FROM audit_log");
$count = $result->fetch_assoc()['total'];
echo "<p><strong>Remaining audit log entries:</strong> $count</p>";

if ($count > 0) {
    echo "<h3>Remaining Audit Log Entries:</h3>";
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
    echo "<p style='color: green;'>No audit log entries found. The system is ready to capture real operations.</p>";
}

echo "<br><a href='admin_audit_log.php' style='background:#1976d2;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>View Audit Log</a>";
echo "<br><br><a href='index.php' style='background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Main System</a>";
?>
