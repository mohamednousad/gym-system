<?php
function paginate(int $total, int $per_page = 5): array {
    $page = max(1, (int)get('page', '1'));
    $total_pages = max(1, (int)ceil($total / $per_page));
    $page = min($page, $total_pages);
    return [
        'page'        => $page,
        'per_page'    => $per_page,
        'total'       => $total,
        'total_pages' => $total_pages,
        'offset'      => ($page - 1) * $per_page,
    ];
}

function render_pagination(array $pag): string {
    if ($pag['total_pages'] <= 1) return '';
    $p = $pag['page'];
    $t = $pag['total_pages'];
    $html = '<nav class="pagination">';
    if ($p > 1) $html .= '<a class="page-btn" href="' . current_url_with_page($p - 1) . '">&laquo; Prev</a>';
    $start = max(1, $p - 2);
    $end   = min($t, $p + 2);
    if ($start > 1) { $html .= '<a class="page-btn" href="' . current_url_with_page(1) . '">1</a>'; if ($start > 2) $html .= '<span class="page-ellipsis">…</span>'; }
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $p ? ' page-btn-active' : '';
        $html .= '<a class="page-btn' . $active . '" href="' . current_url_with_page($i) . '">' . $i . '</a>';
    }
    if ($end < $t) { if ($end < $t - 1) $html .= '<span class="page-ellipsis">…</span>'; $html .= '<a class="page-btn" href="' . current_url_with_page($t) . '">' . $t . '</a>'; }
    if ($p < $t) $html .= '<a class="page-btn" href="' . current_url_with_page($p + 1) . '">Next &raquo;</a>';
    $html .= '</nav>';
    return $html;
}