<?php
require_once 'config/navigation.php';
logout_user();
redirect('/gym-system/login.php?redirect=auth');