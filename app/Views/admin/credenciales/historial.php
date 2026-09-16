<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Credenciales</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= base_url('credenciales') ?>">Generador</a></li>
                <li class="breadcrumb-item active" aria-current="page">Historial</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Historial de envíos</h5>
        <p class="text-muted">
            Pendiente de portar: comparte <code>modulo_historial.py</code>. En el escritorio probablemente
            lee de un JSON/SQLite local; en la web lo más natural es una tabla MySQL
            <code>credenciales_envios</code> (hoja, nombre, correo, fecha, estado) que se registre
            automáticamente en <code>CredencialesController::enviarCorreos()</code>.
        </p>
    </div>
</div>

<?= $this->endSection() ?>
