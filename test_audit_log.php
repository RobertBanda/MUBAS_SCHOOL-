<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Simulate being logged in as Admin
$_SESSION['role'] = 'Admin';

// Ensure audit log table exists
ensure_audit_log_table($conn);

// Add some sample audit log entries for testing
$sample_entries = [
    ['Admin', 'CREATE', 'customers', 1, null, ['Name' => 'John Doe', 'Address' => '123 Main St', 'Phone' => '123-456-7890', 'Email' => 'john@example.com', 'Category' => 'Residential']],
    ['Billing Officer', 'CREATE', 'bills', 1, null, ['Meter_ID' => 1, 'Billing_Period' => '2024-01', 'Units_Consumed' => 10, 'Amount' => 7900, 'Due_Date' => '2024-02-01', 'Status' => 'Pending']],
    ['Manager', 'UPDATE', 'customers', 1, ['Name' => 'John Doe', 'Address' => '123 Main St'], ['Name' => 'John Smith', 'Address' => '456 Oak Ave']],
    ['Customer Care', 'CREATE', 'meterreadings', 1, null, ['Meter_ID' => 1, 'Reading_Date' => '2024-01-15', 'Reading_Value' => 150.5, 'Recorded_By' => 1]],
    ['Field Technical', 'CREATE', 'Store_Transactions', 1, null, ['Store_ID' => 1, 'Transaction_Type' => 'Issue', 'Quantity' => 5, 'Notes' => 'Issued to customer']],
    ['Admin', 'DELETE', 'staff', 1, ['Name' => 'Old Staff', 'Department' => 'Technical', 'Position' => 'Technician'], null],
    ['Billing Officer', 'LOGIN', 'users', null, null, ['role' => 'Billing Officer']],
    ['Manager', 'LOGIN', 'users', null, null, ['role' => 'Manager']],
    ['Customer Care', 'CREATE', 'complaints', 1, null, ['Customer_ID' => 1, 'Type' => 'Water Quality', 'Description' => 'Water taste issue', 'Status' => 'Open']],
    ['Admin', 'CREATE', 'staff', 2, null, ['Name' => 'Jane Smith', 'Department' => 'Customer Care', 'Position' => 'Representative']]
];

foreach ($sample_entries as $entry) {
    $stmt = $conn->prepare("INSERT INTO audit_log (user_role, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $old_json = $entry[4] ? json_encode($entry[4]) : null;
    $new_json = $entry[5] ? json_encode($entry[5]) : null;
    $ip = '127.0.0.1';
    $user_agent = 'Test Browser';
    
    $stmt->bind_param('sssissss', $entry[0], $entry[1], $entry[2], $entry[3], $old_json, $new_json, $ip, $user_agent);
    $stmt->execute();
}

echo "Sample audit log entries added successfully!<br>";
echo "<a href='admin_audit_log.php'>View Audit Log</a>";
?>
