<?php
require_once '../../config/bootstrap.php';
require_admin();
define('PAGE_TITLE', 'Dashboard');
define('PAGE_SUB', 'Live overview of gym operations');

$pdo = db();
$counts = [
    'members'  => $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
    'trainers' => $pdo->query("SELECT COUNT(*) FROM trainers WHERE status='active'")->fetchColumn(),
    'plans'    => $pdo->query("SELECT COUNT(*) FROM workout_plans WHERE status='active'")->fetchColumn(),
    'today'    => $pdo->query("SELECT COUNT(*) FROM attendance WHERE attendance_date=CURDATE()")->fetchColumn(),
    'revenue'  => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(payment_date)=MONTH(CURDATE()) AND YEAR(payment_date)=YEAR(CURDATE())")->fetchColumn(),
    'pending'  => $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND membership_status='pending'")->fetchColumn(),
];
$expired = $pdo->query("SELECT id FROM users WHERE role='user' AND membership_status!='expired' AND renewal_date < CURDATE()")->fetchAll();
foreach ($expired as $eu) {
    $pdo->prepare("UPDATE users SET membership_status='expired' WHERE id=:id")->execute(['id' => $eu['id']]);
    notify((int)$eu['id'], 'Membership Expired', 'Your membership has expired. Please renew.', 'renewal');
}
$recent_attendance = $pdo->query("SELECT a.*,u.name FROM attendance a JOIN users u ON u.id=a.user_id ORDER BY a.check_in DESC LIMIT 8")->fetchAll();
$recent_payments = $pdo->query("SELECT p.*,u.name,mp.name plan_name FROM payments p JOIN users u ON u.id=p.user_id LEFT JOIN membership_plans mp ON mp.id=p.membership_plan_id ORDER BY p.payment_date DESC LIMIT 6")->fetchAll();
include APP_ROOT . '/views/includes/head_admin.php';
?>
<div class="stat-cards">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(56,189,248,0.12);color:var(--info);">&#128101;</div>
    <div class="stat-val"><?= e($counts['members']) ?></div>
    <div class="stat-label">Total Members</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(34,197,94,0.12);color:var(--success);">&#127947;</div>
    <div class="stat-val"><?= e($counts['trainers']) ?></div>
    <div class="stat-label">Active Trainers</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(224,188,82,0.12);color:var(--primary);">&#9989;</div>
    <div class="stat-val"><?= e($counts['today']) ?></div>
    <div class="stat-label">Today Attendance</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(239,68,68,0.12);color:var(--danger);">&#128176;</div>
    <div class="stat-val"><?= money($counts['revenue']) ?></div>
    <div class="stat-label">This Month Revenue</div>
  </div>
</div>

<?php if ($counts['pending'] > 0): ?>
<div class="flash flash-info" style="margin-bottom:20px;">&#128276; <?= (int)$counts['pending'] ?> member(s) have pending membership status. <a href="/gym-pro/views/admin/members.php?status=pending" style="color:var(--primary);font-weight:700;">View &rarr;</a></div>
<?php endif; ?>

<div class="grid-2">
  <div class="card">
    <div class="card-header"><span class="card-title">Recent Attendance</span></div>
    <div class="table-wrap">
      <table>
        <tr><th>Member</th><th>Date</th><th>Check In</th><th>Status</th></tr>
        <?php foreach ($recent_attendance as $r): ?>
        <tr>
          <td><?= e($r['name']) ?></td>
          <td><?= e($r['attendance_date']) ?></td>
          <td><?= e(substr($r['check_in'], 11, 5)) ?></td>
          <td><?= badge_status($r['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recent_attendance)): ?><tr><td colspan="4" class="empty-state">No attendance today</td></tr><?php endif; ?>
      </table>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Recent Payments</span></div>
    <div class="table-wrap">
      <table>
        <tr><th>Member</th><th>Plan</th><th>Amount</th><th>Date</th></tr>
        <?php foreach ($recent_payments as $r): ?>
        <tr>
          <td><?= e($r['name']) ?></td>
          <td><?= e($r['plan_name'] ?? '-') ?></td>
          <td><?= money($r['amount']) ?></td>
          <td><?= e($r['payment_date']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recent_payments)): ?><tr><td colspan="4" class="empty-state">No payments yet</td></tr><?php endif; ?>
      </table>
    </div>
  </div>
</div>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>