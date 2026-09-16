<?= $this->extend('layouts/guest') ?>
<?= $this->section('title') ?>
Restablecer Contraseña
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="login-separater text-center mb-4">
    <span>Restablecer Contraseña</span>
    <hr />
</div>

<div class="form-body">
    <form action="<?= base_url('reset-password/' . $token) ?>" method="post" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Contraseña nueva">
            <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirm" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" placeholder="Confirmar contraseña">
            <div class="invalid-feedback"><?= $errors['password_confirm'] ?? '' ?></div>
        </div>
        <button type="submit" class="btn btn-success">Actualizar contraseña</button>
    </form>
</div>

<?= $this->endSection() ?>