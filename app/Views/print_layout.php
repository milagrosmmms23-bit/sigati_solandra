<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= e($title ?? 'Documento') ?></title>
    <style>
@page{size:A4 portrait;margin:2mm}
*{box-sizing:border-box}
body{font-family:Arial,DejaVu Sans,sans-serif;color:#000;font-size:14.2px;margin:0;background:#fff}
.print-actions{max-width:176mm;margin:0 auto 10px;padding:8px;background:#eef3f7;border-radius:4px}
.print-actions a,.print-actions button{padding:7px 11px;border:0;border-radius:4px;text-decoration:none;background:#086a62;color:white;cursor:pointer}
.quality-sheet{width:100%;max-width:205mm;min-height:293mm;margin:0 auto;background:#fff;padding:7.5mm 5mm 24mm;position:relative;display:block;overflow:hidden}
.quality-table{width:100%;border-collapse:collapse;table-layout:fixed}
.quality-table td,.quality-table th{border:0.75pt solid #222;padding:4.6px 5.8px;vertical-align:middle;line-height:1.16}
.quality-header{width:171mm;margin:0 auto 8.5mm;table-layout:fixed}
.quality-header col:nth-child(1){width:41mm}
.quality-header col:nth-child(2){width:16mm}
.quality-header col:nth-child(3){width:57mm}
.quality-header col:nth-child(4){width:20mm}
.quality-header col:nth-child(5){width:37mm}
.quality-header td{height:7mm;padding:0 3px}
.quality-logo{text-align:center;overflow:hidden;padding:0 4px}
.solandra-logo{width:36mm;max-width:100%;height:auto;display:block;margin:0 auto}
.quality-title{text-align:center;font-size:16.4px;font-weight:bold;color:#000;letter-spacing:0;white-space:nowrap}
.quality-subtitle{text-align:center;font-size:15.8px;font-weight:bold;color:#000;letter-spacing:0;white-space:nowrap}
.quality-meta{font-size:10.8px;padding:0;line-height:0.96;text-align:center}
.quality-meta div{padding:0.8px 2px;text-align:center}
.quality-sign td{font-size:12.3px;height:7mm;line-height:1;vertical-align:bottom;padding:1px 4px;color:#000;text-align:center;font-weight:bold}
.quality-sign span{display:block;color:#000;text-decoration:none;text-align:center;font-weight:bold}
.section-title{background:#e3e7ec;text-align:center;font-weight:bold;font-size:14px}
.field-label{font-weight:bold;width:20%;font-size:13.8px}
.field-value{font-weight:500}
.dash{text-align:center!important}
.equipment-table{margin-bottom:4.2mm;font-size:15.2px}
.equipment-table td{height:auto;min-height:0}
.asset-table{margin-bottom:7.5mm}
.asset-table,.phone-table{width:172.5mm;margin-left:auto;margin-right:auto}
.assignment-table{width:170mm;margin-left:auto;margin-right:auto}
.asset-table col:nth-child(1){width:34.9mm}
.asset-table col:nth-child(2){width:32.5mm}
.asset-table col:nth-child(3){width:15mm}
.asset-table col:nth-child(4){width:35mm}
.asset-table col:nth-child(5){width:17.5mm}
.asset-table col:nth-child(6){width:2.5mm}
.asset-table col:nth-child(7){width:35mm}
.phone-table col:nth-child(1){width:34.9mm}
.phone-table col:nth-child(2){width:30mm}
.phone-table col:nth-child(3){width:2.5mm}
.phone-table col:nth-child(4){width:17.5mm}
.phone-table col:nth-child(5){width:5mm}
.phone-table col:nth-child(6){width:25mm}
.phone-table col:nth-child(7){width:12.5mm}
.phone-table col:nth-child(8){width:45mm}
.assignment-table col:nth-child(1){width:41.9mm}
.assignment-table col:nth-child(2){width:53.1mm}
.assignment-table col:nth-child(3){width:20mm}
.assignment-table col:nth-child(4){width:10mm}
.assignment-table col:nth-child(5){width:17.5mm}
.assignment-table col:nth-child(6){width:27.5mm}
.asset-table .field-label,.phone-table .field-label,.assignment-table .field-label{width:auto}
.asset-table .dash{text-align:center!important}
.observations{height:10mm;vertical-align:middle;line-height:1.16}
.assigned-title{width:170mm;margin:4.2mm auto 0.9mm;font-weight:bold;font-size:15.2px}
.assigned-table{margin-bottom:4.2mm;font-size:15.2px}
.assignment-table tr:first-child td:nth-child(3),.assignment-table tr:first-child td:nth-child(4),.assignment-table tr:nth-child(2) td:nth-child(3){text-align:center}
.assignment-table .field-label{white-space:nowrap}
.legal-text{width:170mm;margin:0 auto 1.55mm;font-size:14.4px;line-height:1.32;text-align:justify}
.signature-line{width:43mm;border-top:1px solid #000;text-align:center;font-weight:bold;margin:0;padding-top:1px;font-size:15px;position:absolute;right:9mm;bottom:22mm}
.quality-footer{width:172mm;border:0.75pt solid #222;text-align:center;color:#000;font-size:12px;padding:2px 1.4px;margin:0;position:absolute;left:50%;transform:translateX(-50%);bottom:9mm}
.return-title{text-align:center;font-weight:bold;font-size:14px;margin:8mm 0 4mm}
.doc-table{width:100%;border-collapse:collapse;margin:4mm 0}
.doc-table th,.doc-table td{border:1px solid #555;padding:5px;text-align:left;vertical-align:top}
.doc-table th{background:#e8edf2}
@media print{.print-actions{display:none}body{background:#fff}.quality-sheet{margin:0 auto;max-width:none;width:205mm}}
    </style>
</head>
<body><?= $content ?></body>
</html>
