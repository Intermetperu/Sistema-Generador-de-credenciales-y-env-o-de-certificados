let editing = false;
const myModal = new bootstrap.Modal(document.getElementById("modalId"));
let table;

document.addEventListener('DOMContentLoaded', function () {
    table = new DataTable('#usersTable', {
        responsive: true,
        ajax: {
            url: base_url + "users/list",
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning" onclick="editRegistro(${row.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="delRegistro(${row.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            },
            { data: 'name' },
            { data: 'email' },
            { data: 'role_name' },
            {
                data: 'is_active',
                render: function (data) {
                    return data == 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>';
                }
            }
        ],
        language
    });

    // Botón nuevo
    document.querySelector('#btnNuevo').addEventListener('click', function () {
        editing = false;
        document.getElementById('userForm').reset();
        document.getElementById('user_id').value = '';
        document.getElementById('modalTitleId').textContent = 'Nuevo Usuario';
        myModal.show();
    });

    // Enviar formulario
    document.getElementById('userForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const notyf = new Notyf({ duration: 3000, ripple: true });
        const formData = new FormData(this);

        const id = formData.get('id');
        const name = formData.get('name')?.trim();
        const email = formData.get('email')?.trim();
        const password = formData.get('password');
        const role_id = formData.get('role_id');
        const is_active = formData.get('is_active');

        let valid = true;

        if (!name || name.length < 3) {
            notyf.error('El nombre es obligatorio y debe tener al menos 3 caracteres.');
            valid = false;
        }

        if (!email || !email.includes('@')) {
            notyf.error('Correo inválido.');
            valid = false;
        }

        if (!id && (!password || password.length < 6)) {
            notyf.error('La contraseña debe tener al menos 6 caracteres.');
            valid = false;
        }

        if (!role_id) {
            notyf.error('Debe seleccionar un rol.');
            valid = false;
        }

        if (!valid) return;

        const url = id
            ? `${base_url}users/update/${id}`
            : `${base_url}users/create`;

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
    const res = await fetch(base_url + `/users/show/${id}`);
    const data = await res.json();

    if (data.status === 'warning') {
        alertSW('warning', data.message);
        return;
    }

    document.getElementById('modalTitleId').textContent = 'Editar Usuario';
    document.getElementById('user_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('email').value = data.email;
    document.getElementById('role_id').value = data.role_id;
    document.getElementById('is_active').value = data.is_active;
    document.getElementById('password').value = '';
    $('#zones').val(data.zones).trigger('change');
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
            const res = await fetch(base_url + `/users/delete/${id}`, {
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