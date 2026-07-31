<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= e($title ?? 'Documento') ?></title>
    <style>
@page{size:A4 portrait;margin:8mm}
*{box-sizing:border-box}
body{font-family:Arial,DejaVu Sans,sans-serif;color:#000;font-size:12px;margin:0;background:#fff}
.print-actions{max-width:176mm;margin:0 auto 10px;padding:8px;background:#eef3f7;border-radius:4px}
.print-actions a,.print-actions button{padding:7px 11px;border:0;border-radius:4px;text-decoration:none;background:#086a62;color:white;cursor:pointer}
.quality-sheet{width:100%;max-width:180mm;min-height:270mm;margin:0 auto;background:#fff;padding:9mm 9mm 7mm;position:relative;display:block;overflow:hidden}
.quality-table{width:100%;border-collapse:collapse;table-layout:fixed}
.quality-table td,.quality-table th{border:1px solid #222;padding:4.2px 6.2px;vertical-align:middle;line-height:1.16}
.quality-header{margin-bottom:6.4mm}
.quality-logo{width:24%;text-align:center;padding:0 4px;overflow:hidden}
.solandra-logo{width:37mm;max-width:100%;height:auto;display:block;margin:0 auto}
.quality-title{text-align:center;font-size:15.5px;font-weight:bold;color:#666;letter-spacing:0}
.quality-subtitle{text-align:center;font-size:15.5px;font-weight:bold;color:#666;letter-spacing:0}
.quality-meta{width:22%;font-size:10.4px;padding:0;line-height:1;text-align:center}
.quality-meta div{border-bottom:1px solid #222;padding:1.1px 3px;text-align:center}
.quality-meta div:last-child{border-bottom:0}
.quality-sign td{font-size:10.5px;height:auto;line-height:1;vertical-align:bottom;padding:1.5px 4px;color:#000;text-align:center}
.quality-sign span{display:block;color:#000;text-decoration:none;text-align:center;font-weight:bold}
.section-title{background:#dfe4ea;text-align:center;font-weight:bold;font-size:11.5px}
.field-label{font-weight:bold;width:20%;font-size:11.5px}
.field-value{font-weight:bold}
.dash{text-align:center!important}
.equipment-table{margin-bottom:5mm;font-size:12.5px}
.equipment-table td{height:auto;min-height:0}
.observations{height:10mm;vertical-align:middle;line-height:1.1}
.assigned-title{font-weight:bold;margin:4.5mm 0 1mm 5mm;font-size:12.5px}
.assigned-table{margin-bottom:4.5mm;font-size:12.5px}
.legal-text{font-size:11.8px;line-height:1.2;text-align:justify;margin:0 0 1.35mm}
.signature-line{width:38mm;border-top:1px solid #000;text-align:center;font-weight:bold;margin:5.2mm 4mm 4.5mm auto;padding-top:1px;font-size:11.5px}
.quality-footer{border:1px solid #999;text-align:center;color:#777;font-size:8.7px;padding:1px;margin:0;position:absolute;left:9mm;right:9mm;bottom:7mm}
.return-title{text-align:center;font-weight:bold;font-size:14px;margin:8mm 0 4mm}
.doc-table{width:100%;border-collapse:collapse;margin:4mm 0}
.doc-table th,.doc-table td{border:1px solid #555;padding:5px;text-align:left;vertical-align:top}
.doc-table th{background:#e8edf2}
@media print{.print-actions{display:none}body{background:#fff}.quality-sheet{margin:0 auto;max-width:none;width:180mm}}
    </style>
</head>
<body><?= $content ?></body>
</html>
