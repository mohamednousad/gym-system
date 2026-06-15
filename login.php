<?php
require_once 'config/bootstrap.php';
if (is_logged_in()) redirect(is_admin() ? '/gym-system/views/admin/dashboard.php' : '/gym-system/views/user/dashboard.php');
$error = '';
if (is_post()) {
    verify_csrf();
    $email = post('email'); $password = $_POST['password'] ?? '';
    $s = db()->prepare('SELECT * FROM users WHERE email=:email LIMIT 1'); $s->execute(['email'=>$email]); $user=$s->fetch();
    if ($user && password_verify($password, $user['password'])) {
        login_user($user);
        redirect($user['role']==='admin' ? '/gym-system/views/admin/dashboard.php' : '/gym-system/views/user/dashboard.php');
    }
    $error = 'Invalid email or password.';
}
$reason_msg = reason_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — MSP GYM</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-system/assets/css/main.css">
  <link rel="stylesheet" href="/gym-system/assets/css/auth.css">
</head>
<body class="auth-wrap">
<div class="auth-card">
  <div class="auth-logo-circle">M</div>
  <div class="auth-brand" style="text-align:center">MSP GYM</div>
  <p class="auth-sub" style="text-align:center">Sign in to your account</p>
  <?php if ($reason_msg): ?><div class="reason-alert"><?= e($reason_msg) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="reason-alert"><?= e($error) ?></div><?php endif; ?>
  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" required placeholder="you@example.com" autofocus></div>
    <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required placeholder="••••••••"></div>
    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Sign In</button>
  </form>
  <p class="auth-link">No account? <a href="/gym-system/register.php">Register as member</a></p>
</div>
</body>
</html>