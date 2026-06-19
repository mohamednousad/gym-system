<?php
$page  = basename($_SERVER['PHP_SELF']);
$me    = current_user();
$badge = $me ? unread_count((int)$me['id']) : 0;
function ub_active(string ...$pages): string {
    global $page;
    return in_array($page, $pages) ? 'active' : '';
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plh7eecIMdR3duFkjyPMQLVuk0K+g==" crossorigin="anonymous" referrerpolicy="no-referrer">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="appSidebar">

  <div class="sidebar-brand">
    <div class="brand-wrap">
      <div class="brand-logo">MSP</div>
      <div class="brand-sub">MEMBER PORTAL</div>
    </div>
  </div>

  <nav class="sidebar-nav">

    <div class="nav-item">
      <a href="/gym-system/views/user/dashboard.php" class="nav-link <?= ub_active('dashboard.php') ?>">
        <span class="nav-icon"><i class="fa-solid fa-gauge-high"></i></span>
        <span class="nav-label">Dashboard</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/user/workout.php" class="nav-link <?= ub_active('workout.php') ?>">
        <span class="nav-icon"><i class="fa-solid fa-fire-flame-curved"></i></span>
        <span class="nav-label">Workout Plans</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/user/membership.php" class="nav-link <?= ub_active('membership.php') ?>">
        <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span>
        <span class="nav-label">Membership</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/user/attendance.php" class="nav-link <?= ub_active('attendance.php') ?>">
        <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span>
        <span class="nav-label">Attendance</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/user/payments.php" class="nav-link <?= ub_active('payments.php') ?>">
        <span class="nav-icon"><i class="fa-solid fa-coins"></i></span>
        <span class="nav-label">Payments</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="/gym-system/views/user/profile.php" class="nav-link <?= ub_active('profile.php') ?>">
        <span class="nav-icon"><i class="fa-solid fa-circle-user"></i></span>
        <span class="nav-label">Profile</span>
      </a>
    </div>
  </nav>

  <div class="sidebar-footer">
    <?= avatar($me['name'] ?? 'Member', $me['profile_image'] ? img_url($me['profile_image']) : null, 34) ?>
    <div class="footer-info">
      <div class="footer-name"><?= e($me['name'] ?? '') ?></div>
      <div class="footer-role"><?= e($me['membership_plan'] ?? 'No Plan') ?></div>
    </div>
  </div>

</aside>

<script>
(function () {
  var sidebar = document.getElementById('appSidebar');
  var overlay = document.getElementById('sidebarOverlay');
  var mobileBtn = document.getElementById('mobileSidebarToggle');

  function open()  { sidebar.classList.add('mobile-open'); overlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
  function close() { sidebar.classList.remove('mobile-open'); overlay.classList.remove('active'); document.body.style.overflow = ''; }

  if (mobileBtn) mobileBtn.addEventListener('click', function () { sidebar.classList.contains('mobile-open') ? close() : open(); });
  if (overlay)   overlay.addEventListener('click', close);
})();
</script>