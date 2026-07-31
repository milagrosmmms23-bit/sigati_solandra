<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= e($title ?? 'Documento') ?></title>
    <style>
@page{size:A4 portrait;margin:3mm}
*{box-sizing:border-box}
body{font-family:Arial,DejaVu Sans,sans-serif;color:#000;font-size:13.6px;margin:0;background:#fff}
.print-actions{max-width:176mm;margin:0 auto 10px;padding:8px;background:#eef3f7;border-radius:4px}
.print-actions a,.print-actions button{padding:7px 11px;border:0;border-radius:4px;text-decoration:none;background:#086a62;color:white;cursor:pointer}
.quality-sheet{width:100%;max-width:202mm;min-height:291mm;margin:0 auto;background:#fff;padding:5.5mm 6mm 5mm;position:relative;display:block;overflow:hidden}
.quality-table{width:100%;border-collapse:collapse;table-layout:fixed}
.quality-table td,.quality-table th{border:1px solid #222;padding:3.6px 5.2px;vertical-align:middle;line-height:1.08}
.quality-header{margin-bottom:4mm}
.quality-logo{width:24%;text-align:center;padding:0 4px;overflow:hidden}
.solandra-logo{width:41mm;max-width:100%;height:auto;display:block;margin:0 auto}
.quality-title{text-align:center;font-size:18.5px;font-weight:bold;color:#666;letter-spacing:0}
.quality-subtitle{text-align:center;font-size:18px;font-weight:bold;color:#666;letter-spacing:0}
.quality-meta{width:22%;font-size:12px;padding:0;line-height:0.94;text-align:center}
.quality-meta div{border-bottom:1px solid #222;padding:0.6px 3px;text-align:center}
.quality-meta div:last-child{border-bottom:0}
.quality-sign td{font-size:12px;height:auto;line-height:0.94;vertical-align:bottom;padding:1px 4px;color:#000;text-align:center}
.quality-sign span{display:block;color:#000;text-decoration:none;text-align:center;font-weight:bold}
.section-title{background:#dfe4ea;text-align:center;font-weight:bold;font-size:13.2px}
.field-label{font-weight:bold;width:20%;font-size:13px}
.field-value{font-weight:bold}
.dash{text-align:center!important}
.equipment-table{margin-bottom:3.2mm;font-size:14.2px}
.equipment-table td{height:auto;min-height:0}
.observations{height:7mm;vertical-align:middle;line-height:1.04}
.assigned-title{font-weight:bold;margin:3mm 0 0.6mm 5mm;font-size:14.2px}
.assigned-table{margin-bottom:3mm;font-size:14.2px}
.legal-text{font-size:13px;line-height:1.07;text-align:justify;margin:0 0 0.75mm}
.signature-line{width:40mm;border-top:1px solid #000;text-align:center;font-weight:bold;margin:3.7mm 4mm 3.2mm auto;padding-top:1px;font-size:12.8px}
.quality-footer{border:1px solid #999;text-align:center;color:#777;font-size:9.6px;padding:1px;margin:0;position:absolute;left:6mm;right:6mm;bottom:5mm}
.return-title{text-align:center;font-weight:bold;font-size:14px;margin:8mm 0 4mm}
.doc-table{width:100%;border-collapse:collapse;margin:4mm 0}
.doc-table th,.doc-table td{border:1px solid #555;padding:5px;text-align:left;vertical-align:top}
.doc-table th{background:#e8edf2}
@media print{.print-actions{display:none}body{background:#fff}.quality-sheet{margin:0 auto;max-width:none;width:202mm}}
    </style>
</head>
<body><?= $content ?></body>
</html>
