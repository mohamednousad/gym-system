<?php
require_once '../../config/bootstrap.php';
require_admin();
define('PAGE_TITLE', 'Payments');
define('PAGE_SUB', 'Record and track membership payments');
$pdo = db();
if (is_post()) {
    verify_csrf();
    $user_id = (int)post('user_id');
    $plan_id = (int)post('membership_plan_id');
    $method  = post('method','cash');
    $ref     = post('reference');
    $plan_s  = $pdo->prepare("SELECT * FROM membership_plans WHERE id=:id AND status='active'"); $plan_s->execute(['id'=>$plan_id]); $plan = $plan_s->fetch();
    if ($user_id && $plan) {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO payments (user_id,membership_plan_id,amount,payment_date,method,status,reference) VALUES (:uid,:pid,:amt,CURDATE(),:method,"paid",:ref)')->execute(['uid'=>$user_id,'pid'=>$plan_id,'amt'=>$plan['price'],'method'=>$method,'ref'=>$ref]);
        $renewal = date('Y-m-d', strtotime('+' . (int)$plan['duration_days'] . ' days'));
        $pdo->prepare("UPDATE users SET membership_plan=:plan,membership_status='active',renewal_date=:renewal WHERE id=:uid")->execute(['plan'=>$plan['name'],'renewal'=>$renewal,'uid'=>$user_id]);
        notify($user_id, 'Payment Received', 'Your ' . $plan['name'] . ' plan payment of ' . money($plan['price']) . ' recorded. Renewal: ' . $renewal . '.', 'payment');
        notify_admins('Payment Recorded', 'Payment recorded for member ID ' . $user_id . '.', 'payment');
        $pdo->commit();
        flash('Payment recorded. Membership activated.');
    } else flash('Invalid member or plan.','error');
    redirect('payments.php');
}
$q = get('q'); $from = get('from_date'); $to = get('to_date'); $method_f = get('method');
$where = '1=1'; $params = [];
if ($q!=='') { $where .= " AND (u.name LIKE :q OR u.email LIKE :q OR p.reference LIKE :q)"; $params[':q']="%$q%"; }
if ($from!=='') { $where .= " AND p.payment_date >= :from"; $params[':from']=$from; }
if ($to!=='') { $where .= " AND p.payment_date <= :to"; $params[':to']=$to; }
if (in_array($method_f,['cash','card','online'])) { $where .= " AND p.method=:method"; $params[':method']=$method_f; }
$cnt_s = $pdo->prepare("SELECT COUNT(*) FROM payments p JOIN users u ON u.id=p.user_id WHERE $where"); $cnt_s->execute($params); $total=(int)$cnt_s->fetchColumn();
$sum_s = $pdo->prepare("SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN users u ON u.id=p.user_id WHERE $where"); $sum_s->execute($params); $total_amount=(float)$sum_s->fetchColumn();
$pag = paginate($total,10);
$s = $pdo->prepare("SELECT p.*,u.name,u.email,mp.name plan_name FROM payments p JOIN users u ON u.id=p.user_id LEFT JOIN membership_plans mp ON mp.id=p.membership_plan_id WHERE $where ORDER BY p.payment_date DESC,p.id DESC LIMIT :lim OFFSET :off");
foreach ($params as $k=>$v) $s->bindValue($k,$v);
$s->bindValue(':lim',$pag['per_page'],PDO::PARAM_INT); $s->bindValue(':off',$pag['offset'],PDO::PARAM_INT); $s->execute();
$rows = $s->fetchAll();
include APP_ROOT . '/views/includes/head_admin.php';
?>
<div class="page-header">
  <div><h2>Payments</h2><p>Total collected: <?= money($total_amount) ?></p></div>
  <button class="btn btn-primary" data-open-modal="payModal">+ Record Payment</button>
</div>
<div class="card filter-card">
  <form method="GET" class="filter-row" onsubmit="return validateDates()">
    <div class="form-group"><label class="form-label">Search</label><input type="text" name="q" class="form-control" placeholder="Member, email, reference..." value="<?= e($q) ?>"></div>
    <div class="form-group"><label class="form-label">From</label><input type="date" name="from_date" id="fd" class="form-control" value="<?= e($from) ?>"></div>
    <div class="form-group"><label class="form-label">To</label><input type="date" name="to_date" id="td" class="form-control" value="<?= e($to) ?>"></div>
    <div class="form-group"><label class="form-label">Method</label>
      <select name="method" class="form-select">
        <option value="">All</option>
        <?php foreach (['cash','card','online'] as $m): ?><option value="<?= $m ?>" <?= $method_f===$m?'selected':'' ?>><?= ucfirst($m) ?></option><?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="payments.php" class="btn btn-secondary">Reset</a>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Member</th><th>Plan</th><th>Amount</th><th>Date</th><th>Method</th><th>Reference</th><th>Status</th></tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><strong><?= e($r['name']) ?></strong><br><span style="font-size:11px;color:var(--muted)"><?= e($r['email']) ?></span></td>
        <td><?= e($r['plan_name'] ?: '-') ?></td>
        <td><?= money($r['amount']) ?></td>
        <td><?= e($r['payment_date']) ?></td>
        <td><?= badge_status($r['method']) ?></td>
        <td><?= e($r['reference'] ?: '-') ?></td>
        <td><?= badge_status($r['status']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="7" class="empty-state">No payments found</td></tr><?php endif; ?>
    </table>
  </div>
  <?= render_pagination($pag) ?>
</div>
<div class="modal-overlay" id="payModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Record Payment</span><button class="modal-close" data-close-modal="payModal">&#x2715;</button></div>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Member *</label><?= member_select() ?></div>
        <div class="form-group"><label class="form-label">Plan *</label><?= plan_select() ?></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Method</label>
            <select name="method" class="form-select">
              <?php foreach (['cash','card','online'] as $m): ?><option value="<?= $m ?>"><?= ucfirst($m) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Reference</label><input name="reference" class="form-control" placeholder="PAY-001"></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close-modal="payModal">Cancel</button><button type="submit" class="btn btn-primary">Record Payment</button></div>
    </form>
  </div>
</div>
<script>
function validateDates(){var f=document.getElementById('fd').value,t=document.getElementById('td').value;if(f&&t&&f>t){alert('From cannot be after To.');return false;}return true;}
</script>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>