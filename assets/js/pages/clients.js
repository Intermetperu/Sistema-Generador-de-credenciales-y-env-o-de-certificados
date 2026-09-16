let editing = false;
const myModal = new bootstrap.Modal(document.getElementById("modalCliente"));
let table;
const zone_filtro = document.querySelector('#zone_filtro');
document.addEventListener('DOMContentLoaded', function () {
    table = new DataTable('#clientsTable', {
        responsive: true,
        ajax: {
            url: base_url + "clients/list",
            data: function (d) {
                d.zona_id = zone_filtro.value;
            },
            dataSrc: 'data'
        },
        columns: [{
            data: null,
            render: function (data, type, row) {
                return `
                    <button class="btn btn-sm btn-warning" onclick="editRegistro(${row.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="delRegistro(${row.id})"><i class="fas fa-trash"></i></button>
                `;
            }
        },
        {
            data: 'zone_name'
        },
        {
            data: 'name'
        },
        {
            data: 'document_type'
        },
        {
            data: 'document_number'
        },
        {
            data: 'phone'
        },
        {
            data: 'email'
        }
        ],
        language
    });

    zone_filtro.addEventListener('change', function () {
        table.ajax.reload();
    });

    document.getElementById('btnExportarPdf').addEventListener('click', function () {
        const zonaId = zone_filtro.value;
        window.open(`${base_url}clients/export-pdf?zona_id=${zonaId}`, '_blank');
    });
    document.getElementById('btnExportarExcel').addEventListener('click', function () {
        const zonaId = zone_filtro.value;
        window.open(`${base_url}clients/export-excel?zona_id=${zonaId}`, '_blank');
    });

    // Botón nuevo
    if (document.querySelector('#btnNuevo')) {
        document.querySelector('#btnNuevo').addEventListener('click', function () {
            editing = false;
            document.getElementById('clientForm').reset();
            document.getElementById('client_id').value = '';
            document.getElementById('modalTitleId').textContent = 'Nuevo Cliente';
            myModal.show();
        });
    }

    // Enviar formulario
    document.getElementById('clientForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const notyf = new Notyf({ duration: 3000, ripple: true });
        const formData = new FormData(this);
        const id = formData.get('id');
        const zone_id = formData.get('zone_id');
        const name = formData.get('name')?.trim();
        const document_number = formData.get('document_number')?.trim();

        let valid = true;

        if (!name || name.length < 3 || name.length > 255) {
            notyf.error('El nombre debe tener entre 3 y 255 caracteres.');
            valid = false;
        }

        if (!document_number || document_number.length < 6) {
            notyf.error('El número de documento no es válido.');
            valid = false;
        }

        if (!zone_id) {
            notyf.error('La zona es requerido.');
            valid = false;
        }

        if (!valid) return;

        const url = id
            ? `${base_url}clients/update/${id}`
            : `${base_url}clients/create`;

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
    const res = await fetch(base_url + `/clients/show/${id}`);
    const data = await res.json();

    if (data.status === 'warning') {
        alertSW('warning', data.message);
        return;
    }

    document.getElementById('modalTitleId').textContent = 'Editar Cliente';
    document.getElementById('client_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('document_type_id').value = data.document_type_id;
    document.getElementById('document_number').value = data.document_number;
    document.getElementById('phone').value = data.phone;
    document.getElementById('email').value = data.email;
    document.getElementById('address').value = data.address;
    document.getElementById('city').value = data.city;
    document.getElementById('birth_date').value = data.birth_date;
    document.getElementById('occupation').value = data.occupation;
    document.getElementById('workplace').value = data.workplace;
    document.getElementById('income').value = data.income;
    document.getElementById('reference_name').value = data.reference_name;
    document.getElementById('reference_phone').value = data.reference_phone;
    document.getElementById('status').value = data.status;
    document.getElementById('zone_id').value = data.zone_id;
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
            const res = await fetch(base_url + `/clients/delete/${id}`, {
                method: 'DELETE'
            });
            const data = await res.json();
            alertSW(data.status, data.message);
            if (data.status === 'success') {
                table.ajax.reload();
            }
        }
    });
}