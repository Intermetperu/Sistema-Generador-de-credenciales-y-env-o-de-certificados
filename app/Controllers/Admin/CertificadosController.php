<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CertificadoBuilder;
use App\Libraries\GoogleSheetsService;
use App\Libraries\MailgunService;
use App\Libraries\TextoUtil;
use App\Models\CategoriaModel;
use App\Models\CertificadoAccesoModel;
use App\Models\EventoModel;
use Config\Certificados as CertificadosConfig;

class CertificadosController extends BaseController
{
    protected CertificadosConfig $config;
    protected EventoModel $eventos;
    protected CategoriaModel $categorias;
    protected CertificadoAccesoModel $accesos;

    public function __construct()
    {
        $this->config = config('Certificados');
        $this->eventos = new EventoModel();
        $this->categorias = new CategoriaModel();
        $this->accesos = new CertificadoAccesoModel();
    }

    /**
     * Vista principal: combo de hojas (según las categorías configuradas para
     * el evento ACTIVO), combo de plantilla de email, tabla.
     */
    public function index()
    {
        $evento = $this->eventos->obtenerActivo();

        if (!$evento) {
            return view('admin/certificados/index', [
                'title' => 'Certificados y Correos',
                'evento' => null,
                'hojas' => [],
                'categorias' => [],
                'hojasSinConfigurar' => [],
                'errorSheets' => null,
            ]);
        }

        $hojasSheet = [];
        $errorSheets = null;
        try {
            $sheets = new GoogleSheetsService($evento['spreadsheet_id'], $evento['hoja_limite']);
            $hojasSheet = $sheets->obtenerHojas();
        } catch (\Throwable $e) {
            $errorSheets = $e->getMessage();
        }

        $categoriasEvento = $this->categorias->porEvento($evento['id']);
        $nombresConfigurados = array_map(fn ($c) => strtoupper($c['nombre_hoja']), $categoriasEvento);

        $hojasListas = [];
        $hojasSinConfigurar = [];
        foreach ($hojasSheet as $h) {
            if (in_array(strtoupper($h), $nombresConfigurados, true)) {
                $hojasListas[] = $h;
            } else {
                $hojasSinConfigurar[] = $h;
            }
        }

        return view('admin/certificados/index', [
            'title' => 'Certificados y Correos',
            'evento' => $evento,
            'hojas' => $hojasListas,
            'categorias' => $categoriasEvento,
            'hojasSinConfigurar' => $hojasSinConfigurar,
            'errorSheets' => $errorSheets,
        ]);
    }

    /**
     * AJAX: carga los datos de una hoja (mismo formato que Credenciales).
     */
    public function datos(string $hoja)
    {
        $evento = $this->eventos->obtenerActivo();
        if (!$evento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay un evento activo. Ve a "Eventos" y activa uno.']);
        }

        try {
            $sheets = new GoogleSheetsService($evento['spreadsheet_id'], $evento['hoja_limite']);
            $resultado = $sheets->obtenerFilas($hoja);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['ok' => false, 'error' => $e->getMessage()]);
        }

        $categoria = $this->categorias->porHoja($evento['id'], $hoja);
        $mapeo = $categoria ? $this->categorias->mapeoColumnas($categoria) : [];

        // Para cada fila, calculamos si el certificado YA fue generado
        // (mismo identificador que usa generarPdf() para nombrar el archivo),
        // así el front puede mostrar "Ver certificado" o "Pendiente".
        $filasConEstado = array_map(function ($row) use ($mapeo) {
            $nombre = TextoUtil::getCol($row, [$mapeo['nombre'] ?? 'NOMBRES']);
            $apellido = TextoUtil::getCol($row, [$mapeo['apellido'] ?? 'APELLIDOS']);
            $nombreLimpio = TextoUtil::limpiarNombreArchivo($nombre);
            $apellidoLimpio = TextoUtil::limpiarNombreArchivo($apellido);
            $ncDisplay = TextoUtil::comoIdentificador(strtoupper("{$nombreLimpio} {$apellidoLimpio}"));

            $pdfPath = rtrim($this->config->dirCertificadosGenerados, '/') . "/{$ncDisplay}.pdf";
            $existe = $ncDisplay !== '' && is_file($pdfPath);

            $row['_certificadoUrl'] = $existe ? site_url("certificados/ver-pdf/{$ncDisplay}") : null;

            return $row;
        }, $resultado['rows']);

        return $this->response->setJSON([
            'ok' => true,
            'headers' => $resultado['headers'],
            'rows' => $filasConEstado,
            'total' => count($filasConEstado),
            'mapeo' => $mapeo,
            'linkEncuesta' => $categoria['link_encuesta'] ?? '',
            'asuntoCorreo' => $categoria['asunto_correo_certificado'] ?? '',
            'plantillaCorreo' => $categoria['plantilla_correo_certificado'] ?? '',
            'plantillaCertificado' => $categoria['plantilla_certificado'] ?? '',
            'plantillaCertificadoUrl' => !empty($categoria['plantilla_certificado'])
                ? site_url("certificados/ver-plantilla/{$categoria['plantilla_certificado']}")
                : null,
        ]);
    }

    /**
     * Guarda el link del Google Forms de la encuesta para una categoría.
     */
    public function guardarLinkEncuesta()
    {
        $evento = $this->eventos->obtenerActivo();
        if (!$evento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay un evento activo.']);
        }

        $hoja = $this->request->getPost('hoja');
        $url = trim((string) $this->request->getPost('url'));

        $categoria = $this->categorias->porHoja($evento['id'], $hoja);
        if (!$categoria) {
            return $this->response->setJSON(['ok' => false, 'error' => "La hoja '{$hoja}' no tiene categoría configurada."]);
        }

        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'La URL no es válida.']);
        }

        $this->categorias->actualizarLinkEncuesta($categoria['id'], $url);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Guarda el asunto y el HTML de la plantilla de correo de certificado
     * para una categoría (lo que luego usa enviarCorreos()).
     *
     * Protección igual que CategoriasController::update(): si el HTML llega
     * vacío pero ya había uno guardado, NO se pisa (se conserva el anterior).
     * Solo se rechaza el guardado si nunca hubo un HTML guardado y tampoco
     * viene uno nuevo, porque la plantilla no puede quedar sin HTML la
     * primera vez. El asunto sí se puede guardar vacío/null sin problema,
     * ya que tiene fallback ('Certificado - {{evento}}') en enviarCorreos().
     */
    public function guardarPlantillaCorreo()
    {
        $evento = $this->eventos->obtenerActivo();
        if (!$evento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay un evento activo.']);
        }

        $hoja = $this->request->getPost('hoja');
        $asunto = trim((string) $this->request->getPost('asunto'));
        $html = (string) $this->request->getPost('html');

        $categoria = $this->categorias->porHoja($evento['id'], $hoja);
        if (!$categoria) {
            return $this->response->setJSON(['ok' => false, 'error' => "La hoja '{$hoja}' no tiene categoría configurada."]);
        }

        $data = ['asunto_correo_certificado' => $asunto !== '' ? $asunto : null];

        // Proteger el HTML: si llega vacío, no pisar lo que ya estaba guardado.
        if (trim($html) !== '') {
            $data['plantilla_correo_certificado'] = $html;
        } elseif (empty($categoria['plantilla_correo_certificado'])) {
            // No hay HTML nuevo ni uno previo guardado: no hay nada que guardar.
            return $this->response->setJSON(['ok' => false, 'error' => 'El HTML no puede estar vacío.']);
        }

        $this->categorias->update($categoria['id'], $data);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Sube/reemplaza la plantilla de certificado de una categoría.
     * Mismo patrón que credenciales: se guarda por categoría/hoja, aunque en
     * la práctica normalmente subirás la misma imagen para todas las
     * categorías de un evento (a menos que tengas variantes por rol).
     */
    public function subirPlantilla()
    {
        $evento = $this->eventos->obtenerActivo();
        if (!$evento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay un evento activo.']);
        }

        $hoja = $this->request->getPost('hoja');
        $categoria = $this->categorias->porHoja($evento['id'], $hoja);
        if (!$categoria) {
            return $this->response->setJSON(['ok' => false, 'error' => "La hoja '{$hoja}' no tiene categoría configurada."]);
        }

        $file = $this->request->getFile('plantilla');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Archivo inválido.']);
        }

        $nombreArchivo = 'cert_' . $evento['id'] . '_' . $categoria['id'] . '_' . $file->getRandomName();
        $file->move(rtrim($this->config->dirPlantillasCertificados, '/'), $nombreArchivo);

        $this->categorias->actualizarPlantillaCertificado($categoria['id'], $nombreArchivo);

        return $this->response->setJSON([
            'ok' => true,
            'archivo' => $nombreArchivo,
            'url' => site_url("certificados/ver-plantilla/{$nombreArchivo}"),
        ]);
    }

    /**
     * Genera certificados en PDF para las filas seleccionadas.
     */
    public function generarPdf()
    {
        $evento = $this->eventos->obtenerActivo();
        if (!$evento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay un evento activo.']);
        }

        $payload = $this->request->getJSON(true) ?? [];
        $hoja = $payload['hoja'] ?? null;
        $filas = $payload['filas'] ?? [];

        if (!$hoja || empty($filas)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Selecciona hoja y filas.']);
        }

        $categoria = $this->categorias->porHoja($evento['id'], $hoja);
        if (!$categoria) {
            return $this->response->setJSON(['ok' => false, 'error' => "La hoja '{$hoja}' no tiene una categoría configurada. Ve a Eventos > Categorías y créala."]);
        }
        if (empty($categoria['plantilla_certificado'])) {
            return $this->response->setJSON(['ok' => false, 'error' => "La categoría '{$hoja}' no tiene plantilla de certificado. Súbela primero."]);
        }

        $plantilla = rtrim($this->config->dirPlantillasCertificados, '/') . '/' . $categoria['plantilla_certificado'];
        $posiciones = $this->posicionesDeCategoria($categoria);
        $definicionCamposExtra = $this->categorias->camposExtraCertificado($categoria);

        $builder = new CertificadoBuilder();
        $okCount = 0;
        $errores = [];
        $nombresGenerados = [];

        foreach ($filas as $fila) {
            $nombre = (string) ($fila['nombre'] ?? '');
            $apellido = (string) ($fila['apellido'] ?? '');
            $raw = $fila['raw'] ?? [];

            $nombreLimpio = TextoUtil::limpiarNombreArchivo($nombre);
            $apellidoLimpio = TextoUtil::limpiarNombreArchivo($apellido);
            $ncDisplay = TextoUtil::comoIdentificador(strtoupper("{$nombreLimpio} {$apellidoLimpio}")) ?: "PARTICIPANTE_{$fila['rowIndex']}";
            $nombreCompleto = trim("{$nombre} {$apellido}");

            // Igual que credenciales: campos extra (ej. ROL: "VOLUNTARIA", "PONENTE")
            // con su valor real tomado de la columna del Sheet mapeada para esta fila.
            $camposExtraConValor = array_map(function ($campo) use ($raw) {
                return [
                    'texto' => TextoUtil::getCol($raw, [$campo['columna']]),
                    'x' => $campo['x'] ?? null,
                    'y' => $campo['y'] ?? 0,
                    'tamFuente' => $campo['tamFuente'] ?? 22,
                    'color' => $campo['color'] ?? null,
                ];
            }, $definicionCamposExtra);

            try {
                $pdfPath = rtrim($this->config->dirCertificadosGenerados, '/') . "/{$ncDisplay}.pdf";
                $builder->generarCertificadoPdf($plantilla, $nombreCompleto, $pdfPath, $posiciones, $camposExtraConValor);

                $correoCorp = trim((string) ($fila['correoCorporativo'] ?? ''));
                $correoPers = trim((string) ($fila['correoPersonal'] ?? ''));
                $this->accesos->obtenerOCrearToken(
                    $evento['id'],
                    $hoja,
                    $ncDisplay,
                    $nombreCompleto,
                    $correoCorp ?: $correoPers
                );

                $okCount++;
                $nombresGenerados[] = $ncDisplay;
            } catch (\Throwable $e) {
                $errores[] = "{$ncDisplay}: {$e->getMessage()}";
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'generados' => $okCount,
            'total' => count($filas),
            'errores' => $errores,
            'nombresGenerados' => $nombresGenerados,
        ]);
    }

    /**
     * Envía correos con el certificado PDF adjunto (sin QR).
     */
    public function enviarCorreos()
    {
        $evento = $this->eventos->obtenerActivo();
        if (!$evento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay un evento activo.']);
        }

        $payload = $this->request->getJSON(true) ?? [];
        $hoja = $payload['hoja'] ?? null;
        $filas = $payload['filas'] ?? [];

        if (!$hoja || empty($filas)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Selecciona hoja y filas.']);
        }

        $categoria = $this->categorias->porHoja($evento['id'], $hoja);
        if (!$categoria || empty($categoria['plantilla_correo_certificado'])) {
            return $this->response->setJSON(['ok' => false, 'error' => "La categoría '{$hoja}' no tiene plantilla de correo de certificado configurada."]);
        }

        $htmlBase = $categoria['plantilla_correo_certificado'];
        $asunto = strtr(
            $categoria['asunto_correo_certificado'] ?: 'Certificado - {{evento}}',
            ['{{evento}}' => $evento['nombre']]
        );

        $mailgun = new MailgunService($evento);

        $exitosos = [];
        $fallidos = [];
        $rowIndicesOk = [];

        foreach ($filas as $fila) {
            $nombre = (string) ($fila['nombre'] ?? '');
            $apellido = (string) ($fila['apellido'] ?? '');
            $rowIndex = (int) ($fila['rowIndex'] ?? -1);

            $nombreLimpio = TextoUtil::limpiarNombreArchivo($nombre);
            $apellidoLimpio = TextoUtil::limpiarNombreArchivo($apellido);
            $ncDisplay = TextoUtil::comoIdentificador(strtoupper("{$nombreLimpio} {$apellidoLimpio}")) ?: "PARTICIPANTE_{$rowIndex}";

            $correoCorp = trim((string) ($fila['correoCorporativo'] ?? ''));
            $correoPers = trim((string) ($fila['correoPersonal'] ?? ''));
            $correo = $correoCorp ?: $correoPers;

            if (!$correo) {
                $fallidos[] = "{$ncDisplay}: sin correo";
                continue;
            }

            $cc = ($correoPers && $correoPers !== $correo) ? $correoPers : '';

            $pdfPath = rtrim($this->config->dirCertificadosGenerados, '/') . "/{$ncDisplay}.pdf";
            if (!is_file($pdfPath)) {
                $fallidos[] = "{$ncDisplay}: PDF no encontrado, genera certificados primero";
                continue;
            }

            $acceso = $this->accesos->obtenerOCrearToken($evento['id'], $hoja, $ncDisplay, trim("{$nombre} {$apellido}"), $correo);
            $linkCertificado = site_url("encuesta/{$acceso['token']}");

            $htmlPersonalizado = strtr($htmlBase, [
                '{{nombre}}' => esc($ncDisplay),
                '{{categoria}}' => esc($hoja),
                '{{evento}}' => esc($evento['nombre']),
                '{{link_certificado}}' => $linkCertificado,
            ]);

            $resultado = $mailgun->enviarConAdjunto(
                $correo,
                $cc,
                $asunto,
                $htmlPersonalizado,
                null, // sin adjunto: el certificado se descarga desde el link tras completar la encuesta
                null
            );

            if ($resultado['ok']) {
                $exitosos[] = $correo;
                if ($rowIndex >= 0) {
                    $rowIndicesOk[] = $rowIndex;
                }
            } else {
                $fallidos[] = "{$ncDisplay}: {$resultado['error']}";
            }
        }

        if (!empty($rowIndicesOk)) {
            try {
                $sheets = new GoogleSheetsService($evento['spreadsheet_id'], $evento['hoja_limite']);
                $sheets->actualizarConfirmacion($hoja, $rowIndicesOk, true);
            } catch (\Throwable $e) {
                $fallidos[] = "Aviso: correos enviados pero no se pudo actualizar el Sheet ({$e->getMessage()})";
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'exitosos' => $exitosos,
            'fallidos' => $fallidos,
        ]);
    }

    /**
     * 👉 NUEVO: Resetea el estado de CONFIRMACION (lo pone en false) para
     * las filas seleccionadas. Útil cuando el Sheet trae datos "sucios" de
     * pruebas anteriores o de una migración, y quieres que el panel refleje
     * el estado real de envío desde cero. No borra ningún PDF generado ni
     * revoca el token de acceso ya creado en `certificado_accesos`; solo
     * corrige el valor mostrado en la columna ESTADO del panel.
     */
    public function resetearConfirmacion()
    {
        $evento = $this->eventos->obtenerActivo();
        if (!$evento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay un evento activo.']);
        }

        $payload = $this->request->getJSON(true) ?? [];
        $hoja = $payload['hoja'] ?? null;
        $rowIndices = $payload['rowIndices'] ?? [];

        if (!$hoja || empty($rowIndices)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Selecciona hoja y filas.']);
        }

        try {
            $sheets = new GoogleSheetsService($evento['spreadsheet_id'], $evento['hoja_limite']);
            $sheets->actualizarConfirmacion($hoja, $rowIndices, false);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['ok' => false, 'error' => $e->getMessage()]);
        }

        return $this->response->setJSON(['ok' => true, 'reseteados' => count($rowIndices)]);
    }

    /**
     * Posiciones que CertificadoBuilder necesita, a partir de la fila de la
     * tabla `categorias`. Solo la posición del nombre va fija por columna;
     * cualquier otro texto variable (rol, reconocimiento, etc.) se maneja
     * vía campos_extra_cert, igual que en credenciales.
     */
protected function posicionesDeCategoria(array $categoria): array
{
    return [
        'nombre' => [
            'x' => isset($categoria['pos_cert_nombre_x']) && $categoria['pos_cert_nombre_x'] !== null
                ? (int) $categoria['pos_cert_nombre_x']
                : null,
            'y' => (int) ($categoria['pos_cert_nombre_y'] ?? 0),
        ],
        'tamFuenteNombre' => (int) ($categoria['tam_fuente_cert_nombre'] ?? 40),
        'colorTexto' => $categoria['color_texto_cert'] ?? '#000000',
        'inicioAreaPct' => (float) ($categoria['area_texto_inicio_pct'] ?? 0),// <-- NUEVO
    ];
}
    /**
     * Sirve el PDF de certificado generado (ver/descargar desde la web).
     */
    public function verPdf(string $nombreArchivo)
    {
        $nombreArchivo = basename($nombreArchivo);
        $ruta = rtrim($this->config->dirCertificadosGenerados, '/') . '/' . $nombreArchivo . '.pdf';

        if (!is_file($ruta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("PDF no encontrado: {$nombreArchivo}");
        }

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombreArchivo . '.pdf"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody(file_get_contents($ruta));
    }

    /**
     * Sirve la imagen de plantilla de certificado de una categoría
     * (para poder previsualizarla desde la pantalla de Certificados).
     */
    public function verPlantilla(string $nombreArchivo)
    {
        $nombreArchivo = basename($nombreArchivo); // seguridad: evita path traversal

        $ruta = rtrim($this->config->dirPlantillasCertificados, '/') . '/' . $nombreArchivo;

        if (!is_file($ruta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Plantilla no encontrada: {$nombreArchivo}");
        }

        $mime = mime_content_type($ruta) ?: 'application/octet-stream';

        return $this->response
            ->setContentType($mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"')
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody(file_get_contents($ruta));
    }

    /**
     * Preview del certificado de una categoría: genera el PDF con datos de
     * ejemplo (no se guarda en dirCertificadosGenerados) y lo sirve inline,
     * para que desde "Categorías" se pueda revisar la posición del texto
     * sin tener que ir a la pantalla de Certificados.
     */
    public function previewCertificado(int $categoriaId)
    {
        $categoria = $this->categorias->find($categoriaId);
        if (!$categoria) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Categoría no encontrada.');
        }
        if (empty($categoria['plantilla_certificado'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Esta categoría no tiene plantilla de certificado subida.');
        }

        $plantilla = rtrim($this->config->dirPlantillasCertificados, '/') . '/' . $categoria['plantilla_certificado'];
        $posiciones = $this->posicionesDeCategoria($categoria);
        $definicionCamposExtra = $this->categorias->camposExtraCertificado($categoria);

        // Mismos campos extra que en generarPdf(), pero con un valor de
        // muestra en vez del dato real de una fila del Sheet.
        $camposExtraConValor = array_map(function ($campo) {
            return [
                'texto' => 'EJEMPLO',
                'x' => $campo['x'] ?? null,
                'y' => $campo['y'] ?? 0,
                'tamFuente' => $campo['tamFuente'] ?? 22,
                'color' => $campo['color'] ?? null,
            ];
        }, $definicionCamposExtra);

        $builder = new CertificadoBuilder();
        $pdfPath = sys_get_temp_dir() . '/preview_cert_' . $categoria['id'] . '_' . uniqid() . '.pdf';

        try {
            $builder->generarCertificadoPdf($plantilla, 'NOMBRE DE EJEMPLO APELLIDO EJEMPLO', $pdfPath, $posiciones, $camposExtraConValor);
            $contenido = file_get_contents($pdfPath);
        } catch (\Throwable $e) {
            if (is_file($pdfPath)) {
                unlink($pdfPath);
            }
            throw $e;
        }

        if (is_file($pdfPath)) {
            unlink($pdfPath);
        }

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="preview_' . $categoria['id'] . '.pdf"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($contenido);
    }
}