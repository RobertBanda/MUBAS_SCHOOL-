<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_login();

$role = current_role();
if ($role !== 'Admin') {
    header('Location: index.php');
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetRole = isset($_POST['target_role']) ? (string)$_POST['target_role'] : '';
    if (!isset(get_role_tabs()[$targetRole])) {
        $msg = 'Invalid role selected';
    } else if (isset($_POST['action']) && $_POST['action'] === 'reset') {
        if (reset_user_password_to_default($conn, $targetRole)) {
            $msg = 'Password reset to default (12345) for role: ' . htmlspecialchars($targetRole);
        } else {
            $msg = 'Failed to reset password';
        }
    } else if (isset($_POST['action']) && $_POST['action'] === 'set') {
        $new = isset($_POST['new']) ? (string)$_POST['new'] : '';
        $confirm = isset($_POST['confirm']) ? (string)$_POST['confirm'] : '';
        if ($new === '' || $confirm === '') {
            $msg = 'Please enter the new password twice';
        } else if ($new !== $confirm) {
            $msg = 'New passwords do not match';
        } else {
            if (set_user_password($conn, $targetRole, $new)) {
                $msg = 'Password updated for role: ' . htmlspecialchars($targetRole);
            } else {
                $msg = 'Failed to update password';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Passwords</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .wrap{max-width:520px;margin:3rem auto;background:#fff;padding:1.5rem;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.08)}
    .grid{display:grid;grid-template-columns:1fr;gap:.75rem}
    .row{display:flex;gap:.5rem;align-items:center}
    .with-icon{display:flex;align-items:center;gap:.4rem}
    .with-icon .material-icons{font-size:18px;color:#1976d2}
    .btn-icon{display:inline-flex;align-items:center;gap:.4rem}
    .link-button{display:inline-flex;align-items:center;gap:.4rem;background:#e3eafc;color:#1976d2;padding:.5rem 1rem;border-radius:24px;text-decoration:none}
  </style>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  </head>
<body>
  <div class="wrap">
    <h2 class="with-icon"><span class="material-icons">admin_panel_settings</span> Admin - Manage Passwords</h2>
    <?php if ($msg): ?><div style="margin:.5rem 0;color:#1976d2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

    <form method="post" class="grid" style="margin-bottom:1rem">
      <label for="target_role" class="with-icon"><span class="material-icons">badge</span>Select role</label>
      <select id="target_role" name="target_role" required>
        <?php foreach (array_keys(get_role_tabs()) as $r): ?>
          <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
        <?php endforeach; ?>
      </select>

      <label for="new" class="with-icon"><span class="material-icons">password</span>New password</label>
      <input type="password" id="new" name="new" placeholder="Enter new password">
      <label for="confirm" class="with-icon"><span class="material-icons">check_circle</span>Confirm new password</label>
      <input type="password" id="confirm" name="confirm" placeholder="Confirm new password">

      <div class="row">
        <button type="submit" name="action" value="set" class="btn-icon link-button"><span class="material-icons">done</span>Set Password</button>
        <button type="submit" name="action" value="reset" class="btn-icon link-button" style="background:#fdecea;color:#d32f2f"><span class="material-icons">restart_alt</span>Reset to 12345</button>
        <a href="index.php" class="btn-icon link-button" style="margin-left:auto"><span class="material-icons">arrow_back</span>Back</a>
      </div>
    </form>
  </div>
</body>
</html>


