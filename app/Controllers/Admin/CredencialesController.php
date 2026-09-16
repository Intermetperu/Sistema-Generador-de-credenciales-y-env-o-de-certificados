<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CredencialBuilder;
use App\Libraries\GoogleSheetsService;
use App\Libraries\MailgunService;
use App\Libraries\TextoUtil;
use App\Models\CategoriaModel;
use App\Models\EventoModel;
use Config\Credenciales as CredencialesConfig;

class CredencialesController extends BaseController
{
    protected CredencialesConfig $config;
    protected EventoModel $eventos;
    protected CategoriaModel $categorias;

    public function __construct()
    {
        $this->config = config('Credenciales');
        $this->eventos = new EventoModel();
        $this->categorias = new CategoriaModel();
    }

    /**
     * Vista principal: combo de hojas (según las categorías configuradas para
     * el evento ACTIVO), combo de plantilla de email, tabla.
     */
    public function index()
    {
        $evento = $this->eventos->obtenerActivo();

        if (!$evento) {
            return view('admin/credenciales/index', [
                'title' => 'Credenciales y Correos',
                'evento' => null,
                'hojas' => [],
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

        // Solo mostramos las hojas que YA tienen una categoría configurada en el panel.
        // Si hay una pestaña nueva en el Sheet sin configurar, avisamos para que la creen.
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

        return view('admin/credenciales/index', [
            'title' => 'Credenciales y Correos',
            'evento' => $evento,
            'hojas' => $hojasListas,
            'hojasSinConfigurar' => $hojasSinConfigurar,
            'errorSheets' => $errorSheets,
        ]);
    }

    /**
     * AJAX: carga los datos de una hoja.
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

        return $this->response->setJSON([
            'ok' => true,
            'headers' => $resultado['headers'],
            'rows' => $resultado['rows'],
            'total' => count($resultado['rows']),
            'mapeo' => $mapeo,
        ]);
    }

    /**
     * Genera credenciales en PDF para las filas seleccionadas, usando la
     * plantilla y posiciones configuradas en la categoría correspondiente.
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
        if (!$categoria['plantilla_credencial']) {
            return $this->response->setJSON(['ok' => false, 'error' => "La categoría '{$hoja}' no tiene imagen de plantilla. Súbela en Editar categoría."]);
        }

        $plantillaJpg = rtrim($this->config->dirPlantillasCredenciales, '/') . '/' . $categoria['plantilla_credencial'];
        $posiciones = $this->posicionesDeCategoria($categoria);
        $definicionCamposExtra = $this->categorias->camposExtra($categoria);

        $builder = new CredencialBuilder();
        $okCount = 0;
        $errores = [];
        $nombresGenerados = [];

        foreach ($filas as $fila) {
            $nombre = (string) ($fila['nombre'] ?? '');
            $apellido = (string) ($fila['apellido'] ?? '');
            $documento = (string) ($fila['documento'] ?? '');
            $raw = $fila['raw'] ?? []; // resto de columnas de la fila del Sheet, tal cual vienen

            $nombreLimpio = TextoUtil::limpiarNombreArchivo($nombre);
            $apellidoLimpio = TextoUtil::limpiarNombreArchivo($apellido);
            $ncDisplay = TextoUtil::comoIdentificador(strtoupper("{$nombreLimpio} {$apellidoLimpio}")) ?: "PARTICIPANTE_{$fila['rowIndex']}";

            $qrData = TextoUtil::normalizar($nombre) . ' ' . TextoUtil::normalizar($apellido) . '$' . TextoUtil::normalizar($documento);

            // Arma los campos extra CON el valor real de esta fila (busca la columna en $raw,
            // sin importar mayúsculas/espacios, para evitar mismatches de capitalización)
            $camposExtraConValor = array_map(function ($campo) use ($raw) {
                return [
                    'texto' => TextoUtil::getCol($raw, [$campo['columna']]),
                    'x' => $campo['x'] ?? null,
                    'y' => $campo['y'] ?? 0,
                    'tamFuente' => $campo['tamFuente'] ?? 32,
                    'color' => $campo['color'] ?? null,
                ];
            }, $definicionCamposExtra);

            try {
                $qrPath = rtrim($this->config->dirQrGenerados, '/') . "/{$ncDisplay}.png";
                $builder->generarQrCredencial($qrData, $qrPath);

                $pdfPath = rtrim($this->config->dirCredencialesGeneradas, '/') . "/{$ncDisplay}.pdf";
                $builder->generarCredencialPdf($plantillaJpg, $nombreLimpio, $apellidoLimpio, $qrPath, $pdfPath, $posiciones, $camposExtraConValor);

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
     * Envía correos con el PDF adjunto, usando la plantilla HTML y el asunto
     * configurados en la categoría, y los datos de Mailgun del evento activo.
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
        if (!$categoria || !$categoria['plantilla_correo_html']) {
            return $this->response->setJSON(['ok' => false, 'error' => "La categoría '{$hoja}' no tiene plantilla de correo configurada."]);
        }

        // La categoría "VIRTUAL" no lleva PDF ni QR (igual que en mailgun_api.py original)
        $esVirtual = strtoupper(trim($hoja)) === 'VIRTUAL';

        $htmlBase = $categoria['plantilla_correo_html'];
        $asunto = $categoria['asunto_correo'] ?: "Credencial - {$hoja}";

        $builder = new CredencialBuilder();
        $mailgun = new MailgunService($evento);

        $exitosos = [];
        $fallidos = [];
        $rowIndicesOk = [];

        foreach ($filas as $fila) {
            $nombre = (string) ($fila['nombre'] ?? '');
            $apellido = (string) ($fila['apellido'] ?? '');
            $documento = (string) ($fila['documento'] ?? '');
            $rowIndex = (int) ($fila['rowIndex'] ?? -1);

            $nombreLimpio = TextoUtil::limpiarNombreArchivo($nombre);
            $apellidoLimpio = TextoUtil::limpiarNombreArchivo($apellido);
            // ncDisplay = nombre completo, tal cual lo usa mailgun_api.py como {{nombre}}
            $ncDisplay = TextoUtil::comoIdentificador(strtoupper("{$nombreLimpio} {$apellidoLimpio}")) ?: "PARTICIPANTE_{$rowIndex}";

            $correoCorp = trim((string) ($fila['correoCorporativo'] ?? ''));
            $correoPers = trim((string) ($fila['correoPersonal'] ?? ''));
            $correo = $correoCorp ?: $correoPers;

            if (!$correo) {
                $fallidos[] = "{$ncDisplay}: sin correo";
                continue;
            }

            $cc = ($correoPers && $correoPers !== $correo) ? $correoPers : '';

            $pdfPath = null;
            $qrEmailPath = null;
            $qrPlaceholder = ''; // {{qr}} se reemplaza vacío salvo que se genere QR

            if (!$esVirtual) {
                $pdfPath = rtrim($this->config->dirCredencialesGeneradas, '/') . "/{$ncDisplay}.pdf";
                if (!is_file($pdfPath)) {
                    $fallidos[] = "{$ncDisplay}: PDF no encontrado, genera credenciales primero";
                    continue;
                }

                $qrData = TextoUtil::normalizar($nombre) . ' ' . TextoUtil::normalizar($apellido) . '$' . TextoUtil::normalizar($documento);
                $qrEmailPath = rtrim($this->config->dirTmp, '/') . "/{$ncDisplay}_email.png";

                try {
                    $builder->generarQrEmail($qrData, $qrEmailPath);
                    $qrPlaceholder = 'cid:qr_code.png';
                } catch (\Throwable $e) {
                    $fallidos[] = "{$ncDisplay}: error generando QR ({$e->getMessage()})";
                    continue;
                }
            }

            // Placeholders REALES de tu plantilla: {{nombre}}, {{categoria}}, {{qr}}
            $htmlPersonalizado = strtr($htmlBase, [
                '{{nombre}}' => esc($ncDisplay),
                '{{categoria}}' => esc($hoja),
                '{{qr}}' => $qrPlaceholder,
            ]);

            $resultado = $mailgun->enviarConAdjunto(
                $correo,
                $cc,
                $asunto,
                $htmlPersonalizado,
                $pdfPath,
                $qrEmailPath
            );

            if ($qrEmailPath) {
                @unlink($qrEmailPath);
            }

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
     * Arma el array de posiciones que CredencialBuilder espera, a partir
     * de la fila de la tabla `categorias`.
     */
    protected function posicionesDeCategoria(array $categoria): array
    {
        return [
            'nombre' => ['x' => (int) $categoria['pos_nombre_x'] ?: null, 'y' => (int) $categoria['pos_nombre_y']],
            'apellido' => ['x' => (int) $categoria['pos_apellido_x'] ?: null, 'y' => (int) $categoria['pos_apellido_y']],
            'qr' => [
                'x' => (int) $categoria['pos_qr_x'] ?: null,
                'y' => (int) $categoria['pos_qr_y'],
                'ancho' => (int) $categoria['qr_ancho'],
                'alto' => (int) $categoria['qr_alto'],
            ],
            'tamFuenteNombre' => (int) $categoria['tam_fuente_nombre'],
            'tamFuenteApellido' => (int) $categoria['tam_fuente_apellido'],
            'colorTexto' => $categoria['color_texto'],
        ];
    }

    /**
     * AJAX: devuelve el asunto + HTML de la plantilla de correo configurada
     * para la hoja/categoría indicada. Lo usa el botón "Preview Envío".
     */
    public function previewPlantilla(string $hoja)
    {
        $evento = $this->eventos->obtenerActivo();
        if (!$evento) {
            return $this->response->setJSON(['ok' => false, 'error' => 'No hay un evento activo.']);
        }

        $categoria = $this->categorias->porHoja($evento['id'], $hoja);
        if (!$categoria || !$categoria['plantilla_correo_html']) {
            return $this->response->setJSON(['ok' => false, 'error' => "La categoría '{$hoja}' no tiene plantilla de correo configurada."]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'asunto' => $categoria['asunto_correo'] ?: "Credencial - {$hoja}",
            'html' => $categoria['plantilla_correo_html'],
            'esVirtual' => strtoupper(trim($hoja)) === 'VIRTUAL',
        ]);
    }

    /**
     * Sirve el PDF generado de una persona específica (para verlo/descargarlo desde la web).
     */
    public function verPdf(string $nombreArchivo)
    {
        // Sanitizar: solo el nombre base, sin rutas (evita path traversal)
        $nombreArchivo = basename($nombreArchivo);
        $ruta = rtrim($this->config->dirCredencialesGeneradas, '/') . '/' . $nombreArchivo . '.pdf';

        if (!is_file($ruta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("PDF no encontrado: {$nombreArchivo}");
        }

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombreArchivo . '.pdf"')
            ->setBody(file_get_contents($ruta));
    }

    // --- Stubs pendientes: se completan con modulo_estadisticas.py / modulo_historial.py / modulo_igv.py ---

    public function estadisticas()
    {
        return view('admin/credenciales/estadisticas', ['title' => 'Estadísticas']);
    }

    public function historial()
    {
        return view('admin/credenciales/historial', ['title' => 'Historial']);
    }

    public function igv()
    {
        return view('admin/credenciales/igv', ['title' => 'Calculadora IGV']);
    }
}
