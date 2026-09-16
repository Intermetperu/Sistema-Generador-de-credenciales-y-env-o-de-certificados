<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/alerts') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Credenciales</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= base_url('eventos') ?>">Eventos</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url("eventos/{$evento['id']}/categorias") ?>"><?= esc($evento['nombre']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Ubicar elementos - <?= esc($categoria['nombre_hoja']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Ubicar elementos sobre la credencial</h5>
        <p class="text-muted">
            1) Elige qué vas a ubicar abajo &nbsp; 2) Haz clic sobre el punto exacto de la imagen &nbsp;
            3) Ajusta tamaño de letra (o del QR) con el control deslizante &nbsp; 4) Guarda.
        </p>

        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <div class="btn-group" id="grupoBotonesModo" role="group"></div>
            <button type="button" id="btnAgregarCampo" class="btn btn-sm btn-outline-success">+ Agregar campo</button>
        </div>
        <p class="text-muted small mb-3">
            Un "campo" extra es cualquier columna de tu Google Sheet que quieras imprimir en la credencial
            (ej: CARGO, EMPRESA, PAÍS). Escribe el nombre EXACTO de la columna tal como aparece en el Sheet.
        </p>

        <div class="mb-3" id="tamanoControl" style="max-width:300px;">
            <label class="form-label">Tamaño: <span id="tamanoLabel">68</span><span id="tamanoUnidad">px</span></label>
            <input type="range" min="10" max="800" step="2" id="tamanoSlider" class="form-range" value="68">
        </div>

        <div id="imgWrapper" style="position:relative; display:inline-block; cursor:crosshair; max-width:420px; width:100%;">
            <img id="plantillaImg"
                 src="<?= base_url("eventos/{$evento['id']}/categorias/{$categoria['id']}/imagen") ?>"
                 style="width:100%; display:block; user-select:none;" draggable="false">
            <!-- Las marcas (nombre, apellido, qr, campos extra) se inyectan por JS -->
        </div>

        <div class="mt-3">
            <button id="btnGuardar" class="btn btn-success">Guardar posiciones</button>
            <a href="<?= base_url("eventos/{$evento['id']}/categorias") ?>" class="btn btn-outline-light">Volver</a>
        </div>
    </div>
</div>

<style>
.marca {
    position: absolute;
    transform: translate(-50%, -50%);
    color: #fff;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    white-space: nowrap;
    pointer-events: none;
}
.marca-qr {
    position: absolute;
    border: 2px dashed #E67E22;
    background: rgba(230, 126, 34, 0.15);
    pointer-events: none;
}
.btn-eliminar-campo {
    margin-left: 4px;
    cursor: pointer;
    font-weight: bold;
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const img = document.getElementById('plantillaImg');
const wrapper = document.getElementById('imgWrapper');
const ANCHO_REAL = <?= (int) ($categoria['plantilla_ancho'] ?: 1) ?>;

// Estado: 'nombre', 'apellido', 'qr', o el nombre de columna de un campo extra
let modo = 'nombre';

let posiciones = {
    nombre: { x: <?= (int) $categoria['pos_nombre_x'] ?>, y: <?= (int) $categoria['pos_nombre_y'] ?>, tamFuente: <?= (int) $categoria['tam_fuente_nombre'] ?> },
    apellido: { x: <?= (int) $categoria['pos_apellido_x'] ?>, y: <?= (int) $categoria['pos_apellido_y'] ?>, tamFuente: <?= (int) $categoria['tam_fuente_apellido'] ?> },
    qr: {
        x: <?= (int) $categoria['pos_qr_x'] ?>, y: <?= (int) $categoria['pos_qr_y'] ?>,
        ancho: <?= (int) $categoria['qr_ancho'] ?: 420 ?>, alto: <?= (int) $categoria['qr_alto'] ?: 420 ?>
    },
};

// Campos extra ya guardados (vienen del PHP)
let camposExtra = <?= json_encode($camposExtra) ?>; // [{columna, x, y, tamFuente, color}, ...]

function escalaActual() {
    return img.clientWidth / ANCHO_REAL;
}

// ---------- Construcción dinámica de botones de modo ----------
function renderBotonesModo() {
    const grupo = document.getElementById('grupoBotonesModo');
    grupo.innerHTML = '';

    const fijos = [
        { id: 'nombre', label: '📝 Nombre', color: '#27AE60' },
        { id: 'apellido', label: '📝 Apellido', color: '#3498DB' },
        { id: 'qr', label: '▦ QR', color: '#E67E22' },
    ];

    fijos.forEach(f => grupo.appendChild(crearBotonModo(f.id, f.label, f.color, false)));

    camposExtra.forEach((campo, idx) => {
        grupo.appendChild(crearBotonModo(campo.columna, '🏷 ' + campo.columna, campo.color || '#9B59B6', true, idx));
    });
}

function crearBotonModo(id, label, color, esEliminable, idxExtra) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-primary btn-sm' + (modo === id ? ' active' : '');
    btn.style.borderColor = color;
    btn.dataset.modo = id;
    btn.innerHTML = label + (esEliminable ? ' <span class="btn-eliminar-campo" data-idx="' + idxExtra + '">✕</span>' : '');

    btn.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-eliminar-campo')) {
            e.stopPropagation();
            const i = parseInt(e.target.dataset.idx, 10);
            const eliminado = camposExtra.splice(i, 1)[0];
            document.getElementById('marca-' + cssSafe(eliminado.columna))?.remove();
            renderBotonesModo();
            if (modo === eliminado.columna) {
                modo = 'nombre';
            }
            actualizarControlTamano();
            return;
        }
        modo = id;
        renderBotonesModo();
        actualizarControlTamano();
    });

    return btn;
}

function cssSafe(texto) {
    return texto.replace(/[^a-zA-Z0-9_-]/g, '_');
}

document.getElementById('btnAgregarCampo').addEventListener('click', () => {
    const columna = prompt('Nombre EXACTO de la columna en tu Google Sheet (ej: CARGO, EMPRESA, PAIS):');
    if (!columna || !columna.trim()) return;
    const nombreCol = columna.trim();

    if (camposExtra.some(c => c.columna.toUpperCase() === nombreCol.toUpperCase())) {
        alert('Ya agregaste ese campo.');
        return;
    }

    const coloresDisponibles = ['#9B59B6', '#1ABC9C', '#F1C40F', '#E84393', '#16A085', '#D35400'];
    const color = coloresDisponibles[camposExtra.length % coloresDisponibles.length];

    camposExtra.push({ columna: nombreCol, x: 0, y: 0, tamFuente: 32, color: color });
    modo = nombreCol;
    renderBotonesModo();
    actualizarControlTamano();
});

// ---------- Control de tamaño (fuente o tamaño de QR según el modo) ----------
function actualizarControlTamano() {
    const slider = document.getElementById('tamanoSlider');
    const label = document.getElementById('tamanoLabel');
    const unidad = document.getElementById('tamanoUnidad');

    if (modo === 'qr') {
        slider.min = 100; slider.max = 800; slider.step = 10;
        slider.value = posiciones.qr.ancho;
        unidad.textContent = 'px (QR)';
    } else if (modo === 'nombre' || modo === 'apellido') {
        slider.min = 10; slider.max = 150; slider.step = 1;
        slider.value = posiciones[modo].tamFuente;
        unidad.textContent = 'px (letra)';
    } else {
        const campo = camposExtra.find(c => c.columna === modo);
        slider.min = 10; slider.max = 150; slider.step = 1;
        slider.value = campo ? campo.tamFuente : 32;
        unidad.textContent = 'px (letra)';
    }
    label.textContent = slider.value;
}

document.getElementById('tamanoSlider').addEventListener('input', (e) => {
    const valor = parseInt(e.target.value, 10);
    document.getElementById('tamanoLabel').textContent = valor;

    if (modo === 'qr') {
        posiciones.qr.ancho = valor;
        posiciones.qr.alto = valor;
    } else if (modo === 'nombre' || modo === 'apellido') {
        posiciones[modo].tamFuente = valor;
    } else {
        const campo = camposExtra.find(c => c.columna === modo);
        if (campo) campo.tamFuente = valor;
    }
    redibujarMarca(modo);
});

// ---------- Clic sobre la imagen: guarda la posición del modo activo ----------
wrapper.addEventListener('click', (e) => {
    const rect = img.getBoundingClientRect();
    const xDisplay = e.clientX - rect.left;
    const yDisplay = e.clientY - rect.top;
    const escala = escalaActual();

    const xReal = Math.round(xDisplay / escala);
    const yReal = Math.round(yDisplay / escala);

    if (modo === 'qr') {
        posiciones.qr.x = xReal - Math.round(posiciones.qr.ancho / 2);
        posiciones.qr.y = yReal - Math.round(posiciones.qr.alto / 2);
    } else if (modo === 'nombre' || modo === 'apellido') {
        posiciones[modo].x = xReal;
        posiciones[modo].y = yReal;
    } else {
        const campo = camposExtra.find(c => c.columna === modo);
        if (campo) { campo.x = xReal; campo.y = yReal; }
    }

    redibujarMarca(modo);
});

// ---------- Dibujo de marcas sobre la imagen ----------
function redibujarMarca(tipo) {
    const escala = escalaActual();

    if (tipo === 'qr') {
        let el = document.getElementById('marca-qr');
        if (!el) {
            el = document.createElement('div');
            el.id = 'marca-qr';
            el.className = 'marca-qr';
            wrapper.appendChild(el);
        }
        el.style.left = (posiciones.qr.x * escala) + 'px';
        el.style.top = (posiciones.qr.y * escala) + 'px';
        el.style.width = (posiciones.qr.ancho * escala) + 'px';
        el.style.height = (posiciones.qr.alto * escala) + 'px';
        el.style.display = 'block';
        return;
    }

    let texto, x, y, color;
    if (tipo === 'nombre' || tipo === 'apellido') {
        texto = tipo.toUpperCase();
        x = posiciones[tipo].x; y = posiciones[tipo].y;
        color = tipo === 'nombre' ? '#27AE60' : '#3498DB';
    } else {
        const campo = camposExtra.find(c => c.columna === tipo);
        if (!campo) return;
        texto = campo.columna;
        x = campo.x; y = campo.y;
        color = campo.color || '#9B59B6';
    }

    const idEl = 'marca-' + cssSafe(tipo);
    let el = document.getElementById(idEl);
    if (!el) {
        el = document.createElement('div');
        el.id = idEl;
        el.className = 'marca';
        wrapper.appendChild(el);
    }
    el.textContent = texto;
    el.style.background = color;
    el.style.left = (x * escala) + 'px';
    el.style.top = (y * escala) + 'px';
    el.style.display = 'block';
}

function redibujarTodo() {
    redibujarMarca('nombre');
    redibujarMarca('apellido');
    redibujarMarca('qr');
    camposExtra.forEach(c => redibujarMarca(c.columna));
}

img.addEventListener('load', redibujarTodo);
window.addEventListener('resize', redibujarTodo);
if (img.complete) redibujarTodo();

renderBotonesModo();
actualizarControlTamano();

// ---------- Guardar ----------
document.getElementById('btnGuardar').addEventListener('click', () => {
    fetch("<?= base_url("eventos/{$evento['id']}/categorias/{$categoria['id']}/guardar-posiciones") ?>", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
        body: JSON.stringify({ ...posiciones, camposExtra })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert('Posiciones guardadas correctamente.');
        } else {
            alert('Error al guardar.');
        }
    })
    .catch(() => alert('Error de red al guardar.'));
});
</script>
<?= $this->endSection() ?>
