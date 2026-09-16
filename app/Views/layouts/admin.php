<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/images/logo.png'); ?>">
    <link href="<?php echo base_url('assets/plugins/simplebar/css/simplebar.css'); ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css'); ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/plugins/metismenu/css/metisMenu.min.css'); ?>" rel="stylesheet" />
    <!-- loader-->
    <link href="<?php echo base_url('assets/css/pace.min.css'); ?>" rel="stylesheet" />
    <script src="<?php echo base_url('assets/js/pace.min.js'); ?>"></script>
    <!-- Bootstrap CSS -->
    <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/css/bootstrap-extended.css'); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?php echo base_url('assets/css/app.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/css/icons.css'); ?>" rel="stylesheet">
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/dark-theme.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/css/semi-dark.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/css/header-colors.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/DataTables/datatables.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2/select2.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2/select2-bootstrap-5-theme.min.css'); ?>" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf/notyf.min.css">
    <title><?= esc($title ?? 'Panel') ?></title>
</head>

<body>
    <!--wrapper-->
    <div class="wrapper">
        <!--sidebar wrapper -->
        <div class="sidebar-wrapper" data-simplebar="true">
            <div class="sidebar-header">
                <div>
                    <img src="<?php echo base_url('assets/images/logo.png'); ?>" class="logo-icon" alt="logo icon">
                </div>
                <div>
                    <h4 class="logo-text"><?= env('TITLE'); ?></h4>
                </div>
                <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
                </div>
            </div>
            <!--navigation-->
            <ul class="metismenu" id="menu">

                <!-- Dashboard (acceso libre, puedes proteger si quieres) -->
                <li>
                    <a href="<?= base_url('dashboard'); ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Panel principal">
                        <div class="parent-icon"><i class="fa-solid fa-chart-area"></i></div>
                        <div class="menu-title">Tablero</div>
                    </a>
                </li>

                <!-- ===== Eventos ===== -->
                <li>
                    <a href="javascript:;" class="has-arrow" title="Eventos">
                        <div class="parent-icon"><i class="fa-solid fa-calendar-days"></i></div>
                        <div class="menu-title">Eventos</div>
                    </a>
                    <ul>
                        <li><a href="<?= base_url('eventos'); ?>"><i class="bx bx-right-arrow-alt"></i>Eventos y categorías</a></li>
                    </ul>
                </li>

                <!-- ===== Credenciales y Correos ===== -->
                <li>
                    <a href="javascript:;" class="has-arrow" title="Credenciales y Correos">
                        <div class="parent-icon"><i class="fa-solid fa-id-card"></i></div>
                        <div class="menu-title">Credenciales</div>
                    </a>
                    <ul>
                        <li><a href="<?= base_url('credenciales'); ?>"><i class="bx bx-right-arrow-alt"></i>Generador</a></li>
                        
                    </ul>
                </li>
                 <li>
                    <a href="javascript:;" class="has-arrow" title="Certificados">
                        <div class="parent-icon"><i class="fa-solid fa-file-certificate"></i></div>
                        <div class="menu-title">Certificados</div>
                    </a>
                    <ul>
                        <li><a href="<?= base_url('certificados'); ?>"><i class="bx bx-right-arrow-alt"></i>Generador</a></li>
                    </ul>
                </li>

                <!--
                    PLANTILLA LIMPIA: aquí van los demás módulos de tu proyecto.
                    Ejemplo de cómo agregar un ítem de menú simple:

                    <li>
                        <a href="<?= base_url('mi-modulo'); ?>" title="Mi módulo">
                            <div class="parent-icon"><i class="fas fa-folder"></i></div>
                            <div class="menu-title">Mi módulo</div>
                        </a>
                    </li>

                    El helper has_permission('Modulo:accion') sigue disponible (revisa
                    app/Helpers/function_helper.php) si quieres reactivar permisos por rol.
                -->
            </ul>


            <!--end navigation-->
        </div>
        <!--end sidebar wrapper -->
        <!--start header -->
        <header>
            <div class="topbar d-flex align-items-center">
                <nav class="navbar navbar-expand">
                    <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
                    </div>
                    <div class="search-bar flex-grow-1">
                        <div class="position-relative">
                            <h6>HOLA BIENVENIDO, <?= session('name'); ?></h6>
                        </div>
                    </div>
                    <div class="user-box dropdown">
                        <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo base_url('assets/images/logo.png'); ?>" class="user-img" alt="user-img">
                            <div class="user-info ps-3">
                                <p class="user-name mb-0"><?= session('email'); ?></p>
                                <p class="designattion mb-0"><span class="badge bg-success">En línea</span></p>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo base_url('profile'); ?>"><i class="bx bx-user"></i><span>PERFIL</span></a>
                            </li>
                            <li>
                                <div class="dropdown-divider mb-0"></div>
                            </li>
                            <li><a class="dropdown-item" href="<?php echo base_url('logout'); ?>"><i class='bx bx-log-out-circle'></i><span>SALIR</span></a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>
        <!--end header -->
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
        <!--end page wrapper -->
        <!--start overlay-->
        <div class="overlay toggle-icon"></div>
        <!--end overlay-->
        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->
        <footer class="page-footer">
            <p class="mb-0">Copyright © <?php echo date('Y'); ?>. Todos los derechos reservados.</p>
        </footer>
    </div>
    <!--end wrapper-->
    <!--start switcher-->
    <div class="switcher-wrapper">
        <div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
        </div>
        <div class="switcher-body">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-uppercase">PERSONALIZADOR DE TEMAS</h5>
                <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
            </div>
            <hr />
            <h6 class="mb-0">Estilos de tema</h6>
            <hr />
            <div class="d-flex align-items-center justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode" checked>
                    <label class="form-check-label" for="lightmode">Blanco</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode">
                    <label class="form-check-label" for="darkmode">Negro</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark">
                    <label class="form-check-label" for="semidark">Semi Negro</label>
                </div>
            </div>
            <hr />
            <div class="form-check">
                <input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault">
                <label class="form-check-label" for="minimaltheme">Tema mínimo</label>
            </div>
            <hr />
            <h6 class="mb-0">Colores de encabezado</h6>
            <hr />
            <div class="header-colors-indigators">
                <div class="row row-cols-auto g-3">
                    <div class="col">
                        <div class="indigator headercolor1" id="headercolor1"></div>
                    </div>
                    <div class="col">
                        <div class="indigator headercolor2" id="headercolor2"></div>
                    </div>
                    <div class="col">
                        <div class="indigator headercolor3" id="headercolor3"></div>
                    </div>
                    <div class="col">
                        <div class="indigator headercolor4" id="headercolor4"></div>
                    </div>
                    <div class="col">
                        <div class="indigator headercolor5" id="headercolor5"></div>
                    </div>
                    <div class="col">
                        <div class="indigator headercolor6" id="headercolor6"></div>
                    </div>
                    <div class="col">
                        <div class="indigator headercolor7" id="headercolor7"></div>
                    </div>
                    <div class="col">
                        <div class="indigator headercolor8" id="headercolor8"></div>
                    </div>
                </div>
            </div>
            <hr />
            <h6 class="mb-0">Colores de la barra lateral</h6>
            <hr />
            <div class="header-colors-indigators">
                <div class="row row-cols-auto g-3">
                    <div class="col">
                        <div class="indigator sidebarcolor1" id="sidebarcolor1"></div>
                    </div>
                    <div class="col">
                        <div class="indigator sidebarcolor2" id="sidebarcolor2"></div>
                    </div>
                    <div class="col">
                        <div class="indigator sidebarcolor3" id="sidebarcolor3"></div>
                    </div>
                    <div class="col">
                        <div class="indigator sidebarcolor4" id="sidebarcolor4"></div>
                    </div>
                    <div class="col">
                        <div class="indigator sidebarcolor5" id="sidebarcolor5"></div>
                    </div>
                    <div class="col">
                        <div class="indigator sidebarcolor6" id="sidebarcolor6"></div>
                    </div>
                    <div class="col">
                        <div class="indigator sidebarcolor7" id="sidebarcolor7"></div>
                    </div>
                    <div class="col">
                        <div class="indigator sidebarcolor8" id="sidebarcolor8"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end switcher-->
    <!-- Bootstrap JS -->
    <script src="<?php echo base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <!--plugins-->
    <script src="<?php echo base_url('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/simplebar/js/simplebar.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/metismenu/js/metisMenu.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js'); ?>"></script>
    <!--app JS-->
    <script src="<?php echo base_url('assets/js/app.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/all.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/DataTables/datatables.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/js/sweetalert2.all.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/select2/select2.min.js'); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf/notyf.min.js"></script>
    <script>
        const base_url = '<?= base_url('/'); ?>';
    </script>
    <script src="<?php echo base_url('assets/js/custom.js'); ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>
