<?php
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', 'Dashboard');
if (!defined('PAGE_SUB'))   define('PAGE_SUB', '');
$_me = current_user();
$_badge = $_me ? unread_count((int)$_me['id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(PAGE_TITLE) ?> — MSP GYM</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-system/assets/css/main.css">
</head>
<body>
<div class="app-layout">
  <?php include APP_ROOT . '/views/includes/sidebar_user.php'; ?>
  <div class="main-content" id="mainContent">
    <header class="topbar">
      <div class="topbar-title"><h1><?= e(PAGE_TITLE) ?></h1><?php if(PAGE_SUB):?><p><?= e(PAGE_SUB) ?></p><?php endif;?></div>
      <div class="topbar-actions">
        <a href="/gym-system/views/user/notifications.php" class="topbar-notif" title="Notifications">&#128276;<?php if($_badge>0):?><span class="notif-dot"></span><?php endif;?></a>
        <div class="topbar-user">
          <?= avatar($_me['name']??'', $_me['profile_image']??null?img_url($_me['profile_image']):null, 32) ?>
          <div><div class="topbar-user-name"><?= e($_me['name']??'') ?></div><div class="topbar-user-role"><?= badge_status($_me['membership_status']??'pending') ?></div></div>
        </div>
        <a href="/gym-system/logout.php" class="btn btn-sm btn-secondary">Logout</a>
      </div>
    </header>
    <div class="page-body">
      <?= render_flash() ?>