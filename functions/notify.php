<?php
function notify(int $user_id, string $title, string $message, string $type = 'general'): void {
    $s = db()->prepare('INSERT INTO notifications (user_id,title,message,type) VALUES (:uid,:title,:msg,:type)');
    $s->execute(['uid' => $user_id, 'title' => $title, 'msg' => $message, 'type' => $type]);
}

function notify_admins(string $title, string $message, string $type = 'general'): void {
    $admins = db()->query("SELECT id FROM users WHERE role='admin'")->fetchAll();
    foreach ($admins as $a) notify((int)$a['id'], $title, $message, $type);
}

function mark_read(int $notif_id, int $user_id): void {
    $s = db()->prepare('UPDATE notifications SET is_read=1 WHERE id=:id AND user_id=:uid');
    $s->execute(['id' => $notif_id, 'uid' => $user_id]);
}

function mark_all_read(int $user_id): void {
    $s = db()->prepare('UPDATE notifications SET is_read=1 WHERE user_id=:uid');
    $s->execute(['uid' => $user_id]);
}