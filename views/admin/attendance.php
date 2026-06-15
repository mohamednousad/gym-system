<?php
require_once '../../config/bootstrap.php';
require_admin();
define('PAGE_TITLE', 'Attendance');
define('PAGE_SUB', 'Members mark their own attendance');
$pdo = db();
$member_f = get_int('member_id');
$from = get('from_date'); $to = get('to_date');
$where = '1=1'; $params = [];
if ($member_f) { $where .= " AND a.user_id=:mid"; $params[':mid']=$member_f; }
if ($from!=='') { $where .= " AND a.attendance_date>=:from"; $params[':from']=$from; }
if ($to!=='') { $where .= " AND a.attendance_date<=:to"; $params[':to']=$to; }
$cnt_s = $pdo->prepare("SELECT COUNT(*) FROM attendance a JOIN users u ON u.id=a.user_id WHERE $where"); $cnt_s->execute($params); $total=(int)$cnt_s->fetchColumn();
$pag = paginate($total,10);
$s = $pdo->prepare("SELECT a.*,u.name,u.email FROM attendance a JOIN users u ON u.id=a.user_id WHERE $where ORDER BY a.attendance_date DESC,a.check_in DESC LIMIT :lim OFFSET :off");
foreach ($params as $k=>$v) $s->bindValue($k,$v);
$s->bindValue(':lim',$pag['per_page'],PDO::PARAM_INT); $s->bindValue(':off',$pag['offset'],PDO::PARAM_INT); $s->execute();
$rows=$s->fetchAll();
$today_count = $pdo->query("SELECT COUNT(*) FROM attendance WHERE attendance_date=CURDATE()")->fetchColumn();
include APP_ROOT . '/views/includes/head_admin.php';
?>
<div class="page-header">
  <div><h2>Attendance</h2><p>Today: <?= (int)$today_count ?> check-ins</p></div>
</div>
<div class="card filter-card">
  <form method="GET" class="filter-row" onsubmit="return validateDates()">
    <div class="form-group"><label class="form-label">Member</label><?= member_select($member_f) ?></div>
    <div class="form-group"><label class="form-label">From</label><input type="date" name="from_date" id="fd" class="form-control" value="<?= e($from) ?>"></div>
    <div class="form-group"><label class="form-label">To</label><input type="date" name="to_date" id="td" class="form-control" value="<?= e($to) ?>"></div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="attendance.php" class="btn btn-secondary">Reset</a>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Member</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Minutes</th><th>Status</th></tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><strong><?= e($r['name']) ?></strong><br><span style="font-size:11px;color:var(--muted)"><?= e($r['email']) ?></span></td>
        <td><?= e($r['attendance_date']) ?></td>
        <td><?= e(substr($r['check_in'],11,5)) ?></td>
        <td><?= $r['check_out'] ? e(substr($r['check_out'],11,5)) : '<span style="color:var(--muted)">—</span>' ?></td>
        <td><?= e($r['duration_minutes'] ?: '—') ?></td>
        <td><?= badge_status($r['status']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($rows)): ?><tr><td colspan="6" class="empty-state">No attendance records</td></tr><?php endif; ?>
    </table>
  </div>
  <?= render_pagination($pag) ?>
</div>
<script>function validateDates(){var f=document.getElementById('fd').value,t=document.getElementById('td').value;if(f&&t&&f>t){alert('From cannot be after To.');return false;}return true;}</script>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>