<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Simulate being logged in as different users to test real operations
echo "<h2>Testing Real System Operations</h2>";
echo "<p>This script simulates real user operations that should appear in the audit log.</p>";

// Test 1: Admin creates a customer
$_SESSION['role'] = 'Admin';
echo "<h3>1. Admin creates a customer</h3>";
$name = 'Real Test Customer';
$address = '123 Real Street';
$phone = '555-0123';
$email = 'real@example.com';
$category = 'Residential';

$sql = "INSERT INTO customers (Name, Address, Phone, Email, Category) VALUES ('$name', '$address', '$phone', '$email', '$category')";
if ($conn->query($sql)) {
    // Log audit action
    if (function_exists('log_audit_action')) {
        $new_values = ['Name' => $name, 'Address' => $address, 'Phone' => $phone, 'Email' => $email, 'Category' => $category];
        log_audit_action($conn, 'CREATE', 'customers', $conn->insert_id, null, $new_values);
    }
    echo "✅ Customer created with ID: " . $conn->insert_id . "<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Test 2: Billing Officer creates a bill
$_SESSION['role'] = 'Billing Officer';
echo "<h3>2. Billing Officer creates a bill</h3>";
$meter_id = 1;
$billing_period = '2024-01';
$units = 15;
$amount = 15000;
$due_date = '2024-02-01';
$status = 'Pending';

$sql = "INSERT INTO bills (Meter_ID, Billing_Period, Units_Consumed, Amount, Due_Date, Status) VALUES ($meter_id, '$billing_period', $units, $amount, '$due_date', '$status')";
if ($conn->query($sql)) {
    // Log audit action
    if (function_exists('log_audit_action')) {
        $new_values = ['Meter_ID' => $meter_id, 'Billing_Period' => $billing_period, 'Units_Consumed' => $units, 'Amount' => $amount, 'Due_Date' => $due_date, 'Status' => $status];
        log_audit_action($conn, 'CREATE', 'bills', $conn->insert_id, null, $new_values);
    }
    echo "✅ Bill created with ID: " . $conn->insert_id . "<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Test 3: Manager updates a customer
$_SESSION['role'] = 'Manager';
echo "<h3>3. Manager updates a customer</h3>";
$customer_id = 1;
$old_name = 'Real Test Customer';
$new_name = 'Updated Test Customer';
$old_address = '123 Real Street';
$new_address = '456 Updated Avenue';

// Get old data first
$old_data = $conn->query("SELECT * FROM customers WHERE Customer_ID = $customer_id")->fetch_assoc();
$new_values = ['Name' => $new_name, 'Address' => $new_address];

$sql = "UPDATE customers SET Name='$new_name', Address='$new_address' WHERE Customer_ID=$customer_id";
if ($conn->query($sql)) {
    // Log audit action
    if (function_exists('log_audit_action') && $old_data) {
        log_audit_action($conn, 'UPDATE', 'customers', $customer_id, $old_data, $new_values);
    }
    echo "✅ Customer updated<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Test 4: Customer Care creates a meter reading
$_SESSION['role'] = 'Customer Care';
echo "<h3>4. Customer Care creates a meter reading</h3>";
$meter_id = 1;
$reading_date = '2024-01-15';
$reading_value = 125.5;
$recorded_by = 1;

$sql = "INSERT INTO meterreadings (Meter_ID, Reading_Date, Reading_Value, Recorded_By) VALUES ($meter_id, '$reading_date', $reading_value, $recorded_by)";
if ($conn->query($sql)) {
    // Log audit action
    if (function_exists('log_audit_action')) {
        $new_values = ['Meter_ID' => $meter_id, 'Reading_Date' => $reading_date, 'Reading_Value' => $reading_value, 'Recorded_By' => $recorded_by];
        log_audit_action($conn, 'CREATE', 'meterreadings', $conn->insert_id, null, $new_values);
    }
    echo "✅ Meter reading created with ID: " . $conn->insert_id . "<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Test 5: Field Technical creates a store transaction
$_SESSION['role'] = 'Field Technical';
echo "<h3>5. Field Technical creates a store transaction</h3>";
$store_id = 1;
$transaction_type = 'Issue';
$quantity = 5;
$notes = 'Issued to customer for repair';

$sql = "INSERT INTO Store_Transactions (Store_ID, Transaction_Type, Quantity, Notes) VALUES ($store_id, '$transaction_type', $quantity, '$notes')";
if ($conn->query($sql)) {
    // Log audit action
    if (function_exists('log_audit_action')) {
        $new_values = ['Store_ID' => $store_id, 'Transaction_Type' => $transaction_type, 'Quantity' => $quantity, 'Notes' => $notes];
        log_audit_action($conn, 'CREATE', 'Store_Transactions', $conn->insert_id, null, $new_values);
    }
    echo "✅ Store transaction created with ID: " . $conn->insert_id . "<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Test 6: Admin deletes a record
$_SESSION['role'] = 'Admin';
echo "<h3>6. Admin deletes a record</h3>";
$delete_id = 1;
$table = 'customers';

// Get data before deletion
$delete_data = $conn->query("SELECT * FROM $table WHERE Customer_ID = $delete_id")->fetch_assoc();
$sql = "DELETE FROM $table WHERE Customer_ID = $delete_id";
if ($conn->query($sql)) {
    // Log audit action
    if (function_exists('log_audit_action') && $delete_data) {
        log_audit_action($conn, 'DELETE', $table, $delete_id, $delete_data, null);
    }
    echo "✅ Record deleted<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

echo "<h3>✅ Real Operations Test Completed!</h3>";
echo "<p>These operations should now appear in the audit log with real user roles and timestamps.</p>";

// Show current audit log count
$result = $conn->query("SELECT COUNT(*) as total FROM audit_log");
$count = $result->fetch_assoc()['total'];
echo "<p><strong>Total audit log entries:</strong> $count</p>";

echo "<br><a href='admin_audit_log.php' style='background:#1976d2;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>View Audit Log</a>";
echo "<br><br><a href='index.php' style='background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Main System</a>";
?>
