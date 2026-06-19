<?php
require_once '../../config/navigation.php';
require_user();
define('PAGE_TITLE', 'My Attendance');
define('PAGE_SUB', 'Your check-in history');
$pdo = db();
$user = current_user();
$from = get('from_date'); $to = get('to_date');
$where = "user_id=:uid"; $params=[':uid'=>$user['id']];
if ($from!=='') { $where .= " AND attendance_date>=:from"; $params[':from']=$from; }
if ($to!=='') { $where .= " AND attendance_date<=:to"; $params[':to']=$to; }
$cnt_s = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE $where"); $cnt_s->execute($params); $total=(int)$cnt_s->fetchColumn();
$pag = paginate($total,5);
$s = $pdo->prepare("SELECT * FROM attendance WHERE $where ORDER BY attendance_date DESC LIMIT :lim OFFSET :off");
foreach ($params as $k=>$v) $s->bindValue($k,$v);
$s->bindValue(':lim',$pag['per_page'],PDO::PARAM_INT); $s->bindValue(':off',$pag['offset'],PDO::PARAM_INT); $s->execute();
$rows=$s->fetchAll();
include APP_ROOT . '/views/includes/head_user.php';
?>
<div class="page-header"><div><h2>Attendance</h2><p><?= $total ?> records</p></div></div>
<div class="card filter-card">
  <form method="GET" class="filter-row" onsubmit="return validateDates()">
    <div class="form-group"><label class="form-label">From</label><input type="date" name="from_date" id="fd" class="form-control" value="<?= e($from) ?>"></div>
    <div class="form-group"><label class="form-label">To</label><input type="date" name="to_date" id="td" class="form-control" value="<?= e($to) ?>"></div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="attendance.php" class="btn btn-secondary">Reset</a>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th><th>Status</th></tr>
      <?php foreach ($rows as $r): ?>
      <tr><td><?= e($r['attendance_date']) ?></td><td><?= e(substr($r['check_in'],11,5)) ?></td><td><?= $r['check_out']?e(substr($r['check_out'],11,5)):'—' ?></td><td><?= $r['duration_minutes']?e($r['duration_minutes']).' min':'—' ?></td><td><?= badge_status($r['status']) ?></td></tr>
      <?php endforeach; ?>
      <?php if(empty($rows)): ?><tr><td colspan="5" class="empty-state">No attendance records</td></tr><?php endif; ?>
    </table>
  </div>
  <?= render_pagination($pag) ?>
</div>
<script>function validateDates(){var f=document.getElementById('fd').value,t=document.getElementById('td').value;if(f&&t&&f>t){alert('From cannot be after To.');return false;}return true;}</script>
<?php include APP_ROOT . '/views/includes/foot_user.php'; ?>