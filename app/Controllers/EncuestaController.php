<?php

namespace App\Controllers;

use App\Models\CategoriaModel;
use App\Models\CertificadoAccesoModel;
use App\Models\EventoModel;
use Config\Certificados as CertificadosConfig;

/**
 * Público, SIN filtro de autenticación — es la página que ve el
 * participante desde su correo, no un admin del panel.
 */
class EncuestaController extends BaseController
{
    protected CertificadoAccesoModel $accesos;
    protected EventoModel $eventos;
    protected CategoriaModel $categorias;
    protected CertificadosConfig $config;

    public function __construct()
    {
        $this->accesos = new CertificadoAccesoModel();
        $this->eventos = new EventoModel();
        $this->categorias = new CategoriaModel();
        $this->config = config('Certificados');
    }

    /**
     * Muestra la encuesta (si no la ha llenado) o la pantalla de
     * descarga (si ya la completó).
     */
    public function index(string $token)
    {
        $acceso = $this->accesos->porToken($token);

        if (!$acceso) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Link no válido.');
        }

        $evento = $this->eventos->find($acceso['evento_id']);
        $categoria = $this->categorias->porHoja($acceso['evento_id'], $acceso['hoja']);

        return view('publico/encuesta', [
            'token' => $token,
            'acceso' => $acceso,
            'evento' => $evento,
            'linkEncuesta' => $categoria['link_encuesta'] ?? '',
        ]);
    }

    /**
     * Sirve la imagen de la plantilla del certificado (SIN el nombre
     * estampado), para usarla como fondo borroso detrás del popup de
     * la encuesta.
     */
    public function preview(string $token)
    {
        $acceso = $this->accesos->porToken($token);
        if (!$acceso) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Link no válido.');
        }

        $categoria = $this->categorias->porHoja($acceso['evento_id'], $acceso['hoja']);
        if (!$categoria || empty($categoria['plantilla_certificado'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Plantilla no encontrada.');
        }

        $ruta = rtrim($this->config->dirPlantillasCertificados, '/') . '/' . $categoria['plantilla_certificado'];
        if (!is_file($ruta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Plantilla no encontrada.');
        }

        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';

        return $this->response
            ->setContentType($mime)
            ->setBody(file_get_contents($ruta));
    }

    /**
     * Marca la encuesta como completada. Se llama por AJAX (fetch) desde
     * la vista, disparado cuando se detecta que el iframe del Google
     * Forms navegó a su página de confirmación (2do onload del iframe).
     *
     * Responde JSON si la petición es AJAX; si no, hace el redirect
     * clásico (por si algún día se llama por un submit normal).
     */
    public function guardar(string $token)
    {
        $acceso = $this->accesos->porToken($token);
        if (!$acceso) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'Link no válido.']);
            }
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Link no válido.');
        }

        if (!$acceso['encuesta_completada']) {
            $this->accesos->marcarEncuestaCompletada((int) $acceso['id'], []);
        }

        if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response->setJSON(['ok' => true]);
        }

        return redirect()->to(site_url("encuesta/{$token}"));
    }

    /**
     * Sirve el PDF, pero SOLO si ya completó la encuesta. Si no, lo
     * regresa a la página de la encuesta.
     */
    public function descargar(string $token)
    {
        $acceso = $this->accesos->porToken($token);
        if (!$acceso) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Link no válido.');
        }

        if (!$acceso['encuesta_completada']) {
            return redirect()->to(site_url("encuesta/{$token}"));
        }

        $ruta = rtrim($this->config->dirCertificadosGenerados, '/') . '/' . $acceso['identificador'] . '.pdf';

        if (!is_file($ruta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Certificado no encontrado.');
        }

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $acceso['identificador'] . '.pdf"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setBody(file_get_contents($ruta));
    }
}