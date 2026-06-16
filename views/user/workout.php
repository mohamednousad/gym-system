<?php
require_once '../../config/bootstrap.php';
require_user();
define('PAGE_TITLE', 'Workout Plans');
define('PAGE_SUB', 'Browse and select your training program');
$pdo = db();
$user = current_user();
if (is_post()) {
    verify_csrf();
    $plan_id = (int)post('workout_plan_id');
    $plan_s = $pdo->prepare("SELECT * FROM workout_plans WHERE id=:id AND status='active'"); $plan_s->execute(['id'=>$plan_id]); $plan=$plan_s->fetch();
    if ($plan) {
        $pdo->prepare("UPDATE user_workout_plans SET status='paused' WHERE user_id=:uid AND status='active'")->execute(['uid'=>$user['id']]);
        $end = $plan['duration_weeks'] ? date('Y-m-d', strtotime('+'.((int)$plan['duration_weeks']*7).' days')) : null;
        $pdo->prepare('INSERT INTO user_workout_plans (user_id,workout_plan_id,start_date,end_date,status) VALUES (:uid,:pid,CURDATE(),:end,"active")')->execute(['uid'=>$user['id'],'pid'=>$plan_id,'end'=>$end]);
        notify((int)$user['id'],'Workout Selected','You started '.$plan['plan_name'].'.','workout');
        notify_admins('Workout Selected',$user['name'].' started '.$plan['plan_name'].'.','workout');
        flash('Workout plan started!');
    }
    redirect('workout.php');
}
$active_wp = $pdo->prepare('SELECT uwp.*,w.plan_name,w.goal,w.difficulty,w.duration_weeks,w.days_per_week,w.notes,t.name trainer FROM user_workout_plans uwp JOIN workout_plans w ON w.id=uwp.workout_plan_id LEFT JOIN trainers t ON t.id=w.trainer_id WHERE uwp.user_id=:uid AND uwp.status="active" LIMIT 1'); $active_wp->execute(['uid'=>$user['id']]); $active_wp=$active_wp->fetch();
$all_plans = $pdo->query('SELECT w.*,t.name trainer FROM workout_plans w LEFT JOIN trainers t ON t.id=w.trainer_id WHERE w.status="active" ORDER BY w.created_at DESC')->fetchAll();
include APP_ROOT . '/views/includes/head_user.php';
?>
<div class="page-header"><div><h2>Workout Plans</h2><p>Select a plan to get started</p></div></div>
<?php if ($active_wp): ?>
<div class="card" style="margin-bottom:20px;border-color:var(--primary)">
  <div class="card-header"><span class="card-title" style="color:var(--primary)">Active Plan: <?= e($active_wp['plan_name']) ?></span><?= badge_status($active_wp['difficulty']) ?></div>
  <div class="form-row">
    <div><span class="text-muted" style="font-size:12px">Goal</span><div style="font-weight:600"><?= e($active_wp['goal']??'—') ?></div></div>
    <div><span class="text-muted" style="font-size:12px">Trainer</span><div style="font-weight:600"><?= e($active_wp['trainer']??'Unassigned') ?></div></div>
    <div><span class="text-muted" style="font-size:12px">Duration</span><div style="font-weight:600"><?= e($active_wp['duration_weeks']??'—') ?> weeks</div></div>
    <div><span class="text-muted" style="font-size:12px">Days/Week</span><div style="font-weight:600"><?= e($active_wp['days_per_week']??'—') ?></div></div>
  </div>
  <?php if ($active_wp['notes']): ?><p style="margin-top:12px;font-size:13px;color:var(--muted)"><?= e($active_wp['notes']) ?></p><?php endif; ?>
</div>
<?php endif; ?>
<div class="grid-3">
  <?php foreach ($all_plans as $p): ?>
  <div class="card" style="<?= $active_wp && $active_wp['workout_plan_id']==$p['id'] ? 'border-color:var(--primary)' : '' ?>">
    <div style="font-weight:700;margin-bottom:6px"><?= e($p['plan_name']) ?></div>
    <?= badge_status($p['difficulty']) ?>
    <div style="margin-top:10px;font-size:12px;color:var(--muted)"><?= e($p['goal']??'') ?> &bull; <?= e($p['duration_weeks']??'?') ?> wks &bull; <?= e($p['days_per_week']??'?') ?> days/wk</div>
    <?php if ($p['trainer']): ?><div style="font-size:12px;margin-top:4px">Trainer: <?= e($p['trainer']) ?></div><?php endif; ?>
    <?php if ($p['notes']): ?><p style="font-size:12px;color:var(--muted);margin-top:8px"><?= e(substr($p['notes'],0,80)) ?>...</p><?php endif; ?>
    <form method="POST" style="margin-top:14px">
      <?= csrf_field() ?>
      <input type="hidden" name="workout_plan_id" value="<?= $p['id'] ?>">
      <button class="btn btn-primary btn-sm" style="width:100%"><?= ($active_wp && $active_wp['workout_plan_id']==$p['id']) ? 'Active' : 'Start This Plan' ?></button>
    </form>
  </div>
  <?php endforeach; ?>
  <?php if(empty($all_plans)): ?><div class="empty-state"><p>No workout plans available yet</p></div><?php endif; ?>
</div>
<?php include APP_ROOT . '/views/includes/foot_user.php'; ?>