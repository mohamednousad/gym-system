<?php
require_once 'config/bootstrap.php';
if (is_logged_in()) {
    redirect(is_admin() ? '/gym-pro/views/admin/dashboard.php' : '/gym-pro/views/user/dashboard.php');
}
redirect('/gym-pro/login.php');