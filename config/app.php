<?php
define('APP_NAME', 'MSP GYM');
define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/uploads/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('CSRF_TOKEN_NAME', '_csrf');
define('SESSION_LIFETIME', 7200);