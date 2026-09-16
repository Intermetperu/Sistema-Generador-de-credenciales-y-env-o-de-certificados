<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success border-0 bg-success alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-white"><i class='bx bx-check-circle'></i></div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">¡Éxito!</h6>
                <div class="text-white"><?= session()->getFlashdata('success') ?></div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-white"><i class='bx bx-error-circle'></i></div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">Error</h6>
                <div class="text-white"><?= session()->getFlashdata('error') ?></div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert alert-warning border-0 bg-warning alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-dark"><i class='bx bx-error'></i></div>
            <div class="ms-3">
                <h6 class="mb-0 text-dark">Advertencia</h6>
                <div class="text-dark"><?= session()->getFlashdata('warning') ?></div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('info')): ?>
    <div class="alert alert-info border-0 bg-info alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-white"><i class='bx bx-info-circle'></i></div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">Información</h6>
                <div class="text-white"><?= session()->getFlashdata('info') ?></div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>
