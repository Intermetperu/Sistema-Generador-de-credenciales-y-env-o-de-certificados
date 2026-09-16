let table;
document.addEventListener('DOMContentLoaded', function () {
    table = new DataTable('#loansTable', {
        responsive: true,
        ajax: {
            url: base_url + "loans/list",
            data: function (d) {
                d.cliente_id = document.getElementById('cliente_id').value;
                d.advisor_id = document.getElementById('advisor_id').value;
                d.tipo_plazo = document.getElementById('tipo_plazo').value;
                d.fecha_inicio = document.getElementById('fecha_inicio').value;
                d.fecha_fin = document.getElementById('fecha_fin').value;
            },
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    let buttons = `
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cogs"></i> Opciones
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="${base_url}loans/installment/detailt/${data.encrypted_id}">
                                        <i class="fas fa-coins"></i> Cuotas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="${base_url}loans/contract/${data.encrypted_id}" target="_blank">
                                        <i class="fas fa-file-pdf"></i> Contrato
                                    </a>
                                </li>`;

                    if (data.status !== 'renovado') {
                        buttons += `
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="delRegistro(${data.id})">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </li>`;
                    }

                    buttons += `
                            </ul>
                        </div>`;
                    return buttons;
                }

            },
            { data: 'asesor_name' },
            { data: 'client_name' },
            { data: 'amount' },
            { data: 'interest_rate' },
            { data: 'term_type' },
            { data: 'term_count' },
            { data: 'start_date' },
            { data: 'status' },
            { data: 'total_payable' },
            { data: 'installment_amount' }
        ],
        language,
        order: [0, 'asc']
    });

    document.getElementById('btnFiltrarPrestamos').addEventListener('click', function () {
        table.ajax.reload();
    });

    document.getElementById('btnExportarPrestamosPdf').addEventListener('click', function () {
        const cliente_id = document.getElementById('cliente_id').value || '';
        const advisor_id = document.getElementById('advisor_id').value || '';
        const tipo_plazo = document.getElementById('tipo_plazo').value || '';
        const fecha_inicio = document.getElementById('fecha_inicio').value || '';
        const fecha_fin = document.getElementById('fecha_fin').value || '';

        const url = `${base_url}loans/export/pdf?cliente_id=${cliente_id}&advisor_id=${advisor_id}&tipo_plazo=${tipo_plazo}&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}`;
        window.open(url, '_blank');
    });

    document.getElementById('btnExportarPrestamosExcel').addEventListener('click', function () {
        const cliente_id = document.getElementById('cliente_id').value || '';
        const advisor_id = document.getElementById('advisor_id').value || '';
        const tipo_plazo = document.getElementById('tipo_plazo').value || '';
        const fecha_inicio = document.getElementById('fecha_inicio').value || '';
        const fecha_fin = document.getElementById('fecha_fin').value || '';

        const url = `${base_url}loans/export/excel?cliente_id=${cliente_id}&advisor_id=${advisor_id}&tipo_plazo=${tipo_plazo}&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}`;
        window.open(url, '_blank');
    });


});

async function delRegistro(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const res = await fetch(base_url + `/loans/delete/${id}`, {
                method: 'DELETE'
            });
            const data = await res.json();

            if (data.status === 'success') {
                alertSW('success', data.message);
                table.ajax.reload();
            } else {
                alertSW('error', data.message);
            }
        }
    });
}
