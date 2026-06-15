<?php
require_once '../../config/bootstrap.php';
require_user();
define('PAGE_TITLE', 'My Payments');
define('PAGE_SUB', 'Your payment history');
$pdo = db();
$user = current_user();
$rows = $pdo->prepare('SELECT p.*,mp.name plan_name FROM payments p LEFT JOIN membership_plans mp ON mp.id=p.membership_plan_id WHERE p.user_id=:uid ORDER BY p.payment_date DESC');
$rows->execute(['uid'=>$user['id']]); $rows=$rows->fetchAll();
$total_paid = array_sum(array_column($rows,'amount'));
include APP_ROOT . '/views/includes/head_user.php';
?>
<div class="page-header"><div><h2>Payment History</h2><p>Total paid: <?= money($total_paid) ?></p></div></div>
<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Plan</th><th>Amount</th><th>Date</th><th>Method</th><th>Status</th><th>Reference</th></tr>
      <?php foreach ($rows as $r): ?>
      <tr><td><?= e($r['plan_name']??'—') ?></td><td><?= money($r['amount']) ?></td><td><?= e($r['payment_date']) ?></td><td><?= badge_status($r['method']) ?></td><td><?= badge_status($r['status']) ?></td><td><?= e($r['reference']??'—') ?></td></tr>
      <?php endforeach; ?>
      <?php if(empty($rows)): ?><tr><td colspan="6" class="empty-state">No payments yet</td></tr><?php endif; ?>
    </table>
  </div>
</div>
<?php include APP_ROOT . '/views/includes/foot_user.php'; ?>