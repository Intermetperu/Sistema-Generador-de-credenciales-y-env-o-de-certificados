let editing = false;
const myModal = new bootstrap.Modal(document.getElementById("modalId"));
let table;
document.addEventListener('DOMContentLoaded', function () {
    table = new DataTable('#paymentMethodsTable', {
        responsive: true,
        ajax: {
            url: base_url + "paymentMethods/list",
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
            data: 'name'
        }
        ],
        language
    });

    // Botón nuevo
    document.querySelector('#btnNuevo').addEventListener('click', function () {
        editing = false;
        document.getElementById('paymentMethodForm').reset();
        document.getElementById('paymentMethod_id').value = '';
        document.getElementById('modalTitleId').textContent = 'Nuevo Forma Pago';
        myModal.show();
    });

    // Enviar formulario
    document.getElementById('paymentMethodForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const notyf = new Notyf({ duration: 3000, ripple: true });

        const formData = new FormData(this);

        // Obtener los campos
        const id = formData.get('id');
        const name = formData.get('name')?.trim();

        // Validación básica en JS
        let valid = true;

        if (!name || name.length < 3 || name.length > 255) {
            notyf.error('El nombre debe tener entre 3 y 255 caracteres.');
            valid = false;
        }

        if (!valid) return; // No enviar si hay errores

        // Enviar al backend
        const url = id
            ? `${base_url}paymentMethods/update/${id}`
            : `${base_url}paymentMethods/create`;

        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.status === 'error') {
            Object.values(data.errors).forEach(msg => notyf.error(msg));
        } else {
            alertSW('success', data.message);
            myModal.hide();
            this.reset();
            table.ajax.reload();
        }
    });

})

async function editRegistro(id) {
    editing = true;
    const res = await fetch(base_url + `/paymentMethods/show/${id}`);
    const data = await res.json();

    if (data.status === 'warning') {
        alertSW('warning', data.message);
        return;
    }

    document.getElementById('modalTitleId').textContent = 'Editar Forma Pago';
    document.getElementById('paymentMethod_id').value = data.id;
    document.getElementById('name').value = data.name;
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
            const res = await fetch(base_url + `/paymentMethods/delete/${id}`, {
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