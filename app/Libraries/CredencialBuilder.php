<?php

namespace App\Libraries;

use Config\Credenciales as CredencialesConfig;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use RuntimeException;

/**
 * Equivalente PHP de: generar_qr_credencial(), generar_qr_email(),
 * y de la lógica de composición de imagen + export a PDF dentro de
 * generar_credenciales_pdf() (usaba qrcode + Pillow; aquí usamos
 * endroid/qr-code + GD + Dompdf).
 *
 * Requiere: composer require endroid/qr-code:^5.0 dompdf/dompdf
 * (dompdf ya está en tu vendor/).
 */
class CredencialBuilder
{
    protected CredencialesConfig $config;

    public function __construct()
    {
        $this->config = config('Credenciales');

        foreach ([
            $this->config->dirQrGenerados,
            $this->config->dirCredencialesGeneradas,
            $this->config->dirTmp,
        ] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }
    }

    /**
     * QR para la credencial PDF: blanco sobre fondo transparente (como en Python).
     */
    public function generarQrCredencial(string $data, string $rutaSalida): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(420)
            ->margin(16)
            ->foregroundColor(new Color(255, 255, 255))
            ->backgroundColor(new Color(0, 0, 0, 100)) // alpha 100 = transparente
            ->build();

        $result->saveToFile($rutaSalida);

        return $rutaSalida;
    }

    /**
     * QR para el correo: negro sobre blanco (como en Python).
     */
    public function generarQrEmail(string $data, string $rutaSalida): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(420)
            ->margin(16)
            ->foregroundColor(new Color(0, 0, 0))
            ->backgroundColor(new Color(255, 255, 255))
            ->build();

        $result->saveToFile($rutaSalida);

        return $rutaSalida;
    }

    /**
     * Compone nombre + apellido + QR + campos extra (CARGO, EMPRESA, etc.) sobre
     * la plantilla JPG de la categoría y exporta el resultado como PDF de una
     * sola página (tamaño = imagen). Equivalente al bloque central de
     * generar_credenciales_pdf() del script Python original.
     *
     * @param array $posiciones {
     *   nombre: {x, y}, apellido: {x, y}, qr: {x, y, ancho, alto},
     *   tamFuenteNombre, tamFuenteApellido, colorTexto
     * }
     * @param array $camposExtra [{texto, x, y, tamFuente, color}, ...] ya con
     *   el VALOR real de la fila (no la definición de columna) — lo arma el
     *   controlador antes de llamar a este método.
     */
    public function generarCredencialPdf(
        string $rutaPlantillaJpg,
        string $nombre,
        string $apellido,
        string $rutaQrPng,
        string $rutaPdfSalida,
        array $posiciones = [],
        array $camposExtra = []
    ): void {
        if (!is_file($rutaPlantillaJpg)) {
            throw new RuntimeException("No existe la plantilla: {$rutaPlantillaJpg}");
        }

        $fuente = rtrim($this->config->dirFonts, '/') . '/' . $this->config->fuenteBold;
        if (!is_file($fuente)) {
            throw new RuntimeException("No existe la fuente TTF: {$fuente}");
        }

        // Defaults = los del config general, pero la categoría los puede sobrescribir
        $posNombre = $posiciones['nombre'] ?? ['x' => null, 'y' => $this->config->yNombre];
        $posApellido = $posiciones['apellido'] ?? ['x' => null, 'y' => $this->config->yApellido];
        $posQr = $posiciones['qr'] ?? ['x' => null, 'y' => $this->config->qrY, 'ancho' => $this->config->qrAncho, 'alto' => $this->config->qrAlto];
        $tamFuenteNombre = $posiciones['tamFuenteNombre'] ?? $this->config->tamFuenteNombre;
        $tamFuenteApellido = $posiciones['tamFuenteApellido'] ?? $this->config->tamFuenteApellido;
        $colorTextoHex = $posiciones['colorTexto'] ?? $this->config->colorTexto;

        $imagen = imagecreatefromjpeg($rutaPlantillaJpg);
        $ancho  = imagesx($imagen);

        [$r, $g, $b] = $this->hexToRgb($colorTextoHex);
        $colorTexto = imagecolorallocate($imagen, $r, $g, $b);

        // Si x es null, se centra horizontalmente (comportamiento original); si no, se usa el x exacto marcado en el selector visual.
        $this->dibujarTexto($imagen, mb_strtoupper($nombre), $fuente, $tamFuenteNombre, $posNombre['x'], $posNombre['y'], $ancho, $colorTexto);
        $this->dibujarTexto($imagen, mb_strtoupper($apellido), $fuente, $tamFuenteApellido, $posApellido['x'], $posApellido['y'], $ancho, $colorTexto);

        // Campos extra dinámicos (CARGO, EMPRESA, PAÍS, etc.)
        foreach ($camposExtra as $campo) {
            $texto = (string) ($campo['texto'] ?? '');
            if ($texto === '') {
                continue;
            }
            $tamCampo = (int) ($campo['tamFuente'] ?? 32);
            [$cr, $cg, $cb] = $this->hexToRgb($campo['color'] ?? $colorTextoHex);
            $colorCampo = imagecolorallocate($imagen, $cr, $cg, $cb);
            $this->dibujarTexto($imagen, $texto, $fuente, $tamCampo, $campo['x'] ?? null, (int) ($campo['y'] ?? 0), $ancho, $colorCampo);
        }

        // Pegar QR (PNG con transparencia) en la posición configurada
        $qrAncho = (int) $posQr['ancho'];
        $qrAlto = (int) $posQr['alto'];
        $qr = imagecreatefrompng($rutaQrPng);
        $qrRedim = imagecreatetruecolor($qrAncho, $qrAlto);
        imagesavealpha($qrRedim, true);
        $transparente = imagecolorallocatealpha($qrRedim, 0, 0, 0, 127);
        imagefill($qrRedim, 0, 0, $transparente);
        imagecopyresampled(
            $qrRedim, $qr,
            0, 0, 0, 0,
            $qrAncho, $qrAlto,
            imagesx($qr), imagesy($qr)
        );

        $qrX = $posQr['x'] !== null ? (int) $posQr['x'] : (int) (($ancho - $qrAncho) / 2);
        imagecopy($imagen, $qrRedim, $qrX, (int) $posQr['y'], 0, 0, $qrAncho, $qrAlto);

        // Guardar PNG temporal compuesto
        $tmpPng = rtrim($this->config->dirTmp, '/') . '/' . uniqid('cred_', true) . '.png';
        imagepng($imagen, $tmpPng);
        imagedestroy($imagen);
        imagedestroy($qr);
        imagedestroy($qrRedim);

        // Envolver el PNG final en un PDF de una sola página (a tamaño real).
        // Se usa 100 DPI para que coincida EXACTO con el tamaño de página que
        // generaba Pillow en el script original (img.save(..., resolution=100.0)).
        [$wPx, $hPx] = getimagesize($tmpPng);
        $wPt = $wPx * 0.72; // 72/100 = 0.72 (100 DPI, igual que Pillow)
        $hPt = $hPx * 0.72;

        $base64 = base64_encode(file_get_contents($tmpPng));
        $html = "<html><head><style>
            @page { margin: 0; size: {$wPt}pt {$hPt}pt; }
            body { margin: 0; }
            img { width: 100%; height: 100%; display: block; }
        </style></head><body>
            <img src=\"data:image/png;base64,{$base64}\">
        </body></html>";

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper([0, 0, $wPt, $hPt]);
        $dompdf->loadHtml($html);
        $dompdf->render();

        file_put_contents($rutaPdfSalida, $dompdf->output());

        @unlink($tmpPng);
    }

    /**
     * Dibuja texto en la imagen. Si $xFijo es null, centra horizontalmente
     * (igual que antes); si viene un valor, usa esa X exacta marcada en el
     * selector visual.
     *
     * Auto-reduce el tamaño de fuente si el texto es demasiado largo para el
     * espacio disponible (evita que nombres/apellidos largos se corten en el
     * borde de la credencial).
     */
    protected function dibujarTexto($imagen, string $texto, string $fuente, int $tamMax, ?int $xFijo, int $y, int $anchoImagen, $color): void
    {
        if ($texto === '') {
            return;
        }

        $margen = 20;
        // Espacio disponible: desde X hasta el borde derecho (con margen), o todo
        // el ancho si está centrado.
        $limiteAncho = $xFijo !== null
            ? max(40, $anchoImagen - $xFijo - $margen)
            : ($anchoImagen - 2 * $margen);

        $tam = $tamMax;
        $anchoTexto = 0;
        while ($tam > 10) {
            $bbox = imagettfbbox($tam, 0, $fuente, $texto);
            $anchoTexto = abs($bbox[2] - $bbox[0]);
            if ($anchoTexto <= $limiteAncho) {
                break;
            }
            $tam -= 2;
        }

        if ($xFijo !== null) {
            $x = $xFijo;
        } else {
            $x = (int) (($anchoImagen - $anchoTexto) / 2);
        }

        // imagettftext usa la línea base; +tam aproxima el "top" usado en Pillow (anchor lt)
        imagettftext($imagen, $tam, 0, $x, $y + $tam, $color, $fuente, $texto);
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}