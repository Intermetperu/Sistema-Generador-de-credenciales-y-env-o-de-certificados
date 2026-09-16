let tableClients;
const myModalList = new bootstrap.Modal(document.getElementById("modalCliente"));
const myModalNuevo = new bootstrap.Modal(document.getElementById("nuevoCliente"));
const zone_filtro = document.querySelector('#zone_filtro');
document.addEventListener('DOMContentLoaded', function () {
    tableClients = new DataTable('#clientsTable', {
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
                    <button class="btn btn-sm btn-warning" onclick="selectClient(${row.id}, '${row.name}')"><i class="fas fa-check-circle"></i></button>
                `;
            }
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
        tableClients.ajax.reload();
    });

    // Botón nuevo
    document.querySelector('#btnNuevoCliente').addEventListener('click', function () {
        editing = false;
        document.getElementById('clientForm').reset();
        document.getElementById('client_id').value = '';
        myModalList.hide();
        myModalNuevo.show();
    });

    // Enviar formulario
    document.getElementById('clientForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const notyf = new Notyf({ duration: 3000, ripple: true });
        const formData = new FormData(this);
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

        const res = await fetch(`${base_url}clients/create`, {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.status === 'error') {
            Object.values(data.errors).forEach(msg => notyf.error(msg));
        } else {
            alertSW('success', data.message);
            myModalNuevo.hide();
            this.reset();
            tableClients.ajax.reload();
        }
    });
});

function selectClient(id, nombre) {
    document.querySelector('#client_id').value = id;
    document.querySelector('#client_name').value = nombre;
    const notyf = new Notyf({ duration: 3000, ripple: true });
    notyf.success('Cliente: ' + nombre + ' Seleccionado');
    myModalList.hide();
}
