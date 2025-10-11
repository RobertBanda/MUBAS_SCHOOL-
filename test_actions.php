<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Simulate being logged in as Admin
$_SESSION['role'] = 'Admin';

// Test the audit logging by performing some actions
echo "<h2>Testing Audit Log System</h2>";

// Test 1: Log a customer creation
log_audit_action($conn, 'CREATE', 'customers', 1, null, [
    'Name' => 'Test Customer',
    'Address' => '123 Test Street',
    'Phone' => '555-0123',
    'Email' => 'test@example.com',
    'Category' => 'Residential'
]);
echo "✅ Logged customer creation<br>";

// Test 2: Log a meter reading
log_audit_action($conn, 'CREATE', 'meterreadings', 1, null, [
    'Meter_ID' => 1,
    'Reading_Date' => '2024-01-15',
    'Reading_Value' => 125.5,
    'Recorded_By' => 1
]);
echo "✅ Logged meter reading<br>";

// Test 3: Log a bill creation
log_audit_action($conn, 'CREATE', 'bills', 1, null, [
    'Meter_ID' => 1,
    'Billing_Period' => '2024-01',
    'Units_Consumed' => 10,
    'Amount' => 7900,
    'Due_Date' => '2024-02-01',
    'Status' => 'Pending'
]);
echo "✅ Logged bill creation<br>";

// Test 4: Log a staff update
log_audit_action($conn, 'UPDATE', 'staff', 1, 
    ['Name' => 'Old Name', 'Department' => 'Old Dept', 'Position' => 'Old Position'],
    ['Name' => 'New Name', 'Department' => 'New Dept', 'Position' => 'New Position']
);
echo "✅ Logged staff update<br>";

// Test 5: Log a deletion
log_audit_action($conn, 'DELETE', 'customers', 1, 
    ['Name' => 'Deleted Customer', 'Address' => '123 Deleted St'], 
    null
);
echo "✅ Logged customer deletion<br>";

// Test 6: Log a login
log_audit_action($conn, 'LOGIN', 'users', null, null, ['role' => 'Admin']);
echo "✅ Logged admin login<br>";

echo "<br><strong>All tests completed!</strong><br>";
echo "<a href='admin_audit_log.php' style='background:#1976d2;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin-top:10px;'>View Audit Log</a>";
?>
