<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Ensure audit log table exists
ensure_audit_log_table($conn);

echo "<h2>Debug Audit Log System</h2>";

// Check if table exists and has data
$result = $conn->query("SHOW TABLES LIKE 'audit_log'");
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>❌ audit_log table does not exist!</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ audit_log table exists</p>";
}

// Check table structure
$result = $conn->query("DESCRIBE audit_log");
echo "<h3>Table Structure:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['Type'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['Null'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['Key'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['Extra'] ?? '') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check data count
$result = $conn->query("SELECT COUNT(*) as total FROM audit_log");
$count = $result->fetch_assoc()['total'];
echo "<p><strong>Total records in audit_log:</strong> $count</p>";

if ($count > 0) {
    echo "<h3>Sample Data (last 5 records):</h3>";
    $result = $conn->query("SELECT * FROM audit_log ORDER BY timestamp DESC LIMIT 5");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>User Role</th><th>Action</th><th>Table</th><th>Record ID</th><th>Timestamp</th><th>IP</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['user_role'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['action'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['table_name'] ?? '') . "</td>";
        echo "<td>" . ($row['record_id'] ? htmlspecialchars($row['record_id']) : 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['timestamp'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['ip_address'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No data in audit_log table</p>";
    
    // Add some test data
    echo "<h3>Adding test data...</h3>";
    $_SESSION['role'] = 'Admin';
    
    // Test the audit logging function
    if (function_exists('log_audit_action')) {
        log_audit_action($conn, 'CREATE', 'customers', 1, null, ['Name' => 'Test Customer', 'Address' => '123 Test St']);
        echo "✅ Test audit entry added<br>";
        
        // Check again
        $result = $conn->query("SELECT COUNT(*) as total FROM audit_log");
        $count = $result->fetch_assoc()['total'];
        echo "<p><strong>New total records:</strong> $count</p>";
    } else {
        echo "❌ log_audit_action function not found!";
    }
}

// Test the query that the admin page uses
echo "<h3>Testing Admin Page Query:</h3>";
$sql = "SELECT * FROM audit_log ORDER BY timestamp DESC LIMIT 10";
$result = $conn->query($sql);

if ($result) {
    echo "<p style='color: green;'>✅ Query executed successfully</p>";
    echo "<p>Records returned: " . $result->num_rows . "</p>";
} else {
    echo "<p style='color: red;'>❌ Query failed: " . $conn->error . "</p>";
}

echo "<br><a href='admin_audit_log.php' style='background:#1976d2;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Admin Audit Log</a>";
echo "<br><br><a href='test_real_operations.php' style='background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Test Real Operations</a>";
?>
