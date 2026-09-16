<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Credenciales</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="<?= base_url('credenciales') ?>">Generador</a></li>
                <li class="breadcrumb-item active" aria-current="page">Calculadora IGV</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card" style="max-width: 420px;">
    <div class="card-body">
        <h5 class="card-title">Calculadora IGV (18% Perú)</h5>
        <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" id="montoIgv" class="form-control" step="0.01">
        </div>
        <div class="mb-2">Sin IGV: <strong id="sinIgv">0.00</strong></div>
        <div class="mb-2">IGV (18%): <strong id="soloIgv">0.00</strong></div>
        <div>Con IGV: <strong id="conIgv">0.00</strong></div>
        <p class="text-muted small mt-3">
            Implementación básica de relleno. Comparte <code>modulo_igv.py</code> si la lógica
            original calcula algo distinto (ej. extraer IGV de un total ya incluido).
        </p>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('montoIgv').addEventListener('input', (e) => {
    const monto = parseFloat(e.target.value) || 0;
    const igv = monto * 0.18;
    document.getElementById('sinIgv').textContent = monto.toFixed(2);
    document.getElementById('soloIgv').textContent = igv.toFixed(2);
    document.getElementById('conIgv').textContent = (monto + igv).toFixed(2);
});
</script>
<?= $this->endSection() ?>
