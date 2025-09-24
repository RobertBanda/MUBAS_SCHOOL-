<?php
session_start();

const AUTH_PASSWORD = '12345';

function get_role_tabs(): array {
    return [
        'Billing Officer' => ['meterreadings', 'bills'],
        'Field Technical' => ['complaints', 'store'],
        'Manager' => ['store_transactions', 'balances', 'customers'],
        'Creak' => ['store', 'store_transactions'],
        'Meter reader' => ['meterreadings', 'store'],
        'Customer Care' => ['customers', 'connections', 'meters', 'payments', 'balances', 'complaints', 'calllogs', 'bills', 'meterreadings'],
    ];
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
                return true;
            }
            return false;
        }
        // No row for role -> accept default password
        if ($password === AUTH_PASSWORD) {
            $_SESSION['role'] = $role;
            return true;
        }
        return false;
    }
    // No DB available, fallback to shared password
    if ($password !== AUTH_PASSWORD) {
        return false;
    }
    $_SESSION['role'] = $role;
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


