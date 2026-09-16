<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/alerts') ?>

<style>
:root {
    --cert-primary: #3b82f6;
    --cert-primary-dark: #1d4ed8;
    --cert-success: #22c55e;
    --cert-success-dark: #15803d;
    --cert-surface: #ffffff;
    --cert-surface-2: #f4f6fb;
    --cert-border: #e5e9f0;
    --cert-text: #1e2433;
    --cert-text-muted: #7b8494;
}

.cert-hero {
    position: relative;
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 22px;
    background: linear-gradient(135deg, #eef3ff 0%, #f7f9fd 60%, #ffffff 100%);
    border: 1px solid var(--cert-border);
    overflow: hidden;
}
.cert-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(59,130,246,0.14) 0%, rgba(59,130,246,0) 70%);
}
.cert-hero::after {
    content: '';
    position: absolute;
    bottom: -80px; left: 10%;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34,197,94,0.10) 0%, rgba(34,197,94,0) 70%);
}
.cert-hero-inner { position: relative; z-index: 1; }
.cert-hero-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--cert-primary), var(--cert-primary-dark));
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(59,130,246,0.28);
}
.cert-hero-title { font-size: 22px; font-weight: 700; color: var(--cert-text); margin: 0; letter-spacing: -0.01em; }
.cert-hero-subtitle { font-size: 13px; color: var(--cert-text-muted); margin: 2px 0 0; }

.cert-card {
    border-radius: 18px;
    background: var(--cert-surface);
    border: 1px solid var(--cert-border);
    box-shadow: 0 12px 32px rgba(30,36,51,0.06);
}
.cert-card .card-body { padding: 26px 28px; }

.cert-section-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--cert-text-muted);
    margin-bottom: 8px;
    display: flex; align-items: center; gap: 6px;
}

.cert-controls-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
    margin-bottom: 22px;
    padding-bottom: 22px;
    border-bottom: 1px solid var(--cert-border);
}
.cert-field { min-width: 220px; }
.cert-field .form-select,
.cert-field .form-control {
    border-radius: 12px;
    background: var(--cert-surface-2);
    border: 1px solid var(--cert-border);
    color: var(--cert-text);
    font-size: 14px;
    padding: 10px 14px;
}
.cert-field .form-select:focus,
.cert-field .form-control:focus {
    border-color: var(--cert-primary);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
}

.cert-pill {
    border-radius: 999px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 12.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.01em;
}
.cert-pill-evento {
    background: linear-gradient(135deg, var(--cert-primary), var(--cert-primary-dark));
    color: #fff;
    box-shadow: 0 6px 16px rgba(59,130,246,0.25);
}
.cert-pill-categoria-none {
    background: #eef1f6;
    color: #64748b;
    border: 1px solid var(--cert-border);
}
.cert-pill-categoria-set {
    background: #fff7e6;
    color: #b7791f;
    border: 1px solid #fde3a7;
}

.cert-upload-box {
    border: 1.5px dashed #c7cedb;
    border-radius: 14px;
    padding: 12px 16px;
    background: var(--cert-surface-2);
    display: flex; align-items: center; gap: 10px;
    transition: border-color .2s ease, background .2s ease;
}
.cert-upload-box:hover { border-color: var(--cert-primary); }
.cert-upload-box i { font-size: 20px; color: var(--cert-primary); }
.cert-upload-box input[type=file] {
    color: var(--cert-text); font-size: 13px; flex: 1; min-width: 140px;
}
.cert-upload-box input[type=file]::file-selector-button {
    background: #e7ecf5;
    color: var(--cert-text); border: none; border-radius: 8px;
    padding: 6px 12px; margin-right: 10px; font-size: 12px;
}

.btn-cert-upload {
    border-radius: 10px !important;
    padding: 8px 16px !important;
    font-weight: 600;
    font-size: 13px;
    background: var(--cert-text) !important;
    border: 1px solid var(--cert-text) !important;
    color: #fff !important;
    white-space: nowrap;
}
.btn-cert-upload:hover:not(:disabled) { background: #333a4d !important; }
.btn-cert-upload:disabled { background: #cbd2df !important; border-color: #cbd2df !important; }

.cert-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 24px;
}
.cert-btn {
    border-radius: 999px !important;
    padding: 10px 20px !important;
    font-weight: 600;
    font-size: 13.5px;
    border: 1px solid var(--cert-border) !important;
    background: var(--cert-surface-2) !important;
    color: #475065 !important;
    display: inline-flex; align-items: center; gap: 7px;
    transition: transform .15s ease, background .15s ease, box-shadow .15s ease;
}
.cert-btn:hover:not(:disabled) { background: #e9edf5 !important; transform: translateY(-1px); }
.cert-btn:active:not(:disabled) { transform: translateY(0); }

.cert-btn-success {
    background: linear-gradient(135deg, #34d399, var(--cert-success-dark)) !important;
    border: none !important;
    color: #ffffff !important;
    box-shadow: 0 10px 22px rgba(34,197,94,0.22);
}
.cert-btn-success:hover:not(:disabled) {
    box-shadow: 0 14px 26px rgba(34,197,94,0.32);
}

.cert-btn-warning {
    background: linear-gradient(135deg, #fbbf24, #b45309) !important;
    border: none !important;
    color: #ffffff !important;
    box-shadow: 0 10px 22px rgba(251,191,36,0.22);
}
.cert-btn-warning:hover:not(:disabled) {
    box-shadow: 0 14px 26px rgba(251,191,36,0.32);
}

.cert-table-wrap {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid var(--cert-border);
}
#tablaCertificados {
    margin-bottom: 0 !important;
    color: var(--cert-text);
    font-size: 13px;
}
#tablaCertificados thead th {
    background: var(--cert-surface-2);
    color: var(--cert-text-muted);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 700;
    border-bottom: 1px solid var(--cert-border) !important;
    padding: 12px 14px;
}
#tablaCertificados tbody td {
    padding: 11px 14px;
    border-color: var(--cert-border) !important;
    vertical-align: middle;
}
#tablaCertificados tbody tr.table-success td { background: rgba(34,197,94,0.06) !important; }
#tablaCertificados tbody tr.table-warning td { background: rgba(251,191,36,0.08) !important; }
#tablaCertificados tbody tr:hover td { background: rgba(59,130,246,0.05) !important; }

.badge.bg-secondary {
    border-radius: 999px;
    background: #eef1f6 !important;
    color: #64748b !important;
    font-weight: 600;
    padding: 6px 12px;
    font-size: 11.5px;
}

.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border-radius: 8px;
    background: var(--cert-surface-2);
    border: 1px solid var(--cert-border);
    color: var(--cert-text);
}
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter label {
    color: var(--cert-text-muted) !important;
    font-size: 12.5px;
}
.dataTables_wrapper .page-link {
    border-radius: 8px !important;
    margin: 0 2px;
}

.cert-log-header {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 8px;
}
.cert-log-dot { width: 8px; height: 8px; border-radius: 50%; }
#logArea {
    border-radius: 12px !important;
    background: #1a1d27 !important;
    border: 1px solid var(--cert-border);
    padding: 14px !important;
}
#logArea::-webkit-scrollbar { width: 8px; }
#logArea::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 8px; }

.cert-btn-success[href], a.cert-btn-success {
    padding: 4px 12px !important;
    font-size: 12px !important;
}

.cert-mail-editor {
    height: 420px;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 12.5px;
    border-radius: 12px;
    background: var(--cert-surface-2);
    border: 1px solid var(--cert-border);
    color: var(--cert-text);
    resize: vertical;
}
.cert-mail-preview-wrap {
    height: 420px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--cert-border);
    background: #fff;
}
.cert-mail-preview-wrap iframe {
    width: 100%;
    height: 100%;
    border: 0;
}
.cert-mail-vars code {
    background: var(--cert-surface-2);
    border: 1px solid var(--cert-border);
    padding: 2px 6px;
    border-radius: 6px;
    color: var(--cert-primary-dark);
}

/* 👉 Contador de estado Enviados/Pendientes sobre la tabla */
#certContadorEstado {
    margin-bottom: 10px;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    gap: 16px;
}
</style>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Certificados</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>"><i class='bx bx-home-alt'></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Generador de Certificados y Correos</li>
            </ol>
        </nav>
    </div>
</div>

<?php if (!$evento): ?>
    <div class="alert alert-warning">
        No hay ningún evento activo. Ve a <a href="<?= base_url('eventos') ?>">Eventos</a>, crea uno y actívalo.
    </div>
<?php else: ?>

<div class="cert-hero">
    <div class="cert-hero-inner d-flex align-items-center gap-3">
        <div class="cert-hero-icon"><i class='bx bx-certification'></i></div>
        <div>
            <p class="cert-hero-title">Generador de certificados</p>
            <p class="cert-hero-subtitle">Sube la plantilla, selecciona a los participantes y envíales su certificado en un clic.</p>
        </div>
    </div>
</div>

<?php if (!empty($errorSheets)): ?>
    <div class="alert alert-danger">
        No se pudo conectar a Google Sheets: <?= esc($errorSheets) ?>
    </div>
<?php endif; ?>

<div class="card cert-card">
    <div class="card-body">

        <div class="cert-controls-row">
            <div>
                <div class="cert-section-label"><i class='bx bx-calendar-event'></i> Evento</div>
                <span class="cert-pill cert-pill-evento"><i class='bx bx-broadcast'></i> <?= esc($evento['nombre']) ?></span>
            </div>

            <div class="cert-field">
                <div class="cert-section-label"><i class='bx bx-spreadsheet'></i> Hoja</div>
                <select id="comboHojas" class="form-select form-select-sm">
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($hojas as $h): ?>
                        <option value="<?= esc($h) ?>"><?= esc($h) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <div class="cert-section-label"><i class='bx bx-purchase-tag'></i> Categoría</div>
                <span id="categoriaLabel" class="cert-pill cert-pill-categoria-none">Ninguna</span>
            </div>

            <div class="cert-field" style="min-width: 320px;">
                <div class="cert-section-label"><i class='bx bx-image-add'></i> Plantilla del certificado</div>
                <div class="cert-upload-box">
                    <i class='bx bx-cloud-upload'></i>
                    <input type="file" id="inputPlantilla" accept=".png,.jpg,.jpeg" disabled>
                    <button id="btnSubirPlantilla" class="btn btn-sm btn-cert-upload" disabled>Subir</button>
                </div>
                <small class="text-muted d-block mt-1" id="plantillaActualTxt"></small>
            </div>

            <div class="cert-field" style="min-width: 320px;">
                <div class="cert-section-label"><i class='bx bx-link'></i> URL de la encuesta (Google Forms)</div>
                <div class="d-flex gap-2">
                    <input type="text" id="inputLinkEncuesta" class="form-control form-control-sm" placeholder="https://forms.gle/..." disabled>
                    <button id="btnGuardarLinkEncuesta" class="btn btn-sm btn-cert-upload" disabled>Guardar</button>
                </div>
                <small class="text-muted d-block mt-1" id="linkEncuestaGuardadoTxt"></small>
            </div>
        </div>

        <div class="cert-actions">
            <button id="btnSelTodos" class="btn btn-sm cert-btn"><i class='bx bx-check-double'></i> Sel. Todos</button>
            <button id="btnSelPendientes" class="btn btn-sm cert-btn"><i class='bx bx-time-five'></i> Sel. Pendientes</button>
            <button id="btnDeseleccionar" class="btn btn-sm cert-btn"><i class='bx bx-x'></i> Deseleccionar</button>
            <button id="btnGenerarPdf" class="btn btn-sm cert-btn cert-btn-success"><i class='bx bx-file'></i> Generar Certificados PDF</button>
            <button id="btnEnviarCorreos" class="btn btn-sm cert-btn cert-btn-success"><i class='bx bx-envelope'></i> Enviar Correos</button>
            <button id="btnResetearEstado" class="btn btn-sm cert-btn cert-btn-warning"><i class='bx bx-reset'></i> Resetear Estado</button>
        </div>

        <div class="cert-table-wrap">
            <div class="table-responsive">
                <table id="tablaCertificados" class="table table-striped table-bordered w-100 mb-0">
                    <thead><tr><!-- columnas dinámicas via JS --></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <div class="cert-log-header">
                <span class="cert-log-dot" style="background:#ef4444;"></span>
                <span class="cert-log-dot" style="background:#f59e0b;"></span>
                <span class="cert-log-dot" style="background:#22c55e;"></span>
                <span class="cert-section-label mb-0 ms-2">Registro de actividad</span>
            </div>
            <div id="logArea" class="bg-dark text-light p-2" style="height:140px; overflow-y:auto; font-family: monospace; font-size: 12px;"></div>
        </div>

    </div>
</div>

<div class="card cert-card mt-3">
    <div class="card-body">
        <div class="cert-section-label"><i class='bx bx-mail-send'></i> Plantilla del correo de certificado</div>
        <p class="text-muted" style="font-size:13px; margin-top:-2px;">
            Este es el correo que recibe el participante con el link para completar la encuesta y descargar su certificado.
            Selecciona una hoja arriba para cargar (o crear) su plantilla.
        </p>

        <div class="row g-3 mb-2">
            <div class="col-md-8">
                <div class="cert-section-label">Asunto</div>
                <input type="text" id="inputAsuntoCorreo" class="form-control form-control-sm"
                       placeholder="Certificado - {{evento}}" disabled>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button id="btnGuardarPlantillaCorreo" class="btn btn-sm cert-btn cert-btn-success w-100" disabled>
                    <i class='bx bx-save'></i> Guardar plantilla de correo
                </button>
            </div>
        </div>

        <div class="cert-mail-vars mb-3" style="font-size:12.5px;">
            Variables disponibles:
            <code>{{nombre}}</code> <code>{{categoria}}</code> <code>{{evento}}</code> <code>{{link_certificado}}</code>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="cert-section-label">Código HTML del correo</div>
                <textarea id="inputHtmlCorreo" class="form-control cert-mail-editor"
                          placeholder="Pega aquí el HTML del correo..." disabled spellcheck="false"></textarea>
            </div>
            <div class="col-lg-6">
                <div class="cert-section-label">Vista previa</div>
                <div class="cert-mail-preview-wrap">
                    <iframe id="previewCorreo" title="Vista previa del correo"></iframe>
                </div>
            </div>
        </div>

        <small class="text-muted d-block mt-2" id="plantillaCorreoGuardadoTxt"></small>
    </div>
</div>

<?php endif; // cierre del if ($evento) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($evento): ?>
<script>
const URL_DATOS = "<?= base_url('certificados/datos') ?>";
const URL_SUBIR_PLANTILLA = "<?= base_url('certificados/subir-plantilla') ?>";
const URL_GUARDAR_LINK_ENCUESTA = "<?= base_url('certificados/guardar-link-encuesta') ?>";
const URL_GUARDAR_PLANTILLA_CORREO = "<?= base_url('certificados/guardar-plantilla-correo') ?>";
const NOMBRE_EVENTO_ACTUAL = "<?= esc($evento['nombre'] ?? '', 'js') ?>";
const URL_GENERAR_PDF = "<?= base_url('certificados/generar-pdf') ?>";
const URL_ENVIAR = "<?= base_url('certificados/enviar') ?>";
const URL_RESETEAR = "<?= base_url('certificados/resetear-confirmacion') ?>";
const CSRF_NAME = "<?= csrf_token() ?>";
const CSRF_HASH = "<?= csrf_hash() ?>";

let tabla = null;
let mapeoActual = {};

// Solo estas columnas se muestran en la tabla de Certificados (el resto del
// Sheet sigue disponible por detrás para identificar filas y enviar correos,
// solo se ocultan visualmente).
// 👉 Se agregó 'CONFIRMACION' para mostrar el estado Enviado/Pendiente.
const COLUMNAS_VISIBLES = ['NOMBRES', 'APELLIDOS', 'CORREO CORPORATIVO', 'CORREO PERSONAL', 'PAÍS', 'PAIS', 'CONFIRMACION'];

function log(msg, tipo = 'info') {
    const colores = { info: '#fff', success: '#2ECC71', warning: '#F39C12', error: '#E74C3C' };
    const hora = new Date().toLocaleTimeString();
    const linea = document.createElement('div');
    linea.style.color = colores[tipo] || '#fff';
    linea.textContent = `[${hora}] ${msg}`;
    document.getElementById('logArea').appendChild(linea);
    document.getElementById('logArea').scrollTop = document.getElementById('logArea').scrollHeight;
}

function toggleControlesPlantilla(activos) {
    document.getElementById('inputPlantilla').disabled = !activos;
    document.getElementById('btnSubirPlantilla').disabled = !activos;
    document.getElementById('inputLinkEncuesta').disabled = !activos;
    document.getElementById('btnGuardarLinkEncuesta').disabled = !activos;
    document.getElementById('inputAsuntoCorreo').disabled = !activos;
    document.getElementById('inputHtmlCorreo').disabled = !activos;
    document.getElementById('btnGuardarPlantillaCorreo').disabled = !activos;
}

function actualizarPreviewCorreo() {
    const html = document.getElementById('inputHtmlCorreo').value;
    const hoja = document.getElementById('comboHojas').value || 'CATEGORÍA';
    const previewHtml = html
        .replaceAll('{{nombre}}', 'JUAN PÉREZ GARCÍA')
        .replaceAll('{{categoria}}', hoja)
        .replaceAll('{{evento}}', NOMBRE_EVENTO_ACTUAL || 'Nombre del evento')
        .replaceAll('{{link_certificado}}', '#');
    document.getElementById('previewCorreo').srcdoc = previewHtml;
}
document.getElementById('inputHtmlCorreo').addEventListener('input', actualizarPreviewCorreo);

function cargarHoja(hoja) {
    if (!hoja) return;
    document.getElementById('categoriaLabel').textContent = hoja;
    document.getElementById('categoriaLabel').classList.remove('cert-pill-categoria-none');
    document.getElementById('categoriaLabel').classList.add('cert-pill-categoria-set');
    toggleControlesPlantilla(true);
    log(`Cargando hoja '${hoja}'...`);

    fetch(`${URL_DATOS}/${encodeURIComponent(hoja)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.ok) {
                log(`Error: ${data.error}`, 'error');
                return;
            }
            mapeoActual = data.mapeo || {};
            pintarTabla(data.headers, data.rows);
            document.getElementById('inputLinkEncuesta').value = data.linkEncuesta || '';
            document.getElementById('linkEncuestaGuardadoTxt').textContent = data.linkEncuesta ? 'Guardado' : 'Sin configurar';

            document.getElementById('inputAsuntoCorreo').value = data.asuntoCorreo || '';
            document.getElementById('inputHtmlCorreo').value = data.plantillaCorreo || '';
            document.getElementById('plantillaCorreoGuardadoTxt').textContent = data.plantillaCorreo ? 'Guardado' : 'Sin configurar';
            actualizarPreviewCorreo();

            log(`Cargados ${data.total} registros de '${hoja}'`, 'success');
        })
        .catch(e => log(`Error de red: ${e}`, 'error'));
}

function pintarTabla(headersCompletos, rows) {
    if (tabla) {
        tabla.destroy();
        document.querySelector('#tablaCertificados thead tr').innerHTML = '';
        document.querySelector('#tablaCertificados tbody').innerHTML = '';
    }

    // Filtramos a solo las columnas que queremos mostrar, respetando el
    // orden en que vienen del Sheet (headersCompletos), pero sin perder
    // los demás datos de cada fila (siguen en el objeto `rows`).
    const headers = headersCompletos.filter(h => COLUMNAS_VISIBLES.includes(h.trim().toUpperCase()));

    const theadRow = document.querySelector('#tablaCertificados thead tr');
    headers.forEach(h => {
        const th = document.createElement('th');
        // 👉 Encabezado más amigable para la columna de estado
        th.textContent = h.trim().toUpperCase() === 'CONFIRMACION' ? 'ESTADO' : h;
        theadRow.appendChild(th);
    });

    const columns = headers.map(h => ({
        data: h,
        render: function (data, type, row) {
            if (h.trim().toUpperCase() === 'CONFIRMACION') {
                // 👉 Badge visual en vez de solo texto
                return data === true
                    ? '<span class="badge" style="background:#22c55e;color:#fff;">✓ Enviado</span>'
                    : '<span class="badge" style="background:#f59e0b;color:#fff;">⏱ Pendiente</span>';
            }
            return data ?? '';
        }
    }));

    tabla = new DataTable('#tablaCertificados', {
        data: rows,
        columns: columns,
        select: { style: 'multi' },
        order: [],
        rowId: (row) => row._rowIndex,
        createdRow: function (row, data) {
            if (buscarColumna(data, 'CONFIRMACION') === true) {
                row.classList.add('table-success');
            } else {
                row.classList.add('table-warning');
            }
        }
    });

    actualizarContadorEstado(rows);
}

// 👉 Contador de enviados/pendientes sobre la tabla
function actualizarContadorEstado(rows) {
    let contador = document.getElementById('certContadorEstado');
    if (!contador) {
        contador = document.createElement('div');
        contador.id = 'certContadorEstado';
        document.querySelector('.cert-table-wrap').before(contador);
    }
    const enviados = rows.filter(r => buscarColumna(r, 'CONFIRMACION') === true).length;
    const pendientes = rows.length - enviados;
    contador.innerHTML = `
        <span style="color:#15803d;">✓ Enviados: ${enviados}</span>
        <span style="color:#b7791f;">⏱ Pendientes: ${pendientes}</span>
        <span style="color:#64748b;">Total: ${rows.length}</span>
    `;
}

document.getElementById('comboHojas').addEventListener('change', (e) => cargarHoja(e.target.value));

document.getElementById('btnSelTodos').addEventListener('click', () => {
    tabla?.rows().select();
});

document.getElementById('btnDeseleccionar').addEventListener('click', () => {
    tabla?.rows().deselect();
});

document.getElementById('btnSelPendientes').addEventListener('click', () => {
    if (!tabla) return;
    tabla.rows().deselect();
    tabla.rows((idx, data) => {
        return buscarColumna(data, 'CONFIRMACION') !== true;
    }).select();
});

function buscarColumna(row, nombreBuscado) {
    if (!nombreBuscado) return '';
    const claveNorm = nombreBuscado.trim().toLowerCase();
    for (const key in row) {
        if (key.trim().toLowerCase() === claveNorm && row[key]) {
            return row[key];
        }
    }
    return '';
}

function filasSeleccionadas() {
    if (!tabla) return [];
    const m = mapeoActual;
    return tabla.rows({ selected: true }).data().toArray().map(r => ({
        rowIndex: r._rowIndex,
        nombre: buscarColumna(r, m.nombre) || buscarColumna(r, 'NOMBRES'),
        apellido: buscarColumna(r, m.apellido) || buscarColumna(r, 'APELLIDOS'),
        documento: buscarColumna(r, m.documento) || buscarColumna(r, 'DOCUMENTO DE IDENTIDAD'),
        correoCorporativo: buscarColumna(r, m.correo_corporativo) || buscarColumna(r, 'CORREO CORPORATIVO'),
        correoPersonal: buscarColumna(r, m.correo_personal) || buscarColumna(r, 'CORREO PERSONAL'),
        raw: r,
    }));
}

document.getElementById('btnSubirPlantilla').addEventListener('click', () => {
    const hoja = document.getElementById('comboHojas').value;
    const file = document.getElementById('inputPlantilla').files[0];
    if (!hoja || !file) {
        alert('Selecciona una hoja y un archivo de plantilla.');
        return;
    }

    const fd = new FormData();
    fd.append('plantilla', file);
    fd.append('hoja', hoja);
    fd.append(CSRF_NAME, CSRF_HASH);

    log('Subiendo plantilla...');
    fetch(URL_SUBIR_PLANTILLA, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) { log(`Error: ${data.error}`, 'error'); return; }
            log(`Plantilla subida: ${data.archivo}`, 'success');
            document.getElementById('plantillaActualTxt').textContent = 'Plantilla actual: ' + data.archivo;
        })
        .catch(e => log(`Error de red: ${e}`, 'error'));
});

document.getElementById('btnGuardarLinkEncuesta').addEventListener('click', () => {
    const hoja = document.getElementById('comboHojas').value;
    const url = document.getElementById('inputLinkEncuesta').value.trim();
    if (!hoja) {
        alert('Selecciona una hoja primero.');
        return;
    }

    const fd = new FormData();
    fd.append('hoja', hoja);
    fd.append('url', url);
    fd.append(CSRF_NAME, CSRF_HASH);

    log('Guardando link de encuesta...');
    fetch(URL_GUARDAR_LINK_ENCUESTA, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) { log(`Error: ${data.error}`, 'error'); return; }
            log('Link de encuesta guardado', 'success');
            document.getElementById('linkEncuestaGuardadoTxt').textContent = url ? 'Guardado' : 'Sin configurar';
        })
        .catch(e => log(`Error de red: ${e}`, 'error'));
});

document.getElementById('btnGuardarPlantillaCorreo').addEventListener('click', () => {
    const hoja = document.getElementById('comboHojas').value;
    const asunto = document.getElementById('inputAsuntoCorreo').value.trim();
    const html = document.getElementById('inputHtmlCorreo').value;

    if (!hoja) {
        alert('Selecciona una hoja primero.');
        return;
    }
    if (!html.trim()) {
        alert('Pega el código HTML del correo antes de guardar.');
        return;
    }

    const fd = new FormData();
    fd.append('hoja', hoja);
    fd.append('asunto', asunto);
    fd.append('html', html);
    fd.append(CSRF_NAME, CSRF_HASH);

    log('Guardando plantilla de correo...');
    fetch(URL_GUARDAR_PLANTILLA_CORREO, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) { log(`Error: ${data.error}`, 'error'); return; }
            log('Plantilla de correo guardada', 'success');
            document.getElementById('plantillaCorreoGuardadoTxt').textContent =
                'Guardado ✓' + (asunto ? ` · Asunto: ${asunto}` : '');
        })
        .catch(e => log(`Error de red: ${e}`, 'error'));
});

document.getElementById('btnGenerarPdf').addEventListener('click', () => {
    const hoja = document.getElementById('comboHojas').value;
    const filas = filasSeleccionadas();
    if (!hoja || filas.length === 0) {
        alert('Selecciona hoja y filas.');
        return;
    }
    log(`Generando ${filas.length} certificados PDF...`);

    fetch(URL_GENERAR_PDF, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', [CSRF_NAME]: CSRF_HASH },
        body: JSON.stringify({ hoja, filas })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) { log(`Error: ${data.error}`, 'error'); return; }
        log(`PDFs generados: ${data.generados}/${data.total}`, data.errores.length ? 'warning' : 'success');
        data.errores.forEach(e => log(e, 'error'));

        (data.nombresGenerados || []).forEach(nombre => {
            const url = `<?= base_url('certificados/ver-pdf') ?>/${encodeURIComponent(nombre)}`;
            const linea = document.createElement('div');
            linea.innerHTML = `<a href="${url}" target="_blank" style="color:#5dade2;">📄 Ver PDF: ${nombre}</a>`;
            document.getElementById('logArea').appendChild(linea);
        });
        document.getElementById('logArea').scrollTop = document.getElementById('logArea').scrollHeight;

        if (document.getElementById('comboHojas').value) {
            cargarHoja(document.getElementById('comboHojas').value);
        }
    })
    .catch(e => log(`Error de red: ${e}`, 'error'));
});

document.getElementById('btnEnviarCorreos').addEventListener('click', () => {
    const hoja = document.getElementById('comboHojas').value;
    const filas = filasSeleccionadas();
    if (!hoja || filas.length === 0) {
        alert('Selecciona hoja y filas.');
        return;
    }
    if (!confirm(`¿Enviar correos con certificado a ${filas.length} destinatarios?`)) return;

    log(`Enviando ${filas.length} correos...`);

    fetch(URL_ENVIAR, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', [CSRF_NAME]: CSRF_HASH },
        body: JSON.stringify({ hoja, filas })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) { log(`Error: ${data.error}`, 'error'); return; }
        log(`Enviados OK: ${data.exitosos.length}`, 'success');
        data.fallidos.forEach(f => log(f, 'error'));
        if (document.getElementById('comboHojas').value) {
            cargarHoja(document.getElementById('comboHojas').value);
        }
    })
    .catch(e => log(`Error de red: ${e}`, 'error'));
});

// 👉 Resetear estado (CONFIRMACION = false) para las filas seleccionadas.
// No borra ningún PDF ni deshace el envío del correo; solo corrige el
// estado que se muestra en el panel cuando el Sheet trae datos incorrectos
// o de pruebas anteriores.
document.getElementById('btnResetearEstado').addEventListener('click', () => {
    const hoja = document.getElementById('comboHojas').value;
    const filas = filasSeleccionadas();
    if (!hoja || filas.length === 0) {
        alert('Selecciona hoja y filas.');
        return;
    }
    if (!confirm(`¿Resetear el estado de ${filas.length} registros a "Pendiente"? Esto NO borra el PDF ni el correo ya enviado, solo el estado en el Sheet.`)) return;

    const rowIndices = filas.map(f => f.rowIndex);

    log(`Reseteando estado de ${filas.length} registros...`);
    fetch(URL_RESETEAR, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', [CSRF_NAME]: CSRF_HASH },
        body: JSON.stringify({ hoja, rowIndices })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) { log(`Error: ${data.error}`, 'error'); return; }
        log(`Estado reseteado: ${data.reseteados} registros`, 'success');
        if (document.getElementById('comboHojas').value) {
            cargarHoja(document.getElementById('comboHojas').value);
        }
    })
    .catch(e => log(`Error de red: ${e}`, 'error'));
});
</script>
<?php endif; // cierre del if ($evento) del bloque scripts ?>
<?= $this->endSection() ?>