let editing = false;
const myModal = new bootstrap.Modal(document.getElementById("modalId"));
let table;

document.addEventListener('DOMContentLoaded', function () {
    table = new DataTable('#documentTypesTable', {
        responsive: true,
        ajax: {
            url: base_url + "documentTypes/list",
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
            { data: 'code' },
            { data: 'name' },
            { data: 'description' }
        ],
        language
    });

    // Botón nuevo
    document.querySelector('#btnNuevo').addEventListener('click', function () {
        editing = false;
        document.getElementById('documentTypeForm').reset();
        document.getElementById('documentType_id').value = '';
        document.getElementById('modalTitleId').textContent = 'Nuevo Tipo de Documento';
        myModal.show();
    });

    // Enviar formulario
    document.getElementById('documentTypeForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const notyf = new Notyf({ duration: 3000, ripple: true });
        const formData = new FormData(this);

        const id = formData.get('id');
        const code = formData.get('code')?.trim();
        const name = formData.get('name')?.trim();

        // Validación básica
        let valid = true;

        if (!code || code.length > 10) {
            notyf.error('El código es obligatorio y debe tener máximo 10 caracteres.');
            valid = false;
        }

        if (!name || name.length < 3 || name.length > 100) {
            notyf.error('El nombre debe tener entre 3 y 100 caracteres.');
            valid = false;
        }

        if (!valid) return;

        const url = id
            ? `${base_url}documentTypes/update/${id}`
            : `${base_url}documentTypes/create`;

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
});

async function editRegistro(id) {
    editing = true;
    const res = await fetch(`${base_url}documentTypes/show/${id}`);
    const data = await res.json();

    if (data.status === 'warning') {
        alertSW('warning', data.message);
        return;
    }

    document.getElementById('modalTitleId').textContent = 'Editar Tipo de Documento';
    document.getElementById('documentType_id').value = data.id;
    document.getElementById('code').value = data.code;
    document.getElementById('name').value = data.name;
    document.getElementById('description').value = data.description ?? '';
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
            const res = await fetch(`${base_url}documentTypes/delete/${id}`, {
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
