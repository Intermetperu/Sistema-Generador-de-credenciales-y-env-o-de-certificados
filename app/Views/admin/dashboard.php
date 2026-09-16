<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/alerts') ?>

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Panel</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="javascript:;"><i class='bx bx-home-alt'></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Tablero</li>
            </ol>
        </nav>
    </div>
</div>

<?php if (!$evento): ?>
    <div class="alert alert-warning">
        No hay ningún evento activo todavía. Ve a <a href="<?= base_url('eventos') ?>">Eventos</a>, crea uno y actívalo
        para empezar a ver estadísticas aquí.
    </div>
<?php elseif ($errorSheets): ?>
    <div class="alert alert-danger">
        No se pudo conectar a Google Sheets para calcular las estadísticas: <?= esc($errorSheets) ?>
    </div>
<?php else: ?>

<div class="mb-3">
    <span class="badge bg-primary">Evento: <?= esc($evento['nombre']) ?></span>
</div>

<!-- KPIs -->
<div class="row row-cols-1 row-cols-md-4 g-3 mb-3">
    <div class="col">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                    <i class="fa-solid fa-users text-primary" style="font-size:24px;"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Total participantes</p>
                    <h3 class="mb-0"><?= number_format($totalParticipantes) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="fa-solid fa-circle-check text-success" style="font-size:24px;"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Confirmados (enviados)</p>
                    <h3 class="mb-0"><?= number_format($totalConfirmados) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                    <i class="fa-solid fa-clock text-warning" style="font-size:24px;"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">Pendientes</p>
                    <h3 class="mb-0"><?= number_format($totalPendientes) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                    <i class="fa-solid fa-chart-pie text-info" style="font-size:24px;"></i>
                </div>
                <div>
                    <p class="text-muted mb-0 small">% Confirmación</p>
                    <h3 class="mb-0">
                        <?= $totalParticipantes > 0 ? round(($totalConfirmados / $totalParticipantes) * 100) : 0 ?>%
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Barras: participantes por categoría -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fa-solid fa-chart-column me-2 text-muted"></i>Participantes por categoría</h5>
                <canvas id="chartCategorias" height="260"></canvas>
            </div>
        </div>
    </div>
    <!-- Torta: confirmados vs pendientes -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fa-solid fa-chart-pie me-2 text-muted"></i>Confirmados vs. Pendientes</h5>
                <canvas id="chartTorta" height="260"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Histograma: inscripciones por día -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fa-solid fa-calendar-days me-2 text-muted"></i>Inscripciones por día (últimos 14 días con datos)</h5>
                <?php if (empty($porDia)): ?>
                    <p class="text-muted">No hay suficientes datos de fecha para mostrar este gráfico.</p>
                <?php else: ?>
                    <canvas id="chartHistograma" height="90"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tabla detalle por categoría -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title"><i class="fa-solid fa-table-list me-2 text-muted"></i>Detalle por categoría</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Confirmados</th>
                        <th class="text-end">Pendientes</th>
                        <th style="width:200px;">Avance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porCategoria as $c): ?>
                        <?php $pct = $c['total'] > 0 ? round(($c['confirmados'] / $c['total']) * 100) : 0; ?>
                        <tr>
                            <td><strong><?= esc($c['categoria']) ?></strong></td>
                            <td class="text-end"><?= number_format($c['total']) ?></td>
                            <td class="text-end text-success"><?= number_format($c['confirmados']) ?></td>
                            <td class="text-end text-warning"><?= number_format($c['pendientes']) ?></td>
                            <td>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $pct ?>%</small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($porCategoria)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Aún no hay categorías configuradas con datos.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($evento && !$errorSheets): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
const porCategoria = <?= json_encode($porCategoria) ?>;
const porDia = <?= json_encode($porDia) ?>;

// --- Barras: participantes por categoría (confirmados/pendientes apilado) ---
new Chart(document.getElementById('chartCategorias'), {
    type: 'bar',
    data: {
        labels: porCategoria.map(c => c.categoria),
        datasets: [
            {
                label: 'Confirmados',
                data: porCategoria.map(c => c.confirmados),
                backgroundColor: '#27AE60',
                borderRadius: 4,
            },
            {
                label: 'Pendientes',
                data: porCategoria.map(c => c.pendientes),
                backgroundColor: '#F39C12',
                borderRadius: 4,
            },
        ]
    },
    options: {
        responsive: true,
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
        },
        plugins: { legend: { position: 'bottom' } }
    }
});

// --- Torta: confirmados vs pendientes (global) ---
new Chart(document.getElementById('chartTorta'), {
    type: 'doughnut',
    data: {
        labels: ['Confirmados', 'Pendientes'],
        datasets: [{
            data: [<?= $totalConfirmados ?>, <?= $totalPendientes ?>],
            backgroundColor: ['#27AE60', '#F39C12'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

<?php if (!empty($porDia)): ?>
// --- Histograma: inscripciones por día ---
new Chart(document.getElementById('chartHistograma'), {
    type: 'bar',
    data: {
        labels: porDia.map(d => d.fecha),
        datasets: [{
            label: 'Inscripciones',
            data: porDia.map(d => d.total),
            backgroundColor: '#3498DB',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        plugins: { legend: { display: false } }
    }
});
<?php endif; ?>
</script>
<?php endif; ?>
<?= $this->endSection() ?>