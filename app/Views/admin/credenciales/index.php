<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/alerts') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Credenciales</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>"><i class='bx bx-home-alt'></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Generador de Credenciales y Correos</li>
            </ol>
        </nav>
    </div>
</div>

<?php if (!$evento): ?>
    <div class="alert alert-warning">
        No hay ningún evento activo. Ve a <a href="<?= base_url('eventos') ?>">Eventos</a>, crea uno y actívalo.
    </div>
<?php else: ?>

<?php if (!empty($errorSheets)): ?>
    <div class="alert alert-danger">
        No se pudo conectar a Google Sheets: <?= esc($errorSheets) ?>
    </div>
<?php endif; ?>

<?php if (!empty($hojasSinConfigurar)): ?>
    <div class="alert alert-info">
        Hay hojas en el Sheet sin categoría configurada todavía:
        <strong><?= esc(implode(', ', $hojasSinConfigurar)) ?></strong>.
        <a href="<?= base_url("eventos/{$evento['id']}/categorias/nueva") ?>">Créalas aquí</a> para que aparezcan en el listado.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">

        <div class="row g-2 align-items-center mb-3">
            <div class="col-auto">
                <span class="badge bg-primary">Evento activo: <?= esc($evento['nombre']) ?></span>
            </div>
            <div class="col-auto">
                <label class="form-label mb-0">Hoja:</label>
                <select id="comboHojas" class="form-select form-select-sm">
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($hojas as $h): ?>
                        <option value="<?= esc($h) ?>"><?= esc($h) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <span id="categoriaLabel" class="badge bg-warning text-dark">Categoría: Ninguna</span>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tablaCredenciales" class="table table-striped table-bordered w-100">
                <thead><tr><!-- columnas dinámicas via JS --></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="row g-2 mt-3">
            <div class="col-auto">
                <button id="btnSelTodos" class="btn btn-sm btn-secondary">Sel. Todos</button>
            </div>
            <div class="col-auto">
                <button id="btnSelPendientes" class="btn btn-sm btn-secondary">Sel. Pendientes</button>
            </div>
            <div class="col-auto">
                <button id="btnDeseleccionar" class="btn btn-sm btn-secondary">Deseleccionar</button>
            </div>
            <div class="col-auto">
                <button id="btnGenerarPdf" class="btn btn-sm btn-success">Generar Cred. PDF</button>
            </div>
            <div class="col-auto">
                <button id="btnEnviarCorreos" class="btn btn-sm btn-success">Enviar Correos</button>
            </div>
            <div class="col-auto">
                <button id="btnPreview" class="btn btn-sm btn-info">👁 Preview Envío</button>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-auto">
                <a href="<?= base_url('credenciales/estadisticas') ?>" class="btn btn-sm btn-outline-light">📊 Estadísticas</a>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('credenciales/historial') ?>" class="btn btn-sm btn-outline-light">📋 Historial</a>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('credenciales/igv') ?>" class="btn btn-sm btn-outline-light">Calculadora IGV</a>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label mb-0">Registro de actividad:</label>
            <div id="logArea" class="bg-dark text-light p-2" style="height:140px; overflow-y:auto; font-family: monospace; font-size: 12px;"></div>
        </div>

    </div>
</div>

<!-- Modal Preview Envío -->
<div class="modal fade" id="modalPreview" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview del correo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Asunto:</strong> <span id="previewAsunto"></span></p>
                <p class="text-muted small">Vista previa con los datos del primer registro seleccionado.</p>
                <iframe id="previewFrame" style="width:100%; height:420px; border:1px solid #444; background:#fff;"></iframe>
            </div>
        </div>
    </div>
</div>

<?php endif; // cierre del if ($evento) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($evento): ?>
<script>
const URL_DATOS = "<?= base_url('credenciales/datos') ?>";
const URL_GENERAR_PDF = "<?= base_url('credenciales/generar-pdf') ?>";
const URL_ENVIAR = "<?= base_url('credenciales/enviar') ?>";
const CSRF_NAME = "<?= csrf_token() ?>";
const CSRF_HASH = "<?= csrf_hash() ?>";

let tabla = null;
let mapeoActual = {}; // mapeo de columnas (nombre, apellido, documento, correo_*) de la categoría actual

function log(msg, tipo = 'info') {
    const colores = { info: '#fff', success: '#2ECC71', warning: '#F39C12', error: '#E74C3C' };
    const hora = new Date().toLocaleTimeString();
    const linea = document.createElement('div');
    linea.style.color = colores[tipo] || '#fff';
    linea.textContent = `[${hora}] ${msg}`;
    document.getElementById('logArea').appendChild(linea);
    document.getElementById('logArea').scrollTop = document.getElementById('logArea').scrollHeight;
}

function cargarHoja(hoja) {
    if (!hoja) return;
    document.getElementById('categoriaLabel').textContent = `Categoría: ${hoja}`;
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
            log(`Cargados ${data.total} registros de '${hoja}'`, 'success');
        })
        .catch(e => log(`Error de red: ${e}`, 'error'));
}

function pintarTabla(headers, rows) {
    const tablaElem = document.getElementById('tablaCredenciales');

    // 1. Destruir la instancia existente de DataTable si ya está inicializada
    if (tabla) {
        tabla.destroy();
        tabla = null;
    }

    // 2. Reconstruir la estructura HTML básica thead y tbody dentro de la tabla
    tablaElem.innerHTML = '<thead><tr></tr></thead><tbody></tbody>';

    const theadRow = tablaElem.querySelector('thead tr');
    headers.forEach(h => {
        const th = document.createElement('th');
        th.textContent = h;
        theadRow.appendChild(th);
    });

    // 3. Mapear las columnas para DataTables
    const columns = headers.map(h => ({
        data: h,
        render: function (data) {
            if (h.toUpperCase() === 'CONFIRMACION') {
                return data === true ? 'Enviado' : 'Pendiente';
            }
            return data ?? '';
        }
    }));

    // 4. Inicializar DataTables con la nueva estructura limpia
    tabla = new DataTable('#tablaCredenciales', {
        data: rows,
        columns: columns,
        select: { style: 'multi' },
        order: [],
        rowId: (row) => row._rowIndex,
        createdRow: function (row, data) {
            if (data[headers[0]] === true) {
                row.classList.add('table-success');
            } else {
                row.classList.add('table-warning');
            }
        }
    });
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
        const headers = Object.keys(data).filter(k => k !== '_rowIndex' && k !== 'DT_RowId');
        return data[headers[0]] !== true;
    }).select();
});

// Busca una columna en la fila SIN importar mayúsculas/minúsculas ni espacios extra
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
        raw: r, // fila completa tal cual vino del Sheet, para campos extra
    }));
}

document.getElementById('btnGenerarPdf').addEventListener('click', () => {
    const hoja = document.getElementById('comboHojas').value;
    const filas = filasSeleccionadas();
    if (!hoja || filas.length === 0) {
        alert('Selecciona hoja y filas.');
        return;
    }
    log(`Generando ${filas.length} credenciales PDF...`);

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
            const url = `<?= base_url('credenciales/ver-pdf') ?>/${encodeURIComponent(nombre)}`;
            const linea = document.createElement('div');
            linea.innerHTML = `<a href="${url}" target="_blank" style="color:#5dade2;">📄 Ver PDF: ${nombre}</a>`;
            document.getElementById('logArea').appendChild(linea);
        });
        document.getElementById('logArea').scrollTop = document.getElementById('logArea').scrollHeight;
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
    if (!confirm(`¿Enviar correos con PDF a ${filas.length} destinatarios?`)) return;

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

document.getElementById('btnPreview').addEventListener('click', () => {
    const hoja = document.getElementById('comboHojas').value;
    const filas = filasSeleccionadas();
    if (!hoja) {
        alert('Selecciona una hoja.');
        return;
    }
    if (filas.length === 0) {
        alert('Selecciona al menos una fila para previsualizar con sus datos.');
        return;
    }

    fetch(`<?= base_url('credenciales/preview') ?>/${encodeURIComponent(hoja)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.ok) { alert(data.error); return; }

            const fila = filas[0];
            const nombreCompleto = `${fila.nombre} ${fila.apellido}`.trim().toUpperCase();
            let html = data.html
                .replaceAll('{{nombre}}', nombreCompleto)
                .replaceAll('{{categoria}}', hoja)
                .replaceAll('{{qr}}', data.esVirtual ? '' : 'https://via.placeholder.com/150?text=QR');

            document.getElementById('previewAsunto').textContent = data.asunto;
            const frame = document.getElementById('previewFrame');
            frame.srcdoc = html;

            new bootstrap.Modal(document.getElementById('modalPreview')).show();
        })
        .catch(e => alert('Error de red: ' + e));
});
</script>
<?php endif; // cierre del if ($evento) del bloque scripts ?>
<?= $this->endSection() ?>