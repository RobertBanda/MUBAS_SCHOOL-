<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Simulate being logged in as Admin
$_SESSION['role'] = 'Admin';

// Ensure audit log table exists
ensure_audit_log_table($conn);

echo "<h2>Complete Audit Log System Test</h2>";

// Clear existing test data
$conn->query("DELETE FROM audit_log WHERE user_role = 'Admin' AND ip_address = '127.0.0.1'");

echo "<h3>Testing All System Operations...</h3>";

// Test 1: Customer Operations
echo "<h4>1. Customer Operations</h4>";
log_audit_action($conn, 'CREATE', 'customers', 1, null, [
    'Name' => 'John Doe',
    'Address' => '123 Main Street',
    'Phone' => '555-0123',
    'Email' => 'john@example.com',
    'Category' => 'Residential'
]);
echo "✅ Customer created<br>";

log_audit_action($conn, 'UPDATE', 'customers', 1, 
    ['Name' => 'John Doe', 'Address' => '123 Main Street'],
    ['Name' => 'John Smith', 'Address' => '456 Oak Avenue']
);
echo "✅ Customer updated<br>";

// Test 2: Meter Operations
echo "<h4>2. Meter Operations</h4>";
log_audit_action($conn, 'CREATE', 'meters', 1, null, [
    'Customer_ID' => 1,
    'Meter_Number' => 'M001',
    'Installation_Date' => '2024-01-01',
    'Status' => 'Active',
    'Last_Service_Date' => '2024-01-01'
]);
echo "✅ Meter created<br>";

// Test 3: Meter Reading Operations
echo "<h4>3. Meter Reading Operations</h4>";
log_audit_action($conn, 'CREATE', 'meterreadings', 1, null, [
    'Meter_ID' => 1,
    'Reading_Date' => '2024-01-15',
    'Reading_Value' => 125.5,
    'Recorded_By' => 1
]);
echo "✅ Meter reading created<br>";

log_audit_action($conn, 'UPDATE', 'meterreadings', 1,
    ['Reading_Value' => 125.5],
    ['Reading_Value' => 130.2]
);
echo "✅ Meter reading updated<br>";

// Test 4: Bill Operations
echo "<h4>4. Bill Operations</h4>";
log_audit_action($conn, 'CREATE', 'bills', 1, null, [
    'Meter_ID' => 1,
    'Billing_Period' => '2024-01',
    'Units_Consumed' => 10,
    'Amount' => 7900,
    'Due_Date' => '2024-02-01',
    'Status' => 'Pending'
]);
echo "✅ Bill created<br>";

// Test 5: Payment Operations
echo "<h4>5. Payment Operations</h4>";
log_audit_action($conn, 'CREATE', 'payments', 1, null, [
    'Bill_ID' => 1,
    'Payment_Date' => '2024-01-20',
    'Amount_Paid' => 7900,
    'Method' => 'Cash'
]);
echo "✅ Payment created<br>";

log_audit_action($conn, 'UPDATE', 'payments', 1,
    ['Amount_Paid' => 7900, 'Method' => 'Cash'],
    ['Amount_Paid' => 8000, 'Method' => 'Bank Transfer']
);
echo "✅ Payment updated<br>";

// Test 6: Staff Operations
echo "<h4>6. Staff Operations</h4>";
log_audit_action($conn, 'CREATE', 'staff', 1, null, [
    'Name' => 'Jane Smith',
    'Department' => 'Customer Care',
    'Position' => 'Representative'
]);
echo "✅ Staff created<br>";

log_audit_action($conn, 'UPDATE', 'staff', 1,
    ['Position' => 'Representative'],
    ['Position' => 'Senior Representative']
);
echo "✅ Staff updated<br>";

// Test 7: Store Operations
echo "<h4>7. Store Operations</h4>";
log_audit_action($conn, 'CREATE', 'store', 1, null, [
    'Complaint_ID' => 1,
    'Customer_ID' => 1,
    'Item_Type' => 'Water Meter',
    'Staff_ID' => 1
]);
echo "✅ Store item created<br>";

// Test 8: Store Transaction Operations
echo "<h4>8. Store Transaction Operations</h4>";
log_audit_action($conn, 'CREATE', 'Store_Transactions', 1, null, [
    'Store_ID' => 1,
    'Transaction_Type' => 'Issue',
    'Quantity' => 5,
    'Notes' => 'Issued to customer for repair'
]);
echo "✅ Store transaction created<br>";

// Test 9: Complaint Operations
echo "<h4>9. Complaint Operations</h4>";
log_audit_action($conn, 'CREATE', 'complaints', 1, null, [
    'Customer_ID' => 1,
    'Type' => 'Water Quality',
    'Description' => 'Water taste issue',
    'Status' => 'Open',
    'Resolved_By' => 1
]);
echo "✅ Complaint created<br>";

log_audit_action($conn, 'UPDATE', 'complaints', 1,
    ['Status' => 'Open'],
    ['Status' => 'Resolved']
);
echo "✅ Complaint updated<br>";

// Test 10: Call Log Operations
echo "<h4>10. Call Log Operations</h4>";
log_audit_action($conn, 'CREATE', 'call_logs', 1, null, [
    'Customer_ID' => 1,
    'Staff_ID' => 1,
    'Notes' => 'Customer called about billing',
    'Status' => 'Completed'
]);
echo "✅ Call log created<br>";

// Test 11: Connection Operations
echo "<h4>11. Connection Operations</h4>";
log_audit_action($conn, 'CREATE', 'connections', 1, null, [
    'Customer_ID' => 1,
    'Connection_Type' => 'Residential'
]);
echo "✅ Connection created<br>";

// Test 12: Login Operations
echo "<h4>12. Login Operations</h4>";
log_audit_action($conn, 'LOGIN', 'users', null, null, ['role' => 'Admin']);
echo "✅ Admin login logged<br>";

log_audit_action($conn, 'LOGIN', 'users', null, null, ['role' => 'Billing Officer']);
echo "✅ Billing Officer login logged<br>";

// Test 13: Deletion Operations
echo "<h4>13. Deletion Operations</h4>";
log_audit_action($conn, 'DELETE', 'customers', 1, 
    ['Name' => 'John Smith', 'Address' => '456 Oak Avenue'], 
    null
);
echo "✅ Customer deletion logged<br>";

log_audit_action($conn, 'DELETE', 'payments', 1, 
    ['Bill_ID' => 1, 'Amount_Paid' => 8000], 
    null
);
echo "✅ Payment deletion logged<br>";

echo "<h3>✅ All Tests Completed!</h3>";

// Show summary
$result = $conn->query("SELECT COUNT(*) as total FROM audit_log WHERE user_role = 'Admin' AND ip_address = '127.0.0.1'");
$count = $result->fetch_assoc()['total'];
echo "<p><strong>Total audit log entries created:</strong> $count</p>";

// Show breakdown by action
$result = $conn->query("SELECT action, COUNT(*) as count FROM audit_log WHERE user_role = 'Admin' AND ip_address = '127.0.0.1' GROUP BY action ORDER BY count DESC");
echo "<h4>Breakdown by Action:</h4>";
echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li><strong>{$row['action']}:</strong> {$row['count']} entries</li>";
}
echo "</ul>";

// Show breakdown by table
$result = $conn->query("SELECT table_name, COUNT(*) as count FROM audit_log WHERE user_role = 'Admin' AND ip_address = '127.0.0.1' GROUP BY table_name ORDER BY count DESC");
echo "<h4>Breakdown by Table:</h4>";
echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li><strong>{$row['table_name']}:</strong> {$row['count']} entries</li>";
}
echo "</ul>";

echo "<br><a href='admin_audit_log.php' style='background:#1976d2;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>View Full Audit Log</a>";
echo "<br><br><a href='check_audit_data.php' style='background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Check Audit Data</a>";
?>
