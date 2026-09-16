<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?= $this->include('partials/alerts') ?>

<style>
    .btn-anim {
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
    }
    .btn-anim:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,.12);
    }
    .btn-anim:active {
        transform: translateY(0);
        box-shadow: none;
    }
    .btn-anim i { transition: transform .15s ease; }
    .btn-anim:hover i { transform: scale(1.15); }

    .row-hover {
        transition: background-color .15s ease;
    }
    .row-hover:hover {
        background-color: rgba(13,110,253,.04);
    }

    .summary-card {
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,.08);
    }

    .avatar-circle {
        transition: transform .2s ease;
    }
    .row-hover:hover .avatar-circle {
        transform: scale(1.08);
    }

    .badge-pill-anim {
        transition: transform .15s ease;
    }
    .row-hover:hover .badge-pill-anim {
        transform: scale(1.05);
    }

    .fade-in-row {
        animation: fadeInRow .35s ease both;
    }
    @keyframes fadeInRow {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Credenciales</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item active" aria-current="page">Eventos</li>
            </ol>
        </nav>
    </div>
</div>

<?php
    $totalEventos = count($eventos);
    $eventoActivo = null;
    foreach ($eventos as $ev) {
        if ($ev['activo']) { $eventoActivo = $ev; break; }
    }
?>

<!-- Tarjetas de resumen -->
<div class="row mb-3">
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card h-100 summary-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:rgba(13,110,253,.1);">
                    <i class="fa-solid fa-calendar-days fs-5 text-primary"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Eventos registrados</p>
                    <h4 class="mb-0"><?= $totalEventos ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card h-100 summary-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:rgba(25,135,84,.1);">
                    <i class="fa-solid fa-shield-halved fs-5 text-success"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Evento activo</p>
                    <h6 class="mb-0"><?= $eventoActivo ? esc($eventoActivo['nombre']) : 'Ninguno' ?></h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 summary-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:rgba(255,193,7,.15);">
                    <i class="fa-solid fa-circle-info fs-5 text-warning"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Recuerda</p>
                    <h6 class="mb-0">Solo un evento activo a la vez</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div>
                <h5 class="card-title mb-1">Eventos</h5>
                <p class="text-muted small mb-0">
                    Cada mes crea un evento nuevo (con el ID del Google Sheet correspondiente) y actívalo.
                    El módulo de credenciales siempre trabaja sobre el evento marcado como <strong>activo</strong>.
                </p>
            </div>
            <a href="<?= base_url('eventos/nuevo') ?>" class="btn btn-primary btn-sm btn-anim flex-shrink-0">
                <i class="fa-solid fa-plus"></i> Nuevo evento
            </a>
        </div>

        <hr class="mt-2 mb-0">

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0 ps-0">Evento</th>
                        <th class="border-0">Spreadsheet ID</th>
                        <th class="border-0">Estado</th>
                        <th class="border-0">Categorías</th>
                        <th class="border-0 text-end pe-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $i => $e): ?>
                        <tr class="row-hover fade-in-row" style="animation-delay: <?= $i * 0.04 ?>s;">
                            <td class="ps-0">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-semibold avatar-circle"
                                         style="width:36px;height:36px;background:<?= $e['activo'] ? '#198754' : '#adb5bd' ?>;font-size:.85rem;">
                                        <?= strtoupper(substr(esc($e['nombre']), 0, 1)) ?>
                                    </div>
                                    <span class="fw-medium"><?= esc($e['nombre']) ?></span>
                                </div>
                            </td>
                            <td>
                                <code class="small bg-light px-2 py-1 rounded d-inline-flex align-items-center gap-1">
                                    <?= esc(substr($e['spreadsheet_id'], 0, 18)) ?>…
                                    <i class="fa-regular fa-copy text-muted btn-anim" role="button"
                                       title="Copiar ID"
                                       onclick="navigator.clipboard.writeText('<?= esc($e['spreadsheet_id']) ?>')"></i>
                                </code>
                            </td>
                            <td>
                                <?php if ($e['activo']): ?>
                                    <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 badge-pill-anim">
                                        <i class="fa-solid fa-circle" style="font-size:.5rem;"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2 badge-pill-anim">
                                        <i class="fa-solid fa-circle" style="font-size:.5rem;"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url("eventos/{$e['id']}/categorias") ?>" class="link-primary text-decoration-none small fw-medium btn-anim d-inline-block">
                                    Ver / configurar <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </td>
                            <td class="text-end pe-0">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if (!$e['activo']): ?>
                                        <form action="<?= base_url("eventos/{$e['id']}/activar") ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-success btn-anim" type="submit" title="Activar">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="<?= base_url("eventos/{$e['id']}/editar") ?>" class="btn btn-sm btn-outline-secondary btn-anim" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="<?= base_url("eventos/{$e['id']}/eliminar") ?>" method="post" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este evento y todas sus categorías?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="btn btn-sm btn-outline-danger btn-anim" type="submit" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($eventos)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 border-0">
                                <i class="fa-solid fa-calendar-xmark fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-3">Aún no hay eventos creados.</p>
                                <a href="<?= base_url('eventos/nuevo') ?>" class="btn btn-primary btn-sm btn-anim">
                                    <i class="fa-solid fa-plus"></i> Crear el primer evento
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>