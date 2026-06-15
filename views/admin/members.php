<?php
require_once '../../config/bootstrap.php';
require_admin();
define('PAGE_TITLE', 'Members');
define('PAGE_SUB', 'Manage gym member accounts');

$pdo = db();
$expired = $pdo->query("SELECT id FROM users WHERE role='user' AND membership_status!='expired' AND renewal_date < CURDATE()")->fetchAll();
foreach ($expired as $eu) {
    $pdo->prepare("UPDATE users SET membership_status='expired' WHERE id=:id")->execute(['id' => $eu['id']]);
    notify((int)$eu['id'], 'Membership Expired', 'Your membership has expired. Please renew.', 'renewal');
}

$q      = get('q');
$status = get('status');
$plan   = get('plan');
$from   = get('from_date');
$to     = get('to_date');
$export = get('export');

$where = "role='user'";
$params = [];
if ($q !== '') { $where .= " AND (name LIKE :q OR email LIKE :q OR phone LIKE :q)"; $params[':q'] = "%$q%"; }
if (in_array($status, ['active','expired','pending'])) { $where .= " AND membership_status=:status"; $params[':status'] = $status; }
if ($plan !== '') { $where .= " AND membership_plan=:plan"; $params[':plan'] = $plan; }
if ($from !== '') { $where .= " AND renewal_date >= :from"; $params[':from'] = $from; }
if ($to !== '') { $where .= " AND renewal_date <= :to"; $params[':to'] = $to; }

$total = (int)db()->prepare("SELECT COUNT(*) FROM users WHERE $where")->execute($params) ? $pdo->prepare("SELECT COUNT(*) FROM users WHERE $where")->execute($params) : 0;
$cnt_s = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $where"); $cnt_s->execute($params); $total = (int)$cnt_s->fetchColumn();

if (in_array($export, ['csv','pdf'])) {
    $all_s = $pdo->prepare("SELECT name,email,phone,membership_plan,membership_status,renewal_date FROM users WHERE $where ORDER BY name");
    $all_s->execute($params); $all = $all_s->fetchAll();
    $cols = ['name'=>'Name','email'=>'Email','phone'=>'Phone','membership_plan'=>'Plan','membership_status'=>'Status','renewal_date'=>'Renewal Date'];
    $export === 'csv' ? export_csv($all, $cols, 'members') : export_pdf($all, $cols, 'Members Report');
}

$pag = paginate($total, 10);
$s = $pdo->prepare("SELECT id,name,email,phone,membership_plan,membership_status,renewal_date,profile_image,created_at FROM users WHERE $where ORDER BY created_at DESC LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) $s->bindValue($k, $v);
$s->bindValue(':lim', $pag['per_page'], PDO::PARAM_INT);
$s->bindValue(':off', $pag['offset'], PDO::PARAM_INT);
$s->execute();
$members = $s->fetchAll();
$all_plans = $pdo->query("SELECT DISTINCT membership_plan FROM users WHERE role='user' AND membership_plan IS NOT NULL AND membership_plan != '' ORDER BY membership_plan")->fetchAll(PDO::FETCH_COLUMN);

$preview = null;
if (get_int('preview') > 0) {
    $ps = $pdo->prepare("SELECT * FROM users WHERE id=:id AND role='user' LIMIT 1");
    $ps->execute(['id' => get_int('preview')]); $preview = $ps->fetch();
}
include APP_ROOT . '/views/includes/head_admin.php';
?>
<div class="page-header">
  <div><h2>Members</h2><p>Manage gym member accounts &mdash; <?= $total ?> total</p></div>
  <div class="page-actions">
    <a href="?<?= http_build_query(array_filter(array_merge($_GET,['export'=>'csv']),fn($v)=>$v!=='')) ?>" class="btn btn-secondary btn-sm">&#8681; CSV</a>
    <a href="?<?= http_build_query(array_filter(array_merge($_GET,['export'=>'pdf']),fn($v)=>$v!=='')) ?>" target="_blank" class="btn btn-danger btn-sm">&#8681; PDF</a>
  </div>
</div>

<div class="card filter-card">
  <form method="GET" class="filter-row" onsubmit="return validateDates()">
    <div class="form-group"><label class="form-label">Search</label><input type="text" name="q" class="form-control" placeholder="Name, email or phone..." value="<?= e($q) ?>"></div>
    <div class="form-group"><label class="form-label">From (Renewal)</label><input type="date" name="from_date" id="fd" class="form-control" value="<?= e($from) ?>" max="<?= date('Y-m-d') ?>"></div>
    <div class="form-group"><label class="form-label">To (Renewal)</label><input type="date" name="to_date" id="td" class="form-control" value="<?= e($to) ?>" max="<?= date('Y-m-d') ?>"></div>
    <div class="form-group"><label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">All</option>
        <?php foreach (['active','expired','pending'] as $opt): ?>
        <option value="<?= $opt ?>" <?= $status===$opt?'selected':'' ?>><?= ucfirst($opt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Plan</label>
      <select name="plan" class="form-select">
        <option value="">All Plans</option>
        <?php foreach ($all_plans as $pl): ?><option value="<?= e($pl) ?>" <?= $plan===$pl?'selected':'' ?>><?= e($pl) ?></option><?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="members.php" class="btn btn-secondary">Reset</a>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Member</th><th>Email</th><th>Phone</th><th>Plan</th><th>Status</th><th>Renewal</th><th>Joined</th></tr>
      <?php if (empty($members)): ?>
      <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">&#128101;</div><p>No members found</p></div></td></tr>
      <?php else: ?>
      <?php foreach ($members as $m): ?>
      <tr>
        <td>
          <div class="d-flex align-center gap-2">
            <span onclick="showImageModal('<?= $m['profile_image'] ? e(img_url($m['profile_image'])) : '' ?>')" style="cursor:<?= $m['profile_image'] ? 'zoom-in' : 'default' ?>">
              <?= avatar($m['name'], $m['profile_image'] ? img_url($m['profile_image']) : null, 36) ?>
            </span>
            <a href="?preview=<?= (int)$m['id'] ?>" style="font-weight:600;color:var(--text)"><?= e($m['name']) ?></a>
          </div>
        </td>
        <td><?= e($m['email']) ?></td>
        <td><?= e($m['phone'] ?: '-') ?></td>
        <td><?= e($m['membership_plan'] ?: '-') ?></td>
        <td><?= badge_status($m['membership_status']) ?></td>
        <td><?= e($m['renewal_date'] ?: '-') ?></td>
        <td><?= e(date('d M Y', strtotime($m['created_at']))) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </table>
  </div>
  <?= render_pagination($pag) ?>
</div>

<?php if ($preview): ?>
<div class="modal-overlay open" id="previewOverlay" onclick="if(event.target===this)window.location='members.php?<?= http_build_query(array_filter(array_diff_key($_GET,['preview'=>0]),fn($v)=>$v!=='')) ?>'">
  <div class="modal" style="max-width:440px;">
    <div class="modal-header">
      <span class="modal-title">Member Profile</span>
      <a href="members.php?<?= http_build_query(array_filter(array_diff_key($_GET,['preview'=>0]),fn($v)=>$v!=='')) ?>" class="modal-close">&#x2715;</a>
    </div>
    <div class="modal-body" style="text-align:center;">
      <?php if ($preview['profile_image']): ?>
        <img src="<?= e(img_url($preview['profile_image'])) ?>" onclick="showImageModal('<?= e(img_url($preview['profile_image'])) ?>')" style="width:84px;height:84px;border-radius:50%;object-fit:cover;margin:0 auto 14px;border:3px solid var(--primary);cursor:zoom-in;">
      <?php else: ?>
        <?= avatar($preview['name'], null, 84) ?>
        <div style="height:14px"></div>
      <?php endif; ?>
      <div style="font-size:18px;font-weight:700;"><?= e($preview['name']) ?></div>
      <div style="color:var(--muted);font-size:13px;margin-bottom:14px;"><?= e($preview['email']) ?></div>
      <?= badge_status($preview['membership_status']) ?>
      <div style="margin-top:18px;text-align:left;">
        <?php foreach (['Phone'=>'phone','Plan'=>'membership_plan','Renewal'=>'renewal_date','Joined'=>'created_at'] as $lbl=>$key): ?>
        <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:13px;">
          <span style="color:var(--muted)"><?= $lbl ?></span>
          <span><?= e($preview[$key] ?: '-') ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function validateDates() {
  var f = document.getElementById('fd').value, t = document.getElementById('td').value;
  if (f && t && f > t) { alert('From date cannot be after To date.'); return false; }
  return true;
}
</script>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>