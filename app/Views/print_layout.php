<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= e($title ?? 'Documento') ?></title>
    <style>
@page{size:A4 portrait;margin:8mm}
*{box-sizing:border-box}
body{font-family:Arial,DejaVu Sans,sans-serif;color:#000;font-size:10.5px;margin:0;background:#fff}
.print-actions{max-width:176mm;margin:0 auto 10px;padding:8px;background:#eef3f7;border-radius:4px}
.print-actions a,.print-actions button{padding:7px 11px;border:0;border-radius:4px;text-decoration:none;background:#086a62;color:white;cursor:pointer}
.quality-sheet{width:100%;max-width:178mm;min-height:270mm;margin:0 auto;background:#fff;border:1px solid #bfbfbf;padding:10mm 11mm 8mm;position:relative;display:block;overflow:hidden}
.quality-table{width:100%;border-collapse:collapse;table-layout:fixed}
.quality-table td,.quality-table th{border:1px solid #222;padding:3px 5px;vertical-align:middle;line-height:1.18}
.quality-header{margin-bottom:8mm}
.quality-logo{width:24%;text-align:center;padding:0 4px;overflow:hidden}
.solandra-logo{width:34mm;max-width:100%;height:auto;display:block;margin:0 auto}
.quality-title{text-align:center;font-size:12.5px;font-weight:bold;color:#666;letter-spacing:0}
.quality-subtitle{text-align:center;font-size:12.5px;font-weight:bold;color:#666;letter-spacing:0}
.quality-meta{width:22%;font-size:8.5px;padding:0;line-height:1.06;text-align:center}
.quality-meta div{border-bottom:1px solid #222;padding:1.5px 3px;text-align:center}
.quality-meta div:last-child{border-bottom:0}
.quality-sign td{font-size:8.5px;height:auto;line-height:1.05;vertical-align:bottom;padding:2px 4px;color:#000;text-align:center}
.quality-sign span{display:block;color:#000;text-decoration:none;text-align:center;font-weight:bold}
.section-title{background:#dfe4ea;text-align:center;font-weight:bold;font-size:9.5px}
.field-label{font-weight:bold;width:20%;font-size:9.5px}
.field-value{font-weight:bold}
.dash{text-align:center!important}
.equipment-table{margin-bottom:7mm;font-size:10.5px}
.equipment-table td{height:auto;min-height:0}
.observations{height:12mm;vertical-align:middle;line-height:1.18}
.assigned-title{font-weight:bold;margin:6mm 0 1.5mm 5mm;font-size:10.5px}
.assigned-table{margin-bottom:6mm;font-size:10.5px}
.legal-text{font-size:10.2px;line-height:1.34;text-align:justify;margin:0 0 2.1mm}
.signature-line{width:38mm;border-top:1px solid #000;text-align:center;font-weight:bold;margin:8mm 4mm 6mm auto;padding-top:1px;font-size:10px}
.quality-footer{border:1px solid #999;text-align:center;color:#777;font-size:7.5px;padding:1px;margin:0;position:absolute;left:11mm;right:11mm;bottom:8mm}
.return-title{text-align:center;font-weight:bold;font-size:14px;margin:8mm 0 4mm}
.doc-table{width:100%;border-collapse:collapse;margin:4mm 0}
.doc-table th,.doc-table td{border:1px solid #555;padding:5px;text-align:left;vertical-align:top}
.doc-table th{background:#e8edf2}
@media print{.print-actions{display:none}body{background:#fff}.quality-sheet{margin:0 auto;max-width:none;width:178mm}}
    </style>
</head>
<body><?= $content ?></body>
</html>
