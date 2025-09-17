
<?php

$servername = "localhost";
$username = "root"; // WAMP default username
$password = "";     // WAMP default password
$dbname = "lwb_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// === CRUD LOGIC for Call Logs ===

// Delete
if (isset($_GET['delete_call'])) {
    $id = intval($_GET['delete_call']);
    $conn->query("DELETE FROM call_logs WHERE Call_ID=$id");
    $msg = "<div style='color:green;margin-bottom:1rem;'>Call log deleted.</div>";
}

// Update
if (isset($_POST['update_call_id'])) {
    $id = intval($_POST['update_call_id']);
    $customer_id = intval($_POST['edit_customer_id']);
    $staff_id = intval($_POST['edit_staff_id']);
    $notes = $conn->real_escape_string($_POST['edit_notes']);
    $status = $conn->real_escape_string($_POST['edit_status']);
    $conn->query("UPDATE call_logs SET Customer_ID=$customer_id, Staff_ID=$staff_id, Notes='$notes', Status='$status' WHERE Call_ID=$id");
    $msg = "<div style='color:green;margin-bottom:1rem;'>Call log updated.</div>";
}

// Insert
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['customer_id'],$_POST['staff_id'],$_POST['notes'],$_POST['status']) && !isset($_POST['update_call_id'])) {
    $customer_id=intval($_POST['customer_id']);
    $staff_id=intval($_POST['staff_id']);
    $notes=$conn->real_escape_string($_POST['notes']);
    $status=$conn->real_escape_string($_POST['status']);
    $conn->query("INSERT INTO call_logs (Customer_ID,Staff_ID,Notes,Status) VALUES ($customer_id,$staff_id,'$notes','$status')");
    $msg = "<div style='color:green;margin-bottom:1rem;'>Call log added.</div>";
}

// Fetch customers and staff
$customers = $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
$staffs = $conn->query("SELECT Staff_ID, Name FROM staff ORDER BY Name");

// For edit
$edit_call_id = isset($_GET['edit_call']) ? intval($_GET['edit_call']) : 0;
$edit_call = null;
if ($edit_call_id) {
    $res = $conn->query("SELECT * FROM call_logs WHERE Call_ID=$edit_call_id");
    $edit_call = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Call Logs</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.tab { display: none; }
button { padding: 0.5rem 1rem; cursor: pointer; }
table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
table, th, td { border: 1px solid #ccc; }
th, td { padding: 0.5rem; text-align: left; }
</style>
<script>
// Simple tab system
function showTab(id){
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(t => t.style.display = 'none');
    document.getElementById(id).style.display = 'block';
}
window.onload = function() {
    showTab('calllogs'); // Show calllogs by default
};
</script>
</head>
<body>

<?php if(isset($msg)) echo $msg; ?>

<!-- Tab buttons -->
<button onclick="showTab('calllogs')">Call Logs</button>

<!-- Call Logs Tab -->
<div id="calllogs" class="tab">
    <h2>Call Logs</h2>

    <!-- Add/Edit Form -->
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1rem; border-radius:5px;">
        <?php if($edit_call): ?>
            <input type="hidden" name="update_call_id" value="<?php echo $edit_call['Call_ID']; ?>">
        <?php endif; ?>

        <label>Customer</label>
        <select name="customer_id" required>
            <option value="">--Select--</option>
            <?php while($c = $customers->fetch_assoc()): ?>
                <option value="<?php echo $c['Customer_ID']; ?>" <?php if($edit_call && $edit_call['Customer_ID']==$c['Customer_ID']) echo 'selected'; ?>>
                    <?php echo $c['Name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Staff</label>
        <select name="staff_id" required>
            <option value="">--Select--</option>
            <?php while($s = $staffs->fetch_assoc()): ?>
                <option value="<?php echo $s['Staff_ID']; ?>" <?php if($edit_call && $edit_call['Staff_ID']==$s['Staff_ID']) echo 'selected'; ?>>
                    <?php echo $s['Name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Notes</label>
        <textarea name="notes" required><?php if($edit_call) echo htmlspecialchars($edit_call['Notes']); ?></textarea>

        <label>Status</label>
        <select name="status">
            <option value="Pending" <?php if($edit_call && $edit_call['Status']=='Pending') echo 'selected'; ?>>Pending</option>
            <option value="Completed" <?php if($edit_call && $edit_call['Status']=='Completed') echo 'selected'; ?>>Completed</option>
            <option value="Follow-up" <?php if($edit_call && $edit_call['Status']=='Follow-up') echo 'selected'; ?>>Follow-up</option>
        </select>

        <button type="submit"><?php echo $edit_call ? 'Update' : 'Add'; ?> Call Log</button>
        <?php if($edit_call): ?>
            <a href="?" style="margin-left:10px;">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Display Table -->
    <table>
        <tr><th>ID</th><th>Customer</th><th>Staff</th><th>Date</th><th>Notes</th><th>Status</th><th>Action</th></tr>
        <?php
        $res=$conn->query("SELECT cl.*,c.Name AS CustomerName,s.Name AS StaffName FROM call_logs cl JOIN customers c ON cl.Customer_ID=c.Customer_ID JOIN staff s ON cl.Staff_ID=s.Staff_ID ORDER BY cl.Call_ID DESC");
        while($row=$res->fetch_assoc()){
            echo "<tr>
                <td>{$row['Call_ID']}</td>
                <td>{$row['CustomerName']}</td>
                <td>{$row['StaffName']}</td>
                <td>{$row['Call_Date']}</td>
                <td>{$row['Notes']}</td>
                <td>{$row['Status']}</td>
                <td>
                    <a href='?delete_call={$row['Call_ID']}' onclick=\"return confirm('Delete?')\">Delete</a> | 
                    <a href='?edit_call={$row['Call_ID']}'>Edit</a>
                </td>
            </tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
