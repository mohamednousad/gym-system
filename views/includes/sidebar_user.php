<?php
$page = basename($_SERVER['PHP_SELF']);
$me   = current_user();
$badge = $me ? unread_count((int)$me['id']) : 0;
function ub_active(string ...$pages): string { global $page; return in_array($page, $pages) ? 'active' : ''; }
?>
<aside class="sidebar" id="appSidebar">
  <div class="sidebar-brand">
    <div><div class="brand-logo">MSP</div><div class="brand-sub">MEMBER PORTAL</div></div>
    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle">&#9776;</button>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <div class="nav-item"><a href="/gym-system/views/user/dashboard.php" class="nav-link <?= ub_active('dashboard.php') ?>"><span class="nav-icon">&#9699;</span><span class="nav-label">Dashboard</span></a></div>
    <div class="nav-section-label">Fitness</div>
    <div class="nav-item"><a href="/gym-system/views/user/workout.php" class="nav-link <?= ub_active('workout.php') ?>"><span class="nav-icon">&#127947;</span><span class="nav-label">Workout Plans</span></a></div>
    <div class="nav-item"><a href="/gym-system/views/user/membership.php" class="nav-link <?= ub_active('membership.php') ?>"><span class="nav-icon">&#127775;</span><span class="nav-label">Membership</span></a></div>
    <div class="nav-section-label">History</div>
    <div class="nav-item"><a href="/gym-system/views/user/attendance.php" class="nav-link <?= ub_active('attendance.php') ?>"><span class="nav-icon">&#9989;</span><span class="nav-label">Attendance</span></a></div>
    <div class="nav-item"><a href="/gym-system/views/user/payments.php" class="nav-link <?= ub_active('payments.php') ?>"><span class="nav-icon">&#128176;</span><span class="nav-label">Payments</span></a></div>
    <div class="nav-item"><a href="/gym-system/views/user/notifications.php" class="nav-link <?= ub_active('notifications.php') ?>"><span class="nav-icon">&#128276;</span><span class="nav-label">Notifications<?php if($badge>0): ?> <span class="badge badge-danger" style="margin-left:4px"><?= $badge ?></span><?php endif; ?></span></a></div>
    <div class="nav-section-label">Account</div>
    <div class="nav-item"><a href="/gym-system/views/user/profile.php" class="nav-link <?= ub_active('profile.php') ?>"><span class="nav-icon">&#128100;</span><span class="nav-label">Profile</span></a></div>
    <div class="nav-item"><a href="/gym-system/logout.php" class="nav-link"><span class="nav-icon">&#8594;</span><span class="nav-label">Logout</span></a></div>
  </nav>
  <div class="sidebar-footer">
    <?= avatar($me['name'] ?? 'Member', $me['profile_image'] ? img_url($me['profile_image']) : null, 34) ?>
    <div class="footer-info">
      <div class="footer-name"><?= e($me['name'] ?? '') ?></div>
      <div class="footer-role"><?= e($me['membership_plan'] ?? 'No Plan') ?></div>
    </div>
  </div>
</aside>