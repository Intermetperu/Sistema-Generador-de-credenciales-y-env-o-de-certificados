<?php

namespace App\Libraries;

use Config\Mailgun as MailgunConfig;
use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

/**
 * Envío de correos vía API de Mailgun con adjunto PDF, equivalente a
 * enviar_correo_con_adjunto() de mailgun_api.py.
 *
 * OJO: aún no me pasaste mailgun_api.py, así que esto sigue el formato
 * estándar de la API de Mailgun (POST /v3/{domain}/messages, multipart/form-data).
 * Si tu script real arma el cuerpo distinto (variables de plantilla, tags,
 * inline images con cid, etc.) ajustamos esto en cuanto me lo compartas.
 */
class MailgunService
{
    protected MailgunConfig $config;
    protected string $domain;
    protected string $apiKey;
    protected string $from;

    /**
     * @param array|null $eventoOverride Fila del evento activo; si trae mailgun_domain/api_key/from
     *                                    no vacíos, se usan en vez de los del config general.
     */
    public function __construct(?array $eventoOverride = null)
    {
        $this->config = config('Mailgun');

        $this->domain = !empty($eventoOverride['mailgun_domain']) ? $eventoOverride['mailgun_domain'] : $this->config->dominio;
        $this->apiKey = !empty($eventoOverride['mailgun_api_key']) ? $eventoOverride['mailgun_api_key'] : $this->config->apiKey;
        $this->from = !empty($eventoOverride['mailgun_from']) ? $eventoOverride['mailgun_from'] : $this->config->from;
    }

    /**
     * Envía un correo individual, opcionalmente con PDF adjunto y QR inline (cid).
     * Replica exactamente la lógica de mailgun_api.py: from fijo, adjunto y QR
     * opcionales (la categoría VIRTUAL no lleva ninguno de los dos).
     *
     * @param string $destinatario
     * @param string $cc          Puede ir vacío
     * @param string $asunto
     * @param string $htmlBody    HTML ya renderizado (placeholders {{nombre}}/{{categoria}}/{{qr}} ya reemplazados)
     * @param string|null $rutaPdf     Ruta absoluta al PDF a adjuntar, o null si no aplica (ej. VIRTUAL)
     * @param string|null $rutaQrInline Ruta absoluta al QR para inline (cid:qr_code.png), o null si no aplica
     * @return array{ok: bool, error?: string}
     */
    public function enviarConAdjunto(
        string $destinatario,
        string $cc,
        string $asunto,
        string $htmlBody,
        ?string $rutaPdf = null,
        ?string $rutaQrInline = null
    ): array {
        $url = "{$this->config->baseUrl}/{$this->domain}/messages";

        $multipart = [
            ['name' => 'from', 'contents' => $this->from],
            ['name' => 'to', 'contents' => $destinatario],
            ['name' => 'subject', 'contents' => $asunto],
            ['name' => 'html', 'contents' => $htmlBody],
        ];

        if ($cc !== '') {
            $multipart[] = ['name' => 'cc', 'contents' => $cc];
        }

        if ($rutaPdf && is_file($rutaPdf)) {
            $multipart[] = [
                'name' => 'attachment',
                'contents' => fopen($rutaPdf, 'r'),
                'filename' => basename($rutaPdf),
            ];
        }

        // El nombre del archivo DEBE ser "qr_code.png" porque el HTML referencia
        // exactamente "cid:qr_code.png" (igual que en mailgun_api.py original).
        if ($rutaQrInline && is_file($rutaQrInline)) {
            $multipart[] = [
                'name' => 'inline',
                'contents' => fopen($rutaQrInline, 'r'),
                'filename' => 'qr_code.png',
            ];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_USERPWD => "api:{$this->apiKey}",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $this->buildCurlFile($multipart),
            CURLOPT_TIMEOUT => 30,
        ]);

        $respuesta = curl_exec($ch);
        $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($errorCurl) {
            return ['ok' => false, 'error' => $errorCurl];
        }

        if ($codigo >= 200 && $codigo < 300) {
            return ['ok' => true];
        }

        return ['ok' => false, 'error' => "HTTP {$codigo}: {$respuesta}"];
    }

    /**
     * Convierte el array tipo Guzzle "multipart" a formato CURLFile para CURLOPT_POSTFIELDS.
     * Detecta el Content-Type real según la extensión: si se manda todo como
     * "application/octet-stream" (genérico), Gmail no reconoce el QR como
     * imagen y por eso nunca lo muestra incrustado en el cuerpo del correo
     * (aunque sí lo adjunta, como archivo suelto descargable).
     */
    protected function buildCurlFile(array $multipart): array
    {
        $fields = [];
        foreach ($multipart as $parte) {
            if (is_resource($parte['contents'])) {
                $meta = stream_get_meta_data($parte['contents']);
                $filename = $parte['filename'] ?? basename($meta['uri']);
                $mime = $this->mimeSegunExtension($filename);
                $fields[$parte['name']] = new \CURLFile($meta['uri'], $mime, $filename);
                fclose($parte['contents']);
            } else {
                $fields[$parte['name']] = $parte['contents'];
            }
        }
        return $fields;
    }

    protected function mimeSegunExtension(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}