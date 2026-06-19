<?php
require_once '../../config/navigation.php';
require_admin();
define('PAGE_TITLE', 'Membership Plans');
define('PAGE_SUB', 'Manage subscription plans');
$pdo = db();
if (is_post()) {
    verify_csrf();
    $id = (int)post('id');
    $data = ['name'=>post('name'),'price'=>post('price'),'duration_days'=>(int)post('duration_days'),'description'=>post('description'),'status'=>post('status','active')];
    if ($id) { $data['id']=$id; $pdo->prepare('UPDATE membership_plans SET name=:name,price=:price,duration_days=:duration_days,description=:description,status=:status WHERE id=:id')->execute($data); flash('Plan updated.'); }
    else { $pdo->prepare('INSERT INTO membership_plans (name,price,duration_days,description,status) VALUES (:name,:price,:duration_days,:description,:status)')->execute($data); flash('Plan created.'); }
    redirect('membership_plans.php');
}
if (get_int('del') > 0) {
    try { $pdo->prepare('DELETE FROM membership_plans WHERE id=:id')->execute(['id'=>get_int('del')]); flash('Plan deleted.'); }
    catch (PDOException $e) { flash('Cannot delete — plan linked to payments.','error'); }
    redirect('membership_plans.php');
}
$export = get('export');
$cnt_s = $pdo->prepare("SELECT COUNT(*) FROM membership_plans"); $cnt_s->execute(); $total=(int)$cnt_s->fetchColumn();
if (in_array($export,['csv','pdf'])) {
    $all = $pdo->query('SELECT * FROM membership_plans ORDER BY price')->fetchAll();
    $cols=['name'=>'Plan','price'=>'Price','duration_days'=>'Days','description'=>'Description','status'=>'Status'];
    $export==='csv' ? export_csv($all,$cols,'membership_plans') : export_pdf($all,$cols,'Membership Plans');
}
$pag = paginate($total);
$s = $pdo->prepare("SELECT * FROM membership_plans ORDER BY price LIMIT :lim OFFSET :off");
$s->bindValue(':lim', $pag['per_page'], PDO::PARAM_INT);
$s->bindValue(':off', $pag['offset'], PDO::PARAM_INT);
$s->execute();
$plans = $s->fetchAll();
include APP_ROOT . '/views/includes/head_admin.php';
?>
<div class="page-header">
  <div><h2>Membership Plans</h2><p>Define subscription tiers for members</p></div>
  <div class="page-actions">
    <a href="?export=csv" class="btn btn-secondary btn-sm">&#8681; CSV</a>
    <button class="btn btn-primary" data-open-modal="planModal">+ Add Plan</button>
  </div>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Name</th><th>Price</th><th>Duration</th><th>Description</th><th>Status</th><th>Actions</th></tr>
      <?php foreach ($plans as $p): ?>
      <tr>
        <td><strong><?= e($p['name']) ?></strong></td>
        <td><?= money($p['price']) ?></td>
        <td><?= e($p['duration_days']) ?> days</td>
        <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($p['description'] ?: '-') ?></td>
        <td><?= badge_status($p['status']) ?></td>
        <td class="td-actions">
          <button class="btn btn-sm btn-secondary" data-open-modal="planModal" data-populate="planForm" data-data='<?= e(json_encode($p)) ?>'>Edit</button>
          <a href="?del=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete plan <?= e(addslashes($p['name'])) ?>?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($plans)): ?><tr><td colspan="6" class="empty-state">No plans yet</td></tr><?php endif; ?>
    </table>
  </div>
  <?= render_pagination($pag) ?>
</div>
<div class="modal-overlay" id="planModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Membership Plan</span><button class="modal-close" data-close-modal="planModal">&#x2715;</button></div>
    <form method="POST" id="planForm">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="">
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Plan Name *</label><input name="name" class="form-control" required placeholder="e.g. Premium"></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Price (LKR) *</label><input type="number" step="0.01" name="price" class="form-control" required placeholder="5000.00"></div>
          <div class="form-group"><label class="form-label">Duration Days *</label><input type="number" name="duration_days" class="form-control" required placeholder="30"></div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" placeholder="Brief plan benefits..."></textarea></div>
        <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close-modal="planModal">Cancel</button><button type="submit" class="btn btn-primary">Save Plan</button></div>
    </form>
  </div>
</div>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>