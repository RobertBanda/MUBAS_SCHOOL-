<?php
require_once __DIR__ . '/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (login($role, $password)) {
        $tab = first_allowed_tab($role);
        header('Location: index.php?tab=' . urlencode($tab));
        exit;
    } else {
        $error = 'Invalid role or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - LWB</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .login-wrapper{max-width:420px;margin:5rem auto;background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);text-align:center}
    .login-wrapper img{max-width:120px;margin-bottom:1rem}
    .login-wrapper h2{margin:0 0 1rem 0;color:#1976d2}
    .login-wrapper form{text-align:left}
    .error{color:#d32f2f;margin-bottom:1rem}
    .with-icon{display:flex;align-items:center;gap:.4rem}
    .with-icon .material-icons{font-size:18px;color:#1976d2}
    .btn-icon{display:inline-flex;align-items:center;gap:.4rem}
    .link-button{display:inline-flex;align-items:center;gap:.4rem;background:#e3eafc;color:#1976d2;padding:.5rem 1rem;border-radius:24px;text-decoration:none}
  </style>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script>document.addEventListener('DOMContentLoaded',function(){var p=document.getElementById('password');if(p)p.focus();});</script>
  </head>
<body>
  <div class="login-wrapper">
    <img src="lwb.png" alt="Logo">
    <h2>LWB Management</h2>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
      <label for="role" class="with-icon"><span class="material-icons">badge</span>Role</label>
      <select id="role" name="role" required>
        <?php foreach (array_keys(get_role_tabs()) as $role): ?>
          <option value="<?php echo htmlspecialchars($role); ?>"><?php echo htmlspecialchars($role); ?></option>
        <?php endforeach; ?>
      </select>
      <label for="password" class="with-icon"><span class="material-icons">lock</span>Password</label>
      <input type="password" id="password" name="password" required placeholder="Enter password" />
      <button type="submit" style="width:100%;margin-top:0.5rem" class="btn-icon"><span class="material-icons">login</span>Login</button>
      <div style="margin-top:.5rem;text-align:right"><a href="forgot_password.php" class="btn-icon link-button" style="gap:.25rem"><span class="material-icons">help_outline</span>Forgot password?</a></div>
    </form>
  </div>
</body>
</html>

