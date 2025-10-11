
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php';
require_once __DIR__ . '/auth.php';
require_login();
?>

<?php
require_once __DIR__ . '/includes/customers_add.php';
require_once __DIR__ . '/includes/customers_list.php';
require_once __DIR__ . '/includes/meters_add.php';
require_once __DIR__ . '/includes/meters_list.php';
require_once __DIR__ . '/includes/connections_add.php';
require_once __DIR__ . '/includes/connections_list.php';
require_once __DIR__ . '/includes/bills_add.php';
require_once __DIR__ . '/includes/bills_list.php';
require_once __DIR__ . '/includes/payments_add.php';
require_once __DIR__ . '/includes/payments_list.php';
require_once __DIR__ . '/includes/complaints_add.php';
require_once __DIR__ . '/includes/complaints_list.php';
require_once __DIR__ . '/includes/staff_add.php';
require_once __DIR__ . '/includes/staff_list.php';
require_once __DIR__ . '/includes/call_logs_add.php';
require_once __DIR__ . '/includes/call_logs_list.php';
require_once __DIR__ . '/includes/store_add.php';
require_once __DIR__ . '/includes/store_list.php';
require_once __DIR__ . '/includes/store_transactions_add.php';
require_once __DIR__ . '/includes/store_transactions_list.php';
require_once __DIR__ . '/includes/balances_list.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LWB Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
<!-- External JavaScript (with defer attribute) -->
<script src="assets/js/script.js" defer></script>
<!-- Material Icons CDN -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6fb;
    margin: 0;
    color: #222;
  <?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  include 'db.php';
  ?>
  }
  header {
    background: #1976d2;
        <h2>Connections</h2>
        <?php
        // Handle delete
        if (isset($_GET['delete_connection'])) {
          $delete_id = intval($_GET['delete_connection']);
          $conn->query("DELETE FROM connections WHERE Connection_ID = $delete_id");
          echo '<div style="color:green;margin-bottom:1rem;">Connection deleted.</div>';
        }
        // Handle update
        if (isset($_POST['update_connection_id'])) {
          $id = intval($_POST['update_connection_id']);
          $customer_id = intval($_POST['edit_customer_id']);
          $type = $conn->real_escape_string(trim($_POST['edit_connection_type']));
          $sql = "UPDATE connections SET Customer_ID=$customer_id, Connection_Type='$type' WHERE Connection_ID=$id";
          if ($conn->query($sql)) {
            echo '<div style="color:green;margin-bottom:1rem;">Connection updated.</div>';
          } else {
            echo '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
          }
        }
        // Handle insert
        if (
          $_SERVER['REQUEST_METHOD'] === 'POST' &&
          isset($_POST['customer_id'], $_POST['connection_type']) &&
          !isset($_POST['update_connection_id'])
        ) {
          $type = $conn->real_escape_string(trim($_POST['connection_type']));
          $customer_id = 0;
          if (isset($_POST['customer_id']) && $_POST['customer_id'] === 'new' && !empty($_POST['new_customer_name'])) {
            $new_name = $conn->real_escape_string(trim($_POST['new_customer_name']));
            // Insert new customer (minimal info)
            $conn->query("INSERT INTO customers (Name) VALUES ('$new_name')");
            $customer_id = $conn->insert_id;
            // Log audit action for customer creation
            if (function_exists('log_audit_action')) {
                $new_values = ['Name' => $new_name];
                log_audit_action($conn, 'CREATE', 'customers', $customer_id, null, $new_values);
            }
          } elseif (isset($_POST['customer_id']) && is_numeric($_POST['customer_id'])) {
            $customer_id = intval($_POST['customer_id']);
          }
          if ($customer_id && $type) {
            // Prevent duplicate connection for same customer and type
            $dupCon = $conn->query("SELECT Connection_ID FROM connections WHERE Customer_ID = $customer_id AND Connection_Type = '$type'");
            if ($dupCon && $dupCon->num_rows > 0) {
              echo '<div style="color:red;margin-bottom:1rem;">Error: This customer already has a connection of this type.</div>';
            } else {
              $sql = "INSERT INTO connections (Customer_ID, Connection_Type) VALUES ($customer_id, '$type')";
              if ($conn->query($sql)) {
                // Log audit action
                if (function_exists('log_audit_action')) {
                    $new_values = ['Customer_ID' => $customer_id, 'Connection_Type' => $type];
                    log_audit_action($conn, 'CREATE', 'connections', $conn->insert_id, null, $new_values);
                }
                echo '<div style="color:green;margin-bottom:1rem;">Connection added successfully.</div>';
              } else {
                echo '<div style="color:red;margin-bottom:1rem;">Error: ' . $conn->error . '</div>';
              }
            }
          } else {
            echo '<div style="color:red;margin-bottom:1rem;">All fields are required.</div>';
          }
        }
        $edit_connection_id = isset($_GET['edit_connection']) ? intval($_GET['edit_connection']) : 0;
        $custRes = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
        $hasCustomers = ($custRes && $custRes->num_rows > 0);
        if (!$edit_connection_id) {
          if ($hasCustomers) {
        ?>
        <form method="post">
          <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
              <label for="customer_id">Customer name</label><br>
              <select id="customer_id" name="customer_id" required>
                <option value="">--Select--</option>
                <?php
                $custRes->data_seek(0);
                while($cust = $custRes->fetch_assoc()) {
                  echo "<option value='{$cust['Customer_ID']}'>{$cust['Name']}</option>";
                }
                ?>
                <option value="new">Add new customer...</option>
              </select>
              <input type="text" id="new_customer_name" name="new_customer_name" placeholder="Enter new customer name" style="display:none;margin-top:0.5rem;" />
            </div>
            <div style="flex:1; min-width:200px;">
              <label for="connection_type">Type of connection</label><br>
              <select id="connection_type" name="connection_type" required>
                <option value="">--Select--</option>
                <option value="Main pipe">Main pipe</option>
                <option value="Plumbing connection">Plumbing connection</option>
                <option value="New water connection">New water connection</option>
                <option value="Domestic">Domestic</option>
                <option value="Commercial">Commercial</option>
                <option value="Institutional">Institutional</option>
              </select>
            </div>
            <div style="min-width:100px;">
              <button type="submit">Add Connection</button>
            </div>
          </div>
        
        </form>
        <?php
          } else {
            echo '<div style="color:red;margin-bottom:1rem;">No customers found. Please add a customer first.</div>';
          }
        }
        ?>
        <table>
          <tr><th>ID</th><th>Customer</th><th>Type of Connection</th><th>Action</th></tr>
          <?php
          $res = $conn->query("SELECT con.*, c.Name as Customer_Name FROM connections con JOIN customers c ON con.Customer_ID=c.Customer_ID ORDER BY con.Connection_ID DESC");
          $edit_connection_id = isset($_GET['edit_connection']) ? intval($_GET['edit_connection']) : 0;
          while($row = $res->fetch_assoc()){
            if ($edit_connection_id === intval($row['Connection_ID'])) {
              // Edit form row
              echo "<tr>
                <form method='post'>
                  <td>{$row['Connection_ID']}<input type='hidden' name='update_connection_id' value='{$row['Connection_ID']}'></td>
                  <td><select name='edit_customer_id' required style='width:100%'>";
                    $custRes2 = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
                    while($cust2 = $custRes2->fetch_assoc()) {
                      $selected = ($cust2['Customer_ID'] == $row['Customer_ID']) ? 'selected' : '';
                      echo "<option value='{$cust2['Customer_ID']}' $selected>{$cust2['Name']}</option>";
                    }
              echo "</select></td>
                  <td><select name='edit_connection_type' required style='width:100%'>
                    <option value='Main pipe'".($row['Connection_Type']==='Main pipe'?' selected':'').">Main pipe</option>
                    <option value='Plumbing connection'".($row['Connection_Type']==='Plumbing connection'?' selected':'').">Plumbing connection</option>
                    <option value='New water connection'".($row['Connection_Type']==='New water connection'?' selected':'').">New water connection</option>
                    <option value='Domestic'".($row['Connection_Type']==='Domestic'?' selected':'').">Domestic</option>
                    <option value='Commercial'".($row['Connection_Type']==='Commercial'?' selected':'').">Commercial</option>
                    <option value='Institutional'".($row['Connection_Type']==='Institutional'?' selected':'').">Institutional</option>
                  </select></td>
                  <td>
                    <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                    <a href='?tab=connections' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
                  </td>
                </form>
              </tr>";
            } else {
              echo "<tr>
                      <td>{$row['Connection_ID']}</td>
                      <td>".htmlspecialchars($row['Customer_Name'])."</td>
                      <td>{$row['Connection_Type']}</td>
                      <td>
                        <a href='?delete_connection={$row['Connection_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this connection?');\"><span class='material-icons'>delete</span></a>
                        <a href='?edit_connection={$row['Connection_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                      </td>
                    </tr>";
            }
          }
          ?>
        </table>
      </div>
    padding: 2rem 1rem 1rem 1rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    position: sticky;
    top: 0;
    z-index: 10;
  }
  header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2.2rem;
    letter-spacing: 1px;
  }
  #search {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    border: none;
    width: 250px;
    font-size: 1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    outline: none;
    margin-top: 0.5rem;
  }
  nav {
    display: flex;
    justify-content: center;
    gap: 1rem;
    background: #fff;
    padding: 1rem 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    position: sticky;
    top: 80px;
    z-index: 9;
  }
  nav button {
    background: #e3eafc;
    color: #1976d2;
    border: none;
    padding: 0.7rem 1.5rem;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
  }
  nav button:hover, nav button.active {
    background: #1976d2;
    color: #fff;
  }
  main {
    max-width: 1200px;
    margin: 2rem auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
    padding: 2rem;
    min-height: 60vh;
  }
  .tab {
    display: none;
    animation: fadeIn 0.5s;
  }
  .tab.active {
    display: block;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: none; }
  }
  h2 {
    color: #1976d2;
    margin-top: 0;
    font-size: 1.5rem;
    border-bottom: 2px solid #e3eafc;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    background: #f9fbff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    margin-bottom: 2rem;
  }
  th, td {
    padding: 0.8rem 1rem;
    text-align: left;
  }
  th {
    background: #e3eafc;
    color: #1976d2;
    font-weight: 600;
    border-bottom: 2px solid #d0d7e6;
  }
  tr:nth-child(even) td {
    background: #f4f6fb;
  }
  tr:hover td {
    background: #e3eafc;
    transition: background 0.2s;
  }
  form {
    margin-bottom: 2rem;
    background: #f9fbff;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
  }
  label {
    font-weight: 500;
    color: #1976d2;
    margin-bottom: 0.2rem;
    display: block;
  }
  input, select {
    margin-top: 0.2rem;
    margin-bottom: 0.7rem;
    padding: 0.5rem;
    border-radius: 5px;
    border: 1px solid #d0d7e6;
    width: 100%;
    font-size: 1rem;
  }
  button[type="submit"] {
    background: #1976d2;
    color: #fff;
    padding: 0.7rem 1.5rem;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s;
  }
  button[type="submit"]:hover {
    background: #1451a3;
  }
  .dashboard-cards {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
  }
  .dashboard-card {
    background: linear-gradient(135deg, #e3eafc 60%, #fff 100%);
    color: #1976d2;
    padding: 2rem 1.5rem;
    border-radius: 12px;
    flex: 1 1 200px;
    min-width: 200px;
    font-size: 1.2rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    text-align: center;
  }
  footer {
    text-align: center;
    padding: 1.5rem 0 1rem 0;
    background: #1976d2;
    color: #fff;
    margin-top: 2rem;
    border-radius: 0 0 16px 16px;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
  }

  
</style>
</head>
<body>
<?php
  $role = isset($_SESSION['role']) ? (string)$_SESSION['role'] : '';
  $defaultTab = 'dashboard';
  if (isset($_REQUEST['tab']) && is_string($_REQUEST['tab'])) {
    $tabCandidate = preg_replace('/[^a-z_]/', '', $_REQUEST['tab']);
    if ($tabCandidate) { $defaultTab = $tabCandidate; }
  }
  if (!is_tab_allowed($role, $defaultTab)) {
    $defaultTab = first_allowed_tab($role);
  }
?>
<script>
  window.DEFAULT_TAB = <?= json_encode($defaultTab) ?>;
</script>

<header>
  <img src="lwb.png" alt="LWB Logo" style="max-width:100px;display:block;margin:0 auto 1rem auto;">
  <h1>LWB Management System</h1>
  <div style="text-align: center; margin: 0.5rem 0; color: #fff; font-size: 1.1rem; font-weight: 500;">
    <?php echo welcome_text(); ?>
  </div>
  <input type="text" id="search" placeholder="Search...">
</header>

<nav class="nav-container">
  <div class="nav-toggle" id="navToggle">Menu</div>
  <div class="nav-menu" id="navMenu">
    <button data-tab="dashboard" onclick="showTab('dashboard')">Dashboard</button>
    <?php if (is_tab_allowed($role, 'customers')): ?><button data-tab="customers" onclick="showTab('customers')">Customers</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'meters')): ?><button data-tab="meters" onclick="showTab('meters')">Meters</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'connections')): ?><button data-tab="connections" onclick="showTab('connections')">Connections</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'meterreadings')): ?><button data-tab="meterreadings" onclick="showTab('meterreadings')">Meter Readings</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'bills')): ?><button data-tab="bills" onclick="showTab('bills')">Bills</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'payments')): ?><button data-tab="payments" onclick="showTab('payments')">Payments</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'balances')): ?><button data-tab="balances" onclick="showTab('balances')">Customer Balances</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'complaints')): ?><button data-tab="complaints" onclick="showTab('complaints')">Complaints</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'calllogs')): ?><button data-tab="calllogs" onclick="showTab('calllogs')">Call Logs</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'staff')): ?><button data-tab="staff" onclick="showTab('staff')">Staff</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'store')): ?><button data-tab="store" onclick="showTab('store')">Store</button><?php endif; ?>
    <?php if (is_tab_allowed($role, 'store_transactions')): ?><button data-tab="store_transactions" onclick="showTab('store_transactions')">Store Transactions</button><?php endif; ?>
    <span style="margin-left:auto"></span>
    <?php if ($role === 'Admin'): ?><a href="admin_passwords.php" style="color:#1976d2;display:inline-flex;align-items:center;text-decoration:none;margin-right:12px"><span class="material-icons" style="font-size:18px;margin-right:4px">admin_panel_settings</span>Manage Passwords</a><?php endif; ?>
    <?php if ($role === 'Admin'): ?><a href="admin_audit_log.php" style="color:#1976d2;display:inline-flex;align-items:center;text-decoration:none;margin-right:12px"><span class="material-icons" style="font-size:18px;margin-right:4px">history</span>Audit Log</a><?php endif; ?>
    <a href="change_password.php" style="color:#1976d2;display:inline-flex;align-items:center;text-decoration:none;margin-right:12px"><span class="material-icons" style="font-size:18px;margin-right:4px">lock</span>Change Password</a>
    <a href="logout.php" style="color:#d32f2f;display:inline-flex;align-items:center;text-decoration:none"><span class="material-icons" style="font-size:18px;margin-right:4px">logout</span>Logout</a>
  </div>
</nav>

<main>

  <div id="connections" class="tab">
    <h2>Connections</h2>
    <?php
    // Unified feedback message for Connections
    $connection_msg = '';
    // Handle connection deletion
    if (isset($_GET['delete_connection'])) {
      $delete_id = intval($_GET['delete_connection']);
      $conn->query("DELETE FROM connections WHERE Connection_ID = $delete_id");
      $connection_msg = '<div style="color:green;margin-bottom:1rem;">Connection deleted.</div>';
    }
    // Handle connection update
    if (isset($_POST['update_connection_id'])) {
      $id = intval($_POST['update_connection_id']);
      $customer_id = intval($_POST['edit_customer_id']);
      $type = $conn->real_escape_string(trim($_POST['edit_connection_type']));
      $sql = "UPDATE connections SET Customer_ID=$customer_id, Connection_Type='$type' WHERE Connection_ID=$id";
      if ($conn->query($sql)) {
        $connection_msg = '<div style="color:green;margin-bottom:1rem;">Connection updated.</div>';
      } else {
        $connection_msg = '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
      }
    }
    // Handle connection insert (with duplicate check and inline customer add)
    if (
      $_SERVER['REQUEST_METHOD'] === 'POST' &&
      isset($_POST['customer_id'], $_POST['connection_type']) &&
      !isset($_POST['update_connection_id'])
    ) {
      $type = $conn->real_escape_string(trim($_POST['connection_type']));
      $customer_id = 0;
      if (isset($_POST['customer_id']) && $_POST['customer_id'] === 'new' && !empty($_POST['new_customer_name'])) {
        $new_name = $conn->real_escape_string(trim($_POST['new_customer_name']));
        $conn->query("INSERT INTO customers (Name) VALUES ('$new_name')");
        $customer_id = $conn->insert_id;
        // Log audit action for customer creation
        if (function_exists('log_audit_action')) {
            $new_values = ['Name' => $new_name];
            log_audit_action($conn, 'CREATE', 'customers', $customer_id, null, $new_values);
        }
      } elseif (isset($_POST['customer_id']) && is_numeric($_POST['customer_id'])) {
        $customer_id = intval($_POST['customer_id']);
      }
      if ($customer_id && $type) {
        // Prevent duplicate connection for same customer and type
        $dupCon = $conn->query("SELECT Connection_ID FROM connections WHERE Customer_ID = $customer_id AND Connection_Type = '$type'");
        if ($dupCon && $dupCon->num_rows > 0) {
          $connection_msg = '<div style="color:red;margin-bottom:1rem;">Error: This customer already has a connection of this type.</div>';
        } else {
          $sql = "INSERT INTO connections (Customer_ID, Connection_Type) VALUES ($customer_id, '$type')";
          if ($conn->query($sql)) {
            // Log audit action
            if (function_exists('log_audit_action')) {
                $new_values = ['Customer_ID' => $customer_id, 'Connection_Type' => $type];
                log_audit_action($conn, 'CREATE', 'connections', $conn->insert_id, null, $new_values);
            }
            $connection_msg = '<div style="color:green;margin-bottom:1rem;">Connection added successfully.</div>';
          } else {
            $connection_msg = '<div style="color:red;margin-bottom:1rem;">Error: ' . $conn->error . '</div>';
          }
        }
      } else {
        $connection_msg = '<div style="color:red;margin-bottom:1rem;">All fields are required.</div>';
      }
    }
    $edit_connection_id = isset($_GET['edit_connection']) ? intval($_GET['edit_connection']) : 0;
    // Show feedback message only when set
    if ($connection_msg) echo $connection_msg;
    if (!$edit_connection_id) {
    ?>
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
        <div style="flex:1; min-width:200px;">
          <label for="customer_id">Customer</label><br>
          <select id="customer_id" name="customer_id" required>
            <option value="">--Select--</option>
            <?php
            $custRes = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
            while($cust = $custRes->fetch_assoc()) {
              echo "<option value='{$cust['Customer_ID']}'>{$cust['Name']} (ID: {$cust['Customer_ID']})</option>";
            }
            ?>
          </select>
        </div>
        <div style="flex:1; min-width:200px;">
          <label for="connection_type">Type of Connection</label><br>
          <select id="connection_type" name="connection_type" required>
            <option value="">--Select--</option>
            <option value="Main pipe">Main pipe</option>
            <option value="Plumbing connection">Plumbing connection</option>
          </select>
        </div>
        <div style="min-width:100px;">
          <button type="submit">Add Connection</button>
        </div>
      </div>
    </form>
    <?php }
    ?>
    <table>
      <tr><th>ID</th><th>Customer</th><th>Type</th><th>Action</th></tr>
      <?php
      $res = $conn->query("SELECT c.*, cu.Name as Customer_Name FROM connections c JOIN customers cu ON c.Customer_ID=cu.Customer_ID ORDER BY c.Connection_ID DESC");
      $edit_connection_id = isset($_GET['edit_connection']) ? intval($_GET['edit_connection']) : 0;
      while($row = $res->fetch_assoc()){
        if ($edit_connection_id === intval($row['Connection_ID'])) {
          // Edit form row
          echo "<tr>
            <form method='post'>
              <td>{$row['Connection_ID']}<input type='hidden' name='update_connection_id' value='{$row['Connection_ID']}'></td>
              <td><select name='edit_customer_id' required style='width:100%'>";
                $custRes = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
                while($cust = $custRes->fetch_assoc()) {
                  $selected = ($cust['Customer_ID'] == $row['Customer_ID']) ? 'selected' : '';
                  echo "<option value='{$cust['Customer_ID']}' $selected>{$cust['Name']} (ID: {$cust['Customer_ID']})</option>";
                }
          echo "</select></td>
              <td><select name='edit_connection_type' required style='width:100%'>
                <option value='Main pipe'".($row['Connection_Type']==='Main pipe'?' selected':'').">Main pipe</option>
                <option value='Plumbing connection'".($row['Connection_Type']==='Plumbing connection'?' selected':'').">Plumbing connection</option>
                <option value='New water connection'".($row['Connection_Type']==='New water connection'?' selected':'').">New water connection</option>
                <option value='Domestic'".($row['Connection_Type']==='Domestic'?' selected':'').">Domestic</option>
                <option value='Commercial'".($row['Connection_Type']==='Commercial'?' selected':'').">Commercial</option>
                <option value='Institutional'".($row['Connection_Type']==='Institutional'?' selected':'').">Institutional</option>
              </select></td>
              <td>
                <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                <a href='?tab=connections' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
              </td>
            </form>
          </tr>";
        } else {
          echo "<tr>
                  <td>{$row['Connection_ID']}</td>
                  <td>{$row['Customer_Name']} (ID: {$row['Customer_ID']})</td>
                  <td>{$row['Connection_Type']}</td>
                  <td>
                    <a href='?delete_connection={$row['Connection_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this connection?');\"><span class='material-icons'>delete</span></a>
                    <a href='?edit_connection={$row['Connection_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                  </td>
                </tr>";
        }
      }
      ?>
    </table>


  </div>

  <div id="bills" class="tab">
    <h2>Bills</h2>
    <?php
    // Handle bill deletion
    if (isset($_GET['delete_bill'])) {
      $delete_id = intval($_GET['delete_bill']);
      $conn->query("DELETE FROM bills WHERE Bill_ID = $delete_id");
      echo '<div style="color:green;margin-bottom:1rem;">Bill deleted.</div>';
    }
    // Handle bill update
    if (isset($_POST['update_bill_id'])) {
      $id = intval($_POST['update_bill_id']);
      $meter_id = intval($_POST['edit_meter_id']);
      $billing_period = '';
      if (isset($_POST['edit_billing_period_month'], $_POST['edit_billing_period_year']) && $_POST['edit_billing_period_month'] && $_POST['edit_billing_period_year']) {
        $billing_period = $_POST['edit_billing_period_year'] . '-' . $_POST['edit_billing_period_month'];
      }
      $units = floatval($_POST['edit_units_consumed']);
      // Recompute amount by business rules
      if ($units >= 0 && $units <= 5) {
        $amount = 3802;
      } elseif ($units >= 6 && $units <= 9) {
        $amount = $units * 790;
      } else {
        $amount = $units * 1431;
      }
      $due_date = $conn->real_escape_string(trim($_POST['edit_due_date']));
      $status = $conn->real_escape_string(trim($_POST['edit_status']));
      $sql = "UPDATE bills SET Meter_ID=$meter_id, Billing_Period='$billing_period', Units_Consumed=$units, Amount=$amount, Due_Date='$due_date', Status='$status' WHERE Bill_ID=$id";
      if ($conn->query($sql)) {
        echo '<div style="color:green;margin-bottom:1rem;">Bill updated.</div>';
      } else {
        echo '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
      }
    }
    // Handle bill insert
    if (
      $_SERVER['REQUEST_METHOD'] === 'POST' &&
      isset($_POST['meter_id'], $_POST['billing_period_month'], $_POST['billing_period_year'], $_POST['units_consumed'], $_POST['amount'], $_POST['due_date'], $_POST['status']) &&
      !isset($_POST['update_bill_id'])
    ) {
      $meter_id = intval($_POST['meter_id']);
      $billing_period = '';
      if (isset($_POST['billing_period_month'], $_POST['billing_period_year']) && $_POST['billing_period_month'] && $_POST['billing_period_year']) {
        $billing_period = $_POST['billing_period_year'] . '-' . $_POST['billing_period_month'];
      }
      $units = floatval($_POST['units_consumed']);
      // Compute amount by business rules
      if ($units >= 0 && $units <= 5) {
        $amount = 3802;
      } elseif ($units >= 6 && $units <= 9) {
        $amount = $units * 790;
      } else {
        $amount = $units * 1431;
      }
      $due_date = $conn->real_escape_string(trim($_POST['due_date']));
      $status = $conn->real_escape_string(trim($_POST['status']));
      if ($meter_id && $billing_period && $due_date && $status) {
        $sql = "INSERT INTO bills (Meter_ID, Billing_Period, Units_Consumed, Amount, Due_Date, Status) VALUES ($meter_id, '$billing_period', $units, $amount, '$due_date', '$status')";
        if ($conn->query($sql)) {
          // Log audit action
          if (function_exists('log_audit_action')) {
              $new_values = ['Meter_ID' => $meter_id, 'Billing_Period' => $billing_period, 'Units_Consumed' => $units, 'Amount' => $amount, 'Due_Date' => $due_date, 'Status' => $status];
              log_audit_action($conn, 'CREATE', 'bills', $conn->insert_id, null, $new_values);
          }
          echo '<div style=\"color:green;margin-bottom:1rem;\">Bill added successfully.</div>';
        } else {
          echo '<div style=\"color:red;margin-bottom:1rem;\">Error: ' . $conn->error . '</div>';
        }
      } else {
        echo '<div style=\"color:red;margin-bottom:1rem;\">All fields are required.</div>';
      }
    }
    $edit_bill_id = isset($_GET['edit_bill']) ? intval($_GET['edit_bill']) : 0;
    if (!$edit_bill_id) {
    ?>
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
        <!-- Customer Name field removed: only Meter, Billing Period, Units, Amount, Due Date, Status remain -->
        <div style="flex:1; min-width:180px;">
          <label for="meter_id">Meter</label><br>
          <select id="meter_id" name="meter_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <?php
            $meterRes = $conn->query("SELECT m.Meter_ID, m.Meter_Number, m.Customer_ID FROM meters m ORDER BY m.Meter_ID");
            $allMeters = [];
            while($meter = $meterRes->fetch_assoc()) {
              $allMeters[$meter['Customer_ID']][] = [
                'Meter_ID' => $meter['Meter_ID'],
                'Meter_Number' => $meter['Meter_Number']
              ];
            }
            foreach ($allMeters as $custId => $meters) {
              foreach ($meters as $meter) {
                echo "<option value='{$meter['Meter_ID']}' data-customer='{$custId}'>Meter #{$meter['Meter_Number']} (ID: {$meter['Meter_ID']})</option>";
              }
            }
            ?>
            <option value="new">Add new meter number...</option>
          </select>
          <input type="text" id="new_meter_number" name="new_meter_number" placeholder="Enter new meter number" style="display:none;margin-top:0.5rem;" />
        </div>
      <!-- Customer autocomplete and related JS removed: only meter, units, etc. remain -->
        <div style="flex:1; min-width:120px;">
          <label for="billing_period_month">Billing Period</label><br>
          <div style="display:flex; gap:0.5rem;">
            <select id="billing_period_month" name="billing_period_month" required>
              <option value="">Month</option>
              <option value="01">January</option>
              <option value="02">February</option>
              <option value="03">March</option>
              <option value="04">April</option>
              <option value="05">May</option>
              <option value="06">June</option>
              <option value="07">July</option>
              <option value="08">August</option>
              <option value="09">September</option>
              <option value="10">October</option>
              <option value="11">November</option>
              <option value="12">December</option>
            </select>
            <select id="billing_period_year" name="billing_period_year" required>
              <option value="">Year</option>
              <?php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= $currentYear - 10; $y--) {
                  echo "<option value='$y'>$y</option>";
                }
              ?>
            </select>
          </div>
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="units_consumed">Units Consumed (m³)</label><br>
          <input type="number" step="0.01" id="units_consumed" name="units_consumed" required>
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="amount">Amount</label><br>
          <input type="number" step="0.01" id="amount" name="amount" required readonly>
        </div>
<script>
// Auto-calculate amount based on units consumed
document.getElementById('units_consumed').addEventListener('input', function() {
  var units = parseFloat(this.value);
  var amount = 0;
  if (!isNaN(units)) {
    if (units >= 0 && units <= 5) {
      amount = 3802;
    } else if (units >= 6 && units <= 9) {
      amount = units * 790;
    } else if (units >= 10) {
      amount = units * 1431;
    }
  }
  document.getElementById('amount').value = amount;
});
</script>
        <div style="flex:1; min-width:120px;">
          <label for="due_date">Due Date</label><br>
          <input type="date" id="due_date" name="due_date" required>
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="status">Status</label><br>
          <select id="status" name="status" required>
            <option value="">--Select--</option>
            <option value="Unpaid">Unpaid</option>
            <option value="Paid">Paid</option>
            <option value="Overdue">Overdue</option>
          </select>
        </div>
        <div style="min-width:100px;">
          <button type="submit">Add Bill</button>
        </div>
      </div>
    </form>
    <?php }
    ?>
    <table>
      <tr><th>ID</th><th>Meter</th><th>Billing Period</th><th>Units</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Action</th></tr>
      <?php
  $res = $conn->query("SELECT b.*, m.Meter_Number FROM bills b LEFT JOIN meters m ON b.Meter_ID=m.Meter_ID ORDER BY b.Bill_ID DESC");
      $edit_bill_id = isset($_GET['edit_bill']) ? intval($_GET['edit_bill']) : 0;
      while($row = $res->fetch_assoc()){
        if ($edit_bill_id === intval($row['Bill_ID'])) {
          // Edit form row
          echo "<tr>";
          echo "<form method='post' style='display:contents;'>";
          echo "<td>{$row['Bill_ID']}<input type='hidden' name='update_bill_id' value='{$row['Bill_ID']}'></td>";
          echo "<td><select name='edit_meter_id' required style='width:100%'>";
          $meterRes = $conn->query("SELECT m.Meter_ID, m.Meter_Number FROM meters m ORDER BY m.Meter_ID");
          while($meter = $meterRes->fetch_assoc()) {
            $selected = ($meter['Meter_ID'] == $row['Meter_ID']) ? 'selected' : '';
            echo "<option value='{$meter['Meter_ID']}' $selected>Meter #{$meter['Meter_Number']} (ID: {$meter['Meter_ID']})</option>";
          }
          echo "</select></td>";
          $bp = explode('-', $row['Billing_Period']);
          $bp_year = $bp[0] ?? '';
          $bp_month = $bp[1] ?? '';
          echo '<td><div style="display:flex; gap:0.5rem;">';
          echo '<select name="edit_billing_period_month" required style="width:60%;">';
          echo '<option value="">Month</option>';
          $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
          ];
          foreach ($months as $num => $name) {
            $sel = ($bp_month == $num) ? 'selected' : '';
            echo "<option value='$num' $sel>$name</option>";
          }
          echo '</select>';
          echo '<select name="edit_billing_period_year" required style="width:40%;">';
          echo '<option value="">Year</option>';
          $currentYear = date('Y');
          for ($y = $currentYear; $y >= $currentYear - 10; $y--) {
            $sel = ($bp_year == $y) ? 'selected' : '';
            echo "<option value='$y' $sel>$y</option>";
          }
          echo '</select>';
          echo '</div></td>';
          echo "<td><input type='number' step='0.01' name='edit_units_consumed' value='{$row['Units_Consumed']}' required style='width:100%;'></td>";
          echo "<td><input type='number' step='0.01' name='edit_amount' value='{$row['Amount']}' required style='width:100%;'></td>";
          echo "<td><input type='date' name='edit_due_date' value='{$row['Due_Date']}' required style='width:100%;'></td>";
          echo "<td><select name='edit_status' required style='width:100%'>";
          echo "<option value='Unpaid'" . ($row['Status']==='Unpaid'?' selected':'') . ">Unpaid</option>";
          echo "<option value='Paid'" . ($row['Status']==='Paid'?' selected':'') . ">Paid</option>";
          echo "<option value='Overdue'" . ($row['Status']==='Overdue'?' selected':'') . ">Overdue</option>";
          echo "</select></td>";
          echo "<td>";
          echo "<button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>";
          echo "<a href='?tab=bills' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>";
          echo "</td>";
          echo "</form>";
          echo "</tr>";
        } else {
          echo "<tr>";
          echo "<td>{$row['Bill_ID']}</td>";
          echo "<td>Meter #{$row['Meter_Number']} (ID: {$row['Meter_ID']})</td>";
          echo "<td>{$row['Billing_Period']}</td>";
          echo "<td>{$row['Units_Consumed']}</td>";
          echo "<td>{$row['Amount']}</td>";
          echo "<td>{$row['Due_Date']}</td>";
          echo "<td>{$row['Status']}</td>";
          echo "<td>";
          echo "<a href='?delete_bill={$row['Bill_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this bill?');\"><span class='material-icons'>delete</span></a>";
          echo "<a href='?edit_bill={$row['Bill_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>";
          echo "</td>";
          echo "</tr>";
        }
      }
      ?>
    </table>
  </div>

  <div id="dashboard" class="tab active">
    <h2>Dashboard</h2>
    <?php
      $totalCustomersRow = $conn->query("SELECT COUNT(*) AS total FROM customers")->fetch_assoc();
      $totalBillsRow = $conn->query("SELECT COUNT(*) AS total FROM bills")->fetch_assoc();
      $totalRevenueRow = $conn->query("SELECT COALESCE(SUM(Amount_Paid), 0) AS total FROM payments")->fetch_assoc();
      $pendingComplaintsRow = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE TRIM(Status) IN ('Open','In Progress')")->fetch_assoc();

      $totalCustomers = isset($totalCustomersRow['total']) ? (int)$totalCustomersRow['total'] : 0;
      $totalBills = isset($totalBillsRow['total']) ? (int)$totalBillsRow['total'] : 0;
      $totalRevenue = isset($totalRevenueRow['total']) ? (float)$totalRevenueRow['total'] : 0.0;
      $pendingComplaints = isset($pendingComplaintsRow['total']) ? (int)$pendingComplaintsRow['total'] : 0;
    ?>
    <div class="dashboard-cards">
      <div class="dashboard-card">Total Customers<br><span><?= $totalCustomers ?></span></div>
      <div class="dashboard-card">Total Bills<br><span><?= $totalBills ?></span></div>
      <div class="dashboard-card">Revenue<br><span>MK<?= number_format($totalRevenue, 2) ?></span></div>
      <div class="dashboard-card">Pending Complaints<br><span><?= $pendingComplaints ?></span></div>
    </div>
  </div>



  <div id="meterreadings" class="tab">
    <h2>Meter Readings</h2>
    <?php
    // Handle meter reading deletion
    if (isset($_GET['delete_meterreading'])) {
      $delete_id = intval($_GET['delete_meterreading']);
      // Get meter reading data before deletion for audit log
      $reading_data = $conn->query("SELECT * FROM meterreadings WHERE Reading_ID = $delete_id")->fetch_assoc();
      $conn->query("DELETE FROM meterreadings WHERE Reading_ID = $delete_id");
      // Log audit action
      if (function_exists('log_audit_action') && $reading_data) {
          log_audit_action($conn, 'DELETE', 'meterreadings', $delete_id, $reading_data, null);
      }
      echo '<div style="color:green;margin-bottom:1rem;">Meter reading deleted.</div>';
    }
    // Handle meter reading update
    if (isset($_POST['update_meterreading_id'])) {
      $id = intval($_POST['update_meterreading_id']);
      $meter_id = intval($_POST['edit_meter_id']);
      $reading_date = $conn->real_escape_string(trim($_POST['edit_reading_date']));
      $reading_value = floatval($_POST['edit_reading_value']);
      $recorded_by = intval($_POST['edit_recorded_by']);
      
      // Get old values for audit log
      $old_data = $conn->query("SELECT * FROM meterreadings WHERE Reading_ID = $id")->fetch_assoc();
      $new_values = ['Meter_ID' => $meter_id, 'Reading_Date' => $reading_date, 'Reading_Value' => $reading_value, 'Recorded_By' => $recorded_by];
      
      $sql = "UPDATE meterreadings SET Meter_ID=$meter_id, Reading_Date='$reading_date', Reading_Value=$reading_value, Recorded_By=$recorded_by WHERE Reading_ID=$id";
      if ($conn->query($sql)) {
        // Log audit action
        if (function_exists('log_audit_action') && $old_data) {
            log_audit_action($conn, 'UPDATE', 'meterreadings', $id, $old_data, $new_values);
        }
        echo '<div style="color:green;margin-bottom:1rem;">Meter reading updated.</div>';
      } else {
        echo '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
      }
    }
    // Handle meter reading insert
    if (
      $_SERVER['REQUEST_METHOD'] === 'POST' &&
      isset($_POST['meter_id'], $_POST['reading_date'], $_POST['reading_value'], $_POST['recorded_by']) &&
      !isset($_POST['update_meterreading_id'])
    ) {
      $meter_id = intval($_POST['meter_id']);
      $reading_date = $conn->real_escape_string(trim($_POST['reading_date']));
      $reading_value = floatval($_POST['reading_value']);
      $recorded_by = 0;
      if (isset($_POST['recorded_by']) && $_POST['recorded_by'] === 'new' && !empty($_POST['new_staff_name'])) {
        $new_staff_name = $conn->real_escape_string(trim($_POST['new_staff_name']));
        $conn->query("INSERT INTO staff (Name) VALUES ('$new_staff_name')");
        $recorded_by = $conn->insert_id;
        // Log audit action for staff creation
        if (function_exists('log_audit_action')) {
            $new_values = ['Name' => $new_staff_name];
            log_audit_action($conn, 'CREATE', 'staff', $recorded_by, null, $new_values);
        }
      } elseif (isset($_POST['recorded_by']) && is_numeric($_POST['recorded_by'])) {
        $recorded_by = intval($_POST['recorded_by']);
      }
      if ($meter_id && $reading_date && $reading_value && $recorded_by) {
        $sql = "INSERT INTO meterreadings (Meter_ID, Reading_Date, Reading_Value, Recorded_By) VALUES ($meter_id, '$reading_date', $reading_value, $recorded_by)";
        if ($conn->query($sql)) {
          // Log audit action
          if (function_exists('log_audit_action')) {
              $new_values = ['Meter_ID' => $meter_id, 'Reading_Date' => $reading_date, 'Reading_Value' => $reading_value, 'Recorded_By' => $recorded_by];
              log_audit_action($conn, 'CREATE', 'meterreadings', $conn->insert_id, null, $new_values);
          }
          echo '<div style="color:green;margin-bottom:1rem;">Meter reading added successfully.</div>';
        } else {
          echo '<div style="color:red;margin-bottom:1rem;">Error: ' . $conn->error . '</div>';
        }
      } else {
        echo '<div style="color:red;margin-bottom:1rem;">All fields are required.</div>';
      }
    }
    $edit_meterreading_id = isset($_GET['edit_meterreading']) ? intval($_GET['edit_meterreading']) : 0;
    if (!$edit_meterreading_id) {
    ?>
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
        <div style="flex:1; min-width:200px;">
          <label for="customer_select">Customer</label><br>
          <select id="customer_select" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <?php
            $custRes = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
            while($cust = $custRes->fetch_assoc()) {
              echo "<option value='{$cust['Customer_ID']}'>{$cust['Name']}</option>";
            }
            ?>
          </select>
          <input type="hidden" id="meter_id_hidden" name="meter_id" required>
        </div>
        <div style="flex:1; min-width:200px;">
          <label for="meter_id">Meter Number</label><br>
          <select id="meter_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <?php
            $meterRes = $conn->query("SELECT m.Meter_ID, m.Meter_Number, m.Customer_ID FROM meters m ORDER BY m.Meter_ID");
            $metersByCustomer = [];
            while($meter = $meterRes->fetch_assoc()) {
              $metersByCustomer[$meter['Customer_ID']][] = [
                'Meter_ID' => $meter['Meter_ID'],
                'Meter_Number' => $meter['Meter_Number']
              ];
              echo "<option value='{$meter['Meter_ID']}' data-customer='{$meter['Customer_ID']}'>Meter #{$meter['Meter_Number']} (ID: {$meter['Meter_ID']})</option>";
            }
            ?>
          </select>
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="reading_date">Reading Date</label><br>
          <input type="date" id="reading_date" name="reading_date" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="reading_value">Reading Value (m³)</label><br>
          <input type="number" step="0.01" id="reading_value" name="reading_value" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="recorded_by">Recorded By (Staff)</label><br>
          <select id="recorded_by" name="recorded_by" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <?php
            $staffRes = $conn->query("SELECT Staff_ID, Name FROM staff ORDER BY Name");
            while($staff = $staffRes->fetch_assoc()) {
              echo "<option value='{$staff['Staff_ID']}'>".htmlspecialchars($staff['Name'])." (ID: {$staff['Staff_ID']})</option>";
            }
            ?>
            <option value="new">Add new staff...</option>
          </select>
          <input type="text" id="new_staff_name" name="new_staff_name" placeholder="Enter new staff name" style="display:none;margin-top:0.5rem;" />
        </div>
        <div style="min-width:100px;">
          <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">Add Reading</button>
        </div>
      </div>
      <script>
      // Auto-select meter when customer is chosen
      var customerSelect = document.getElementById('customer_select');
      var meterSelect = document.getElementById('meter_id');
      var meterIdHidden = document.getElementById('meter_id_hidden');
      // Map of customer_id to meter_id (first meter only)
      var metersByCustomer = <?php
        $meterRes2 = $conn->query("SELECT Meter_ID, Customer_ID FROM meters ORDER BY Meter_ID");
        $map = [];
        while($m = $meterRes2->fetch_assoc()) {
          if (!isset($map[$m['Customer_ID']])) $map[$m['Customer_ID']] = $m['Meter_ID'];
        }
        echo json_encode($map);
      ?>;
      customerSelect.addEventListener('change', function() {
        var custId = this.value;
        if (metersByCustomer[custId]) {
          meterSelect.value = metersByCustomer[custId];
          meterIdHidden.value = metersByCustomer[custId];
        } else {
          meterSelect.value = '';
          meterIdHidden.value = '';
        }
      });
      meterSelect.addEventListener('change', function() {
        meterIdHidden.value = this.value;
      });
      document.getElementById('recorded_by').addEventListener('change', function() {
        var show = this.value === 'new';
        document.getElementById('new_staff_name').style.display = show ? 'block' : 'none';
        document.getElementById('new_staff_name').required = show;
      });
      </script>
    </form>
    <?php }
    ?>
    <table>
      <tr><th>ID</th><th>Customer</th><th>Reading Date</th><th>Reading Value (m³)</th><th>Recorded By</th><th>Action</th></tr>
      <?php
      $res = $conn->query("SELECT mr.*, s.Name as Staff_Name, c.Name as Customer_Name FROM meterreadings mr JOIN staff s ON mr.Recorded_By=s.Staff_ID JOIN meters m ON mr.Meter_ID=m.Meter_ID JOIN customers c ON m.Customer_ID=c.Customer_ID ORDER BY mr.Reading_ID DESC");
      $edit_meterreading_id = isset($_GET['edit_meterreading']) ? intval($_GET['edit_meterreading']) : 0;
      while($row = $res->fetch_assoc()){
        if ($edit_meterreading_id === intval($row['Reading_ID'])) {
          // Edit form row
          echo "<tr>
            <form method='post'>
              <td>{$row['Reading_ID']}<input type='hidden' name='update_meterreading_id' value='{$row['Reading_ID']}'></td>
              <td><select name='edit_meter_id' required style='width:100%'>";
                $meterRes = $conn->query("SELECT m.Meter_ID, c.Name as Customer_Name FROM meters m JOIN customers c ON m.Customer_ID = c.Customer_ID ORDER BY c.Name");
                while($meter = $meterRes->fetch_assoc()) {
                  $selected = ($meter['Meter_ID'] == $row['Meter_ID']) ? 'selected' : '';
                  echo "<option value='{$meter['Meter_ID']}' $selected>{$meter['Customer_Name']} (Meter ID: {$meter['Meter_ID']})</option>";
                }
          echo "</select></td>
              <td><input type='date' name='edit_reading_date' value='{$row['Reading_Date']}' required style='width:100%'></td>
              <td><input type='number' step='0.01' name='edit_reading_value' value='{$row['Reading_Value']}' required style='width:100%'></td>
              <td><select name='edit_recorded_by' required style='width:100%'>";
                $staffRes = $conn->query("SELECT Staff_ID, Name FROM staff ORDER BY Name");
                while($staff = $staffRes->fetch_assoc()) {
                  $selected = ($staff['Staff_ID'] == $row['Recorded_By']) ? 'selected' : '';
                  echo "<option value='{$staff['Staff_ID']}' $selected>".htmlspecialchars($staff['Name'])." (ID: {$staff['Staff_ID']})</option>";
                }
          echo "</select></td>
              <td>
                <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                <a href='?tab=meterreadings' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
              </td>
            </form>
          </tr>";
        } else {
          echo "<tr>
                  <td>{$row['Reading_ID']}</td>
                  <td>{$row['Customer_Name']} (Meter ID: {$row['Meter_ID']})</td>
                  <td>{$row['Reading_Date']}</td>
                  <td>{$row['Reading_Value']}</td>
                  <td>{$row['Staff_Name']} (ID: {$row['Recorded_By']})</td>
                  <td>
                    <a href='?delete_meterreading={$row['Reading_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this reading?');\"><span class='material-icons'>delete</span></a>
                    <a href='?edit_meterreading={$row['Reading_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                  </td>
                </tr>";
        }
      }
      ?>
    </table>
  </div>


  <div id="payments" class="tab">
      <h2>Payments</h2>
      <?php
      // Handle payment actions and show messages only after action
      $payment_message = '';
      if (isset($_GET['delete_payment'])) {
        $delete_id = intval($_GET['delete_payment']);
        // Get payment data before deletion for audit log
        $payment_data = $conn->query("SELECT * FROM payments WHERE Payment_ID = $delete_id")->fetch_assoc();
        $conn->query("DELETE FROM payments WHERE Payment_ID = $delete_id");
        // Log audit action
        if (function_exists('log_audit_action') && $payment_data) {
            log_audit_action($conn, 'DELETE', 'payments', $delete_id, $payment_data, null);
        }
        $payment_message = '<div style="color:green;margin-bottom:1rem;">Payment deleted.</div>';
      }
      if (isset($_POST['update_payment_id'])) {
        $id = intval($_POST['update_payment_id']);
        $bill_id = intval($_POST['edit_bill_id']);
        $payment_date = $conn->real_escape_string(trim($_POST['edit_payment_date']));
        $amount_paid = floatval($_POST['edit_amount_paid']);
        $method = $conn->real_escape_string(trim($_POST['edit_method']));
        
        // Get old values for audit log
        $old_data = $conn->query("SELECT * FROM payments WHERE Payment_ID = $id")->fetch_assoc();
        $new_values = ['Bill_ID' => $bill_id, 'Payment_Date' => $payment_date, 'Amount_Paid' => $amount_paid, 'Method' => $method];
        
        $sql = "UPDATE payments SET Bill_ID=$bill_id, Payment_Date='$payment_date', Amount_Paid=$amount_paid, Method='$method' WHERE Payment_ID=$id";
        if ($conn->query($sql)) {
          // Log audit action
          if (function_exists('log_audit_action') && $old_data) {
              log_audit_action($conn, 'UPDATE', 'payments', $id, $old_data, $new_values);
          }
          $payment_message = '<div style="color:green;margin-bottom:1rem;">Payment updated.</div>';
        } else {
          $payment_message = '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
        }
      }
      if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['bill_id'], $_POST['payment_date'], $_POST['amount_paid'], $_POST['method']) &&
        !isset($_POST['update_payment_id'])
      ) {
        $bill_id = intval($_POST['bill_id']);
        $payment_date = $conn->real_escape_string(trim($_POST['payment_date']));
        $amount_paid = floatval($_POST['amount_paid']);
        $method = $conn->real_escape_string(trim($_POST['method']));
        if ($bill_id && $payment_date && $amount_paid && $method) {
          $sql = "INSERT INTO payments (Bill_ID, Payment_Date, Amount_Paid, Method) VALUES ($bill_id, '$payment_date', $amount_paid, '$method')";
          if ($conn->query($sql)) {
            // Log audit action
            if (function_exists('log_audit_action')) {
                $new_values = ['Bill_ID' => $bill_id, 'Payment_Date' => $payment_date, 'Amount_Paid' => $amount_paid, 'Method' => $method];
                log_audit_action($conn, 'CREATE', 'payments', $conn->insert_id, null, $new_values);
            }
            $payment_message = '<div style="color:green;margin-bottom:1rem;">Payment added successfully.</div>';
          } else {
            $payment_message = '<div style="color:red;margin-bottom:1rem;">Error: ' . $conn->error . '</div>';
          }
        } else {
          $payment_message = '<div style="color:red;margin-bottom:1rem;">All fields are required.</div>';
        }
      }
      $edit_payment_id = isset($_GET['edit_payment']) ? intval($_GET['edit_payment']) : 0;
      if (!$edit_payment_id) {
      ?>
  <?php if ($payment_message) echo $payment_message; ?>
  <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
        <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
          <div style="flex:1; min-width:120px;">
            <label for="bill_id">Bill</label><br>
            <select id="bill_id" name="bill_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
              <option value="">--Select--</option>
              <?php
              $billRes = function_exists('get_bill_ids') ? get_bill_ids($conn) : $conn->query("SELECT Bill_ID FROM bills ORDER BY Bill_ID DESC");
              while($bill = $billRes->fetch_assoc()) {
                echo "<option value='{$bill['Bill_ID']}'>Bill ID: {$bill['Bill_ID']}</option>";
              }
              ?>
            </select>
          </div>
          <div style="flex:1; min-width:120px;">
            <label for="payment_date">Payment Date</label><br>
            <input type="date" id="payment_date" name="payment_date" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
          </div>
          <div style="flex:1; min-width:120px;">
            <label for="amount_paid">Amount Paid</label><br>
            <input type="number" step="0.01" id="amount_paid" name="amount_paid" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
          </div>
          <div style="flex:1; min-width:120px;">
            <label for="method">Method</label><br>
            <select id="method" name="method" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
              <option value="">--Select--</option>
              <option value="Cash">Cash</option>
              <option value="Bank">Bank</option>
              <option value="Mobile Money">Mobile Money</option>
              <option value="Online">Online</option>
            </select>
          </div>
          <div style="min-width:100px;">
            <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">Add Payment</button>
          </div>
        </div>
      </form>
      <?php }
      ?>
      <table>
        <tr><th>ID</th><th>Bill ID</th><th>Payment Date</th><th>Amount Paid</th><th>Method</th><th>Action</th></tr>
        <?php
        $res = function_exists('get_payments') ? get_payments($conn) : $conn->query("SELECT * FROM payments ORDER BY Payment_ID DESC");
        $edit_payment_id = isset($_GET['edit_payment']) ? intval($_GET['edit_payment']) : 0;
        while($row = $res->fetch_assoc()){
          if ($edit_payment_id === intval($row['Payment_ID'])) {
            // Edit form row
            echo "<tr>
              <form method='post'>
                <td>{$row['Payment_ID']}<input type='hidden' name='update_payment_id' value='{$row['Payment_ID']}'></td>
                <td><select name='edit_bill_id' required style='width:100%'>";
                  $billRes = function_exists('get_bill_ids') ? get_bill_ids($conn) : $conn->query("SELECT Bill_ID FROM bills ORDER BY Bill_ID DESC");
                  while($bill = $billRes->fetch_assoc()) {
                    $selected = ($bill['Bill_ID'] == $row['Bill_ID']) ? 'selected' : '';
                    echo "<option value='{$bill['Bill_ID']}' $selected>Bill ID: {$bill['Bill_ID']}</option>";
                  }
            echo "</select></td>
                <td><input type='date' name='edit_payment_date' value='{$row['Payment_Date']}' required style='width:100%'></td>
                <td><input type='number' step='0.01' name='edit_amount_paid' value='{$row['Amount_Paid']}' required style='width:100%'></td>
                <td><select name='edit_method' required style='width:100%'>
                  <option value='Cash'".($row['Method']==='Cash'?' selected':'').">Cash</option>
                  <option value='Bank'".($row['Method']==='Bank'?' selected':'').">Bank</option>
                  <option value='Mobile Money'".($row['Method']==='Mobile Money'?' selected':'').">Mobile Money</option>
                  <option value='Online'".($row['Method']==='Online'?' selected':'').">Online</option>
                </select></td>
                <td>
                  <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                  <a href='?tab=payments' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
                </td>
              </form>
            </tr>";
          } else {
            echo "<tr>
                    <td>{$row['Payment_ID']}</td>
                    <td>{$row['Bill_ID']}</td>
                    <td>{$row['Payment_Date']}</td>
                    <td>{$row['Amount_Paid']}</td>
                    <td>{$row['Method']}</td>
                    <td>
                      <a href='?delete_payment={$row['Payment_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this payment?');\"><span class='material-icons'>delete</span></a>
                      <a href='?edit_payment={$row['Payment_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                    </td>
                  </tr>";
          }
        }
        ?>
      </table>
  </div>

  <div id="balances" class="tab">
      <h2>Customer Balances</h2>
      <table>
        <tr><th>Customer</th><th>Total Billed</th><th>Total Paid</th><th>Balance</th></tr>
        <?php
        $balanceRes = $conn->query("
          SELECT cu.Name as Customer_Name,
                 IFNULL(SUM(b.Amount),0) as TotalBilled,
                 IFNULL(SUM(p.Amount_Paid),0) as TotalPaid,
                 IFNULL(SUM(b.Amount),0) - IFNULL(SUM(p.Amount_Paid),0) as Balance
          FROM customers cu
          LEFT JOIN meters m ON cu.Customer_ID = m.Customer_ID
          LEFT JOIN bills b ON m.Meter_ID = b.Meter_ID
          LEFT JOIN payments p ON b.Bill_ID = p.Bill_ID
          GROUP BY cu.Customer_ID
          ORDER BY cu.Name ASC
        ");
        while($row = $balanceRes->fetch_assoc()) {
          echo "<tr>";
          echo "<td>" . htmlspecialchars($row['Customer_Name']) . "</td>";
          echo "<td>" . number_format($row['TotalBilled'],2) . "</td>";
          echo "<td>" . number_format($row['TotalPaid'],2) . "</td>";
          echo "<td>" . number_format($row['Balance'],2) . "</td>";
          echo "</tr>";
        }
        ?>
      </table>
  <?php
  // (No payment logic here; balances tab only shows balances table)
      // Handle payment update
      if (isset($_POST['update_payment_id'])) {
        $id = intval($_POST['update_payment_id']);
        $bill_id = intval($_POST['edit_bill_id']);
        $payment_date = $conn->real_escape_string(trim($_POST['edit_payment_date']));
        $amount_paid = floatval($_POST['edit_amount_paid']);
        $method = $conn->real_escape_string(trim($_POST['edit_method']));
        $sql = "UPDATE payments SET Bill_ID=$bill_id, Payment_Date='$payment_date', Amount_Paid=$amount_paid, Method='$method' WHERE Payment_ID=$id";
        if ($conn->query($sql)) {
          echo '<div style="color:green;margin-bottom:1rem;">Payment updated.</div>';
        } else {
          echo '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
        }
      }
      // Handle payment insert
      if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['bill_id'], $_POST['payment_date'], $_POST['amount_paid'], $_POST['method']) &&
        !isset($_POST['update_payment_id'])
      ) {
        $bill_id = intval($_POST['bill_id']);
        $payment_date = $conn->real_escape_string(trim($_POST['payment_date']));
        $amount_paid = floatval($_POST['amount_paid']);
        $method = $conn->real_escape_string(trim($_POST['method']));
        if ($bill_id && $payment_date && $amount_paid && $method) {
          $sql = "INSERT INTO payments (Bill_ID, Payment_Date, Amount_Paid, Method) VALUES ($bill_id, '$payment_date', $amount_paid, '$method')";
          if ($conn->query($sql)) {
            // Log audit action
            if (function_exists('log_audit_action')) {
                $new_values = ['Bill_ID' => $bill_id, 'Payment_Date' => $payment_date, 'Amount_Paid' => $amount_paid, 'Method' => $method];
                log_audit_action($conn, 'CREATE', 'payments', $conn->insert_id, null, $new_values);
            }
            echo '<div style="color:green;margin-bottom:1rem;">Payment added successfully.</div>';
          } else {
            echo '<div style="color:red;margin-bottom:1rem;">Error: ' . $conn->error . '</div>';
          }
        } else {
          echo '<div style="color:red;margin-bottom:1rem;">All fields are required.</div>';
        }
      }
      $edit_payment_id = isset($_GET['edit_payment']) ? intval($_GET['edit_payment']) : 0;
      if (!$edit_payment_id) {
      ?>
      <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
        <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
          <div style="flex:1; min-width:120px;">
            <label for="bill_id">Bill</label><br>
            <select id="bill_id" name="bill_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
              <option value="">--Select--</option>
              <?php
              $billRes = function_exists('get_bill_ids') ? get_bill_ids($conn) : $conn->query("SELECT Bill_ID FROM bills ORDER BY Bill_ID DESC");
              while($bill = $billRes->fetch_assoc()) {
                echo "<option value='{$bill['Bill_ID']}'>Bill ID: {$bill['Bill_ID']}</option>";
              }
              ?>
            </select>
          </div>
          <div style="flex:1; min-width:120px;">
            <label for="payment_date">Payment Date</label><br>
            <input type="date" id="payment_date" name="payment_date" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
          </div>
          <div style="flex:1; min-width:120px;">
            <label for="amount_paid">Amount Paid</label><br>
            <input type="number" step="0.01" id="amount_paid" name="amount_paid" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
          </div>
          <div style="flex:1; min-width:120px;">
            <label for="method">Method</label><br>
            <select id="method" name="method" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
              <option value="">--Select--</option>
              <option value="Cash">Cash</option>
              <option value="Bank">Bank</option>
              <option value="Mobile Money">Mobile Money</option>
              <option value="Online">Online</option>
            </select>
          </div>
          <div style="min-width:100px;">
            <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">Add Payment</button>
          </div>
        </div>
      </form>
      <?php }
      ?>
      <table>
        <tr><th>ID</th><th>Bill ID</th><th>Payment Date</th><th>Amount Paid</th><th>Method</th><th>Action</th></tr>
        <?php
        $res = function_exists('get_payments') ? get_payments($conn) : $conn->query("SELECT * FROM payments ORDER BY Payment_ID DESC");
        $edit_payment_id = isset($_GET['edit_payment']) ? intval($_GET['edit_payment']) : 0;
        while($row = $res->fetch_assoc()){
          if ($edit_payment_id === intval($row['Payment_ID'])) {
            // Edit form row
            echo "<tr>
              <form method='post'>
                <td>{$row['Payment_ID']}<input type='hidden' name='update_payment_id' value='{$row['Payment_ID']}'></td>
                <td><select name='edit_bill_id' required style='width:100%'>";
                  $billRes = function_exists('get_bill_ids') ? get_bill_ids($conn) : $conn->query("SELECT Bill_ID FROM bills ORDER BY Bill_ID DESC");
                  while($bill = $billRes->fetch_assoc()) {
                    $selected = ($bill['Bill_ID'] == $row['Bill_ID']) ? 'selected' : '';
                    echo "<option value='{$bill['Bill_ID']}' $selected>Bill ID: {$bill['Bill_ID']}</option>";
                  }
            echo "</select></td>
                <td><input type='date' name='edit_payment_date' value='{$row['Payment_Date']}' required style='width:100%'></td>
                <td><input type='number' step='0.01' name='edit_amount_paid' value='{$row['Amount_Paid']}' required style='width:100%'></td>
                <td><select name='edit_method' required style='width:100%'>
                  <option value='Cash'".($row['Method']==='Cash'?' selected':'').">Cash</option>
                  <option value='Bank'".($row['Method']==='Bank'?' selected':'').">Bank</option>
                  <option value='Mobile Money'".($row['Method']==='Mobile Money'?' selected':'').">Mobile Money</option>
                  <option value='Online'".($row['Method']==='Online'?' selected':'').">Online</option>
                </select></td>
                <td>
                  <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                  <a href='?tab=payments' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
                </td>
              </form>
            </tr>";
          } else {
            echo "<tr>
                    <td>{$row['Payment_ID']}</td>
                    <td>{$row['Bill_ID']}</td>
                    <td>{$row['Payment_Date']}</td>
                    <td>{$row['Amount_Paid']}</td>
                    <td>{$row['Method']}</td>
                    <td>
                      <a href='?delete_payment={$row['Payment_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this payment?');\"><span class='material-icons'>delete</span></a>
                      <a href='?edit_payment={$row['Payment_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                    </td>
                  </tr>";
          }
        }
        ?>
      </table>
    </div>
  <div id="customers" class="tab">
    <h2>Customers</h2>
  <?php
  // Handle customer deletion
  if (isset($_GET['delete_customer'])) {
    $delete_id = intval($_GET['delete_customer']);
    // Get customer data before deletion for audit log
    $customer_data = $conn->query("SELECT * FROM customers WHERE Customer_ID = $delete_id")->fetch_assoc();
  $conn->query("DELETE FROM customers WHERE Customer_ID = $delete_id");
    // Log audit action
    if (function_exists('log_audit_action') && $customer_data) {
        log_audit_action($conn, 'DELETE', 'customers', $delete_id, $customer_data, null);
    }
    echo '<div style="color:green;margin-bottom:1rem;">Customer deleted.</div>';
  }
  // Handle customer update (including optional connection type update)
  if (isset($_POST['update_customer_id'])) {
    $id = intval($_POST['update_customer_id']);
    $name = $conn->real_escape_string(trim($_POST['edit_name']));
    $address = $conn->real_escape_string(trim($_POST['edit_address']));
    $phone = $conn->real_escape_string(trim($_POST['edit_phone']));
    $email = $conn->real_escape_string(trim($_POST['edit_email']));
    $category = $conn->real_escape_string(trim($_POST['edit_category']));
    
    // Get old values for audit log
    $old_data = $conn->query("SELECT * FROM customers WHERE Customer_ID = $id")->fetch_assoc();
    $new_values = ['Name' => $name, 'Address' => $address, 'Phone' => $phone, 'Email' => $email, 'Category' => $category];
    
    $sql = "UPDATE customers SET Name='$name', Address='$address', Phone='$phone', Email='$email', Category='$category' WHERE Customer_ID=$id";
    if ($conn->query($sql)) {
      // Log audit action
      if (function_exists('log_audit_action') && $old_data) {
          log_audit_action($conn, 'UPDATE', 'customers', $id, $old_data, $new_values);
      }
      // Optionally update connection type if provided
      if (isset($_POST['edit_connection_type'])) {
        $newType = $conn->real_escape_string(trim($_POST['edit_connection_type']));
        if ($newType !== '') {
          // Check if a connection already exists with this type
          $existsRes = $conn->query("SELECT Connection_ID FROM connections WHERE Customer_ID = $id AND Connection_Type = '$newType' LIMIT 1");
          if ($existsRes && $existsRes->num_rows > 0) {
            // Already has this type, nothing to change
          } else {
            // Update latest connection type if any; otherwise create
            $latestRes = $conn->query("SELECT Connection_ID FROM connections WHERE Customer_ID = $id ORDER BY Connection_ID DESC LIMIT 1");
            if ($latestRes && $rowC = $latestRes->fetch_assoc()) {
              $cid = intval($rowC['Connection_ID']);
              $conn->query("UPDATE connections SET Connection_Type = '$newType' WHERE Connection_ID = $cid");
            } else {
              $conn->query("INSERT INTO connections (Customer_ID, Connection_Type) VALUES ($id, '$newType')");
            }
          }
        }
      }
      echo '<div style="color:green;margin-bottom:1rem;">Customer updated.</div>';
    } else {
      echo '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
    }
  }
  // Handle customer insert via module
  if (function_exists('handle_customer_add')) { handle_customer_add($conn); }
  // Handle meter deletion
  if (isset($_GET['delete_meter'])) {
    $delete_id = intval($_GET['delete_meter']);
  $conn->query("DELETE FROM meters WHERE Meter_ID = $delete_id");
    echo '<div style="color:green;margin-bottom:1rem;">Meter deleted.</div>';
  }
  // Handle meter update
  if (isset($_POST['update_meter_id'])) {
    $id = intval($_POST['update_meter_id']);
  }
  // Show add customer form only if not editing
  $edit_id = isset($_GET['edit_customer']) ? intval($_GET['edit_customer']) : 0;
  if (!$edit_id) {
  ?>
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
        <div style="flex:1; min-width:150px;">
          <label for="name">Name</label><br>
          <input type="text" id="name" name="name" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:150px;">
          <label for="address">Address</label><br>
          <input type="text" id="address" name="address" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="phone">Phone</label><br>
          <input type="text" id="phone" name="phone" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:150px;">
          <label for="email">Email</label><br>
          <input type="email" id="email" name="email" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="category">Category</label><br>
          <select id="category" name="category" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <option value="Domestic">Domestic</option>
            <option value="Commercial">Commercial</option>
            <option value="Institutional">Institutional</option>
          </select>
        </div>
        <div style="min-width:100px;">
          <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">Add Customer</button>
        </div>
      </div>
    </form>
  <?php }
?>
    <table>
      <tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th><th>Email</th><th>Category</th><th>Connection Type</th><th>Action</th></tr>
      <?php
  // Fetch latest connection type per customer
  $res = function_exists('get_customers') ? get_customers($conn) : $conn->query("SELECT * FROM customers ORDER BY Customer_ID DESC");
      $edit_id = isset($_GET['edit_customer']) ? intval($_GET['edit_customer']) : 0;
      while($row = $res->fetch_assoc()){
        $connType = '';
        $ctRes = $conn->query("SELECT Connection_Type FROM connections WHERE Customer_ID = ".$row['Customer_ID']." ORDER BY Connection_ID DESC LIMIT 1");
        if ($ctRes && $ctRow = $ctRes->fetch_assoc()) { $connType = $ctRow['Connection_Type']; }
        if ($edit_id === intval($row['Customer_ID'])) {
          // Edit form row
          echo "<tr>
            <form method='post'>
              <td>{$row['Customer_ID']}<input type='hidden' name='update_customer_id' value='{$row['Customer_ID']}'></td>
              <td><input type='text' name='edit_name' value='".htmlspecialchars($row['Name'])."' required style='width:100%'></td>
              <td><input type='text' name='edit_address' value='".htmlspecialchars($row['Address'])."' required style='width:100%'></td>
              <td><input type='text' name='edit_phone' value='".htmlspecialchars($row['Phone'])."' required style='width:100%'></td>
              <td><input type='email' name='edit_email' value='".htmlspecialchars($row['Email'])."' required style='width:100%'></td>
              <td>
                <select name='edit_category' required style='width:100%'>
                  <option value='Domestic'".($row['Category']==='Domestic'?' selected':'').">Domestic</option>
                  <option value='Commercial'".($row['Category']==='Commercial'?' selected':'').">Commercial</option>
                  <option value='Institutional'".($row['Category']==='Institutional'?' selected':'').">Institutional</option>
                </select>
              </td>
              <td>
                <select name='edit_connection_type' style='width:100%'>
                  <option value=''>--Select--</option>
                  <option value='Main pipe'".($connType==='Main pipe'?' selected':'').">Main pipe</option>
                  <option value='Plumbing connection'".($connType==='Plumbing connection'?' selected':'').">Plumbing connection</option>
                  <option value='New water connection'".($connType==='New water connection'?' selected':'').">New water connection</option>
                  <option value='Domestic'".($connType==='Domestic'?' selected':'').">Domestic</option>
                  <option value='Commercial'".($connType==='Commercial'?' selected':'').">Commercial</option>
                  <option value='Institutional'".($connType==='Institutional'?' selected':'').">Institutional</option>
                </select>
              </td>
              <td>
                <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                <a href='?tab=customers' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
              </td>
            </form>
          </tr>";
        } else {
          echo "<tr>
                  <td>{$row['Customer_ID']}</td>
                  <td>{$row['Name']}</td>
                  <td>{$row['Address']}</td>
                  <td>{$row['Phone']}</td>
                  <td>{$row['Email']}</td>
                  <td>{$row['Category']}</td>
                  <td>".($connType!=='' ? $connType : '-')."</td>
                  <td>
                    <a href='?delete_customer={$row['Customer_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this customer?');\"><span class='material-icons'>delete</span></a>
                    <a href='?edit_customer={$row['Customer_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                  </td>
                </tr>";
        }
      }
      ?>
    </table>
  </div>

  <div id="meters" class="tab">
    <h2>Meters</h2>
    <?php
    // Handle meter form submission via module
    if (function_exists('handle_meter_add')) { handle_meter_add($conn); }
    ?>
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
        <div style="flex:1; min-width:120px;">
          <label for="meter_number">Meter Number</label><br>
          <input type="text" id="meter_number" name="meter_number" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="customer_id">Customer</label><br>
          <select id="customer_id" name="customer_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <?php
            $custRes = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM Customers ORDER BY Name");
            while($cust = $custRes->fetch_assoc()) {
              echo "<option value='{$cust['Customer_ID']}'>{$cust['Name']} (ID: {$cust['Customer_ID']})</option>";
            }
            ?>
          </select>
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="installation_date">Installation Date</label><br>
          <input type="date" id="installation_date" name="installation_date" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="status">Status</label><br>
          <select id="status" name="status" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Faulty">Faulty</option>
          </select>
        </div>
        <div style="flex:1; min-width:120px;">
          <label for="last_service_date">Last Service Date</label><br>
          <input type="date" id="last_service_date" name="last_service_date" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="min-width:100px;">
          <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">Add Meter</button>
        </div>
      </div>
    </form>
    <table>
      <tr><th>Meter ID</th><th>Customer</th><th>Meter Number</th><th>Installation Date</th><th>Status</th><th>Last Service Date</th><th>Action</th></tr>
      <?php
  $res = function_exists('get_meters') ? get_meters($conn) : $conn->query("SELECT M.*, C.Name as Customer_Name FROM meters M JOIN customers C ON M.Customer_ID=C.Customer_ID ORDER BY M.Meter_ID DESC");
      $edit_meter_id = isset($_GET['edit_meter']) ? intval($_GET['edit_meter']) : 0;
      while($row = $res->fetch_assoc()){
        if ($edit_meter_id === intval($row['Meter_ID'])) {
          // Edit form row for meter
          echo "<tr>
            <form method='post'>
              <td>{$row['Meter_ID']}<input type='hidden' name='update_meter_id' value='{$row['Meter_ID']}'></td>
              <td><select name='edit_customer_id' required style='width:100%'>";
                $custRes = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
                while($cust = $custRes->fetch_assoc()) {
                  $selected = ($cust['Customer_ID'] == $row['Customer_ID']) ? 'selected' : '';
                  echo "<option value='{$cust['Customer_ID']}' $selected>{$cust['Name']} (ID: {$cust['Customer_ID']})</option>";
                }
          echo "</select></td>
              <td><input type='text' name='edit_meter_number' value='".htmlspecialchars($row['Meter_Number'])."' required style='width:100%'></td>
              <td><input type='date' name='edit_installation_date' value='{$row['Installation_Date']}' required style='width:100%'></td>
              <td><select name='edit_status' required style='width:100%'>
                <option value='Active'".($row['Status']==='Active'?' selected':'').">Active</option>
                <option value='Inactive'".($row['Status']==='Inactive'?' selected':'').">Inactive</option>
                <option value='Faulty'".($row['Status']==='Faulty'?' selected':'').">Faulty</option>
              </select></td>
              <td><input type='date' name='edit_last_service_date' value='{$row['Last_Service_Date']}' required style='width:100%'></td>
              <td>
                <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                <a href='?tab=meters' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
              </td>
            </form>
          </tr>";
        } else {
          echo "<tr>
                  <td>{$row['Meter_ID']}</td>
                  <td>{$row['Customer_Name']} (ID: {$row['Customer_ID']})</td>
                  <td>{$row['Meter_Number']}</td>
                  <td>{$row['Installation_Date']}</td>
                  <td>{$row['Status']}</td>
                  <td>{$row['Last_Service_Date']}</td>
                  <td>
                    <a href='?delete_meter={$row['Meter_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this meter?');\"><span class='material-icons'>delete</span></a>
                    <a href='?edit_meter={$row['Meter_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                  </td>
                </tr>";
        }
      }
      ?>
    </table>
  </div>



  <div id="staff" class="tab">
    <h2>Staff</h2>
    <?php
    // Handle staff deletion
    if (isset($_GET['delete_staff'])) {
      $delete_id = intval($_GET['delete_staff']);
      // Get staff data before deletion for audit log
      $staff_data = $conn->query("SELECT * FROM staff WHERE Staff_ID = $delete_id")->fetch_assoc();
      $conn->query("DELETE FROM staff WHERE Staff_ID = $delete_id");
      // Log audit action
      if (function_exists('log_audit_action') && $staff_data) {
          log_audit_action($conn, 'DELETE', 'staff', $delete_id, $staff_data, null);
      }
      echo '<div style="color:green;margin-bottom:1rem;">Staff deleted.</div>';
    }
    // Handle staff update
    if (isset($_POST['update_staff_id'])) {
      $id = intval($_POST['update_staff_id']);
      $name = $conn->real_escape_string(trim($_POST['edit_staff_name']));
      $department = $conn->real_escape_string(trim($_POST['edit_staff_department']));
      $position = $conn->real_escape_string(trim($_POST['edit_staff_position']));
      
      // Get old values for audit log
      $old_data = $conn->query("SELECT * FROM staff WHERE Staff_ID = $id")->fetch_assoc();
      $new_values = ['Name' => $name, 'Department' => $department, 'Position' => $position];
      
      $sql = "UPDATE staff SET Name='$name', Department='$department', Position='$position' WHERE Staff_ID=$id";
      if ($conn->query($sql)) {
        // Log audit action
        if (function_exists('log_audit_action') && $old_data) {
            log_audit_action($conn, 'UPDATE', 'staff', $id, $old_data, $new_values);
        }
        echo '<div style="color:green;margin-bottom:1rem;">Staff updated.</div>';
      } else {
        echo '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
      }
    }
    // Handle staff insert
    if (
      $_SERVER['REQUEST_METHOD'] === 'POST' &&
      isset($_POST['staff_name'], $_POST['staff_department'], $_POST['staff_position']) &&
      !isset($_POST['update_staff_id'])
    ) {
      $name = $conn->real_escape_string(trim($_POST['staff_name']));
      $department = $conn->real_escape_string(trim($_POST['staff_department']));
      $position = $conn->real_escape_string(trim($_POST['staff_position']));
      if ($name && $department && $position) {
        $sql = "INSERT INTO staff (Name, Department, Position) VALUES ('$name', '$department', '$position')";
        if ($conn->query($sql)) {
          // Log audit action
          if (function_exists('log_audit_action')) {
              $new_values = ['Name' => $name, 'Department' => $department, 'Position' => $position];
              log_audit_action($conn, 'CREATE', 'staff', $conn->insert_id, null, $new_values);
          }
          echo '<div style="color:green;margin-bottom:1rem;">Staff added successfully.</div>';
        } else {
          echo '<div style="color:red;margin-bottom:1rem;">Error: ' . $conn->error . '</div>';
        }
      } else {
        echo '<div style="color:red;margin-bottom:1rem;">All fields are required.</div>';
      }
    }
    ?>
    <?php
    $edit_staff_id = isset($_GET['edit_staff']) ? intval($_GET['edit_staff']) : 0;
    if (!$edit_staff_id) {
    ?>
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
        <div style="flex:1; min-width:150px;">
          <label for="staff_name">Name</label><br>
          <input type="text" id="staff_name" name="staff_name" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:150px;">
          <label for="staff_department">Department</label><br>
          <input type="text" id="staff_department" name="staff_department" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:150px;">
          <label for="staff_position">Position</label><br>
          <input type="text" id="staff_position" name="staff_position" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="min-width:100px;">
          <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">Add Staff</button>
        </div>
      </div>
    </form>
    <?php }
    ?>
    <table>
      <tr><th>ID</th><th>Name</th><th>Department</th><th>Position</th><th>Action</th></tr>
      <?php
      $res = $conn->query("SELECT * FROM staff ORDER BY Staff_ID DESC");
      $edit_staff_id = isset($_GET['edit_staff']) ? intval($_GET['edit_staff']) : 0;
      while($row = $res->fetch_assoc()){
        if ($edit_staff_id === intval($row['Staff_ID'])) {
          // Edit form row
          echo "<tr>
            <form method='post'>
              <td>{$row['Staff_ID']}<input type='hidden' name='update_staff_id' value='{$row['Staff_ID']}'></td>
              <td><input type='text' name='edit_staff_name' value='".htmlspecialchars($row['Name'])."' required style='width:100%'></td>
              <td><input type='text' name='edit_staff_department' value='".htmlspecialchars($row['Department'])."' required style='width:100%'></td>
              <td><input type='text' name='edit_staff_position' value='".htmlspecialchars($row['Position'])."' required style='width:100%'></td>
              <td>
                <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                <a href='?tab=staff' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
              </td>
            </form>
          </tr>";
        } else {
          echo "<tr>
                  <td>{$row['Staff_ID']}</td>
                  <td>{$row['Name']}</td>
                  <td>{$row['Department']}</td>
                  <td>{$row['Position']}</td>
                  <td>
                    <a href='?delete_staff={$row['Staff_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this staff member?');\"><span class='material-icons'>delete</span></a>
                    <a href='?edit_staff={$row['Staff_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                  </td>
                </tr>";
        }
      }
      ?>
    </table>
  </div>


  <div id="complaints" class="tab">
    <h2>Complaints</h2>
    <?php
    // Handle complaint deletion
    if (isset($_GET['delete_complaint'])) {
      $delete_id = intval($_GET['delete_complaint']);
      $conn->query("DELETE FROM complaints WHERE Complaint_ID = $delete_id");
      echo '<div style="color:green;margin-bottom:1rem;">Complaint deleted.</div>';
    }
    // Handle complaint update
    if (isset($_POST['update_complaint_id'])) {
      $id = intval($_POST['update_complaint_id']);
      $customer_id = intval($_POST['edit_complaint_customer_id']);
      $date_logged = $conn->real_escape_string(trim($_POST['edit_complaint_date_logged']));
      $type = $conn->real_escape_string(trim($_POST['edit_complaint_type']));
      $status = $conn->real_escape_string(trim($_POST['edit_complaint_status']));
      $resolved_by = intval($_POST['edit_complaint_resolved_by']);
      $sql = "UPDATE complaints SET Customer_ID=$customer_id, Date_Logged='$date_logged', Type='$type', Status='$status', Resolved_By=$resolved_by WHERE Complaint_ID=$id";
      if ($conn->query($sql)) {
        echo '<div style="color:green;margin-bottom:1rem;">Complaint updated.</div>';
      } else {
        echo '<div style="color:red;margin-bottom:1rem;">Error updating: ' . $conn->error . '</div>';
      }
    }
    // Handle complaint insert
    if (
      $_SERVER['REQUEST_METHOD'] === 'POST' &&
      isset($_POST['complaint_customer_id'], $_POST['complaint_date_logged'], $_POST['complaint_type'], $_POST['complaint_status'], $_POST['complaint_resolved_by']) &&
      !isset($_POST['update_complaint_id'])
    ) {
      $customer_id = intval($_POST['complaint_customer_id']);
      $date_logged = $conn->real_escape_string(trim($_POST['complaint_date_logged']));
      $type = $conn->real_escape_string(trim($_POST['complaint_type']));
      $status = $conn->real_escape_string(trim($_POST['complaint_status']));
      $resolved_by = intval($_POST['complaint_resolved_by']);
      if ($customer_id && $date_logged && $type && $status && $resolved_by) {
        $sql = "INSERT INTO complaints (Customer_ID, Date_Logged, Type, Status, Resolved_By) VALUES ($customer_id, '$date_logged', '$type', '$status', $resolved_by)";
        if ($conn->query($sql)) {
          echo '<div style="color:green;margin-bottom:1rem;">Complaint added successfully.</div>';
        } else {
          echo '<div style="color:red;margin-bottom:1rem;">Error: ' . $conn->error . '</div>';
        }
      } else {
        echo '<div style="color:red;margin-bottom:1rem;">All fields are required.</div>';
      }
    }
    $edit_complaint_id = isset($_GET['edit_complaint']) ? intval($_GET['edit_complaint']) : 0;
    if (!$edit_complaint_id) {
    ?>
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
      <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
        <div style="flex:1; min-width:150px;">
          <label for="complaint_customer_id">Customer</label><br>
          <select id="complaint_customer_id" name="complaint_customer_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <?php
            $custRes = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
            while($cust = $custRes->fetch_assoc()) {
              echo "<option value='{$cust['Customer_ID']}'>{$cust['Name']} (ID: {$cust['Customer_ID']})</option>";
            }
            ?>
          </select>
        </div>
        <div style="flex:1; min-width:150px;">
          <label for="complaint_date_logged">Date Logged</label><br>
          <input type="date" id="complaint_date_logged" name="complaint_date_logged" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
        </div>
        <div style="flex:1; min-width:150px;">
          <label for="complaint_type">Type</label><br>
          <select id="complaint_type" name="complaint_type" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <option value="Leakage">Leakage</option>
            <option value="Billing">Billing</option>
            <option value="Meter Fault (stolen_meter)">Meter Fault (stolen_meter)</option>
            <option value="Meter Fault (meter_Glass_Damage)">Meter Fault (meter_Glass_Damage)</option>
            <option value="Service Request">Service Request</option>
          </select>
        </div>
        <div style="flex:1; min-width:150px;">
          <label for="complaint_status">Status</label><br>
          <select id="complaint_status" name="complaint_status" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <option value="Open">Open</option>
            <option value="In Progress">In Progress</option>
            <option value="Resolved">Resolved</option>
          </select>
        </div>
        <div style="flex:1; min-width:150px;">
          <label for="complaint_resolved_by">Resolved By (Staff)</label><br>
          <select id="complaint_resolved_by" name="complaint_resolved_by" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            <option value="">--Select--</option>
            <?php
            $staffRes = $conn->query("SELECT Staff_ID, Name FROM staff ORDER BY Name");
            while($staff = $staffRes->fetch_assoc()) {
              echo "<option value='{$staff['Staff_ID']}'>{$staff['Name']} (ID: {$staff['Staff_ID']})</option>";
            }
            ?>
          </select>
        </div>
        <div style="min-width:100px;">
          <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">Add Complaint</button>
        </div>
      </div>
    </form>
    <?php }
    ?>
    <table>
      <tr><th>ID</th><th>Customer</th><th>Date Logged</th><th>Type</th><th>Status</th><th>Resolved By</th><th>Action</th></tr>
      <?php
      $res = $conn->query("SELECT c.*, cu.Name as Customer_Name, s.Name as Staff_Name FROM complaints c JOIN customers cu ON c.Customer_ID=cu.Customer_ID JOIN staff s ON c.Resolved_By=s.Staff_ID ORDER BY c.Complaint_ID DESC");
      $edit_complaint_id = isset($_GET['edit_complaint']) ? intval($_GET['edit_complaint']) : 0;
      while($row = $res->fetch_assoc()){
        if ($edit_complaint_id === intval($row['Complaint_ID'])) {
          // Edit form row
          echo "<tr>
            <form method='post'>
              <td>{$row['Complaint_ID']}<input type='hidden' name='update_complaint_id' value='{$row['Complaint_ID']}'></td>
              <td><select name='edit_complaint_customer_id' required style='width:100%'>";
                $custRes = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
                while($cust = $custRes->fetch_assoc()) {
                  $selected = ($cust['Customer_ID'] == $row['Customer_ID']) ? 'selected' : '';
                  echo "<option value='{$cust['Customer_ID']}' $selected>{$cust['Name']} (ID: {$cust['Customer_ID']})</option>";
                }
          echo "</select></td>
              <td><input type='date' name='edit_complaint_date_logged' value='{$row['Date_Logged']}' required style='width:100%'></td>
              <td><select name='edit_complaint_type' required style='width:100%'>
                <option value='Leakage'".($row['Type']==='Leakage'?' selected':'').">Leakage</option>
                <option value='Billing'".($row['Type']==='Billing'?' selected':'').">Billing</option>
                <option value='Meter Fault (stolen_meter)'".($row['Type']==='Meter Fault (stolen_meter)'?' selected':'').">Meter Fault (stolen_meter)</option>
                <option value='Meter Fault (meter_Glass_Damage)'".($row['Type']==='Meter Fault (meter_Glass_Damage)'?' selected':'').">Meter Fault (meter_Glass_Damage)</option>
                <option value='Service Request'".($row['Type']==='Service Request'?' selected':'').">Service Request</option>
              </select></td>
              <td><select name='edit_complaint_status' required style='width:100%'>
                <option value='Open'".($row['Status']==='Open'?' selected':'').">Open</option>
                <option value='In Progress'".($row['Status']==='In Progress'?' selected':'').">In Progress</option>
                <option value='Resolved'".($row['Status']==='Resolved'?' selected':'').">Resolved</option>
              </select></td>
              <td><select name='edit_complaint_resolved_by' required style='width:100%'>";
                $staffRes = $conn->query("SELECT Staff_ID, Name FROM staff ORDER BY Name");
                while($staff = $staffRes->fetch_assoc()) {
                  $selected = ($staff['Staff_ID'] == $row['Resolved_By']) ? 'selected' : '';
                  echo "<option value='{$staff['Staff_ID']}' $selected>{$staff['Name']} (ID: {$staff['Staff_ID']})</option>";
                }
          echo "</select></td>
              <td>
                <button type='submit' title='Save' style='background:none;border:none;cursor:pointer;color:#1976d2;'><span class='material-icons'>save</span></button>
                <a href='?tab=complaints' title='Cancel' style='color:#888;'><span class='material-icons'>cancel</span></a>
              </td>
            </form>
          </tr>";
        } else {
          echo "<tr>
                  <td>{$row['Complaint_ID']}</td>
                  <td>{$row['Customer_Name']} (ID: {$row['Customer_ID']})</td>
                  <td>{$row['Date_Logged']}</td>
                  <td>{$row['Type']}</td>
                  <td>{$row['Status']}</td>
                  <td>{$row['Staff_Name']} (ID: {$row['Resolved_By']})</td>
                  <td>
                    <a href='?delete_complaint={$row['Complaint_ID']}' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this complaint?');\"><span class='material-icons'>delete</span></a>
                    <a href='?edit_complaint={$row['Complaint_ID']}' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                  </td>
                </tr>";
        }
      }
      ?>
    </table>
  </div>









<!-- Call Logs Tab -->
<div id="calllogs" class="tab">
    <h2>Call Logs</h2>
    <?php
    // === CRUD LOGIC for Call Logs ===
    $call_msg = '';
    
    // Delete
    if (isset($_GET['delete_call'])) {
        $id = intval($_GET['delete_call']);
        $conn->query("DELETE FROM call_logs WHERE Call_ID=$id");
        $call_msg = "<div style='color:green;margin-bottom:1rem;'>Call log deleted.</div>";
    }

    // Update
    if (isset($_POST['update_call_id'])) {
        $id = intval($_POST['update_call_id']);
        $customer_id = intval($_POST['edit_customer_id']);
        $staff_id = intval($_POST['edit_staff_id']);
        $notes = $conn->real_escape_string($_POST['edit_notes']);
        $status = $conn->real_escape_string($_POST['edit_status']);
        $conn->query("UPDATE call_logs SET Customer_ID=$customer_id, Staff_ID=$staff_id, Notes='$notes', Status='$status' WHERE Call_ID=$id");
        $call_msg = "<div style='color:green;margin-bottom:1rem;'>Call log updated.</div>";
    }

    // Insert
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['customer_id'],$_POST['staff_id'],$_POST['notes'],$_POST['status']) && !isset($_POST['update_call_id'])) {
        $customer_id=intval($_POST['customer_id']);
        $staff_id=intval($_POST['staff_id']);
        $notes=$conn->real_escape_string($_POST['notes']);
        $status=$conn->real_escape_string($_POST['status']);
        $conn->query("INSERT INTO call_logs (Customer_ID,Staff_ID,Notes,Status) VALUES ($customer_id,$staff_id,'$notes','$status')");
        $call_msg = "<div style='color:green;margin-bottom:1rem;'>Call log added.</div>";
    }

    // Fetch customers and staff
    $customers = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
    $staffs = $conn->query("SELECT Staff_ID, Name FROM staff ORDER BY Name");

    // For edit
    $edit_call_id = isset($_GET['edit_call']) ? intval($_GET['edit_call']) : 0;
    $edit_call = null;
    if ($edit_call_id) {
        $res = $conn->query("SELECT * FROM call_logs WHERE Call_ID=$edit_call_id");
        $edit_call = $res->fetch_assoc();
    }
    
    if ($call_msg) echo $call_msg;
    ?>

    <!-- Add/Edit Form -->
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
        <?php if($edit_call): ?>
            <input type="hidden" name="update_call_id" value="<?php echo $edit_call['Call_ID']; ?>">
        <?php endif; ?>

        <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label for="customer_id">Customer</label><br>
                <select id="customer_id" name="customer_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="">--Select--</option>
                    <?php 
                    $customers->data_seek(0);
                    while($c = $customers->fetch_assoc()): ?>
                        <option value="<?php echo $c['Customer_ID']; ?>" <?php if($edit_call && $edit_call['Customer_ID']==$c['Customer_ID']) echo 'selected'; ?>>
                            <?php echo $c['Name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="flex:1; min-width:200px;">
                <label for="staff_id">Staff</label><br>
                <select id="staff_id" name="staff_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="">--Select--</option>
                    <?php 
                    $staffs->data_seek(0);
                    while($s = $staffs->fetch_assoc()): ?>
                        <option value="<?php echo $s['Staff_ID']; ?>" <?php if($edit_call && $edit_call['Staff_ID']==$s['Staff_ID']) echo 'selected'; ?>>
                            <?php echo $s['Name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="flex:1; min-width:200px;">
                <label for="notes">Notes</label><br>
                <textarea id="notes" name="notes" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6; min-height:80px;"><?php if($edit_call) echo htmlspecialchars($edit_call['Notes']); ?></textarea>
            </div>

            <div style="flex:1; min-width:120px;">
                <label for="status">Status</label><br>
                <select id="status" name="status" style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="Pending" <?php if($edit_call && $edit_call['Status']=='Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Completed" <?php if($edit_call && $edit_call['Status']=='Completed') echo 'selected'; ?>>Completed</option>
                    <option value="Follow-up" <?php if($edit_call && $edit_call['Status']=='Follow-up') echo 'selected'; ?>>Follow-up</option>
                </select>
            </div>

            <div style="min-width:100px;">
                <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;"><?php echo $edit_call ? 'Update' : 'Add'; ?> Call Log</button>
                <?php if($edit_call): ?>
                    <a href="?tab=calllogs" style="margin-left:10px;color:#888;">Cancel</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Display Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Staff</th>
            <th>Date</th>
            <th>Notes</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php
        $res=$conn->query("SELECT cl.*,c.Name AS CustomerName,s.Name AS StaffName FROM call_logs cl JOIN customers c ON cl.Customer_ID=c.Customer_ID JOIN staff s ON cl.Staff_ID=s.Staff_ID ORDER BY cl.Call_ID DESC");
        if ($res->num_rows > 0) {
            while($row=$res->fetch_assoc()){
                echo "<tr>
                    <td>{$row['Call_ID']}</td>
                    <td>{$row['CustomerName']}</td>
                    <td>{$row['StaffName']}</td>
                    <td>{$row['Call_Date']}</td>
                    <td>{$row['Notes']}</td>
                    <td>{$row['Status']}</td>
                    <td>
                        <a href='?delete_call={$row['Call_ID']}&tab=calllogs' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this call log?');\"><span class='material-icons'>delete</span></a>
                        <a href='?edit_call={$row['Call_ID']}&tab=calllogs' title='Edit' style='color:#1976d2;'><span class='material-icons'>edit</span></a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='7' style='text-align:center;'>No call logs found</td></tr>";
        }
        ?>
    </table>
</div>


<!-- Store Tab - Add this with your other tabs -->
<div id="store" class="tab">
    <h2>Store Management</h2>
    <?php
    // === CRUD LOGIC for Store ===
    $store_msg = '';
    
    // Delete item
    if (isset($_GET['delete_store'])) {
        $id = intval($_GET['delete_store']);
        $conn->query("DELETE FROM Store WHERE Store_ID=$id");
        $store_msg = "<div class='msg success'>Store item deleted successfully.</div>";
    }

    // Update item
    if (isset($_POST['update_store_id'])) {
        $id = intval($_POST['update_store_id']);
        $complaint_id = !empty($_POST['edit_complaint_id']) ? intval($_POST['edit_complaint_id']) : 'NULL';
        $customer_id = !empty($_POST['edit_customer_id']) ? intval($_POST['edit_customer_id']) : 'NULL';
        $item_type = $conn->real_escape_string($_POST['edit_item_type']);
        $staff_id = !empty($_POST['edit_staff_id']) ? intval($_POST['edit_staff_id']) : 'NULL';
        
        $sql = "UPDATE Store SET 
                Complaint_ID=$complaint_id, 
                Customer_ID=$customer_id, 
                Item_Type='$item_type', 
                Staff_ID=$staff_id 
                WHERE Store_ID=$id";
        
        if ($conn->query($sql)) {
            $store_msg = "<div class='msg success'>Store item updated successfully.</div>";
        } else {
            $store_msg = "<div class='msg error'>Error updating: " . $conn->error . "</div>";
        }
    }

    // Insert new item
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_type']) && !isset($_POST['update_store_id'])) {
        $complaint_id = !empty($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 'NULL';
        $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : 'NULL';
        $item_type = $conn->real_escape_string($_POST['item_type']);
        $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : 'NULL';
        
        $sql = "INSERT INTO Store (Complaint_ID, Customer_ID, Item_Type, Staff_ID) 
                VALUES ($complaint_id, $customer_id, '$item_type', $staff_id)";
        
        if ($conn->query($sql)) {
            $store_msg = "<div class='msg success'>Store item added successfully.</div>";
        } else {
            $store_msg = "<div class='msg error'>Error: " . $conn->error . "</div>";
        }
    }

    // Fetch data for dropdowns
    $complaints = $conn->query("SELECT Complaint_ID, Type FROM complaints ORDER BY Complaint_ID DESC");
    $customers = function_exists('get_customer_id_name_list') ? get_customer_id_name_list($conn) : $conn->query("SELECT Customer_ID, Name FROM customers ORDER BY Name");
    $staffs = $conn->query("SELECT Staff_ID, Name FROM staff ORDER BY Name");

    // For edit
    $edit_store_id = isset($_GET['edit_store']) ? intval($_GET['edit_store']) : 0;
    $edit_store = null;
    if ($edit_store_id) {
        $res = $conn->query("SELECT * FROM Store WHERE Store_ID=$edit_store_id");
        $edit_store = $res->fetch_assoc();
    }
    
    if ($store_msg) echo $store_msg;
    ?>

    <!-- Add/Edit Form -->
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
        <?php if($edit_store): ?>
            <input type="hidden" name="update_store_id" value="<?php echo $edit_store['Store_ID']; ?>">
        <?php endif; ?>

        <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label for="complaint_id">Complaint (Optional)</label><br>
                <select id="complaint_id" name="complaint_id" style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="">--Select Complaint--</option>
                    <?php 
                    $complaints->data_seek(0);
                    while($comp = $complaints->fetch_assoc()): ?>
                        <option value="<?php echo $comp['Complaint_ID']; ?>" 
                            <?php if($edit_store && $edit_store['Complaint_ID']==$comp['Complaint_ID']) echo 'selected'; ?>>
                            #<?php echo $comp['Complaint_ID']; ?> - <?php echo $comp['Type']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="flex:1; min-width:200px;">
                <label for="customer_id">Customer (Optional)</label><br>
                <select id="customer_id" name="customer_id" style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="">--Select Customer--</option>
                    <?php 
                    $customers->data_seek(0);
                    while($cust = $customers->fetch_assoc()): ?>
                        <option value="<?php echo $cust['Customer_ID']; ?>" 
                            <?php if($edit_store && $edit_store['Customer_ID']==$cust['Customer_ID']) echo 'selected'; ?>>
                            <?php echo $cust['Name']; ?> (ID: <?php echo $cust['Customer_ID']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="flex:1; min-width:200px;">
                <label for="item_type">Item Type *</label><br>
                <select id="item_type" name="item_type" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="">--Select Item Type--</option>
                    <option value="Pipes - PVC" <?php if($edit_store && $edit_store['Item_Type']=='Pipes - PVC') echo 'selected'; ?>>Pipes - PVC</option>
                    <option value="Pipes - Copper" <?php if($edit_store && $edit_store['Item_Type']=='Pipes - Copper') echo 'selected'; ?>>Pipes - Copper</option>
                    <option value="Pipes - PPR" <?php if($edit_store && $edit_store['Item_Type']=='Pipes - PPR') echo 'selected'; ?>>Pipes - PPR</option>
                    <option value="Valves - Gate" <?php if($edit_store && $edit_store['Item_Type']=='Valves - Gate') echo 'selected'; ?>>Valves - Gate</option>
                    <option value="Valves - Stop Cork" <?php if($edit_store && $edit_store['Item_Type']=='Valves - Stop Cork') echo 'selected'; ?>>Valves - Stop Cork</option>
                    <option value="Taps" <?php if($edit_store && $edit_store['Item_Type']=='Taps') echo 'selected'; ?>>Taps</option>
                    <option value="Water Meters" <?php if($edit_store && $edit_store['Item_Type']=='Water Meters') echo 'selected'; ?>>Water Meters</option>
                </select>
            </div>

            <div style="flex:1; min-width:200px;">
                <label for="staff_id">Staff (Optional)</label><br>
                <select id="staff_id" name="staff_id" style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="">--Select Staff--</option>
                    <?php 
                    $staffs->data_seek(0);
                    while($staff = $staffs->fetch_assoc()): ?>
                        <option value="<?php echo $staff['Staff_ID']; ?>" 
                            <?php if($edit_store && $edit_store['Staff_ID']==$staff['Staff_ID']) echo 'selected'; ?>>
                            <?php echo $staff['Name']; ?> (ID: <?php echo $staff['Staff_ID']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="min-width:100px;">
                <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">
                    <?php echo $edit_store ? 'Update' : 'Add'; ?> Item
                </button>
                <?php if($edit_store): ?>
                    <a href="?tab=store" style="margin-left:10px;color:#888;">Cancel</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Display Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Complaint</th>
            <th>Customer</th>
            <th>Item Type</th>
            <th>Staff</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
        <?php
        $res = $conn->query("
            SELECT s.*, 
                   c.Type as Complaint_Type, 
                   cu.Name as Customer_Name, 
                   st.Name as Staff_Name 
            FROM Store s
            LEFT JOIN complaints c ON s.Complaint_ID = c.Complaint_ID
            LEFT JOIN customers cu ON s.Customer_ID = cu.Customer_ID
            LEFT JOIN staff st ON s.Staff_ID = st.Staff_ID
            ORDER BY s.Store_ID DESC
        ");
        
        if ($res->num_rows > 0) {
            while($row = $res->fetch_assoc()){
                echo "<tr>
                    <td>{$row['Store_ID']}</td>
                    <td>" . ($row['Complaint_ID'] ? "#{$row['Complaint_ID']} - {$row['Complaint_Type']}" : 'N/A') . "</td>
                    <td>" . ($row['Customer_ID'] ? "{$row['Customer_Name']} (ID: {$row['Customer_ID']})" : 'N/A') . "</td>
                    <td>{$row['Item_Type']}</td>
                    <td>" . ($row['Staff_ID'] ? "{$row['Staff_Name']} (ID: {$row['Staff_ID']})" : 'N/A') . "</td>
                    <td>{$row['Created_At']}</td>
                    <td>
                        <a href='?delete_store={$row['Store_ID']}&tab=store' title='Delete' style='color:red; margin-right:10px;' onclick=\"return confirm('Delete this store item?');\">
                            <span class='material-icons'>delete</span>
                        </a>
                        <a href='?edit_store={$row['Store_ID']}&tab=store' title='Edit' style='color:#1976d2;'>
                            <span class='material-icons'>edit</span>
                        </a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='7' style='text-align:center;'>No store items found</td></tr>";
        }
        ?>
    </table>
</div>




<!-- Store Transactions Tab -->
<div id="store_transactions" class="tab">
    <h2>Store Transactions</h2>
    <?php
    // === CRUD LOGIC for Store Transactions ===
    $transaction_msg = '';
    
    // Handle transaction submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['store_id'], $_POST['transaction_type'], $_POST['quantity'])) {
        $store_id = intval($_POST['store_id']);
        $transaction_type = $conn->real_escape_string($_POST['transaction_type']);
        $quantity = intval($_POST['quantity']);
        $notes = $conn->real_escape_string($_POST['notes']);
        
        // Get current balance
        $current_balance = 0;
        $balance_result = $conn->query("
            SELECT Balance_After_Transaction 
            FROM Store_Transactions 
            WHERE Store_ID = $store_id 
            ORDER BY Transaction_ID DESC 
            LIMIT 1
        ");
        
        if ($balance_result && $balance_result->num_rows > 0) {
            $row = $balance_result->fetch_assoc();
            $current_balance = $row['Balance_After_Transaction'];
        }
        
        // Calculate new balance
        if ($transaction_type === 'IN') {
            $new_balance = $current_balance + $quantity;
        } else {
            $new_balance = $current_balance - $quantity;
            if ($new_balance < 0) $new_balance = 0; // Prevent negative balance
        }
        
        // Insert transaction
        $sql = "INSERT INTO Store_Transactions (Store_ID, Transaction_Type, Quantity, Balance_After_Transaction, Notes) 
                VALUES ($store_id, '$transaction_type', $quantity, $new_balance, '$notes')";
        
        if ($conn->query($sql)) {
            $transaction_msg = "<div class='msg success'>Transaction recorded successfully.</div>";
        } else {
            $transaction_msg = "<div class='msg error'>Error: " . $conn->error . "</div>";
        }
    }
    
    // Fetch store items for dropdown
    $store_items = $conn->query("
        SELECT s.Store_ID, s.Item_Type, 
               COALESCE((
                   SELECT Balance_After_Transaction 
                   FROM Store_Transactions st 
                   WHERE st.Store_ID = s.Store_ID 
                   ORDER BY st.Transaction_ID DESC 
                   LIMIT 1
               ), 0) as Current_Balance
        FROM Store s 
        ORDER BY s.Item_Type
    ");
    
    if ($transaction_msg) echo $transaction_msg;
    ?>

    <!-- Transaction Form -->
    <form method="post" style="margin-bottom:2rem; background:#f9fbff; padding:1.5rem; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
        <div style="display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label for="store_id">Item *</label><br>
                <select id="store_id" name="store_id" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="">--Select Item--</option>
                    <?php 
                    if ($store_items->num_rows > 0) {
                        while($item = $store_items->fetch_assoc()):
                    ?>
                        <option value="<?php echo $item['Store_ID']; ?>" data-balance="<?php echo $item['Current_Balance']; ?>">
                            <?php echo $item['Item_Type']; ?> (Current: <?php echo $item['Current_Balance']; ?>)
                        </option>
                    <?php 
                        endwhile;
                    }
                    ?>
                </select>
            </div>

            <div style="flex:1; min-width:150px;">
                <label for="transaction_type">Transaction Type *</label><br>
                <select id="transaction_type" name="transaction_type" required style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
                    <option value="IN">Add Items (IN)</option>
                    <option value="OUT">Remove Items (OUT)</option>
                </select>
            </div>

            <div style="flex:1; min-width:120px;">
                <label for="quantity">Quantity *</label><br>
                <input type="number" id="quantity" name="quantity" min="1" required 
                       style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            </div>

            <div style="flex:1; min-width:200px;">
                <label for="notes">Notes</label><br>
                <input type="text" id="notes" name="notes" 
                       style="width:100%;padding:0.5rem; border-radius:5px; border:1px solid #d0d7e6;">
            </div>

            <div style="min-width:100px;">
                <button type="submit" style="background:#1976d2;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:5px;font-size:1rem;cursor:pointer;">
                    Record Transaction
                </button>
            </div>
        </div>
    </form>

    <!-- Transactions Table -->
    <h3>Transaction History</h3>
    <table>
        <tr>
            <th>Transaction ID</th>
            <th>Item Type</th>
            <th>Type</th>
            <th>Quantity</th>
            <th>Balance After</th>
            <th>Date</th>
            <th>Notes</th>
        </tr>
        <?php
        $transactions = $conn->query("
            SELECT st.*, s.Item_Type 
            FROM Store_Transactions st 
            JOIN Store s ON st.Store_ID = s.Store_ID 
            ORDER BY st.Transaction_ID DESC
            LIMIT 50
        ");
        
        if ($transactions->num_rows > 0) {
            while($transaction = $transactions->fetch_assoc()):
        ?>
            <tr>
                <td><?php echo $transaction['Transaction_ID']; ?></td>
                <td><?php echo $transaction['Item_Type']; ?></td>
                <td>
                    <span style="color: <?php echo $transaction['Transaction_Type'] === 'IN' ? 'green' : 'red'; ?>;">
                        <?php echo $transaction['Transaction_Type']; ?>
                    </span>
                </td>
                <td><?php echo $transaction['Quantity']; ?></td>
                <td><?php echo $transaction['Balance_After_Transaction']; ?></td>
                <td><?php echo $transaction['Transaction_Date']; ?></td>
                <td><?php echo $transaction['Notes']; ?></td>
            </tr>
        <?php
            endwhile;
        } else {
            echo "<tr><td colspan='7' style='text-align:center;'>No transactions found</td></tr>";
        }
        ?>
    </table>
</div>








</main>

<footer>
  <p>© 2025 Lilongwe Water Board Management System Call 253 </p>
</footer>
<script src="assets/js/script.js"></script>

</body>
</html>
