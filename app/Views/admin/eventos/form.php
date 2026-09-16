<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/alerts') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Credenciales</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= base_url('eventos') ?>">Eventos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($title) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <h5 class="card-title"><?= esc($title) ?></h5>

        <form action="<?= $evento ? base_url("eventos/{$evento['id']}/actualizar") : base_url('eventos') ?>" method="post">
            <?= csrf_field() ?>
            <?php if ($evento): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nombre del evento</label>
                <input type="text" name="nombre" class="form-control" required
                       placeholder="Ej: Aguas y Relaves - Julio 2026"
                       value="<?= old('nombre', $evento['nombre'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Spreadsheet ID de Google Sheets</label>
                <input type="text" name="spreadsheet_id" class="form-control" required
                       placeholder="1efs5ikqyuEdgIoYRc3NLdVHHYKoJ67cGbyRzfsSMUB4"
                       value="<?= old('spreadsheet_id', $evento['spreadsheet_id'] ?? '') ?>">
                <div class="form-text">Es el ID que aparece en la URL del Sheet: .../d/<strong>ESTE_ID</strong>/edit</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Hoja límite (donde se detiene el listado de categorías)</label>
                <input type="text" name="hoja_limite" class="form-control"
                       value="<?= old('hoja_limite', $evento['hoja_limite'] ?? 'VIRTUAL') ?>">
            </div>

            <hr>
            <p class="text-muted small mb-2">Mailgun (opcional: si lo dejas vacío, se usa el de la configuración general)</p>

            <div class="mb-3">
                <label class="form-label">Dominio Mailgun</label>
                <input type="text" name="mailgun_domain" class="form-control"
                       value="<?= old('mailgun_domain', $evento['mailgun_domain'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">API Key Mailgun</label>
                <input type="text" name="mailgun_api_key" class="form-control"
                       value="<?= old('mailgun_api_key', $evento['mailgun_api_key'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">From (remitente)</label>
                <input type="text" name="mailgun_from" class="form-control"
                       placeholder='"Eventos" <no-reply@tudominio.com>'
                       value="<?= old('mailgun_from', $evento['mailgun_from'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="<?= base_url('eventos') ?>" class="btn btn-outline-light">Cancelar</a>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
