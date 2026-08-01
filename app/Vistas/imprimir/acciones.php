<?php if (empty($pdf)): ?>
    <div class="print-actions">
        <button onclick="window.print()">Imprimir</button>
        <a href="<?= $isAssignment ? url('asignaciones/'.$registro['id'].'/pdf') : url('devoluciones/'.$registro['id'].'/pdf') ?>">
            Descargar PDF
        </a>
    </div>
<?php endif; ?>
