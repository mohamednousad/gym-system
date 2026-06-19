<?php
require_once '../../config/navigation.php';
require_user();
define('PAGE_TITLE', 'Membership');
define('PAGE_SUB', 'Choose your subscription plan');
$pdo = db();
$user = current_user();
if (is_post()) {
    verify_csrf();
    $plan_id = (int)post('plan_id');
    $plan_s = $pdo->prepare("SELECT * FROM membership_plans WHERE id=:id AND status='active'"); $plan_s->execute(['id'=>$plan_id]); $plan=$plan_s->fetch();
    if ($plan) {
        notify((int)$user['id'],'Plan Request Sent','Your '.$plan['name'].' request was sent to admin. Please complete payment.','renewal');
        notify_admins('Membership Requested',$user['name'].' requested '.$plan['name'].'.' ,'renewal');
        flash('Plan requested! Admin will process payment.');
    }
    redirect('membership.php');
}
$plans = $pdo->query("SELECT * FROM membership_plans WHERE status='active' ORDER BY price")->fetchAll();
include APP_ROOT . '/views/includes/head_user.php';
?>
<div class="page-header"><div><h2>Membership Plans</h2><p>Current plan: <strong><?= e($user['membership_plan']??'None') ?></strong> &mdash; <?= badge_status($user['membership_status']??'pending') ?></p></div></div>
<?php if ($user['renewal_date']): ?>
<div class="flash flash-info" style="margin-bottom:20px;">Your membership renews on <strong><?= e($user['renewal_date']) ?></strong></div>
<?php endif; ?>
<div class="grid-3">
  <?php foreach ($plans as $p): ?>
  <div class="card" style="<?= $user['membership_plan']===$p['name'] ? 'border-color:var(--primary)' : '' ?>">
    <?php if($user['membership_plan']===$p['name']): ?><div class="badge badge-success" style="margin-bottom:10px">Current Plan</div><?php endif; ?>
    <div style="font-size:22px;font-weight:800;color:var(--primary);margin-bottom:4px"><?= money($p['price']) ?></div>
    <div style="font-weight:700;font-size:16px;margin-bottom:4px"><?= e($p['name']) ?></div>
    <div class="text-muted" style="font-size:13px;margin-bottom:12px"><?= e($p['duration_days']) ?> days</div>
    <p style="font-size:12px;color:var(--muted);margin-bottom:14px"><?= e($p['description']??'') ?></p>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
      <button class="btn btn-primary" style="width:100%" <?= $user['membership_plan']===$p['name']?'disabled':'' ?>>
        <?= $user['membership_plan']===$p['name'] ? 'Active' : 'Request This Plan' ?>
      </button>
    </form>
  </div>
  <?php endforeach; ?>
</div>
<?php include APP_ROOT . '/views/includes/foot_user.php'; ?>