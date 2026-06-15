<?php
require_once 'config/bootstrap.php';
if (is_logged_in()) redirect('/gym-pro/views/user/dashboard.php');
$error = ''; $success = '';
if (is_post()) {
    verify_csrf();
    $name = post('name'); $email = post('email'); $phone = post('phone'); $password = $_POST['password'] ?? '';
    if ($name && filter_var($email,FILTER_VALIDATE_EMAIL) && strlen($password)>=6) {
        try {
            db()->prepare("INSERT INTO users (name,email,password,phone,role,membership_status) VALUES (:n,:e,:p,:ph,'user','pending')")
               ->execute(['n'=>$name,'e'=>$email,'p'=>password_hash($password,PASSWORD_BCRYPT),'ph'=>$phone]);
            notify_admins('New Member Registered',$name.' created a member account.','general');
            $success = 'Account created! You can now login.';
        } catch (PDOException $ex) { $error = 'Email already registered.'; }
    } else { $error = 'Please enter valid details. Password must be at least 6 characters.'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register — MSP GYM</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-pro/assets/css/main.css">
  <link rel="stylesheet" href="/gym-pro/assets/css/auth.css">
</head>
<body class="auth-wrap">
<div class="auth-card">
  <div class="auth-logo-circle">&#43;</div>
  <div class="auth-brand" style="text-align:center">Join MSP GYM</div>
  <p class="auth-sub" style="text-align:center">Create your member profile</p>
  <?php if ($error): ?><div class="reason-alert"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="flash flash-success"><span class="flash-icon">&#10003;</span><?= e($success) ?></div><?php endif; ?>
  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-group"><label class="form-label">Full Name *</label><input name="name" class="form-control" required placeholder="John Smith"></div>
    <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required placeholder="you@email.com"></div>
    <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-control" placeholder="+94 77 000 0000"></div>
    <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required placeholder="Min. 6 characters"></div>
    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Create Account</button>
  </form>
  <p class="auth-link">Already registered? <a href="/gym-pro/login.php">Sign in</a></p>
</div>
</body>
</html>