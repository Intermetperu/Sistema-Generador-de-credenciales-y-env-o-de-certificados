<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Config del módulo Credenciales / Correos (FLOTACIÓN 2026).
 * Equivalente al diccionario CONFIG del script Python original.
 */
class Credenciales extends BaseConfig
{
    // --- Google Sheets ---
    public string $scopes        = 'https://www.googleapis.com/auth/spreadsheets';

    // Ruta al credentials.json (cuenta de servicio). NUNCA debe vivir en /public.
    // Colócalo en writable/credenciales/credentials.json (fuera del webroot)
    // o mejor aún fuera del repo y apunta la ruta absoluta desde .env.
    public string $keyFile       = WRITEPATH . 'credenciales/credentials.json';

    // ID del spreadsheet. Defínelo en .env como credenciales.spreadsheetId
    public string $spreadsheetId = '';

    // Nombres de hoja que se ignoran al listar categorías
    public array $hojasIgnoradas = ['Hoja 1', 'Hoja 2'];

    // Hoja en la que se detiene el listado (igual que el script: rompe en "VIRTUAL")
    public string $hojaLimite = 'VIRTUAL';

    // --- Carpetas de trabajo (todas dentro de writable/, NO en /public) ---
    public string $dirPlantillasCorreo       = WRITEPATH . 'credenciales/plantillas-correo';
    public string $dirPlantillasCredenciales = WRITEPATH . 'credenciales/plantillas-credenciales';
    public string $dirQrGenerados            = WRITEPATH . 'credenciales/qr';
    public string $dirCredencialesGeneradas  = WRITEPATH . 'credenciales/pdf';
    public string $dirTmp                    = WRITEPATH . 'credenciales/tmp';
    public string $dirFonts                  = WRITEPATH . 'credenciales/fonts';

    // --- Tipografía y posicionamiento sobre la plantilla (igual que Pillow) ---
    public string $fuenteBold    = 'arialbd.ttf'; // debe existir dentro de dirFonts
    public int $tamFuenteNombre  = 68;
    public int $tamFuenteApellido = 68;
    public int $yNombre   = 610;
    public int $yApellido = 690;
    public int $qrY       = 750;
    public int $qrAncho   = 420;
    public int $qrAlto    = 420;
    public string $colorTexto = '#FFFFFF';

    // --- Mailgun ---
    public string $mailgunDomain = ''; // .env: credenciales.mailgunDomain
    public string $mailgunApiKey = ''; // .env: credenciales.mailgunApiKey
    public string $mailgunFrom   = ''; // ej: "Eventos <no-reply@tudominio.com>"
    public string $mailgunBaseUrl = 'https://api.mailgun.net/v3';

    // Columnas que se intentan leer (multi-alias, como get_col() en Python)
    public array $aliasDocumento = [
        'Documento de Identidad / Pasaporte / ID',
        'DOCUMENTO DE IDENTIDAD',
        'documento',
    ];
}
