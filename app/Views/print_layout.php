<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= e($title ?? 'Documento') ?></title>
    <style>
@page{size:A4 portrait;margin:5mm 7mm 4mm}
*{box-sizing:border-box}
body{font-family:Arial,DejaVu Sans,sans-serif;color:#000;font-size:14px;margin:0;background:#fff}
.print-actions{max-width:180mm;margin:0 auto 10px;padding:8px;background:#eef3f7;border-radius:4px}
.print-actions a,.print-actions button{padding:7px 11px;border:0;border-radius:4px;text-decoration:none;background:#086a62;color:white;cursor:pointer}
.quality-sheet{width:100%;max-width:196mm;min-height:0;margin:0 auto;background:#fff;display:block}
.quality-table{width:100%;border-collapse:collapse;table-layout:fixed}
.quality-table td,.quality-table th{border:1px solid #222;padding:4.5px 6px;vertical-align:middle;line-height:1.14}
.quality-header{margin-bottom:5.5mm}
.quality-logo{width:24%;text-align:center;padding:0 3px;overflow:hidden}
.solandra-logo{width:44mm;max-width:100%;height:auto;display:block;margin:0 auto}
.quality-title{text-align:center;font-size:18px;font-weight:bold;color:#555;letter-spacing:0}
.quality-subtitle{text-align:center;font-size:17px;font-weight:bold;color:#555;letter-spacing:0}
.quality-meta{width:22%;font-size:12.5px;padding:0;line-height:0.98;text-align:center}
.quality-meta div{border-bottom:1px solid #222;padding:1px 4px;text-align:center}
.quality-meta div:last-child{border-bottom:0}
.quality-sign td{font-size:14px;height:auto;line-height:1.05;vertical-align:bottom;padding:2px 4px;color:#000}
.quality-sign span{display:block;color:#000;text-decoration:none;text-align:center;font-weight:bold}
.section-title{background:#dfe4ea;text-align:center;font-weight:bold;font-size:14.5px}
.field-label{font-weight:bold;width:20%;font-size:14.5px}
.field-value{font-weight:bold}
.dash{text-align:center!important}
.equipment-table{margin-bottom:4.5mm;font-size:17px}
.equipment-table td{height:auto;min-height:0}
.observations{height:auto;vertical-align:top;line-height:1.1}
.assigned-title{font-weight:bold;margin:4.5mm 0 1mm 5mm;font-size:17px}
.assigned-table{margin-bottom:4.5mm;font-size:17px}
.legal-text{font-size:15.2px;line-height:1.16;text-align:justify;margin:0 0 1.3mm}
.signature-line{width:42mm;border-top:1px solid #000;text-align:center;font-weight:bold;margin:4.5mm 3mm 1mm auto;padding-top:1px;font-size:14px}
.quality-footer{border:1px solid #999;text-align:center;color:#777;font-size:10px;padding:1px;margin:0 4mm}
.return-title{text-align:center;font-weight:bold;font-size:14px;margin:8mm 0 4mm}
.doc-table{width:100%;border-collapse:collapse;margin:4mm 0}
.doc-table th,.doc-table td{border:1px solid #555;padding:5px;text-align:left;vertical-align:top}
.doc-table th{background:#e8edf2}
@media print{.print-actions{display:none}.quality-sheet{margin:0 auto}}
    </style>
</head>
<body><?= $content ?></body>
</html>