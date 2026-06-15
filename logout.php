<?php
require_once 'config/bootstrap.php';
logout_user();
redirect('/gym-system/login.php?auth=logged_out');