<?= $this->extend('layouts/guest') ?>

<?= $this->section('title') ?>
Cambiar Contraseña
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $errors = session('errors') ?? []; ?>

<div class="login-separater text-center mb-4">
    <span>Cambiar Contraseña</span>
    <hr />
</div>

<div class="form-body">
    <form action="<?= base_url('forgot-password') ?>" method="post" class="mt-4">
        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" name="email" value="<?= old('email') ?>" placeholder="Correo electrónico">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>
        <button type="submit" class="btn btn-primary">Enviar enlace</button>
    </form>
</div> <!-- form-body -->


<?= $this->endSection() ?>