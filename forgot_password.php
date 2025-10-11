<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Only Admin can reset others' passwords
if (current_role() !== null && current_role() !== 'Admin') {
    header('Location: index.php');
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = isset($_POST['role']) ? (string)$_POST['role'] : '';
    if (!isset(get_role_tabs()[$role])) {
        $msg = 'Invalid role';
    } else if (reset_user_password_to_default($conn, $role)) {
        $msg = 'Password reset to default (12345) for role: ' . htmlspecialchars($role);
    } else {
        $msg = 'Failed to reset password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>.wrap{max-width:420px;margin:4rem auto;background:#fff;padding:1.5rem;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.08);text-align:center}.with-icon{display:flex;align-items:center;gap:.4rem;text-align:left}.with-icon .material-icons{font-size:18px;color:#1976d2}.btn-icon{display:inline-flex;align-items:center;gap:.4rem}.link-button{display:inline-flex;align-items:center;gap:.4rem;background:#e3eafc;color:#1976d2;padding:.5rem 1rem;border-radius:24px;text-decoration:none}</style>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
  <div class="wrap">
    <img src="lwb.png" alt="Logo" style="max-width:100px;margin-bottom:.5rem">
    <h2><span class="material-icons" style="vertical-align:middle;font-size:24px;color:#1976d2">restart_alt</span> Reset Password</h2>
    <?php if ($msg): ?><div style="margin:.5rem 0;color:#1976d2"><?php echo $msg; ?></div><?php endif; ?>
    <form method="post">
      <label for="role" class="with-icon"><span class="material-icons">badge</span>Select role</label>
      <select id="role" name="role" required>
        <?php foreach (array_keys(get_role_tabs()) as $r): ?>
          <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-icon link-button"><span class="material-icons">refresh</span>Reset to 12345</button>
      <a href="login.php" class="btn-icon link-button" style="margin-left:1rem"><span class="material-icons">arrow_back</span>Back to login</a>
    </form>
  </div>
</body>
</html>

