<?php
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', 'Dashboard');
if (!defined('PAGE_SUB'))   define('PAGE_SUB', '');
$_me    = current_user();
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
  <button class="topbar-mobile-toggle" id="mobileSidebarToggle">
    &#9776;
  </button>

  <div class="topbar-actions">
    <a href="/gym-system/views/user/notifications.php" class="topbar-notif" title="Notifications">
      &#128276;
      <?php if ($_badge > 0): ?><span class="notif-dot"></span><?php endif; ?>
    </a>

    <div class="topbar-profile-wrap" style="position:relative;">
      <button
        type="button"
        id="profileDropdownBtn"
        class="topbar-user"
        style="border:0;background:transparent;color:inherit;cursor:pointer;"
      >
        <?= avatar($_me['name'] ?? '', $_me['profile_image'] ? img_url($_me['profile_image']) : null, 32) ?>
        <div class="topbar-user-info">
          <div class="topbar-user-name"><?= e($_me['name'] ?? '') ?></div>
          <div class="topbar-user-role"><?= badge_status($_me['membership_status'] ?? 'pending') ?></div>
        </div>
      </button>

      <div
        id="profileDropdownMenu"
        style="display:none;position:absolute;right:0;top:44px;min-width:130px;padding:8px;z-index:999;"
      >
        <a href="/gym-system/logout.php" class="btn btn-sm btn-secondary" style="width:100%;justify-content:center;">
          Logout
        </a>
      </div>
    </div>
  </div>
</header>

<script>
document.getElementById("profileDropdownBtn").addEventListener("click", function () {
  const menu = document.getElementById("profileDropdownMenu");
  menu.style.display = menu.style.display === "none" ? "block" : "none";
});
</script>
<div class="page-body">
<?= render_flash() ?>