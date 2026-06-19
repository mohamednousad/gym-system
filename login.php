<?php
require_once 'config/navigation.php';
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
    if (!$user) { $error = 'No account found with that email.'; }
    else { $error = 'Incorrect password. Please try again.'; }
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
  <?php if ($error): ?><div class="reason-alert" id="server-error"><?= e($error) ?></div><?php endif; ?>
  <div class="reason-alert" id="client-error" style="display:none"></div>
  <form method="POST" id="loginForm" novalidate>
    <?= csrf_field() ?>
    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" id="loginEmail" class="form-control" required placeholder="you@example.com" autofocus value="<?= e(post('email')) ?>">
      <span class="field-error" id="emailErr" style="color:#e55;font-size:.82rem;display:none"></span>
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <input type="password" name="password" id="loginPassword" class="form-control" required placeholder="••••••••">
      <span class="field-error" id="passErr" style="color:#e55;font-size:.82rem;display:none"></span>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Sign In</button>
  </form>
  <p class="auth-link">Don't have an account? <a href="/gym-system/register.php">Sign Up</a></p>
</div>
<script>
  var form = document.getElementById('loginForm');
  var emailEl = document.getElementById('loginEmail');
  var passEl = document.getElementById('loginPassword');
  var emailErr = document.getElementById('emailErr');
  var passErr = document.getElementById('passErr');
  var clientErr = document.getElementById('client-error');

  function showErr(el, msg) { el.textContent = msg; el.style.display = 'block'; }
  function clearErr(el) { el.textContent = ''; el.style.display = 'none'; }

  emailEl.addEventListener('input', function() { clearErr(emailErr); clearErr(clientErr); });
  passEl.addEventListener('input', function() { clearErr(passErr); clearErr(clientErr); });

  form.addEventListener('submit', function(e) {
    var valid = true;
    clearErr(emailErr); clearErr(passErr); clearErr(clientErr);

    var email = emailEl.value.trim();
    var pass = passEl.value;

    if (!email) {
      showErr(emailErr, 'Email is required.'); valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showErr(emailErr, 'Enter a valid email address.'); valid = false;
    }

    if (!pass) {
      showErr(passErr, 'Password is required.'); valid = false;
    }

    if (!valid) e.preventDefault();
  });
</script>
</body>
</html>