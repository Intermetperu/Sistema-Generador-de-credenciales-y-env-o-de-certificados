<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Mi Perfil</h5>
        <hr>

        <?= $this->include('partials/alerts') ?>

        <form action="<?= base_url('profile/update') ?>" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="name">Nombre</label>
                    <input type="text" name="name" class="form-control" placeholder="Tu nombre completo" value="<?= esc($user['name']) ?>" required>
                </div>

                <div class="mb-3 col-md-6">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" placeholder="tu@email.com" value="<?= esc($user['email']) ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="avatar">Cambiar Avatar</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*" onchange="previewImage(event)">
                </div>

                <div class="mb-3 col-md-6 text-center">
                    <label>Vista Previa</label><br>
                    <?php $url = $user['avatar'] ? 'assets/images/avatars/' . $user['avatar'] : 'assets/images/logo.png';  ?>
                    <img id="avatarPreview" src="<?= base_url($url) ?>" alt="Avatar" width="120" class="rounded-circle shadow">
                </div>
            </div>

            <button class="btn btn-primary mt-2">Guardar Cambios</button>
        </form>
    </div>
</div>

<!-- FORMULARIO CAMBIO DE CONTRASEÑA -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Cambiar Contraseña</h5>
        <hr>

        <form action="<?= base_url('profile/change-password') ?>" method="post">
            <div class="row">
                <div class="mb-3 col-md-4">
                    <label for="current_password">Contraseña Actual</label>
                    <input type="password" name="current_password" class="form-control" placeholder="********" required>
                </div>

                <div class="mb-3 col-md-4">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" name="new_password" class="form-control" placeholder="********" required>
                </div>

                <div class="mb-3 col-md-4">
                    <label for="confirm_password">Confirmar Nueva</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="********" required>
                </div>
            </div>

            <button class="btn btn-warning">Actualizar Contraseña</button>
        </form>
    </div>
</div>


<script>
    function previewImage(event) {
        const [file] = event.target.files;
        if (file) {
            document.getElementById('avatarPreview').src = URL.createObjectURL(file);
        }
    }
</script>

<?= $this->endSection() ?>