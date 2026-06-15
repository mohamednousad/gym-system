<?php
require_once '../../config/bootstrap.php';
require_user();
define('PAGE_TITLE', 'Notifications');
define('PAGE_SUB', 'Your activity alerts');
$pdo = db();
$user = current_user();
if (get_int('read') > 0) { mark_read(get_int('read'),(int)$user['id']); redirect('notifications.php'); }
if (get('read_all')==='1') { mark_all_read((int)$user['id']); flash('All marked as read.'); redirect('notifications.php'); }
$rows = $pdo->prepare('SELECT * FROM notifications WHERE user_id=:uid ORDER BY created_at DESC'); $rows->execute(['uid'=>$user['id']]); $rows=$rows->fetchAll();
$unread = count(array_filter($rows, fn($n)=>!$n['is_read']));
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
</div>
<?php include APP_ROOT . '/views/includes/foot_user.php'; ?>