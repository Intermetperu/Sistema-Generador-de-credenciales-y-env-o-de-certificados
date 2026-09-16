<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
        <div class="d-flex align-items-start">
            <div class="font-35 text-white"><i class='bx bx-x-circle'></i></div>
            <div class="ms-3">
                <h6 class="mb-1 text-white">Errores de validación</h6>
                <ul class="mb-0 text-white">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>
