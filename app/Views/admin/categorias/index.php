<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/alerts') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Credenciales</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= base_url('eventos') ?>">Eventos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($evento['nombre']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Categorías de "<?= esc($evento['nombre']) ?>"</h5>
            <a href="<?= base_url("eventos/{$evento['id']}/categorias/nueva") ?>" class="btn btn-success btn-sm">+ Nueva categoría</a>
        </div>

        <p class="text-muted">
            El <strong>nombre de hoja</strong> debe coincidir exactamente con el nombre de la pestaña
            en tu Google Sheet (ej: "VIP", "PRENSA", "AUTOR"). Cuando agreguen una categoría nueva
            en el Sheet, créala aquí una sola vez y queda lista para siempre.
        </p>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Plantilla</th>
                        <th>Hoja</th>
                        <th>Posiciones</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $c): ?>
                        <tr>
                            <td style="width:90px;">
                                <?php if ($c['plantilla_credencial']): ?>
                                    <img src="<?= base_url("eventos/{$evento['id']}/categorias/{$c['id']}/imagen") ?>"
                                         alt="" style="width:80px;border-radius:4px;">
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= esc($c['nombre_hoja']) ?></strong></td>
                            <td>
                                <?php if ($c['pos_nombre_x'] || $c['pos_qr_x']): ?>
                                    <span class="badge bg-success">Configuradas</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url("eventos/{$evento['id']}/categorias/{$c['id']}/posicionar") ?>"
                                   class="btn btn-sm btn-outline-info">🎯 Ubicar elementos</a>
                                <a href="<?= base_url("eventos/{$evento['id']}/categorias/{$c['id']}/editar") ?>"
                                   class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="<?= base_url("eventos/{$evento['id']}/categorias/{$c['id']}/eliminar") ?>" method="post" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta categoría?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categorias)): ?>
                        <tr><td colspan="4" class="text-center text-muted">Aún no hay categorías para este evento.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
