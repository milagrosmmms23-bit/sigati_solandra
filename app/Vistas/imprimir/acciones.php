<?php if (empty($pdf)): ?>
    <div class="print-actions">
        <button onclick="window.print()">Imprimir</button>
        <a href="<?= $isAssignment ? url('asignaciones/'.$item['id'].'/pdf') : url('devoluciones/'.$item['id'].'/pdf') ?>">
            Descargar PDF
        </a>
    </div>
<?php endif; ?>
