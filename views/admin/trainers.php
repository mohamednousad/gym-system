<?php
require_once '../../config/bootstrap.php';
require_admin();
define('PAGE_TITLE', 'Trainers');
define('PAGE_SUB', 'Manage training staff');

$pdo = db();
if (is_post()) {
    verify_csrf();
    $id = (int)post('id');
    $data = ['name'=>post('name'),'email'=>post('email'),'phone'=>post('phone'),'specialization'=>post('specialization'),'experience_years'=>(int)post('experience_years'),'status'=>post('status','active')];
    if ($id) {
        $data['id'] = $id;
        $pdo->prepare('UPDATE trainers SET name=:name,email=:email,phone=:phone,specialization=:specialization,experience_years=:experience_years,status=:status WHERE id=:id')->execute($data);
        flash('Trainer updated.');
    } else {
        $pdo->prepare('INSERT INTO trainers (name,email,phone,specialization,experience_years,status) VALUES (:name,:email,:phone,:specialization,:experience_years,:status)')->execute($data);
        flash('Trainer added.');
    }
    redirect('trainers.php');
}
if (is_post() && post('_method') === 'DELETE') {
    verify_csrf();
    $pdo->prepare('DELETE FROM trainers WHERE id=:id')->execute(['id' => (int)post('del_id')]);
    flash('Trainer removed.');
    redirect('trainers.php');
}
if (isset($_GET['del'])) {
    verify_csrf();
    $pdo->prepare('DELETE FROM trainers WHERE id=:id')->execute(['id' => get_int('del')]);
    flash('Trainer removed.');
    redirect('trainers.php');
}

$q = get('q');
$status_f = get('status');
$where = '1=1'; $params = [];
if ($q !== '') {
    $where .= " AND (name LIKE :q1 OR email LIKE :q2 OR specialization LIKE :q3)";
    $params[':q1'] = "%$q%";
    $params[':q2'] = "%$q%";
    $params[':q3'] = "%$q%";
}
if (in_array($status_f, ['active','inactive'])) { $where .= " AND status=:st"; $params[':st'] = $status_f; }
$cnt_s = $pdo->prepare("SELECT COUNT(*) FROM trainers WHERE $where"); $cnt_s->execute($params); $total = (int)$cnt_s->fetchColumn();
$pag = paginate($total, 10);
$s = $pdo->prepare("SELECT * FROM trainers WHERE $where ORDER BY created_at DESC LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) $s->bindValue($k, $v);
$s->bindValue(':lim', $pag['per_page'], PDO::PARAM_INT);
$s->bindValue(':off', $pag['offset'], PDO::PARAM_INT);
$s->execute();
$rows = $s->fetchAll();
include APP_ROOT . '/views/includes/head_admin.php';
?>
<div class="page-header">
  <div><h2>Trainers</h2><p><?= $total ?> trainer(s) registered</p></div>
  <button class="btn btn-primary" data-open-modal="addTrainerModal">+ Add Trainer</button>
</div>

<div class="card filter-card">
  <form method="GET" class="filter-row">
    <div class="form-group"><label class="form-label">Search</label><input type="text" name="q" class="form-control" placeholder="Name, email, specialization..." value="<?= e($q) ?>"></div>
    <div class="form-group"><label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">All</option>
        <option value="active" <?= $status_f==='active'?'selected':'' ?>>Active</option>
        <option value="inactive" <?= $status_f==='inactive'?'selected':'' ?>>Inactive</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="trainers.php" class="btn btn-secondary">Reset</a>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Name</th><th>Email</th><th>Phone</th><th>Specialization</th><th>Exp. Years</th><th>Status</th><th>Actions</th></tr>
      <?php if (empty($rows)): ?><tr><td colspan="7"><div class="empty-state"><div class="empty-icon"></div><p>No trainers found</p></div></td></tr>
      <?php else: foreach ($rows as $r): ?>
      <tr>
        <td><strong><?= e($r['name']) ?></strong></td>
        <td><?= e($r['email']) ?></td>
        <td><?= e($r['phone'] ?: '-') ?></td>
        <td><?= e($r['specialization'] ?: '-') ?></td>
        <td><?= e($r['experience_years'] ?: '-') ?></td>
        <td><?= badge_status($r['status']) ?></td>
        <td class="td-actions">
          <button class="btn btn-sm btn-secondary"
            data-open-modal="addTrainerModal"
            data-populate="trainerForm"
            data-data='<?= e(json_encode($r)) ?>'>Edit</button>
          <form method="POST" id="delT<?= $r['id'] ?>" style="display:none">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="del_id" value="<?= $r['id'] ?>">
          </form>
          <button class="btn btn-sm btn-danger" onclick="confirmDelete('delT<?= $r['id'] ?>','Delete trainer <?= e(addslashes($r['name'])) ?>?')">Delete</button>
        </td>
      </tr>
      <?php endforeach; endif; ?>
    </table>
  </div>
  <?= render_pagination($pag) ?>
</div>

<div class="modal-overlay" id="addTrainerModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Trainer Details</span>
      <button class="modal-close" data-close-modal="addTrainerModal">&#x2715;</button>
    </div>
    <form method="POST" id="trainerForm">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Name *</label><input name="name" class="form-control" required placeholder="Full name"></div>
          <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required placeholder="email@example.com"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Phone</label><input name="phone" class="form-control" placeholder="+94 77..."></div>
          <div class="form-group"><label class="form-label">Experience Years</label><input type="number" name="experience_years" class="form-control" min="0" placeholder="e.g. 5"></div>
        </div>
        <div class="form-group"><label class="form-label">Specialization</label><input name="specialization" class="form-control" placeholder="e.g. Weight Training"></div>
        <div class="form-group"><label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-close-modal="addTrainerModal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Trainer</button>
      </div>
    </form>
  </div>
</div>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>