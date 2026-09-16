<!DOCTYPE html>
<html>

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/images/logo.png'); ?>">
	<!--plugins-->
	<!-- loader-->
	<link href="<?php echo base_url('assets/css/pace.min.css'); ?>" rel="stylesheet" />
	<script src="<?php echo base_url('assets/js/pace.min.js'); ?>"></script>
	<!-- Bootstrap CSS -->
	<link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/bootstrap-extended.css'); ?>" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/app.css'); ?>" rel="stylesheet">
	<link href="<?php echo base_url('assets/css/icons.css'); ?>" rel="stylesheet">
	<title>Acceso al sistema</title>
</head>

<body class="">
	<!--wrapper-->
	<div class="wrapper">
		<div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
			<div class="container-fluid">
				<div class="row min-vh-100">
					<!-- COLUMNA IZQUIERDA: FONDO + TEXTO BIENVENIDA -->
					<div class="col-lg-6 d-none d-lg-flex p-0">
						<div class="w-100 h-100" style="background-image: url('<?php echo base_url('assets/images/fondo.jpg'); ?>'); background-size: cover; background-position: center;">
							<div class="d-flex flex-column justify-content-center align-items-center h-100 text-white text-center px-4" style="background: rgba(0,0,0,0.5);">
							
							</div>
						</div>
					</div>

					<!-- COLUMNA DERECHA: LOGIN -->
					<div class="col-lg-6 d-flex align-items-center justify-content-center">
						<div class="col mx-auto" style="max-width: 400px;">
							<div class="card">
								<div class="card-body">
									<div class="border p-4 rounded">

										<div class="d-flex justify-content-between align-items-center">
											<img src="<?php echo base_url('assets/images/logo.png'); ?>" width="100" alt="Logo" />
											<h6 class="mb-0"><?= $this->renderSection('title') ?></h6>
										</div>


										<?php if (!empty(session()->getFlashdata('error'))) { ?>
											<div class="alert border-0 border-start border-5 border-danger alert-dismissible fade show py-2">
												<div class="d-flex align-items-center">
													<div class="font-35 text-danger"><i class='bx bxs-check-circle'></i></div>
													<div class="ms-3">
														<div><?php echo session()->getFlashdata('error'); ?></div>
													</div>
												</div>
												<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
											</div>
										<?php } ?>

                                        <?php if (!empty(session()->getFlashdata('success'))) { ?>
											<div class="alert border-0 border-start border-5 border-success alert-dismissible fade show py-2">
												<div class="d-flex align-items-center">
													<div class="font-35 text-success"><i class='bx bxs-check-circle'></i></div>
													<div class="ms-3">
														<div><?php echo session()->getFlashdata('success'); ?></div>
													</div>
												</div>
												<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
											</div>
										<?php } ?>

										<?= $this->renderSection('content') ?>

									</div> <!-- border -->
								</div> <!-- card-body -->
							</div> <!-- card -->
						</div> <!-- col mx-auto -->
					</div> <!-- col-lg-6 -->
				</div> <!-- row -->
			</div> <!-- container-fluid -->
		</div> <!-- section-authentication-signin -->
	</div> <!-- wrapper -->

	<!--end wrapper-->
	<!-- Bootstrap JS -->
	<script src="<?php echo base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
	<!--plugins-->
	<script src="<?php echo base_url('assets/js/jquery.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js'); ?>"></script>
	<!--Password show & hide js -->
	<?= $this->renderSection('script') ?>
</body>