<?php
$page  = basename($_SERVER['PHP_SELF']);
$me    = current_user();
$badge = $me ? unread_count((int)$me['id']) : 0;
function sb_active(array $pages): string {
    global $page;
    return in_array($page, $pages) ? 'active' : '';
}
function sb_parent_active(array $pages): string {
    global $page;
    return in_array($page, $pages) ? 'active' : '';
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eecIMdR3duFkjyPMQLVuk0K+g==" crossorigin="anonymous" referrerpolicy="no-referrer">

<aside class="sidebar" id="appSidebar">

  <div class="sidebar-brand">
    <div class="brand-wrap">
      <div class="brand-logo">MSP</div>
      <div class="brand-sub">GYM SYSTEM</div>
    </div>
    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">
      <i class="fa-solid fa-bars"></i>
    </button>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-item">
      <a href="/gym-system/views/admin/dashboard.php" class="nav-link <?= sb_active(['dashboard.php']) ?>">
        <span class="nav-icon"><i class="fa-solid fa-gauge-high"></i></span>
        <span class="nav-label">Dashboard</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/members.php" class="nav-link <?= sb_active(['members.php']) ?>">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
        <span class="nav-label">Members</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/trainers.php" class="nav-link <?= sb_active(['trainers.php']) ?>">
        <span class="nav-icon"><i class="fa-solid fa-dumbbell"></i></span>
        <span class="nav-label">Trainers</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/membership_plans.php" class="nav-link <?= sb_active(['membership_plans.php']) ?>">
        <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span>
        <span class="nav-label">Membership Plans</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/workout_plans.php" class="nav-link <?= sb_active(['workout_plans.php']) ?>">
        <span class="nav-icon"><i class="fa-solid fa-fire-flame-curved"></i></span>
        <span class="nav-label">Workout Plans</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/attendance.php" class="nav-link <?= sb_active(['attendance.php']) ?>">
        <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span>
        <span class="nav-label">Attendance</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/payments.php" class="nav-link <?= sb_active(['payments.php']) ?>">
        <span class="nav-icon"><i class="fa-solid fa-coins"></i></span>
        <span class="nav-label">Payments</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/admin/reports.php" class="nav-link <?= sb_active(['reports.php']) ?>">
        <span class="nav-icon"><i class="fa-solid fa-chart-bar"></i></span>
        <span class="nav-label">Reports</span>
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

<script>
(function () {
  var sidebar  = document.getElementById('appSidebar');
  var overlays = document.querySelectorAll('#sidebarOverlay');
  var overlay  = overlays[0];
  var toggles  = [document.getElementById('sidebarToggle'), document.getElementById('mobileSidebarToggle')];

  function open()  { sidebar.classList.add('mobile-open'); if(overlay) { overlay.classList.add('active'); } document.body.style.overflow = 'hidden'; }
  function close() { sidebar.classList.remove('mobile-open'); if(overlay) { overlay.classList.remove('active'); } document.body.style.overflow = ''; }

  toggles.forEach(function(btn) {
    if (!btn) return;
    btn.addEventListener('click', function() {
      sidebar.classList.contains('mobile-open') ? close() : open();
    });
  });

  if (overlay) overlay.addEventListener('click', close);
})();
</script>