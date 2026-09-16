<?= $this->extend('layouts/guest') ?>
<?= $this->section('title') ?>
	Iniciar Sesión
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="login-separater text-center mb-4">
	<span>INICIA SESIÓN CON EL CORREO ELECTRÓNICO</span>
	<hr />
</div>

<div class="form-body">
	<form class="row g-3" action="<?= base_url('auth/login') ?>" method="POST" autocomplete="off">
		<div class="col-12">
			<label for="email" class="form-label">Correo</label>
			<input type="email" class="form-control" id="email" name="email" placeholder="Correo Electrónico" value="<?php echo set_value('email', 'admin@gmail.com'); ?>">
			<?php if (isset($validation)) { ?>
				<span class="text-danger fw-bold"><?php echo $validation->getError('email'); ?></span>
			<?php } ?>
		</div>
		<div class="col-12">
			<label for="clave" class="form-label">Contraseña</label>
			<div class="input-group" id="show_hide_password">
				<input type="password" class="form-control border-end-0" id="password" name="password" placeholder="Escriba su contraseña" value="admin123">
				<a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
			</div>
			<?php if (isset($validation)) { ?>
				<span class="text-danger fw-bold"><?php echo $validation->getError('password'); ?></span>
			<?php } ?>
			<div class="text-center mt-3">
				<a href="<?= base_url('forgot-password') ?>" class="text-decoration-none">
					¿Olvidaste tu contraseña?
				</a>
			</div>

		</div>
		<div class="col-12">
			<div class="d-grid">
				<button type="submit" class="btn btn-primary"><i class="bx bxs-lock-open"></i>Acceder</button>
			</div>
		</div>
	</form>
</div> <!-- form-body -->


<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
	$(document).ready(function() {
		$("#show_hide_password a").on('click', function(event) {
			event.preventDefault();
			if ($('#show_hide_password input').attr("type") == "text") {
				$('#show_hide_password input').attr('type', 'password');
				$('#show_hide_password i').addClass("bx-hide");
				$('#show_hide_password i').removeClass("bx-show");
			} else if ($('#show_hide_password input').attr("type") == "password") {
				$('#show_hide_password input').attr('type', 'text');
				$('#show_hide_password i').removeClass("bx-hide");
				$('#show_hide_password i').addClass("bx-show");
			}
		});
	});
</script>
<?= $this->endSection() ?>