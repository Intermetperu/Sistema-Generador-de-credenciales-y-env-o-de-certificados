let editing = false;
const myModal = new bootstrap.Modal(document.getElementById("modalCaja"));
let table;
document.addEventListener('DOMContentLoaded', function () {
    table = new DataTable('#cashboxesTable', {
        responsive: true,
        ajax: {
            url: base_url + "cashboxes/list",
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    let buttons = '';

                    if (row.is_open == 1) {
                        buttons += `
                            <a class="btn btn-sm btn-info me-1" href="${base_url + 'cashboxes/viewMovements?cashbox_id=' + row.id}" title="Ver Movimientos">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-primary me-1" onclick="cerrarCaja(${row.id})" title="Cerrar Caja">
                                <i class="fas fa-lock"></i>
                            </button>
                            <button class="btn btn-sm btn-warning me-1" onclick="editRegistro(${row.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="delRegistro(${row.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    } else {
                        buttons = `<span class="text-muted">Caja cerrada</span>`;
                    }

                    return buttons;
                }
            },

            { data: 'name' },
            { data: 'currency_name' },
            {
                data: 'balance',
                render: function (data) {
                    return parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'status',
                render: function (data) {
                    if (data == 1) {
                        return `<span class="badge bg-success">Activa</span>`;
                    } else {
                        return `<span class="badge bg-secondary">Inactiva</span>`;
                    }
                }
            }
        ],
        language
    });

    // Botón nuevo
    document.querySelector('#btnNuevo').addEventListener('click', function () {
        editing = false;
        document.getElementById('cashboxForm').reset();
        document.getElementById('cashbox_id').value = '';
        document.getElementById('modalTitleId').textContent = 'Nueva Caja';
        myModal.show();
    });

    // Enviar formulario
    document.getElementById('cashboxForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const notyf = new Notyf({ duration: 3000, ripple: true });

        const formData = new FormData(this);
        const id = formData.get('id');
        const name = formData.get('name')?.trim();
        const currency_id = formData.get('currency_id');
        const balance = formData.get('balance');

        // Validaciones básicas
        let valid = true;

        if (!name || name.length < 3 || name.length > 255) {
            notyf.error('El nombre debe tener entre 3 y 255 caracteres.');
            valid = false;
        }

        if (!currency_id) {
            notyf.error('Selecciona una moneda.');
            valid = false;
        }

        if (isNaN(balance) || balance < 0) {
            notyf.error('El saldo debe ser un número válido.');
            valid = false;
        }

        if (!valid) return;

        const url = id
            ? `${base_url}cashboxes/update/${id}`
            : `${base_url}cashboxes/create`;

        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.status === 'error') {
            Object.values(data.errors).forEach(msg => notyf.error(msg));
        } else {
            alertSW(data.status, data.message);
            if (data.status == 'success') {
                myModal.hide();
                this.reset();
                table.ajax.reload();
            }
        }
    });
});

async function editRegistro(id) {
    editing = true;
    const res = await fetch(base_url + `cashboxes/show/${id}`);
    const data = await res.json();

    document.getElementById('modalTitleId').textContent = 'Editar Caja';
    document.getElementById('cashbox_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('currency_id').value = data.currency_id;
    document.getElementById('balance').value = data.balance;
    myModal.show();
}

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
            const res = await fetch(base_url + `cashboxes/delete/${id}`, {
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

async function cerrarCaja(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const res = await fetch(base_url + `cashboxes/close/${id}`, {
                method: 'PUT'
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