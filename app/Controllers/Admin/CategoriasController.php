<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoriaModel;
use App\Models\EventoModel;
use Config\Credenciales as CredencialesConfig;

class CategoriasController extends BaseController
{
    protected CategoriaModel $categorias;
    protected EventoModel $eventos;
    protected CredencialesConfig $config;

    public function __construct()
    {
        $this->categorias = new CategoriaModel();
        $this->eventos = new EventoModel();
        $this->config = config('Credenciales');

        if (!is_dir($this->config->dirPlantillasCredenciales)) {
            mkdir($this->config->dirPlantillasCredenciales, 0775, true);
        }
    }

    public function index(int $eventoId)
    {
        $evento = $this->eventos->find($eventoId);
        if (!$evento) {
            return redirect()->to(base_url('eventos'))->with('error', 'Evento no encontrado.');
        }

        return view('admin/categorias/index', [
            'title' => 'Categorías de ' . $evento['nombre'],
            'evento' => $evento,
            'categorias' => $this->categorias->porEvento($eventoId),
        ]);
    }

    public function create(int $eventoId)
    {
        $evento = $this->eventos->find($eventoId);
        if (!$evento) {
            return redirect()->to(base_url('eventos'))->with('error', 'Evento no encontrado.');
        }

        return view('admin/categorias/form', [
            'title' => 'Nueva categoría',
            'evento' => $evento,
            'categoria' => null,
        ]);
    }

    public function store(int $eventoId)
    {
        $data = $this->request->getPost([
            'nombre_hoja', 'pos_nombre_x', 'pos_nombre_y', 'pos_apellido_x', 'pos_apellido_y',
            'pos_qr_x', 'pos_qr_y', 'qr_ancho', 'qr_alto',
            'tam_fuente_nombre', 'tam_fuente_apellido', 'color_texto',
            'plantilla_correo_html', 'asunto_correo',
        ]);
        // Quitar claves en null (campos que no vienen en el form de creación,
        // como las posiciones, que se configuran después en "Ubicar elementos")
        // para que la base de datos aplique sus valores por defecto en vez de
        // intentar insertar NULL en columnas NOT NULL.
        $data = array_filter($data, fn ($v) => $v !== null);
        $data['evento_id'] = $eventoId;
        $data['mapeo_columnas'] = $this->mapeoDesdePost();

        if (!$this->categorias->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->categorias->errors());
        }

        $id = $this->categorias->getInsertID();
        $this->guardarPlantillaSiViene($id);
        $this->guardarBannerYAutogenerar($id);

        return redirect()->to(base_url("eventos/{$eventoId}/categorias"))->with('success', 'Categoría creada. Ahora ubica el texto y el QR sobre la imagen.');
    }

    public function edit(int $eventoId, int $id)
    {
        $evento = $this->eventos->find($eventoId);
        $categoria = $this->categorias->find($id);
        if (!$evento || !$categoria) {
            return redirect()->to(base_url('eventos'))->with('error', 'No encontrado.');
        }

        return view('admin/categorias/form', [
            'title' => 'Editar categoría',
            'evento' => $evento,
            'categoria' => $categoria,
        ]);
    }

    public function update(int $eventoId, int $id)
    {
        $data = $this->request->getPost([
            'nombre_hoja', 'pos_nombre_x', 'pos_nombre_y', 'pos_apellido_x', 'pos_apellido_y',
            'pos_qr_x', 'pos_qr_y', 'qr_ancho', 'qr_alto',
            'tam_fuente_nombre', 'tam_fuente_apellido', 'color_texto',
            'plantilla_correo_html', 'asunto_correo',
        ]);
        $data = array_filter($data, fn ($v) => $v !== null);

        // Proteger asunto y HTML del correo: si llegan vacíos, NO sobrescribir
        // lo que ya estaba guardado (evita perder contenido por accidente al
        // editar solo otro campo, como el mapeo de columnas).
        foreach (['asunto_correo', 'plantilla_correo_html'] as $campoProtegido) {
            if (isset($data[$campoProtegido]) && trim((string) $data[$campoProtegido]) === '') {
                unset($data[$campoProtegido]);
            }
        }

        $data['mapeo_columnas'] = $this->mapeoDesdePost();

        $this->categorias->update($id, $data);
        $this->guardarPlantillaSiViene($id);
        $this->guardarBannerYAutogenerar($id);

        if ($this->categorias->errors()) {
            return redirect()->back()->withInput()->with('errors', $this->categorias->errors());
        }

        return redirect()->to(base_url("eventos/{$eventoId}/categorias"))->with('success', 'Categoría actualizada.');
    }

    public function delete(int $eventoId, int $id)
    {
        $categoria = $this->categorias->find($id);
        if ($categoria && $categoria['plantilla_credencial']) {
            $ruta = rtrim($this->config->dirPlantillasCredenciales, '/') . '/' . $categoria['plantilla_credencial'];
            if (is_file($ruta)) {
                unlink($ruta);
            }
        }
        $this->categorias->delete($id);

        return redirect()->to(base_url("eventos/{$eventoId}/categorias"))->with('success', 'Categoría eliminada.');
    }

    /**
     * Sirve la imagen de la plantilla (vive en writable/, no es accesible por URL directa).
     */
    public function imagen(int $id)
    {
        $categoria = $this->categorias->find($id);
        if (!$categoria || !$categoria['plantilla_credencial']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $ruta = rtrim($this->config->dirPlantillasCredenciales, '/') . '/' . $categoria['plantilla_credencial'];
        if (!is_file($ruta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response->setContentType('image/jpeg')->setBody(file_get_contents($ruta));
    }

    /**
     * Vista del selector visual: clic sobre la imagen para ubicar nombre, apellido y QR.
     */
    public function posicionar(int $eventoId, int $id)
    {
        $evento = $this->eventos->find($eventoId);
        $categoria = $this->categorias->find($id);
        if (!$evento || !$categoria) {
            return redirect()->to(base_url('eventos'))->with('error', 'No encontrado.');
        }
        if (!$categoria['plantilla_credencial']) {
            return redirect()
                ->to(base_url("eventos/{$eventoId}/categorias/{$id}/editar"))
                ->with('error', 'Primero sube la imagen de la plantilla.');
        }

        return view('admin/categorias/posicionar', [
            'title' => 'Ubicar elementos - ' . $categoria['nombre_hoja'],
            'evento' => $evento,
            'categoria' => $categoria,
            'camposExtra' => $this->categorias->camposExtra($categoria),
        ]);
    }

    /**
     * AJAX: guarda las posiciones que el usuario marcó haciendo clic sobre la imagen,
     * incluyendo cualquier cantidad de campos extra (CARGO, EMPRESA, etc.).
     */
    public function guardarPosiciones(int $eventoId, int $id)
    {
        $payload = $this->request->getJSON(true);

        $this->categorias->update($id, [
            'pos_nombre_x' => (int) ($payload['nombre']['x'] ?? 0),
            'pos_nombre_y' => (int) ($payload['nombre']['y'] ?? 0),
            'tam_fuente_nombre' => (int) ($payload['nombre']['tamFuente'] ?? 68),
            'pos_apellido_x' => (int) ($payload['apellido']['x'] ?? 0),
            'pos_apellido_y' => (int) ($payload['apellido']['y'] ?? 0),
            'tam_fuente_apellido' => (int) ($payload['apellido']['tamFuente'] ?? 68),
            'pos_qr_x' => (int) ($payload['qr']['x'] ?? 0),
            'pos_qr_y' => (int) ($payload['qr']['y'] ?? 0),
            'qr_ancho' => (int) ($payload['qr']['ancho'] ?? 420),
            'qr_alto' => (int) ($payload['qr']['alto'] ?? 420),
            'campos_extra' => json_encode($payload['camposExtra'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Sube la imagen de cabecera del correo (banner) a public/uploads/ —
     * a diferencia de la plantilla de credencial, ESTA imagen debe ser
     * públicamente accesible por internet (Gmail/Outlook la descargan desde
     * fuera), por eso va en /public y no en /writable.
     *
     * Si el checkbox "regenerar_html_auto" viene marcado, arma un HTML de
     * correo simple automáticamente usando esa imagen — así el usuario no
     * tiene que escribir HTML a mano.
     */
    protected function guardarBannerYAutogenerar(int $categoriaId): void
    {
        $archivo = $this->request->getFile('imagen_cabecera');
        $regenerar = (bool) $this->request->getPost('regenerar_html_auto');

        $categoria = $this->categorias->find($categoriaId);
        $rutaPublicaRelativa = $categoria['imagen_cabecera_correo'] ?? null;

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $dirPublico = FCPATH . 'uploads/correo-cabeceras';
            if (!is_dir($dirPublico)) {
                mkdir($dirPublico, 0775, true);
            }

            $nombreArchivo = "cat_{$categoriaId}_" . time() . '.' . $archivo->getExtension();
            $archivo->move($dirPublico, $nombreArchivo, true);

            $rutaPublicaRelativa = 'uploads/correo-cabeceras/' . $nombreArchivo;
            $this->categorias->update($categoriaId, ['imagen_cabecera_correo' => $rutaPublicaRelativa]);
        }

        if ($regenerar && $rutaPublicaRelativa) {
            $urlPublica = base_url($rutaPublicaRelativa);
            $this->categorias->update($categoriaId, [
                'plantilla_correo_html' => $this->plantillaSimpleHtml($urlPublica),
            ]);
        }
    }

    /**
     * Plantilla de correo genérica: banner arriba + saludo + QR + nota del PDF.
     * Usa los mismos placeholders reales de mailgun_api.py: {{nombre}}, {{categoria}}, {{qr}}.
     */
    protected function plantillaSimpleHtml(string $urlImagen): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4; padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">
          <tr>
            <td>
              <img src="{$urlImagen}" alt="" style="width:100%; display:block;">
            </td>
          </tr>
          <tr>
            <td style="padding:32px; text-align:center;">
              <h2 style="margin:0 0 16px; color:#1A2835;">Hola {{nombre}}</h2>
              <p style="color:#555; font-size:15px; margin:0 0 24px;">
                Tu credencial para la categoría <strong>{{categoria}}</strong> está lista.
                Muestra este código QR en el área de registro:
              </p>
              <img src="{{qr}}" alt="QR" style="width:180px; height:180px;">
              <p style="color:#999; font-size:12px; margin-top:24px;">
                Revisa también el PDF adjunto con tu credencial completa.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    protected function guardarPlantillaSiViene(int $categoriaId): void
    {
        $archivo = $this->request->getFile('plantilla');
        if (!$archivo || !$archivo->isValid() || $archivo->hasMoved()) {
            return;
        }

        $nombreArchivo = "cat_{$categoriaId}." . $archivo->getExtension();
        $archivo->move($this->config->dirPlantillasCredenciales, $nombreArchivo, true);

        $rutaCompleta = rtrim($this->config->dirPlantillasCredenciales, '/') . '/' . $nombreArchivo;
        [$ancho, $alto] = getimagesize($rutaCompleta);

        $this->categorias->update($categoriaId, [
            'plantilla_credencial' => $nombreArchivo,
            'plantilla_ancho' => $ancho,
            'plantilla_alto' => $alto,
        ]);
    }

    /**
     * Arma el JSON de mapeo de columnas a partir de los campos mapeo_* del formulario.
     */
    protected function mapeoDesdePost(): string
    {
        $mapeo = [
            'nombre' => $this->request->getPost('mapeo_nombre') ?: 'NOMBRES',
            'apellido' => $this->request->getPost('mapeo_apellido') ?: 'APELLIDOS',
            'documento' => $this->request->getPost('mapeo_documento') ?: 'DOCUMENTO DE IDENTIDAD',
            'correo_corporativo' => $this->request->getPost('mapeo_correo_corporativo') ?: 'CORREO CORPORATIVO',
            'correo_personal' => $this->request->getPost('mapeo_correo_personal') ?: 'CORREO PERSONAL',
        ];

        return json_encode($mapeo, JSON_UNESCAPED_UNICODE);
    }
}
