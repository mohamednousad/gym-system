<?php
function upload_image(string $field, ?string $old_path = null): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $old_path;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash('File upload error: code ' . $file['error'], 'error');
        return $old_path;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        flash('File too large. Maximum size is 5MB.', 'error');
        return $old_path;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        flash('Invalid file type. Only JPEG, PNG, GIF, WEBP allowed.', 'error');
        return $old_path;
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        flash('Failed to save uploaded file.', 'error');
        return $old_path;
    }
    if ($old_path && file_exists(APP_ROOT . '/' . $old_path)) {
        @unlink(APP_ROOT . '/' . $old_path);
    }
    return 'uploads/' . $filename;
}

function img_url(?string $path): string {
    if (!$path) return '';
    return '/gym-pro/' . ltrim($path, '/');
}