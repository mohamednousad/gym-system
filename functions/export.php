<?php
function export_csv(array $data, array $columns, string $filename): never {
    $formatted_filename = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $filename), '_'));
    $final_filename = $formatted_filename . '_' . date('dmY') . '_' . random_int(1000, 9999);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $final_filename) . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, array_values($columns));
    foreach ($data as $row) {
        $line = [];
        foreach ($columns as $key => $label) $line[] = $row[$key] ?? '';
        fputcsv($out, $line);
    }
    fclose($out);
    exit;
}

function export_pdf(array $data, array $columns, string $title): never {
    $filename_title = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $title), '_'));
    $filename = $filename_title . '_' . date('dmY') . '_' . random_int(1000, 9999);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . e($filename) . '</title>';
    echo '<style>
    body{font-family:Arial,sans-serif;padding:20px;font-size:12px;}
    h1{border-bottom:1px solid #ddd;padding-bottom:8px;margin-bottom:20px;}
    table{width:100%;border-collapse:collapse;}
    th{background:#2563eb;color:#ddd;padding:10px;text-align:left;font-size:11px;text-transform:uppercase;}
    td{padding:9px 10px;border-bottom:1px solid #ddd;}
    tr:nth-child(even) td{background:#f9f9f9;}
    .meta{color:#666;font-size:11px;margin-bottom:16px;}
    @media print{button{display:none}}
    </style></head><body>';
    echo '<button onclick="window.print()" style="margin-bottom:16px;padding:8px 16px;color: white;background:#2563eb;border:none;border-radius:4px;cursor:pointer;font-weight:700;">Print / Save PDF</button>';
    echo '<h1>' . e($title) . '</h1>';
    echo '<p class="meta">Generated: ' . date('d M Y H:i') . ' &nbsp;|&nbsp; Records: ' . count($data) . '</p>';
    echo '<table><tr>';
    foreach ($columns as $label) echo '<th>' . e($label) . '</th>';
    echo '</tr>';
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($columns as $key => $label) echo '<td>' . e($row[$key] ?? '-') . '</td>';
        echo '</tr>';
    }
    echo '</table></body></html>';
    exit;
}