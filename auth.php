<?php
session_start();

const AUTH_PASSWORD = '12345';

function get_role_tabs(): array {
    // Base role-to-tabs mapping
    $roles = [
        'Billing Officer' => ['meterreadings', 'bills'],
        'Field Technical' => ['complaints', 'store'],
        'Manager' => ['store_transactions', 'balances', 'customers'],
        'Creak' => ['store', 'store_transactions'],
        'Meter reader' => ['meterreadings', 'store'],
        'Customer Care' => ['customers', 'connections', 'meters', 'payments', 'balances', 'complaints', 'calllogs', 'bills', 'meterreadings'],
    ];

    // Admin role: allow all tabs (except the default 'dashboard' which is always allowed)
    $allTabs = array_keys(get_all_tabs_labels());
    $adminTabs = [];
    foreach ($allTabs as $tab) {
        if ($tab !== 'dashboard') {
            $adminTabs[] = $tab;
        }
    }
    $roles['Admin'] = $adminTabs;

    return $roles;
}

function get_all_tabs_labels(): array {
    return [
        'dashboard' => 'Dashboard',
        'customers' => 'Customers',
        'meters' => 'Meters',
        'connections' => 'Connections',
        'meterreadings' => 'Meter Readings',
        'bills' => 'Bills',
        'payments' => 'Payments',
        'balances' => 'Customer Balances',
        'complaints' => 'Complaints',
        'calllogs' => 'Call Logs',
        'staff' => 'Staff',
        'store' => 'Store',
        'store_transactions' => 'Store Transactions',
    ];
}

function require_login(): void {
    if (!isset($_SESSION['role'])) {
        header('Location: login.php');
        exit;
    }
}

function login(string $role, string $password): bool {
    $roles = get_role_tabs();
    if (!isset($roles[$role])) {
        return false;
    }
    // If DB connection is available, check per-role password; fallback to default
    $useDb = isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli;
    if ($useDb) {
        $mysqli = $GLOBALS['conn'];
        ensure_users_table($mysqli);
        $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE role = ? LIMIT 1");
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $hash = $row['password_hash'];
            if ($hash && password_verify($password, $hash)) {
                $_SESSION['role'] = $role;
                $_SESSION['login_time'] = time();
                // Log login action
                if (function_exists('log_audit_action_global')) {
                    log_audit_action_global('LOGIN', 'users', null, null, ['role' => $role]);
                }
                return true;
            }
            // If custom password fails, still allow default password as fallback
            if ($password === AUTH_PASSWORD) {
                $_SESSION['role'] = $role;
                $_SESSION['login_time'] = time();
                // Log login action
                if (function_exists('log_audit_action_global')) {
                    log_audit_action_global('LOGIN', 'users', null, null, ['role' => $role]);
                }
                return true;
            }
            return false;
        }
        // No row for role -> accept default password
        if ($password === AUTH_PASSWORD) {
            $_SESSION['role'] = $role;
            $_SESSION['login_time'] = time();
            // Log login action
            if (function_exists('log_audit_action') && isset($GLOBALS['conn'])) {
                log_audit_action($GLOBALS['conn'], 'LOGIN', 'users', null, null, ['role' => $role]);
            }
            return true;
        }
        return false;
    }
    // No DB available, fallback to shared password
    if ($password !== AUTH_PASSWORD) {
        return false;
    }
    $_SESSION['role'] = $role;
    $_SESSION['login_time'] = time();
    // Log login action
    if (function_exists('log_audit_action') && isset($GLOBALS['conn'])) {
        log_audit_action($GLOBALS['conn'], 'LOGIN', 'users', null, null, ['role' => $role]);
    }
    return true;
}

function ensure_users_table(mysqli $conn): void {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS users (\n".
        "  role VARCHAR(64) PRIMARY KEY,\n".
        "  password_hash VARCHAR(255) NULL\n".
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_audit_log_table(mysqli $conn): void {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS audit_log (\n".
        "  id INT AUTO_INCREMENT PRIMARY KEY,\n".
        "  user_role VARCHAR(64) NOT NULL,\n".
        "  action VARCHAR(50) NOT NULL,\n".
        "  table_name VARCHAR(50) NOT NULL,\n".
        "  record_id INT,\n".
        "  old_values TEXT,\n".
        "  new_values TEXT,\n".
        "  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n".
        "  ip_address VARCHAR(45),\n".
        "  user_agent TEXT\n".
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function log_audit_action(mysqli $conn, string $action, string $table_name, int $record_id = null, array $old_values = null, array $new_values = null): void {
    ensure_audit_log_table($conn);
    
    $user_role = current_role() ?? 'Unknown';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $old_json = $old_values ? json_encode($old_values) : null;
    $new_json = $new_values ? json_encode($new_values) : null;
    
    $stmt = $conn->prepare("INSERT INTO audit_log (user_role, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssissss', $user_role, $action, $table_name, $record_id, $old_json, $new_json, $ip_address, $user_agent);
    $stmt->execute();
}

// Helper function to log audit action with global connection
function log_audit_action_global(string $action, string $table_name, int $record_id = null, array $old_values = null, array $new_values = null): void {
    if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
        log_audit_action($GLOBALS['conn'], $action, $table_name, $record_id, $old_values, $new_values);
    }
}

function set_user_password(mysqli $conn, string $role, string $newPassword): bool {
    ensure_users_table($conn);
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users(role, password_hash) VALUES(?, ?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash)");
    $stmt->bind_param('ss', $role, $hash);
    return $stmt->execute();
}

function reset_user_password_to_default(mysqli $conn, string $role): bool {
    return set_user_password($conn, $role, AUTH_PASSWORD);
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function role_allowed_tabs(string $role): array {
    $map = get_role_tabs();
    return $map[$role] ?? [];
}

function first_allowed_tab(string $role): string {
    $allowed = role_allowed_tabs($role);
    return $allowed[0] ?? 'dashboard';
}

function is_tab_allowed(string $role, string $tab): bool {
    if ($tab === 'dashboard') { return true; }
    return in_array($tab, role_allowed_tabs($role), true);
}


// Convenience helpers
function current_role(): ?string {
    return $_SESSION['role'] ?? null;
}

function welcome_text(): string {
    $role = current_role();
    if (!$role) { return 'Welcome'; }
    // Normalize spacing/case if ever needed; currently keys are display-ready
    return 'Welcome, ' . $role;
}


