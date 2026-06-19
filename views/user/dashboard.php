<?php
require_once '../../config/navigation.php';
require_user();
define('PAGE_TITLE', 'My Dashboard');
define('PAGE_SUB', 'Your fitness overview');
$pdo = db();
$user = current_user();
if (is_post()) {
    verify_csrf();
    $action = post('action');
    if ($action === 'checkin') {
        $exists = $pdo->prepare('SELECT id FROM attendance WHERE user_id=:uid AND attendance_date=CURDATE()');
        $exists->execute(['uid'=>$user['id']]);
        if ($exists->fetch()) {
            flash('Already checked in today.','error');
        } else {
            $status = (int)date('H') >= 9 ? 'late' : 'present';
            $pdo->prepare('INSERT INTO attendance (user_id,attendance_date,check_in,status) VALUES (:uid,CURDATE(),NOW(),:st)')->execute(['uid'=>$user['id'],'st'=>$status]);
            notify((int)$user['id'],'Attendance Marked','Checked in for today.','attendance');
            notify_admins('Attendance Marked',$user['name'].' checked in.','attendance');
            flash('Attendance marked!');
        }
    } elseif ($action === 'checkout') {
        $pdo->prepare("UPDATE attendance SET check_out=NOW(),duration_minutes=TIMESTAMPDIFF(MINUTE,check_in,NOW()) WHERE user_id=:uid AND attendance_date=CURDATE() AND check_out IS NULL")->execute(['uid'=>$user['id']]);
        flash('Checked out. Great session!');
    }
    redirect('dashboard.php');
}
$today = $pdo->prepare('SELECT * FROM attendance WHERE user_id=:uid AND attendance_date=CURDATE()');
$today->execute(['uid'=>$user['id']]);
$today = $today->fetch();
$active_wp = $pdo->prepare('SELECT uwp.*,w.plan_name,w.goal,w.difficulty,t.name trainer FROM user_workout_plans uwp JOIN workout_plans w ON w.id=uwp.workout_plan_id LEFT JOIN trainers t ON t.id=w.trainer_id WHERE uwp.user_id=:uid AND uwp.status="active" ORDER BY uwp.id DESC LIMIT 1');
$active_wp->execute(['uid'=>$user['id']]);
$active_wp = $active_wp->fetch();
$recent_att = $pdo->prepare('SELECT * FROM attendance WHERE user_id=:uid ORDER BY attendance_date DESC LIMIT 6');
$recent_att->execute(['uid'=>$user['id']]);
$recent_att = $recent_att->fetchAll();
$notes = $pdo->prepare('SELECT * FROM notifications WHERE user_id=:uid AND is_read=0 ORDER BY created_at DESC LIMIT 5');
$notes->execute(['uid'=>$user['id']]);
$notes = $notes->fetchAll();
include APP_ROOT . '/views/includes/head_user.php';
?>

<div class="quick-bar">
  <div>
    <div style="font-weight:700;font-size:16px;">Today's Attendance</div>
    <div class="text-muted" style="font-size:13px;"><?= $today ? 'Checked in at ' . e(substr($today['check_in'],11,5)) : 'Not checked in yet' ?></div>
  </div>
  <form method="POST">
    <?= csrf_field() ?>
    <?php if (!$today): ?>
      <input type="hidden" name="action" value="checkin">
      <button class="btn btn-primary"><i class="fa-solid fa-check"></i> Check In Now</button>
    <?php elseif (!$today['check_out']): ?>
      <input type="hidden" name="action" value="checkout">
      <button class="btn btn-success"><i class="fa-solid fa-hand"></i> Check Out</button>
    <?php else: ?>
      <span class="badge badge-success"><i class="fa-solid fa-check-circle"></i> Completed Today</span>
    <?php endif; ?>
  </form>
</div>

<div class="stat-cards">
  <div class="stat-card">
    <div class="stat-val"><?= e($user['membership_plan'] ?: 'None') ?></div>
    <div class="stat-label">Membership Plan</div>
    <div><?= badge_status($user['membership_status']) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-val"><?= e($user['renewal_date'] ?: '—') ?></div>
    <div class="stat-label">Renewal Date</div>
  </div>
  <div class="stat-card">
    <div class="stat-val"><?= e($active_wp['plan_name'] ?? 'None') ?></div>
    <div class="stat-label">Active Workout</div>
  </div>
  <div class="stat-card">
    <div class="stat-val"><?= count($notes) ?></div>
    <div class="stat-label">Unread Alerts</div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-history"></i> Recent Attendance</span>
      <a href="/gym-system/views/user/attendance.php" class="btn btn-sm btn-secondary">View All</a>
    </div>
    <div class="table-wrap">
      <table>
        <tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr>
        <?php foreach ($recent_att as $a): ?>
        <tr><td><?= e($a['attendance_date']) ?></td><td><?= e(substr($a['check_in'],11,5)) ?></td><td><?= $a['check_out'] ? e(substr($a['check_out'],11,5)) : '—' ?></td><td><?= badge_status($a['status']) ?></td></tr>
        <?php endforeach; ?>
        <?php if(empty($recent_att)): ?><tr><td colspan="4" class="empty-state">No attendance history</td></tr><?php endif; ?>
      </table>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-bell"></i> Unread Notifications</span>
      <a href="/gym-system/views/user/notifications.php" class="btn btn-sm btn-secondary">View All</a>
    </div>
    <?php foreach ($notes as $n): ?>
    <div class="notif-item unread">
      <div class="notif-title"><?= e($n['title']) ?></div>
      <div class="notif-msg"><?= e($n['message']) ?></div>
      <div class="notif-meta"><?= badge_status($n['type']) ?><span class="text-muted" style="font-size:11px;"><?= time_ago($n['created_at']) ?></span></div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($notes)): ?><div class="empty-state"><p><i class="fa-solid fa-party-horn"></i> All caught up</p></div><?php endif; ?>
  </div>
</div>

<?php include APP_ROOT . '/views/includes/foot_user.php'; ?>