<?php
require_once 'config/bootstrap.php';
logout_user();
redirect('/gym-pro/login.php?auth=logged_out');