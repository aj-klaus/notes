<?php
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $file = "notes_files/{$id}.pdf";

    if (file_exists($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "File not found.";
    }
}
?>
