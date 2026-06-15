<?php
function e(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post(string $key, string $default = ''): string {
    return trim($_POST[$key] ?? $default);
}

function get(string $key, string $default = ''): string {
    return trim($_GET[$key] ?? $default);
}

function get_int(string $key, int $default = 0): int {
    return (int)($_GET[$key] ?? $default);
}

function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function flash(string $text, string $type = 'success'): void {
    $_SESSION['flash'] = ['text' => $text, 'type' => $type];
}

function render_flash(): string {
    if (!isset($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $icon = $f['type'] === 'success' ? '✓' : ($f['type'] === 'error' ? '✕' : 'ℹ');
    return '<div class="flash flash-' . e($f['type']) . '"><span class="flash-icon">' . $icon . '</span>' . e($f['text']) . '</div>';
}

function money(mixed $amount): string {
    return 'LKR ' . number_format((float)$amount, 2);
}

function badge_status(string $status): string {
    $map = [
        'active'    => 'success',
        'expired'   => 'danger',
        'pending'   => 'info',
        'paid'      => 'success',
        'failed'    => 'danger',
        'present'   => 'success',
        'late'      => 'warning',
        'inactive'  => 'danger',
        'completed' => 'info',
        'paused'    => 'warning',
    ];
    $cls = $map[strtolower($status)] ?? 'default';
    return '<span class="badge badge-' . $cls . '">' . e(ucfirst($status)) . '</span>';
}

function avatar(string $name, ?string $img = null, int $size = 40): string {
    $initial = strtoupper(mb_substr($name, 0, 1));
    if ($img) {
        return '<img src="' . e($img) . '" class="avatar" style="width:' . $size . 'px;height:' . $size . 'px" alt="' . e($name) . '">';
    }
    return '<div class="avatar-initial" style="width:' . $size . 'px;height:' . $size . 'px;font-size:' . (int)($size * 0.4) . 'px">' . $initial . '</div>';
}

function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return (int)($diff / 60) . 'm ago';
    if ($diff < 86400) return (int)($diff / 3600) . 'h ago';
    return (int)($diff / 86400) . 'd ago';
}

function url_with(array $params, array $remove = []): string {
    $current = $_GET;
    foreach ($remove as $k) unset($current[$k]);
    $merged = array_merge($current, $params);
    unset($merged['page']);
    return '?' . http_build_query(array_filter($merged, fn($v) => $v !== ''));
}

function current_url_with_page(int $page): string {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

function member_select(int $selected = 0, string $name = 'user_id'): string {
    $rows = db()->query("SELECT id,name,email FROM users WHERE role='user' ORDER BY name")->fetchAll();
    $html = '<select name="' . e($name) . '" class="form-select">';
    $html .= '<option value="">— Select Member —</option>';
    foreach ($rows as $r) {
        $sel = (int)$selected === (int)$r['id'] ? 'selected' : '';
        $html .= '<option value="' . (int)$r['id'] . '" ' . $sel . '>' . e($r['name']) . ' – ' . e($r['email']) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function plan_select(int $selected = 0, string $name = 'membership_plan_id'): string {
    $rows = db()->query("SELECT id,name,price,duration_days FROM membership_plans WHERE status='active' ORDER BY price")->fetchAll();
    $html = '<select name="' . e($name) . '" class="form-select">';
    foreach ($rows as $r) {
        $sel = (int)$selected === (int)$r['id'] ? 'selected' : '';
        $html .= '<option value="' . (int)$r['id'] . '" ' . $sel . '>' . e($r['name']) . ' — ' . money($r['price']) . ' / ' . (int)$r['duration_days'] . ' days</option>';
    }
    $html .= '</select>';
    return $html;
}

function unread_count(int $user_id): int {
    $s = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0');
    $s->execute(['id' => $user_id]);
    return (int)$s->fetchColumn();
}