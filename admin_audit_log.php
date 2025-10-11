<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_login();

$role = current_role();
if ($role !== 'Admin') {
    header('Location: index.php');
    exit;
}

// Ensure audit log table exists
ensure_audit_log_table($conn);

// Get filter parameters
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_table = isset($_GET['table']) ? $_GET['table'] : '';
$filter_user = isset($_GET['user']) ? $_GET['user'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;

// Build query with filters
$where_conditions = [];
$params = [];
$param_types = '';

if ($filter_action) {
    $where_conditions[] = "action = ?";
    $params[] = $filter_action;
    $param_types .= 's';
}

if ($filter_table) {
    $where_conditions[] = "table_name = ?";
    $params[] = $filter_table;
    $param_types .= 's';
}

if ($filter_user) {
    $where_conditions[] = "user_role = ?";
    $params[] = $filter_user;
    $param_types .= 's';
}

if ($filter_date_from) {
    $where_conditions[] = "DATE(timestamp) >= ?";
    $params[] = $filter_date_from;
    $param_types .= 's';
}

if ($filter_date_to) {
    $where_conditions[] = "DATE(timestamp) <= ?";
    $params[] = $filter_date_to;
    $param_types .= 's';
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
$limit_clause = "LIMIT " . max(1, min(1000, $limit));

$sql = "SELECT * FROM audit_log $where_clause ORDER BY timestamp DESC $limit_clause";
$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Get unique values for filter dropdowns
$actions_result = $conn->query("SELECT DISTINCT action FROM audit_log ORDER BY action");
$tables_result = $conn->query("SELECT DISTINCT table_name FROM audit_log ORDER BY table_name");
$users_result = $conn->query("SELECT DISTINCT user_role FROM audit_log ORDER BY user_role");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Audit Log</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .wrap{max-width:1200px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.08)}
    .filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;padding:1rem;background:#f5f5f5;border-radius:8px}
    .with-icon{display:flex;align-items:center;gap:.4rem}
    .with-icon .material-icons{font-size:18px;color:#1976d2}
    .btn-icon{display:inline-flex;align-items:center;gap:.4rem}
    .link-button{display:inline-flex;align-items:center;gap:.4rem;background:#e3eafc;color:#1976d2;padding:.5rem 1rem;border-radius:24px;text-decoration:none}
    .audit-table{width:100%;border-collapse:collapse;margin-top:1rem}
    .audit-table th,.audit-table td{padding:8px 12px;text-align:left;border-bottom:1px solid #ddd}
    .audit-table th{background:#f5f5f5;font-weight:600}
    .audit-table tr:hover{background:#f9f9f9}
    .json-data{max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;color:#1976d2}
    .json-data:hover{text-decoration:underline}
    .action-badge{padding:2px 8px;border-radius:12px;font-size:12px;font-weight:500}
    .action-CREATE{background:#e8f5e8;color:#2e7d32}
    .action-UPDATE{background:#fff3e0;color:#f57c00}
    .action-DELETE{background:#ffebee;color:#d32f2f}
    .action-LOGIN{background:#e3f2fd;color:#1976d2}
  </style>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
  <div class="wrap">
    <img src="lwb.png" alt="LWB Logo" style="max-width:100px;display:block;margin:0 auto 1rem auto;">
    <h2 class="with-icon"><span class="material-icons">history</span> Admin Audit Log</h2>
    
    <!-- Filters -->
    <div class="filters">
      <form method="get" style="display:contents">
        <div>
          <label for="action" class="with-icon"><span class="material-icons">filter_list</span>Action</label>
          <select id="action" name="action">
            <option value="">All Actions</option>
            <?php while($row = $actions_result->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($row['action'] ?? ''); ?>" <?php echo $filter_action === ($row['action'] ?? '') ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($row['action'] ?? ''); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div>
          <label for="table" class="with-icon"><span class="material-icons">table_chart</span>Table</label>
          <select id="table" name="table">
            <option value="">All Tables</option>
            <?php while($row = $tables_result->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($row['table_name'] ?? ''); ?>" <?php echo $filter_table === ($row['table_name'] ?? '') ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($row['table_name'] ?? ''); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div>
          <label for="user" class="with-icon"><span class="material-icons">person</span>User</label>
          <select id="user" name="user">
            <option value="">All Users</option>
            <?php while($row = $users_result->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($row['user_role'] ?? ''); ?>" <?php echo $filter_user === ($row['user_role'] ?? '') ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($row['user_role'] ?? ''); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div>
          <label for="date_from" class="with-icon"><span class="material-icons">date_range</span>From Date</label>
          <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
        </div>
        
        <div>
          <label for="date_to" class="with-icon"><span class="material-icons">date_range</span>To Date</label>
          <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
        </div>
        
        <div>
          <label for="limit" class="with-icon"><span class="material-icons">list</span>Limit</label>
          <select id="limit" name="limit">
            <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50 records</option>
            <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100 records</option>
            <option value="200" <?php echo $limit === 200 ? 'selected' : ''; ?>>200 records</option>
            <option value="500" <?php echo $limit === 500 ? 'selected' : ''; ?>>500 records</option>
          </select>
        </div>
        
        <div style="display:flex;align-items:end;gap:.5rem">
          <button type="submit" class="btn-icon link-button"><span class="material-icons">search</span>Filter</button>
          <a href="admin_audit_log.php" class="btn-icon link-button" style="background:#ffebee;color:#d32f2f"><span class="material-icons">clear</span>Clear Filters</a>
        </div>
      </form>
    </div>
    
    <!-- Results -->
    <div style="margin-bottom:1rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
      <a href="index.php" class="btn-icon link-button"><span class="material-icons">arrow_back</span>Back to Dashboard</a>
      <a href="test_audit_log.php" class="btn-icon link-button" style="background:#e8f5e8;color:#2e7d32"><span class="material-icons">add</span>Add Test Data</a>
      <a href="logout.php" class="btn-icon link-button" style="background:#ffebee;color:#d32f2f"><span class="material-icons">logout</span>Logout</a>
      
      <?php if ($filter_action || $filter_table || $filter_user || $filter_date_from || $filter_date_to): ?>
        <div style="background:#f0f0f0;padding:.5rem 1rem;border-radius:20px;font-size:.9rem;color:#666">
          <strong>Active Filters:</strong>
          <?php if ($filter_action): ?><span style="background:#e3f2fd;color:#1976d2;padding:2px 8px;border-radius:12px;margin:0 4px;font-size:.8rem">Action: <?php echo htmlspecialchars($filter_action); ?></span><?php endif; ?>
          <?php if ($filter_table): ?><span style="background:#e8f5e8;color:#2e7d32;padding:2px 8px;border-radius:12px;margin:0 4px;font-size:.8rem">Table: <?php echo htmlspecialchars($filter_table); ?></span><?php endif; ?>
          <?php if ($filter_user): ?><span style="background:#fff3e0;color:#f57c00;padding:2px 8px;border-radius:12px;margin:0 4px;font-size:.8rem">User: <?php echo htmlspecialchars($filter_user); ?></span><?php endif; ?>
          <?php if ($filter_date_from): ?><span style="background:#f3e5f5;color:#7b1fa2;padding:2px 8px;border-radius:12px;margin:0 4px;font-size:.8rem">From: <?php echo htmlspecialchars($filter_date_from); ?></span><?php endif; ?>
          <?php if ($filter_date_to): ?><span style="background:#f3e5f5;color:#7b1fa2;padding:2px 8px;border-radius:12px;margin:0 4px;font-size:.8rem">To: <?php echo htmlspecialchars($filter_date_to); ?></span><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    
    <table class="audit-table">
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>User</th>
          <th>Action</th>
          <th>Table</th>
          <th>Record ID</th>
          <th>Old Values</th>
          <th>New Values</th>
          <th>IP Address</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['timestamp'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($row['user_role'] ?? ''); ?></td>
              <td><span class="action-badge action-<?php echo htmlspecialchars($row['action'] ?? ''); ?>"><?php echo htmlspecialchars($row['action'] ?? ''); ?></span></td>
              <td><?php echo htmlspecialchars($row['table_name'] ?? ''); ?></td>
              <td><?php echo $row['record_id'] ? htmlspecialchars($row['record_id']) : 'N/A'; ?></td>
              <td>
                <?php if ($row['old_values']): ?>
                  <span class="json-data" onclick="showJson('<?php echo htmlspecialchars($row['old_values'] ?? ''); ?>')">View JSON</span>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
              <td>
                <?php if ($row['new_values']): ?>
                  <span class="json-data" onclick="showJson('<?php echo htmlspecialchars($row['new_values'] ?? ''); ?>')">View JSON</span>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($row['ip_address'] ?? ''); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align:center;padding:2rem;color:#666">No audit log entries found</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <script>
    function showJson(jsonString) {
      try {
        const jsonObj = JSON.parse(jsonString);
        const formatted = JSON.stringify(jsonObj, null, 2);
        alert('JSON Data:\n\n' + formatted);
      } catch (e) {
        alert('Raw Data:\n\n' + jsonString);
      }
    }
  </script>
</body>
</html>
