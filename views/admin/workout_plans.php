<?php
require_once '../../config/navigation.php';
require_admin();
define('PAGE_TITLE', 'Workout Plans');
define('PAGE_SUB', 'Manage fitness workout programs');
$pdo = db();
if (is_post()) {
    verify_csrf();
    $id = (int)post('id');
    $data = ['trainer_id'=>post('trainer_id') ?: null,'plan_name'=>post('plan_name'),'goal'=>post('goal'),'duration_weeks'=>(int)post('duration_weeks'),'days_per_week'=>(int)post('days_per_week'),'difficulty'=>post('difficulty','beginner'),'status'=>post('status','active'),'notes'=>post('notes')];
    if ($id) { $data['id']=$id; $pdo->prepare('UPDATE workout_plans SET trainer_id=:trainer_id,plan_name=:plan_name,goal=:goal,duration_weeks=:duration_weeks,days_per_week=:days_per_week,difficulty=:difficulty,status=:status,notes=:notes WHERE id=:id')->execute($data); flash('Plan updated.'); }
    else { $pdo->prepare('INSERT INTO workout_plans (trainer_id,plan_name,goal,duration_weeks,days_per_week,difficulty,status,notes) VALUES (:trainer_id,:plan_name,:goal,:duration_weeks,:days_per_week,:difficulty,:status,:notes)')->execute($data); flash('Plan created.'); }
    redirect('workout_plans.php');
}
if (get_int('del') > 0) {
    try { $pdo->prepare('DELETE FROM workout_plans WHERE id=:id')->execute(['id'=>get_int('del')]); flash('Plan deleted.'); }
    catch (PDOException $e) { flash('Cannot delete — plan in use.','error'); }
    redirect('workout_plans.php');
}
$trainers = $pdo->query("SELECT id,name FROM trainers WHERE status='active' ORDER BY name")->fetchAll();
$cnt_s = $pdo->prepare("SELECT COUNT(*) FROM workout_plans"); $cnt_s->execute(); $total=(int)$cnt_s->fetchColumn();
$pag = paginate($total);
$s = $pdo->prepare("SELECT w.*,t.name trainer_name FROM workout_plans w LEFT JOIN trainers t ON t.id=w.trainer_id ORDER BY w.created_at DESC LIMIT :lim OFFSET :off");
$s->bindValue(':lim', $pag['per_page'], PDO::PARAM_INT);
$s->bindValue(':off', $pag['offset'], PDO::PARAM_INT);
$s->execute();
$rows = $s->fetchAll();
include APP_ROOT . '/views/includes/head_admin.php';
?>
<style>
.field-error{color:#dc2626;font-size:.78rem;margin-top:3px;display:none;}
.input-invalid{border-color:#dc2626!important;}
</style>
<div class="page-header">
  <div><h2>Workout Plans</h2><p>Create and manage fitness programs</p></div>
  <button class="btn btn-primary" data-open-modal="wModal">+ Add Plan</button>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Plan Name</th><th>Trainer</th><th>Goal</th><th>Duration</th><th>Days/Week</th><th>Difficulty</th><th>Status</th><th>Actions</th></tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><strong><?= e($r['plan_name']) ?></strong></td>
        <td><?= e($r['trainer_name'] ?: 'Unassigned') ?></td>
        <td><?= e($r['goal'] ?: '-') ?></td>
        <td><?= e($r['duration_weeks'] ?: '-') ?> wk</td>
        <td><?= e($r['days_per_week'] ?: '-') ?></td>
        <td><?= badge_status($r['difficulty']) ?></td>
        <td><?= badge_status($r['status']) ?></td>
        <td class="td-actions">
          <button class="btn btn-sm btn-secondary" data-open-modal="wModal" data-populate="wForm" data-data='<?= e(json_encode($r)) ?>'>Edit</button>
          <a href="?del=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this plan?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="8" class="empty-state">No workout plans</td></tr><?php endif; ?>
    </table>
  </div>
  <?= render_pagination($pag) ?>
</div>
<div class="modal-overlay" id="wModal">
  <div class="modal" style="max-width:580px;">
    <div class="modal-header"><span class="modal-title">Workout Plan</span><button class="modal-close" data-close-modal="wModal">&#x2715;</button></div>
    <form method="POST" id="wForm" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Plan Name *</label>
            <input name="plan_name" id="f_plan_name" class="form-control" placeholder="e.g. Strength Builder">
            <div class="field-error" id="e_plan_name">Plan name is required.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Trainer</label>
            <select name="trainer_id" class="form-select">
              <option value="">No trainer</option>
              <?php foreach ($trainers as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Goal</label>
            <input name="goal" id="f_goal" class="form-control" placeholder="e.g. Muscle Gain">
            <div class="field-error" id="e_goal">Goal is required.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Difficulty</label>
            <select name="difficulty" class="form-select">
              <?php foreach (['beginner','intermediate','advanced'] as $d): ?><option value="<?= $d ?>"><?= ucfirst($d) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Duration Weeks *</label>
            <input type="number" name="duration_weeks" id="f_duration_weeks" class="form-control" min="1" placeholder="8">
            <div class="field-error" id="e_duration_weeks">Enter a valid duration (min 1).</div>
          </div>
          <div class="form-group">
            <label class="form-label">Days Per Week *</label>
            <input type="number" name="days_per_week" id="f_days_per_week" class="form-control" min="1" max="7" placeholder="4">
            <div class="field-error" id="e_days_per_week">Enter days between 1 and 7.</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" placeholder="Plan description..."></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close-modal="wModal">Cancel</button><button type="submit" class="btn btn-primary">Save Plan</button></div>
    </form>
  </div>
</div>
<script>
document.getElementById('wForm').addEventListener('submit', function(e) {
    var ok = true;
    function validate(fieldId, errorId, condition) {
        var el = document.getElementById(fieldId);
        var err = document.getElementById(errorId);
        if (condition) {
            el.classList.add('input-invalid');
            err.style.display = 'block';
            ok = false;
        } else {
            el.classList.remove('input-invalid');
            err.style.display = 'none';
        }
    }
    var planName = document.getElementById('f_plan_name').value.trim();
    var goal = document.getElementById('f_goal').value.trim();
    var durationWeeks = parseInt(document.getElementById('f_duration_weeks').value);
    var daysPerWeek = parseInt(document.getElementById('f_days_per_week').value);
    validate('f_plan_name', 'e_plan_name', planName === '');
    validate('f_goal', 'e_goal', goal === '');
    validate('f_duration_weeks', 'e_duration_weeks', isNaN(durationWeeks) || durationWeeks < 1);
    validate('f_days_per_week', 'e_days_per_week', isNaN(daysPerWeek) || daysPerWeek < 1 || daysPerWeek > 7);
    if (!ok) e.preventDefault();
});
document.querySelectorAll('#wForm input').forEach(function(el) {
    el.addEventListener('input', function() {
        el.classList.remove('input-invalid');
        var err = document.getElementById('e_' + el.id.replace('f_',''));
        if (err) err.style.display = 'none';
    });
});
</script>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>