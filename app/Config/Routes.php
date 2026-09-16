<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --- Autenticación (igual que en el proyecto original) ---
$routes->get('/', 'AuthController::index');
$routes->post('/auth/login', 'AuthController::login');
$routes->get('/logout', 'AuthController::logout');

$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::sendResetLink');
$routes->get('reset-password/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('reset-password/(:segment)', 'AuthController::updatePassword/$1');

// ===== Encuesta pública para desbloquear certificado =====
// SIN filtro de auth: el participante entra desde su correo, sin haber
// iniciado sesión en el panel.
$routes->get('encuesta/(:segment)', 'EncuestaController::index/$1');
$routes->get('encuesta/(:segment)/preview', 'EncuestaController::preview/$1');
$routes->post('encuesta/(:segment)/guardar', 'EncuestaController::guardar/$1');
$routes->get('encuesta/(:segment)/descargar', 'EncuestaController::descargar/$1');

// --- Zona protegida: plantilla admin + sidebar ---
$routes->group('', ['filter' => 'AuthCheck'], function ($routes) {
    $routes->get('/dashboard', 'Admin\DashboardController::index');

    $routes->get('profile', 'Admin\ProfileController::edit');
    $routes->post('profile/update', 'Admin\ProfileController::update');
    $routes->post('profile/change-password', 'Admin\ProfileController::changePassword');

    // ===== Módulo Credenciales / Correos =====
    $routes->get('credenciales', 'Admin\CredencialesController::index');
    $routes->get('credenciales/datos/(:any)', 'Admin\CredencialesController::datos/$1');
    $routes->get('credenciales/preview/(:any)', 'Admin\CredencialesController::previewPlantilla/$1');
    $routes->post('credenciales/generar-pdf', 'Admin\CredencialesController::generarPdf');
    $routes->get('credenciales/ver-pdf/(:any)', 'Admin\CredencialesController::verPdf/$1');
    $routes->post('credenciales/enviar', 'Admin\CredencialesController::enviarCorreos');
    $routes->get('credenciales/estadisticas', 'Admin\CredencialesController::estadisticas');
    $routes->get('credenciales/historial', 'Admin\CredencialesController::historial');
    $routes->get('credenciales/igv', 'Admin\CredencialesController::igv');

    // ===== Módulo Certificados / Correos =====
    $routes->get('certificados', 'Admin\CertificadosController::index');
    $routes->get('certificados/datos/(:any)', 'Admin\CertificadosController::datos/$1');
    $routes->post('certificados/subir-plantilla', 'Admin\CertificadosController::subirPlantilla');
    $routes->post('certificados/generar-pdf', 'Admin\CertificadosController::generarPdf');
    $routes->post('certificados/enviar', 'Admin\CertificadosController::enviarCorreos');
    $routes->get('certificados/ver-pdf/(:any)', 'Admin\CertificadosController::verPdf/$1');
    $routes->get('certificados/preview/(:num)', 'Admin\CertificadosController::previewCertificado/$1');
    $routes->post('certificados/guardar-link-encuesta', 'Admin\CertificadosController::guardarLinkEncuesta');
    $routes->post('certificados/resetear-confirmacion', 'Admin\CertificadosController::resetearConfirmacion');
    $routes->post('certificados/guardar-plantilla-correo', 'Admin\CertificadosController::guardarPlantillaCorreo');
    
    

    // ===== Eventos (CRUD) =====
    $routes->get('eventos', 'Admin\EventosController::index');
    $routes->get('eventos/nuevo', 'Admin\EventosController::create');
    $routes->post('eventos', 'Admin\EventosController::store');
    $routes->get('eventos/(:num)/editar', 'Admin\EventosController::edit/$1');
    $routes->put('eventos/(:num)/actualizar', 'Admin\EventosController::update/$1');
    $routes->post('eventos/(:num)/actualizar', 'Admin\EventosController::update/$1');
    $routes->post('eventos/(:num)/activar', 'Admin\EventosController::activar/$1');
    $routes->delete('eventos/(:num)/eliminar', 'Admin\EventosController::delete/$1');
    $routes->post('eventos/(:num)/eliminar', 'Admin\EventosController::delete/$1');

    // ===== Categorías de un evento (CRUD anidado) =====
    $routes->get('eventos/(:num)/categorias', 'Admin\CategoriasController::index/$1');
    $routes->get('eventos/(:num)/categorias/nueva', 'Admin\CategoriasController::create/$1');
    $routes->post('eventos/(:num)/categorias', 'Admin\CategoriasController::store/$1');
    $routes->get('eventos/(:num)/categorias/(:num)/editar', 'Admin\CategoriasController::edit/$1/$2');
    $routes->put('eventos/(:num)/categorias/(:num)/actualizar', 'Admin\CategoriasController::update/$1/$2');
    $routes->post('eventos/(:num)/categorias/(:num)/actualizar', 'Admin\CategoriasController::update/$1/$2');
    $routes->delete('eventos/(:num)/categorias/(:num)/eliminar', 'Admin\CategoriasController::delete/$1/$2');
    $routes->post('eventos/(:num)/categorias/(:num)/eliminar', 'Admin\CategoriasController::delete/$1/$2');
    $routes->get('eventos/(:num)/categorias/(:num)/imagen', 'Admin\CategoriasController::imagen/$2');
    $routes->get('eventos/(:num)/categorias/(:num)/posicionar', 'Admin\CategoriasController::posicionar/$1/$2');
    $routes->post('eventos/(:num)/categorias/(:num)/guardar-posiciones', 'Admin\CategoriasController::guardarPosiciones/$1/$2');
    
});