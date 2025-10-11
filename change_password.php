<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_login();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = isset($_POST['current']) ? (string)$_POST['current'] : '';
    $new = isset($_POST['new']) ? (string)$_POST['new'] : '';
    $confirm = isset($_POST['confirm']) ? (string)$_POST['confirm'] : '';
    $role = (string)$_SESSION['role'];

    if ($new !== $confirm) {
        $msg = 'New passwords do not match';
    } else if (!login($role, $current)) { // re-use login check for current password
        $msg = 'Current password is incorrect';
    } else {
        if (set_user_password($conn, $role, $new)) {
            $msg = 'Password changed successfully';
        } else {
            $msg = 'Failed to change password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Change Password</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>.wrap{max-width:480px;margin:3rem auto;background:#fff;padding:1.5rem;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.08)}.with-icon{display:flex;align-items:center;gap:.4rem}.with-icon .material-icons{font-size:18px;color:#1976d2}.btn-icon{display:inline-flex;align-items:center;gap:.4rem}.link-button{display:inline-flex;align-items:center;gap:.4rem;background:#e3eafc;color:#1976d2;padding:.5rem 1rem;border-radius:24px;text-decoration:none}</style>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
  <div class="wrap">
    <h2><span class="material-icons" style="vertical-align:middle;font-size:24px;color:#1976d2">lock</span> Change Password - <?php echo welcome_text(); ?></h2>
    <?php if ($msg): ?><div style="margin:.5rem 0;color:#1976d2"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <form method="post">
      <label for="current" class="with-icon"><span class="material-icons">key</span>Current password</label>
      <input type="password" id="current" name="current" required>
      <label for="new" class="with-icon"><span class="material-icons">password</span>New password</label>
      <input type="password" id="new" name="new" required>
      <label for="confirm" class="with-icon"><span class="material-icons">check_circle</span>Confirm new password</label>
      <input type="password" id="confirm" name="confirm" required>
      <button type="submit" class="btn-icon link-button"><span class="material-icons">done</span>Change Password</button>
      <a href="index.php" class="btn-icon link-button" style="margin-left:1rem"><span class="material-icons">arrow_back</span>Back</a>
    </form>
  </div>
</body>
</html>

