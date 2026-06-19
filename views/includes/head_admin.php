<?php
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', 'Admin');
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
<?php include APP_ROOT . '/views/includes/sidebar_admin.php'; ?>
<div class="main-content" id="mainContent">
<header class="topbar">
  <button class="topbar-mobile-toggle" id="mobileSidebarToggle">
    &#9776;
  </button>

  <div class="topbar-actions">
    <a href="/gym-system/views/admin/notifications.php" class="topbar-notif" title="Notifications" style="color:#e0bc52;">
      &#128276;<?php if ($_badge > 0): ?><span class="notif-dot"></span><?php endif; ?>
    </a>

    <div class="topbar-user" onclick="toggleProfileMenu()" style="position:relative;cursor:pointer;">
      <?= avatar($_me['name'] ?? 'Admin', null, 32) ?>
      <div class="topbar-user-info">
        <div class="topbar-user-name"><?= e($_me['name'] ?? 'Admin') ?></div>
        <div class="topbar-user-role"><?= e($_me['role'] ?? '') ?></div>
      </div>

      <div id="profileDropdown" style="display:none;position:absolute;right:0;top:46px;background:#1e1f20;border:1px solid #3f4146;border-radius:10px;min-width:130px;z-index:999;box-shadow:0 8px 32px rgba(0,0,0,.28);">
        <a href="/gym-system/logout.php" style="display:block;padding:10px 14px;color:#e4e6eb;font-size:13px;">
          Logout
        </a>
      </div>
    </div>
  </div>
</header>

<script>
function toggleProfileMenu() {
  const menu = document.getElementById("profileDropdown");
  menu.style.display = menu.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function(e) {
  const user = document.querySelector(".topbar-user");
  const menu = document.getElementById("profileDropdown");

  if (user && menu && !user.contains(e.target)) {
    menu.style.display = "none";
  }
});
</script>
<div class="page-body">
<?= render_flash() ?>