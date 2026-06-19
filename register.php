<?php
require_once 'config/navigation.php';
if (is_logged_in()) redirect('/gym-system/views/user/dashboard.php');
$error = ''; $success = '';
if (is_post()) {
    verify_csrf();
    $name = trim(post('name')); $email = trim(post('email')); $phone = trim(post('phone')); $password = $_POST['password'] ?? '';
    $errors = [];
    if (!$name || strlen($name) < 3) $errors[] = 'Full name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($phone && !preg_match('/^\+?[\d\s\-]{7,15}$/', $phone)) $errors[] = 'Please enter a valid phone number.';
    if ($errors) {
        $error = implode('<br>', $errors);
    } else {
        try {
            db()->prepare("INSERT INTO users (name,email,password,phone,role,membership_status) VALUES (:n,:e,:p,:ph,'user','pending')")
               ->execute(['n'=>$name,'e'=>$email,'p'=>password_hash($password,PASSWORD_BCRYPT),'ph'=>$phone]);
            notify_admins('New Member Registered',$name.' created a member account.','general');
            $success = 'Account created! Login to your account.';
        } catch (PDOException $ex) { $error = 'Email already registered.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register — MSP GYM</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-system/assets/css/main.css">
  <link rel="stylesheet" href="/gym-system/assets/css/auth.css">
</head>
<body class="auth-wrap">
<div class="auth-card">
  <div class="auth-logo-circle">&#43;</div>
  <div class="auth-brand" style="text-align:center">Join MSP GYM</div>
  <p class="auth-sub" style="text-align:center">Create your member profile</p>
  <?php if ($error): ?><div class="reason-alert"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div class="flash flash-success"><span class="flash-icon">&#10003;</span><?= e($success) ?></div><?php endif; ?>
  <form method="POST" id="regForm" novalidate>
    <?= csrf_field() ?>
    <div class="form-group">
      <label class="form-label">Full Name *</label>
      <input name="name" id="name" class="form-control" required minlength="2" placeholder="John Smith" value="<?= e(post('name')) ?>">
      <span class="field-error" id="nameErr"></span>
    </div>
    <div class="form-group">
      <label class="form-label">Email *</label>
      <input type="email" name="email" id="email" class="form-control" required placeholder="you@email.com" value="<?= e(post('email')) ?>">
      <span class="field-error" id="emailErr"></span>
    </div>
    <div class="form-group">
      <label class="form-label">Phone</label>
      <input name="phone" id="phone" class="form-control" placeholder="+94 77 000 0000" value="<?= e(post('phone')) ?>">
      <span class="field-error" id="phoneErr"></span>
    </div>
    <div class="form-group">
      <label class="form-label">Password *</label>
      <input type="password" name="password" id="password" class="form-control" required placeholder="Min. 6 characters">
      <span class="field-error" id="passErr"></span>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Create Account</button>
  </form>
  <p class="auth-link">Already have an account? <a href="/gym-system/login.php">Sign in</a></p>
</div>
<style>
.field-error{display:block;color:#e53e3e;font-size:12px;margin-top:4px;min-height:16px;}
.form-control.invalid{border-color:#e53e3e;}
.form-control.valid{border-color:#38a169;}
</style>
<script>
(function(){
  var form=document.getElementById('regForm');
  function show(id,msg){document.getElementById(id).textContent=msg;}
  function mark(el,ok){el.classList.toggle('invalid',!ok);el.classList.toggle('valid',ok);}
  function vName(){var v=document.getElementById('name').value.trim();var ok=v.length>=3;mark(document.getElementById('name'),ok);show('nameErr',ok?'':'Full name must be at least 3 characters.');return ok;}
  function vEmail(){var v=document.getElementById('email').value.trim();var ok=/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);mark(document.getElementById('email'),ok);show('emailErr',ok?'':'Please enter a valid email address.');return ok;}
  function vPhone(){var v=document.getElementById('phone').value.trim();var ok=v===''||/^\+?[\d\s\-]{7,15}$/.test(v);mark(document.getElementById('phone'),ok);show('phoneErr',ok?'':'Please enter a valid phone number.');return ok;}
  function vPass(){var v=document.getElementById('password').value;var ok=v.length>=6;mark(document.getElementById('password'),ok);show('passErr',ok?'':'Password must be at least 6 characters.');return ok;}
  document.getElementById('name').addEventListener('input',vName);
  document.getElementById('email').addEventListener('input',vEmail);
  document.getElementById('phone').addEventListener('input',vPhone);
  document.getElementById('password').addEventListener('input',vPass);
  form.addEventListener('submit',function(e){if(![vName(),vEmail(),vPhone(),vPass()].every(Boolean))e.preventDefault();});
})();
</script>
</body>
</html>