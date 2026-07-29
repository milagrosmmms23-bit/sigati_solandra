<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= e($title ?? 'Documento') ?></title>
    <style>
@page{size:A4 portrait;margin:11mm 15mm 7mm}
*{box-sizing:border-box}
body{font-family:Arial,DejaVu Sans,sans-serif;color:#000;font-size:11px;margin:0;background:#fff}
.print-actions{max-width:180mm;margin:0 auto 10px;padding:8px;background:#eef3f7;border-radius:4px}
.print-actions a,.print-actions button{padding:7px 11px;border:0;border-radius:4px;text-decoration:none;background:#086a62;color:white;cursor:pointer}
.quality-sheet{width:100%;max-width:180mm;min-height:279mm;margin:0 auto;background:#fff;display:flex;flex-direction:column}
.quality-table{width:100%;border-collapse:collapse;table-layout:fixed}
.quality-table td,.quality-table th{border:1px solid #222;padding:4px 5px;vertical-align:middle}
.quality-header{margin-bottom:9mm}
.quality-logo{width:24%;text-align:center;padding:0 5px}
.solandra-logo{width:33mm;height:auto;display:block;margin:0 auto}
.quality-title{text-align:center;font-size:11px;font-weight:bold;color:#666;letter-spacing:0}
.quality-subtitle{text-align:center;font-size:11px;font-weight:bold;color:#777;letter-spacing:0}
.quality-meta{width:22%;font-size:8px;padding:0;line-height:1.08}
.quality-meta div{border-bottom:1px solid #222;padding:2px 3px}
.quality-meta div:last-child{border-bottom:0}
.quality-sign td{font-size:8px;height:15px;line-height:1.08;vertical-align:bottom;padding:2px 4px}
.quality-sign span{display:block;color:#7b0000;text-decoration:underline;text-align:center}
.section-title{background:#dfe4ea;text-align:center;font-weight:bold;font-size:9px}
.field-label{font-weight:bold;width:20%;font-size:9px}
.field-value{font-weight:bold}
.dash{text-align:center!important}
.equipment-table{margin-bottom:8mm}
.equipment-table td{height:18px}
.observations{height:34px;vertical-align:top}
.assigned-title{font-weight:bold;margin:3mm 0 1.2mm 5mm}
.assigned-table{margin-bottom:7mm}
.legal-text{font-size:10px;line-height:1.38;text-align:justify;margin:0 0 2.1mm}
.signature-line{width:42mm;border-top:1px solid #000;text-align:center;font-weight:bold;margin:auto 3mm 4mm auto;padding-top:1px}
.quality-footer{border:1px solid #999;text-align:center;color:#777;font-size:8px;padding:1px;margin:0 4mm}
.return-title{text-align:center;font-weight:bold;font-size:14px;margin:8mm 0 4mm}
.doc-table{width:100%;border-collapse:collapse;margin:4mm 0}
.doc-table th,.doc-table td{border:1px solid #555;padding:5px;text-align:left;vertical-align:top}
.doc-table th{background:#e8edf2}
@media print{.print-actions{display:none}.quality-sheet{margin:0 auto}}
    </style>
</head>
<body><?= $content ?></body>
</html>