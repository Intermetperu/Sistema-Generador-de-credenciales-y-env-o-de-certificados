let editing = false;
const myModal = new bootstrap.Modal(document.getElementById("modalGasto"));
let table;

document.addEventListener('DOMContentLoaded', function () {
    table = new DataTable('#expensesTable', {
        responsive: true,
        ajax: {
            url: base_url + "expenses/list",
            data: function (d) {
                d.categoria_id = $('#categoria_id').val();
                d.fecha_inicio = $('#fecha_inicio').val();
                d.fecha_fin = $('#fecha_fin').val();
            },
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning" onclick="editRegistro(${row.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="delRegistro(${row.id})"><i class="fas fa-trash"></i></button>
                    `;
                }
            },
            { data: 'category' },
            { data: 'concept' },
            { data: 'amount' },
            { data: 'username' },
            { data: 'boxname' },
            { data: 'created_at' }
        ],
        language
    });

    document.getElementById('btnFiltrar').addEventListener('click', function () {
        table.ajax.reload();
    });

    document.getElementById('btnExportarPdf').addEventListener('click', function () {
        const categoria_id = document.getElementById('categoria_id').value || '';
        const fecha_inicio = document.getElementById('fecha_inicio').value || '';
        const fecha_fin = document.getElementById('fecha_fin').value || '';

        const url = `${base_url}expenses/export/pdf?categoria_id=${categoria_id}&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}`;
        window.open(url, '_blank');
    });

    document.getElementById('btnExportarExcel').addEventListener('click', function () {
        const categoria_id = document.getElementById('categoria_id').value || '';
        const fecha_inicio = document.getElementById('fecha_inicio').value || '';
        const fecha_fin = document.getElementById('fecha_fin').value || '';

        const url = `${base_url}expenses/export/excel?categoria_id=${categoria_id}&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}`;
        window.open(url, '_blank');
    });

    // Botón nuevo
    document.querySelector('#btnNuevo').addEventListener('click', function () {
        editing = false;
        document.getElementById('expenseForm').reset();
        document.getElementById('expense_id').value = '';
        document.getElementById('modalTitleId').textContent = 'Nuevo Gasto';
        myModal.show();
    });

    // Enviar formulario
    document.getElementById('expenseForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const notyf = new Notyf({ duration: 3000, ripple: true });
        const formData = new FormData(this);

        const id = formData.get('id');
        const concept = formData.get('concept')?.trim();
        const amount = parseFloat(formData.get('amount'));
        const category = formData.get('category_id');
        const box = formData.get('cashbox_id');
        const currency_id = formData.get('currency_id');

        let valid = true;

        if (!concept || concept.length < 3 || concept.length > 255) {
            notyf.error('El concepto debe tener entre 3 y 255 caracteres.');
            valid = false;
        }

        if (isNaN(amount) || amount <= 0) {
            notyf.error('El monto debe ser mayor que 0.');
            valid = false;
        }

        if (!category) {
            notyf.error('Debe seleccionar una categoría.');
            valid = false;
        }

        if (!currency_id) {
            notyf.error('Debe seleccionar una moneda.');
            valid = false;
        }

        if (!box) {
            notyf.error('Debe seleccionar una caja.');
            valid = false;
        }

        if (!valid) return;

        const url = id
            ? `${base_url}expenses/update/${id}`
            : `${base_url}expenses/create`;

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
    const res = await fetch(`${base_url}expenses/show/${id}`);
    const data = await res.json();

    if (data.status === 'warning') {
        alertSW('warning', data.message);
        return;
    }

    document.getElementById('modalTitleId').textContent = 'Editar Gasto';
    document.getElementById('expense_id').value = data.id;
    document.getElementById('concept').value = data.concept;
    document.getElementById('amount').value = data.amount;
    document.getElementById('category_id').value = data.category_id;
    document.getElementById('cashbox_id').value = data.box_id;
    document.getElementById('currency_id').value = data.currency_id;
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
            const res = await fetch(`${base_url}expenses/delete/${id}`, {
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