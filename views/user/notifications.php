<?php
require_once '../../config/navigation.php';
require_user();
define('PAGE_TITLE', 'Notifications');
define('PAGE_SUB', 'Your activity alerts');
$pdo = db();
$user = current_user();
if (get_int('read') > 0) { mark_read(get_int('read'),(int)$user['id']); redirect('notifications.php'); }
if (get('read_all')==='1') { mark_all_read((int)$user['id']); flash('All marked as read.'); redirect('notifications.php'); }
$cnt_s = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=:uid'); $cnt_s->execute(['uid'=>$user['id']]); $total=(int)$cnt_s->fetchColumn();
$unread_s = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND is_read=0'); $unread_s->execute(['uid'=>$user['id']]); $unread=(int)$unread_s->fetchColumn();
$pag = paginate($total, 4);
$s = $pdo->prepare('SELECT * FROM notifications WHERE user_id=:uid ORDER BY created_at DESC LIMIT :lim OFFSET :off');
$s->bindValue(':uid', $user['id'], PDO::PARAM_INT);
$s->bindValue(':lim', $pag['per_page'], PDO::PARAM_INT);
$s->bindValue(':off', $pag['offset'], PDO::PARAM_INT);
$s->execute();
$rows = $s->fetchAll();
include APP_ROOT . '/views/includes/head_user.php';
?>
<div class="page-header">
  <div><h2>Notifications</h2><p><?= $unread ?> unread</p></div>
  <?php if($unread>0): ?><a href="?read_all=1" class="btn btn-secondary btn-sm">Mark all read</a><?php endif; ?>
</div>
<div class="card">
  <?php if(empty($rows)): ?><div class="empty-state"><div class="empty-icon">&#128276;</div><p>No notifications</p></div>
  <?php else: foreach ($rows as $n): ?>
  <div class="notif-item <?= !$n['is_read']?'unread':'' ?>">
    <div class="notif-title"><?= e($n['title']) ?></div>
    <div class="notif-msg"><?= e($n['message']) ?></div>
    <div class="notif-meta"><?= badge_status($n['type']) ?><span class="text-muted" style="font-size:11px"><?= time_ago($n['created_at']) ?></span><?php if(!$n['is_read']): ?><a href="?read=<?= $n['id'] ?>" class="btn btn-sm btn-secondary">Mark read</a><?php endif; ?></div>
  </div>
  <?php endforeach; endif; ?>
  <?= render_pagination($pag) ?>
</div>
<?php include APP_ROOT . '/views/includes/foot_user.php'; ?>