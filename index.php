<?php
require_once 'config/bootstrap.php';
if (is_logged_in()) {
    redirect(is_admin() ? '/gym-system/views/admin/dashboard.php' : '/gym-system/views/user/dashboard.php');
}
redirect('/gym-system/login.php');