<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= e($title ?? 'Documento') ?></title>
    <style>
        @page{margin:22mm 16mm}*{box-sizing:border-box}body{font-family:DejaVu Sans,Arial,sans-serif;color:#182235;font-size:11px;margin:0}.print-actions{margin:0 0 16px;padding:10px;background:#eef3f7;border-radius:8px}.print-actions a,.print-actions button{padding:8px 12px;border:0;border-radius:6px;text-decoration:none;background:#086a62;color:white;cursor:pointer}.doc-header{display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #0d7369;padding-bottom:12px;margin-bottom:18px}.logo-box{font-size:23px;font-weight:800;color:#0d7369}.logo-box span{display:block;font-size:10px;color:#5b6576;letter-spacing:1px}.doc-meta{text-align:right}.doc-title{text-align:center;margin:12px 0 18px}.doc-title h1{font-size:18px;margin:0}.doc-title p{margin:5px 0;color:#5c6572}.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 24px;margin-bottom:16px}.info-row{border-bottom:1px solid #dce1e7;padding:6px 0}.info-row b{display:inline-block;min-width:120px}.doc-table{width:100%;border-collapse:collapse;margin:14px 0}.doc-table th,.doc-table td{border:1px solid #b9c1ca;padding:7px;vertical-align:top}.doc-table th{background:#edf3f2;text-align:left}.terms{font-size:10px;text-align:justify;line-height:1.45;margin-top:18px}.signatures{display:grid;grid-template-columns:1fr 1fr;gap:70px;margin-top:65px}.signature{text-align:center;border-top:1px solid #222;padding-top:7px}.doc-footer{position:fixed;bottom:-12mm;left:0;right:0;text-align:center;font-size:9px;color:#687181}@media print{.print-actions{display:none}}
    </style>
</head>
<body><?= $content ?></body>
</html>
