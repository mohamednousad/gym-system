<?php
require_once '../../config/navigation.php';
require_admin();
define('PAGE_TITLE', 'Notifications');
define('PAGE_SUB', 'Send and manage system alerts');
$pdo = db();
$me = current_user();
if (is_post()) {
    verify_csrf();
    $target = post('target'); $title = post('title'); $msg = post('message'); $type = post('type','general');
    if ($target === 'all') { $ids=$pdo->query("SELECT id FROM users WHERE role='user'")->fetchAll(); foreach ($ids as $row) notify((int)$row['id'],$title,$msg,$type); flash('Notification sent to all members.'); }
    elseif ($target === 'admins') { notify_admins($title,$msg,$type); flash('Notification sent to admins.'); }
    else { notify((int)$target,$title,$msg,$type); flash('Notification sent.'); }
    redirect('notifications.php');
}
if (get_int('read') > 0) { mark_read(get_int('read'),(int)$me['id']); redirect('notifications.php'); }
if (get('read_all') === '1') { mark_all_read((int)$me['id']); flash('All marked as read.'); redirect('notifications.php'); }
$unread_cnt_s = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0'); $unread_cnt_s->execute(['id'=>$me['id']]); $unread_count=(int)$unread_cnt_s->fetchColumn();
$cnt_s = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=:id'); $cnt_s->execute(['id'=>$me['id']]); $total=(int)$cnt_s->fetchColumn();
$pag = paginate($total, 4);
$s = $pdo->prepare('SELECT * FROM notifications WHERE user_id=:id ORDER BY created_at DESC LIMIT :lim OFFSET :off');
$s->bindValue(':id', $me['id'], PDO::PARAM_INT);
$s->bindValue(':lim', $pag['per_page'], PDO::PARAM_INT);
$s->bindValue(':off', $pag['offset'], PDO::PARAM_INT);
$s->execute();
$rows = $s->fetchAll();
include APP_ROOT . '/views/includes/head_admin.php';
?>
<div class="page-header">
  <div><h2>Notifications</h2><p><?= $unread_count ?> unread</p></div>
  <div class="page-actions">
    <?php if ($unread_count): ?><a href="?read_all=1" class="btn btn-secondary btn-sm">Mark all read</a><?php endif; ?>
    <button class="btn btn-primary" data-open-modal="sendModal">+ Send Notification</button>
  </div>
</div>
<div class="card">
  <?php if (empty($rows)): ?><div class="empty-state"><div class="empty-icon">&#128276;</div><p>No notifications</p></div>
  <?php else: foreach ($rows as $n): ?>
  <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>">
    <div class="notif-title"><?= e($n['title']) ?></div>
    <div class="notif-msg"><?= e($n['message']) ?></div>
    <div class="notif-meta">
      <?= badge_status($n['type']) ?>
      <span class="text-muted" style="font-size:11px;"><?= time_ago($n['created_at']) ?></span>
      <?php if (!$n['is_read']): ?><a href="?read=<?= $n['id'] ?>" class="btn btn-sm btn-secondary">Mark read</a><?php endif; ?>
    </div>
  </div>
  <?php endforeach; endif; ?>
  <?= render_pagination($pag) ?>
</div>
<div class="modal-overlay" id="sendModal">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Send Notification</span><button class="modal-close" data-close-modal="sendModal">&#x2715;</button></div>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Target *</label>
        <select name="target" class="form-select">
          <option value="all">All Members</option>
          <option value="admins">Admins</option>
          <?php
            $members = $pdo->query("SELECT id,name,email FROM users WHERE role='user' ORDER BY name")->fetchAll();
            foreach ($members as $m): ?>
            <option value="<?= $m['id'] ?>"><?= e($m['name']) ?> – <?= e($m['email']) ?></option>
          <?php endforeach; ?>
        </select>
        </div>
        <div class="form-group"><label class="form-label">Title *</label><input name="title" class="form-control" required placeholder="Notification title"></div>
        <div class="form-group"><label class="form-label">Message *</label><textarea name="message" class="form-control" required placeholder="Message content..."></textarea></div>
        <div class="form-group"><label class="form-label">Type</label>
          <select name="type" class="form-select">
            <?php foreach (['general','payment','renewal','workout','attendance'] as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close-modal="sendModal">Cancel</button><button type="submit" class="btn btn-primary">Send</button></div>
    </form>
  </div>
</div>
<?php include APP_ROOT . '/views/includes/foot_admin.php'; ?>