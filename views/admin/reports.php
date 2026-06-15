<?php
require_once '../../config/bootstrap.php';
require_admin();
define('PAGE_TITLE', 'Reports');
define('PAGE_SUB', 'Filter and export gym data');
$pdo = db();
$member_f = get_int('member_id');
$from = get('from_date'); $to = get('to_date'); $rtype = get('rtype','attendance');
$where = "u.role='user'"; $params = [];
if ($member_f) { $where .= " AND u.id=:mid"; $params[':mid']=$member_f; }
if ($from!=='') { $where .= ($rtype==='attendance' ? " AND a.attendance_date>=:from" : " AND p.payment_date>=:from"); $params[':from']=$from; }
if ($to!=='') { $where .= ($rtype==='attendance' ? " AND a.attendance_date<=:to" : " AND p.payment_date<=:to"); $params[':to']=$to; }
if ($rtype==='payments') {
    $sql="SELECT u.name,u.email,p.payment_date,p.amount,p.method,p.status pay_status,p.reference,mp.name plan_name FROM payments p JOIN users u ON u.id=p.user_id LEFT JOIN membership_plans mp ON mp.id=p.membership_plan_id WHERE $where ORDER BY p.payment_date DESC";
} else {
    $sql="SELECT u.name,u.email,a.attendance_date,a.check_in,a.check_out,a.duration_minutes,a.status FROM attendance a JOIN users u ON u.id=a.user_id WHERE $where ORDER BY a.attendance_date DESC";
}
$s=$pdo->prepare($sql); $s->execute($params); $rows=$s->fetchAll();
$export=get('export');
if ($rtype==='payments' && in_array($export,['csv','pdf'])) {
    $cols=['name'=>'Member','email'=>'Email','payment_date'=>'Date','amount'=>'Amount','method'=>'Method','pay_status'=>'Status','reference'=>'Ref','plan_name'=>'Plan'];
    $export==='csv' ? export_csv($rows,$cols,'payments_report') : export_pdf($rows,$cols,'Payments Report');
}
if ($rtype==='attendance' && in_array($export,['csv','pdf'])) {
    $cols=['name'=>'Member','email'=>'Email','attendance_date'=>'Date','check_in'=>'Check In','check_out'=>'Check Out','duration_minutes'=>'Minutes','status'=>'Status'];
    $export==='csv' ? export_csv($rows,$cols,'attendance_report') : export_pdf($rows,$cols,'Attendance Report');
}
include APP_ROOT . '/views/includes/head_admin.php';
?>
<div class="page-header">
  <div><h2>Reports</h2><p><?= count($rows) ?> records</p></div>
  <div class="page-actions">
    <a href="?<?= http_build_query(array_merge($_GET,['export'=>'csv'])) ?>" class="btn btn-secondary btn-sm">&#8681; CSV</a>
    <a href="?<?= http_build_query(array_merge($_GET,['export'=>'pdf'])) ?>" target="_blank" class="btn btn-danger btn-sm">&#8681; PDF</a>
  </div>
</div>
<div class="card filter-card">
  <form method="GET" class="filter-row" onsubmit="return validateDates()">
    <div class="form-group"><label class="form-label">Report Type</label>
      <select name="rtype" class="form-select">
        <option value="attendance" <?= $rtype==='attendance'?'selected':'' ?>>Attendance</option>
        <option value="payments" <?= $rtype==='payments'?'selected':'' ?>>Payments</option>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Member</label><?= member_select($member_f) ?></div>
    <div class="form-group"><label class="form-label">From</label><input type="date" name="from_date" id="fd" class="form-control" value="<?= e($from) ?>"></div>
    <div class="form-group"><label class="form-label">To</label><input type="date" name="to_date" id="td" class="form-control" value="<?= e($to) ?>"></div>
    <button type="submit" class="btn btn-primary">Generate</button>
    <a href="reports.php" class="btn btn-secondary">Reset</a>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <?php if ($rtype==='payments'): ?>
      <tr><th>Member</th><th>Plan</th><th>Amount</th><th>Date</th><th>Method</th><th>Status</th><th>Ref</th></tr>
      <?php foreach ($rows as $r): ?>
      <tr><td><?= e($r['name']) ?><br><small class="text-muted"><?= e($r['email']) ?></small></td><td><?= e($r['plan_name']??'-') ?></td><td><?= money($r['amount']) ?></td><td><?= e($r['payment_date']) ?></td><td><?= badge_status($r['method']) ?></td><td><?= badge_status($r['pay_status']) ?></td><td><?= e($r['reference']??'-') ?></td></tr>
      <?php endforeach; ?>
      <?php else: ?>
      <tr><th>Member</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Minutes</th><th>Status</th></tr>
      <?php foreach ($rows as $r): ?>
      <tr><td><?= e($r['name']) ?><br><small class="text-muted"><?= e($r['email']) ?></small></td><td><?= e($r['attendance_date']) ?></td><td><?= e($r['check_in']?substr($r['check_in'],11,5):'—') ?></td><td><?= e($r['check_out']?substr($r['check_out'],11,5):'—') ?></td><td><?= e($r['duration_minutes']??'—') ?></td><td><?= badge_status($r['status']) ?></td></tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if(empty($rows)): ?><tr><td colspan="7" class="empty-state">No records. Apply filters and click Generate.</td></tr><?php endif; ?>
    </table>
  </div>
</div>
<script>function validateDates(){var f=document.getElementById('fd').value,t=document.getElementById('td').value;if(f&&t&&f>t){alert('From cannot be after To.');return false;}return true;}</script>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>