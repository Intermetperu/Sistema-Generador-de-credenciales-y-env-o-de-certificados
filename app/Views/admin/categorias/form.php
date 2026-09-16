<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/alerts') ?>

<?php $mapeo = $categoria ? json_decode($categoria['mapeo_columnas'] ?? '{}', true) : []; ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Credenciales</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= base_url('eventos') ?>">Eventos</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url("eventos/{$evento['id']}/categorias") ?>"><?= esc($evento['nombre']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($title) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <h5 class="card-title"><?= esc($title) ?></h5>

        <form action="<?= $categoria
                ? base_url("eventos/{$evento['id']}/categorias/{$categoria['id']}/actualizar")
                : base_url("eventos/{$evento['id']}/categorias") ?>"
              method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if ($categoria): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Nombre de la hoja (debe coincidir con la pestaña en Sheets)</label>
                <input type="text" name="nombre_hoja" class="form-control" required
                       placeholder="Ej: VIP"
                       value="<?= old('nombre_hoja', $categoria['nombre_hoja'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Plantilla de credencial (imagen JPG)</label>
                <input type="file" name="plantilla" class="form-control" accept="image/jpeg">
                <?php if ($categoria && $categoria['plantilla_credencial']): ?>
                    <div class="form-text">
                        Ya tiene imagen cargada
                        (<?= $categoria['plantilla_ancho'] ?>x<?= $categoria['plantilla_alto'] ?>px).
                        Sube otra para reemplazarla.
                    </div>
                <?php endif; ?>
            </div>

            <hr>
            <p class="text-muted small mb-2">
                Mapeo de columnas del Sheet (déjalo igual si tus columnas se llaman como por defecto)
            </p>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Columna Nombre</label>
                    <input type="text" name="mapeo_nombre" class="form-control" value="<?= esc($mapeo['nombre'] ?? 'NOMBRES') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Columna Apellido</label>
                    <input type="text" name="mapeo_apellido" class="form-control" value="<?= esc($mapeo['apellido'] ?? 'APELLIDOS') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Columna Documento</label>
                    <input type="text" name="mapeo_documento" class="form-control" value="<?= esc($mapeo['documento'] ?? 'DNI') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Columna Correo corporativo</label>
                    <input type="text" name="mapeo_correo_corporativo" class="form-control" value="<?= esc($mapeo['correo_corporativo'] ?? 'CORREO CORPORATIVO') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Columna Correo personal</label>
                    <input type="text" name="mapeo_correo_personal" class="form-control" value="<?= esc($mapeo['correo_personal'] ?? 'CORREO PERSONAL') ?>">
                </div>
            </div>

            <hr>
            <p class="text-muted small mb-2">Correo de confirmación — banner (opcional)</p>

            <div class="mb-3">
                <label class="form-label">Imagen de cabecera del correo (banner)</label>
                <input type="file" name="imagen_cabecera" class="form-control" accept="image/jpeg,image/png">
                <?php if ($categoria && $categoria['imagen_cabecera_correo']): ?>
                    <div class="form-text">
                        Banner actual:<br>
                        <img src="<?= base_url($categoria['imagen_cabecera_correo']) ?>" style="max-width:300px; margin-top:6px; border-radius:6px;">
                    </div>
                <?php endif; ?>
                <div class="form-text">
                    ⚠️ Esta imagen debe verse en internet (Gmail/Outlook la descargan desde afuera), así que
                    solo va a mostrarse en los correos reales una vez que subas este proyecto a un dominio con
                    hosting real — en <code>localhost</code> no la van a poder cargar los destinatarios (pero
                    sí se ve bien en el "Preview Envío" dentro del panel, porque ahí la carga tu propio navegador).
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="regenerar_html_auto" value="1" class="form-check-input" id="regenerarAuto">
                <label class="form-check-label" for="regenerarAuto">
                    Generar el HTML del correo automáticamente con este banner
                    (reemplaza lo que tengas escrito abajo en "HTML del correo")
                </label>
            </div>

            <hr>
            <p class="text-muted small mb-2">Correo de confirmación — HTML manual (modo avanzado)</p>

            <div class="mb-3">
                <label class="form-label">Asunto</label>
                <input type="text" name="asunto_correo" class="form-control"
                       placeholder="Tu credencial - Evento 2026"
                       value="<?= old('asunto_correo', $categoria['asunto_correo'] ?? '') ?>">
                <div class="form-text">
                    En tu script original el asunto variaba según la categoría. Usa, por ejemplo:<br>
                    • Categoría "VIRTUAL" → <code>CONFIRMACIÓN DE INSCRIPCIÓN VIRTUAL | FLOTACIÓN 2026</code><br>
                    • Resto de categorías → <code>QR PARA INGRESO | FLOTACIÓN 2026</code>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    HTML del correo
                    <span class="text-muted">(usa <code>{{nombre}}</code>, <code>{{categoria}}</code>, <code>{{qr}}</code> — minúsculas, igual que tu script original)</span>
                </label>
                <textarea name="plantilla_correo_html" class="form-control" rows="10"
                          style="font-family: monospace; font-size: 13px;"
                          placeholder="<html>...<h1>Hola {{nombre}}</h1>...<img src='{{qr}}'>..."><?= old('plantilla_correo_html', $categoria['plantilla_correo_html'] ?? '') ?></textarea>
                <div class="form-text">
                    <code>{{qr}}</code> debe ir dentro de un <code>&lt;img src="{{qr}}"&gt;</code> en tu HTML.
                    Si la categoría se llama exactamente <strong>VIRTUAL</strong>, el sistema no adjunta PDF ni QR (igual que tu script).
                    Si usas el checkbox de arriba, no necesitas escribir nada aquí — se llena solo.
                </div>
            </div>

            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="<?= base_url("eventos/{$evento['id']}/categorias") ?>" class="btn btn-outline-light">Cancelar</a>

            <?php if ($categoria): ?>
                <a href="<?= base_url("eventos/{$evento['id']}/categorias/{$categoria['id']}/posicionar") ?>" class="btn btn-info ms-2">
                    🎯 Ir a ubicar elementos sobre la imagen
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
