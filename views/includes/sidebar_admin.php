<?php
$page = basename($_SERVER['PHP_SELF']);
$dir  = basename(dirname($_SERVER['PHP_SELF']));
$me   = current_user();
$badge = $me ? unread_count((int)$me['id']) : 0;
function sb_active(array $pages): string {
    global $page;
    return in_array($page, $pages) ? 'active' : '';
}
function sb_sub_open(array $pages): string {
    global $page;
    return in_array($page, $pages) ? 'display:block' : 'display:none';
}
function sb_parent_active(array $pages): string {
    global $page;
    return in_array($page, $pages) ? 'active' : '';
}
?>
<aside class="sidebar" id="appSidebar">
  <div class="sidebar-brand">
    <div>
      <div class="brand-logo">MSP</div>
      <div class="brand-sub">GYM SYSTEM</div>
    </div>
    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">&#9776;</button>
  </div>

  <nav class="sidebar-nav">

    <div class="nav-section-label">Main</div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/dashboard.php" class="nav-link <?= sb_active(['dashboard.php']) ?>">
        <span class="nav-icon">&#9699;</span>
        <span class="nav-label">Dashboard</span>
      </a>
    </div>

    <div class="nav-section-label">People</div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/members.php" class="nav-link <?= sb_parent_active(['members.php']) ?>">
        <span class="nav-icon">&#128101;</span>
        <span class="nav-label">Members</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="#" class="nav-link <?= sb_parent_active(['trainers.php']) ?>" data-submenu="trainersMenu" aria-expanded="<?= in_array($page, ['trainers.php']) ? 'true' : 'false' ?>">
        <span class="nav-icon">&#127947;</span>
        <span class="nav-label">Trainers</span>
        <span class="nav-chevron">&#9660;</span>
      </a>
      <div class="nav-submenu" id="trainersMenu" style="<?= sb_sub_open(['trainers.php']) ?>">
        <a href="/gym-system/views/admin/trainers.php" class="nav-link <?= sb_active(['trainers.php']) ?>">
          <span class="nav-label">All Trainers</span>
        </a>
      </div>
    </div>

    <div class="nav-section-label">Fitness</div>

    <div class="nav-item">
      <a href="#" class="nav-link <?= sb_parent_active(['membership_plans.php']) ?>" data-submenu="plansMenu" aria-expanded="<?= in_array($page, ['membership_plans.php']) ? 'true' : 'false' ?>">
        <span class="nav-icon">&#127775;</span>
        <span class="nav-label">Memberships</span>
        <span class="nav-chevron">&#9660;</span>
      </a>
      <div class="nav-submenu" id="plansMenu" style="<?= sb_sub_open(['membership_plans.php']) ?>">
        <a href="/gym-system/views/admin/membership_plans.php" class="nav-link <?= sb_active(['membership_plans.php']) ?>">
          <span class="nav-label">Plans</span>
        </a>
      </div>
    </div>

    <div class="nav-item">
      <a href="#" class="nav-link <?= sb_parent_active(['workout_plans.php']) ?>" data-submenu="workoutMenu" aria-expanded="<?= in_array($page, ['workout_plans.php']) ? 'true' : 'false' ?>">
        <span class="nav-icon">&#127947;&#65039;</span>
        <span class="nav-label">Workout</span>
        <span class="nav-chevron">&#9660;</span>
      </a>
      <div class="nav-submenu" id="workoutMenu" style="<?= sb_sub_open(['workout_plans.php']) ?>">
        <a href="/gym-system/views/admin/workout_plans.php" class="nav-link <?= sb_active(['workout_plans.php']) ?>">
          <span class="nav-label">Workout Plans</span>
        </a>
      </div>
    </div>

    <div class="nav-section-label">Operations</div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/attendance.php" class="nav-link <?= sb_active(['attendance.php']) ?>">
        <span class="nav-icon">&#9989;</span>
        <span class="nav-label">Attendance</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="#" class="nav-link <?= sb_parent_active(['payments.php']) ?>" data-submenu="payMenu" aria-expanded="<?= in_array($page, ['payments.php']) ? 'true' : 'false' ?>">
        <span class="nav-icon">&#128176;</span>
        <span class="nav-label">Payments</span>
        <span class="nav-chevron">&#9660;</span>
      </a>
      <div class="nav-submenu" id="payMenu" style="<?= sb_sub_open(['payments.php']) ?>">
        <a href="/gym-system/views/admin/payments.php" class="nav-link <?= sb_active(['payments.php']) ?>">
          <span class="nav-label">All Payments</span>
        </a>
        <a href="/gym-system/views/admin/payments.php?action=add" class="nav-link">
          <span class="nav-label">Record Payment</span>
        </a>
      </div>
    </div>

    <div class="nav-section-label">System</div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/reports.php" class="nav-link <?= sb_active(['reports.php']) ?>">
        <span class="nav-icon">&#128202;</span>
        <span class="nav-label">Reports</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/notifications.php" class="nav-link <?= sb_active(['notifications.php']) ?>">
        <span class="nav-icon">&#128276;</span>
        <span class="nav-label">Notifications<?php if ($badge > 0): ?> <span class="badge badge-danger" style="margin-left:4px"><?= $badge ?></span><?php endif; ?></span>
      </a>
    </div>

  </nav>

  <div class="sidebar-footer">
    <?= avatar($me['name'] ?? 'Admin', null, 34) ?>
    <div class="footer-info">
      <div class="footer-name"><?= e($me['name'] ?? 'Admin') ?></div>
      <div class="footer-role"><?= e($me['role'] ?? '') ?></div>
    </div>
  </div>
</aside>