<?php

namespace App\Libraries;

require_once APPPATH . 'ThirdParty/fpdf/fpdf.php';

/**
 * CertificadoBuilder
 * ---------------------------------------------------------------------
 * Toma una plantilla (imagen JPG/PNG con el diseño fijo del certificado:
 * logo, marco, firma, etc.) y le "estampa" encima el nombre del
 * participante (y cualquier campo extra, ej. ROL) en las coordenadas
 * configuradas por categoría. El resultado se guarda como PDF.
 *
 * Requiere:
 *  - Extensión GD de PHP (casi siempre viene activada; si no, pídele a tu
 *    hosting que la active en cPanel > Select PHP Version > Extensiones).
 *  - Librería FPDF vía Composer:  composer require setasign/fpdf
 *  - Una fuente TTF en el servidor (ver $rutaFuente más abajo).
 */
class CertificadoBuilder
{
    /**
     * Ruta a la fuente TTF a usar para el texto. Súbela tú mismo, por
     * ejemplo copiando cualquier .ttf (Arial, Montserrat, etc.) a esta
     * carpeta. GD y FPDF necesitan el archivo físico, no solo el nombre.
     */
    protected string $rutaFuente = WRITEPATH . 'fonts/arial.ttf';

    /**
     * Carpeta temporal donde se arma la imagen ya con el texto estampado,
     * antes de convertirla a PDF. Se borra automáticamente al terminar.
     */
    protected string $dirTmp = WRITEPATH . 'uploads/tmp';

    public function __construct()
    {
        if (!is_dir($this->dirTmp)) {
            mkdir($this->dirTmp, 0775, true);
        }
    }

    /**
     * Genera el certificado en PDF para UNA persona.
     *
     * @param string $rutaPlantilla   Ruta absoluta a la imagen de fondo (jpg/png)
     * @param string $nombreCompleto  Nombre a estampar (ej. "Dayana Mia Barrenechea Galarza")
     * @param string $rutaPdfSalida   Ruta absoluta donde se guardará el PDF final
     * @param array  $posiciones      ['nombre' => ['x'=>?,'y'=>int], 'tamFuenteNombre'=>int,
     *                                 'colorTexto'=>'#000000', 'inicioAreaPct'=>float]
     *                                 x = null significa "centrar horizontalmente"
     *                                 inicioAreaPct = fracción (0 a 1) del ancho de la imagen
     *                                 donde empieza el área blanca (para no centrar el texto
     *                                 incluyendo la franja/logo lateral). Se aplica sobre el
     *                                 ancho REAL de la imagen cargada, no sobre ningún valor
     *                                 guardado de "ancho" en la base de datos.
     * @param array  $camposExtra     [['texto'=>string,'x'=>?,'y'=>int,'tamFuente'=>int,'color'=>?], ...]
     *                                 (ej. el ROL: "VOLUNTARIA", "PONENTE")
     *
     * @throws \RuntimeException si la plantilla o la fuente no existen, o si GD falla.
     */
    public function generarCertificadoPdf(
        string $rutaPlantilla,
        string $nombreCompleto,
        string $rutaPdfSalida,
        array $posiciones,
        array $camposExtra = []
    ): void {
        if (!is_file($rutaPlantilla)) {
            throw new \RuntimeException("Plantilla no encontrada: {$rutaPlantilla}");
        }
        if (!is_file($this->rutaFuente)) {
            throw new \RuntimeException("Fuente TTF no encontrada en: {$this->rutaFuente}. Sube un archivo .ttf ahí.");
        }
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('La extensión GD de PHP no está activada en el servidor.');
        }

        // 1) Cargar la plantilla como imagen GD (soporta jpg y png)
        $imagen = $this->cargarImagen($rutaPlantilla);
        $anchoImagen = imagesx($imagen);

        // Porcentaje (0 a 1) donde empieza el área blanca. Se calcula sobre
        // el ancho REAL de la imagen ya cargada, así que no depende de
        // ninguna columna de "ancho" guardada en la base de datos. Si no
        // viene configurado, se usa 0 (comportamiento anterior: centra en
        // toda la imagen).
        $porcentajeInicioArea = (float) ($posiciones['inicioAreaPct'] ?? 0);
        $inicioAreaX = (int) ($anchoImagen * $porcentajeInicioArea);

        // 2) Estampar el nombre
        $tamFuenteNombre = $posiciones['tamFuenteNombre'] ?? 40;
        $colorHexNombre = $posiciones['colorTexto'] ?? '#000000';
        $colorNombre = $this->hexAColorGd($imagen, $colorHexNombre);

        $xNombre = $posiciones['nombre']['x'] ?? null;
        $yNombre = $posiciones['nombre']['y'] ?? 0;

        // Si el nombre es muy largo para el ancho disponible, reducimos el
        // tamaño de fuente automáticamente (sin bajar de un mínimo legible)
        // para que nunca se salga ni se corte, en vez de desbordar la
        // imagen. Aplica tanto si el nombre va centrado (x = null) como si
        // tiene una posición x fija configurada (ej. pos_cert_nombre_x).
        //
        // OJO (fix): el espacio disponible depende de DÓNDE arranca
        // realmente el texto, no de dónde empieza el área blanca. Si el
        // nombre va centrado (x = null), arranca en $inicioAreaX. Pero si
        // tiene una x fija configurada (ej. 615), arranca ahí, que
        // normalmente está MÁS a la derecha que $inicioAreaX. Usar
        // $inicioAreaX en ese caso subestimaba el espacio ocupado y
        // sobreestimaba el espacio libre, dejando que el texto se pasara
        // del borde derecho (el bug original).
        $margenDerechoPx = (int) round($anchoImagen * 0.02);
        $puntoInicioTextoNombre = $xNombre ?? $inicioAreaX;
        $anchoDisponibleNombre = $anchoImagen - $puntoInicioTextoNombre - $margenDerechoPx;

        $tamFuenteNombreAjustado = $this->ajustarTamFuenteParaAncho(
            mb_strtoupper($nombreCompleto),
            $tamFuenteNombre,
            $anchoDisponibleNombre
        );

        $this->dibujarTextoCentrado(
            $imagen,
            mb_strtoupper($nombreCompleto),
            $xNombre,
            $yNombre,
            $tamFuenteNombreAjustado,
            $colorNombre,
            $anchoImagen,
            $inicioAreaX
        );

        // 3) Estampar campos extra (ROL, fecha, horas, etc.)
        foreach ($camposExtra as $campo) {
            $texto = trim((string) ($campo['texto'] ?? ''));
            if ($texto === '') {
                continue; // si la fila no trae valor para este campo, se omite
            }

            $tamFuente = $campo['tamFuente'] ?? 22;
            $colorHex = $campo['color'] ?? $colorHexNombre;
            $color = $this->hexAColorGd($imagen, $colorHex);
            $x = $campo['x'] ?? null;
            $y = $campo['y'] ?? 0;

            $this->dibujarTextoCentrado($imagen, mb_strtoupper($texto), $x, $y, $tamFuente, $color, $anchoImagen, $inicioAreaX);
        }

        // 4) Guardar la imagen ya "estampada" como JPG temporal
        $rutaTmpJpg = rtrim($this->dirTmp, '/') . '/' . uniqid('cert_') . '.jpg';
        imagejpeg($imagen, $rutaTmpJpg, 92);
        imagedestroy($imagen);

        // 5) Convertir esa imagen a PDF de una sola página, tamaño exacto de la imagen
        $this->imagenAPdf($rutaTmpJpg, $rutaPdfSalida);

        // 6) Limpiar el temporal
        @unlink($rutaTmpJpg);
    }

    /**
     * Carga jpg o png según la extensión del archivo.
     */
    protected function cargarImagen(string $ruta)
    {
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        $imagen = match ($ext) {
            'png' => imagecreatefrompng($ruta),
            'jpg', 'jpeg' => imagecreatefromjpeg($ruta),
            default => throw new \RuntimeException("Formato de plantilla no soportado: .{$ext} (usa jpg o png)"),
        };

        if ($imagen === false) {
            throw new \RuntimeException("No se pudo abrir la plantilla: {$ruta}");
        }

        return $imagen;
    }

    /**
     * Convierte un color '#RRGGBB' al identificador de color que usa GD
     * para esa imagen en particular.
     */
    protected function hexAColorGd($imagen, string $hex): int
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            $hex = '000000';
        }
        [$r, $g, $b] = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];

        return imagecolorallocate($imagen, $r, $g, $b);
    }

    /**
     * Calcula el tamaño de fuente más grande (hasta $tamFuenteBase) con el
     * que $texto entra dentro de $anchoDisponible, dejando un margen de
     * seguridad a los lados para que nunca quede pegado al borde. Si con el
     * tamaño mínimo ($tamFuenteMinimo) el texto sigue sin entrar, se usa
     * igual el mínimo (evita nombres microscópicos ilegibles; en ese caso
     * extremo el texto podría rozar el borde, pero es un caso raro).
     */
    protected function ajustarTamFuenteParaAncho(
        string $texto,
        int $tamFuenteBase,
        int $anchoDisponible,
        int $tamFuenteMinimo = 18,
        float $margenSeguridadPct = 0.92
    ): int {
        if ($texto === '' || $anchoDisponible <= 0) {
            return $tamFuenteBase;
        }

        $anchoMaximoPermitido = (int) ($anchoDisponible * $margenSeguridadPct);
        $tamFuente = $tamFuenteBase;

        while ($tamFuente > $tamFuenteMinimo) {
            $caja = imagettfbbox($tamFuente, 0, $this->rutaFuente, $texto);
            $anchoTexto = abs($caja[2] - $caja[0]);

            if ($anchoTexto <= $anchoMaximoPermitido) {
                break;
            }

            $tamFuente--;
        }

        return $tamFuente;
    }

    /**
     * Dibuja texto sobre la imagen. Si $x es null, centra horizontalmente
     * el texto respecto al área útil de la imagen (desde $inicioAreaX
     * hasta el borde derecho), no respecto al ancho total. Esto evita que
     * el centrado se vea "desplazado" cuando la plantilla tiene una franja
     * lateral (logo, color de fondo, etc.) que no es parte del área de texto.
     */
    protected function dibujarTextoCentrado($imagen, string $texto, ?int $x, int $y, int $tamFuente, int $color, int $anchoImagen, int $inicioAreaX = 0): void
    {
        if ($x === null) {
            // imagettfbbox calcula el ancho real que ocupará el texto con esa fuente/tamaño,
            // para poder centrarlo (igual que hace PIL/Pillow con textbbox en Python).
            $caja = imagettfbbox($tamFuente, 0, $this->rutaFuente, $texto);
            $anchoTexto = abs($caja[2] - $caja[0]);
            $anchoDisponible = $anchoImagen - $inicioAreaX;
            $x = $inicioAreaX + (int) (($anchoDisponible - $anchoTexto) / 2);
        }

        imagettftext($imagen, $tamFuente, 0, $x, $y, $color, $this->rutaFuente, $texto);
    }

    /**
     * Arma un PDF de una sola página que contiene la imagen a tamaño completo,
     * respetando la proporción y usando el tamaño de la imagen como tamaño de página.
     */
    protected function imagenAPdf(string $rutaImagen, string $rutaPdfSalida): void
    {
        [$anchoPx, $altoPx] = getimagesize($rutaImagen);

        // Convertimos pixeles a milímetros asumiendo 96 DPI (estándar de pantalla/Canva).
        // Si tu plantilla fue exportada a otra resolución, ajusta este valor.
        $dpi = 96;
        $anchoMm = $anchoPx / $dpi * 25.4;
        $altoMm = $altoPx / $dpi * 25.4;

        $orientacion = $anchoMm >= $altoMm ? 'L' : 'P';

        $pdf = new \FPDF($orientacion, 'mm', [$anchoMm, $altoMm]);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->Image($rutaImagen, 0, 0, $anchoMm, $altoMm);

        $dirDestino = dirname($rutaPdfSalida);
        if (!is_dir($dirDestino)) {
            mkdir($dirDestino, 0775, true);
        }

        $pdf->Output('F', $rutaPdfSalida);
    }
}
