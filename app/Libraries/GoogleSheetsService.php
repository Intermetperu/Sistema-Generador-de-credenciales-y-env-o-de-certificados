<?php

namespace App\Libraries;

use Config\Credenciales as CredencialesConfig;
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use RuntimeException;

/**
 * Equivalente PHP de las funciones de Google Sheets del script Python:
 * init_google_services(), obtener_hojas(), sheet_to_df(), obtener_id_hoja(),
 * actualizar_estado_confirmacion().
 *
 * Requiere: composer require google/apiclient:^2.15
 */
class GoogleSheetsService
{
    protected CredencialesConfig $config;
    protected GoogleSheets $service;
    protected string $spreadsheetId;
    protected string $hojaLimite;

    /**
     * @param string|null $spreadsheetId Si se pasa, sobrescribe el del config (uso: evento activo desde BD).
     * @param string|null $hojaLimite    Idem.
     */
    public function __construct(?string $spreadsheetId = null, ?string $hojaLimite = null)
    {
        $this->config = config('Credenciales');
        $this->spreadsheetId = $spreadsheetId ?? $this->config->spreadsheetId;
        $this->hojaLimite = $hojaLimite ?? $this->config->hojaLimite;

        if (!is_file($this->config->keyFile)) {
            throw new RuntimeException(
                'No se encontró credentials.json en: ' . $this->config->keyFile
            );
        }

        $client = new GoogleClient();
        $client->setAuthConfig($this->config->keyFile);
        $client->setScopes([$this->config->scopes]);

        $this->service = new GoogleSheets($client);
    }

    /**
     * Lista las hojas/categorías disponibles, igual que obtener_hojas().
     * Ignora hojas basura y se detiene al llegar a la hoja límite ("VIRTUAL").
     */
  public function obtenerHojas(): array
{
    $metadata = $this->service->spreadsheets->get($this->spreadsheetId);
    $hojas = [];

    foreach ($metadata->getSheets() as $hoja) {
        $nombre = trim($hoja->getProperties()->getTitle());

        if (in_array($nombre, $this->config->hojasIgnoradas, true)) {
            continue;
        }

        $hojas[] = $nombre;
    }

    return $hojas;
}

    public function obtenerIdHoja(string $nombreHoja): ?int
    {
        $metadata = $this->service->spreadsheets->get($this->spreadsheetId);

        foreach ($metadata->getSheets() as $hoja) {
            if ($hoja->getProperties()->getTitle() === $nombreHoja) {
                return $hoja->getProperties()->getSheetId();
            }
        }

        return null;
    }

    /**
     * Lee A:L de la hoja indicada y devuelve filas asociativas (encabezado => valor),
     * ocultando la columna A (MATERIALES), igual que sheet_to_df().
     * Cada fila incluye '_rowIndex' = índice de fila real en la hoja (1-based, sin contar encabezado),
     * que es lo que se usa luego para actualizar CONFIRMACION.
     */
    public function obtenerFilas(string $hojaSeleccionada): array
    {
        $range = "{$hojaSeleccionada}!A:L";
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $values = $response->getValues() ?? [];

        if (empty($values)) {
            return ['headers' => [], 'rows' => []];
        }

        $columnasCompletas = $values[0];
        // Ocultar columna A (MATERIALES) -> nos quedamos desde la columna B
        $headers = array_slice($columnasCompletas, 1);

        $filas = [];
        for ($i = 1; $i < count($values); $i++) {
            $fila = $values[$i];
            // Igualar longitud de la fila a la cabecera completa
            $filaCompleta = array_pad($fila, count($columnasCompletas), '');
            $filaVisible = array_slice($filaCompleta, 1);

            $asoc = array_combine($headers, $filaVisible);

            // Normalizar CONFIRMACION (primera columna visible) a booleano
            if (isset($headers[0]) && strtoupper($headers[0]) === 'CONFIRMACION') {
                $valorConf = strtolower(trim((string) ($asoc[$headers[0]] ?? '')));
                $asoc[$headers[0]] = in_array($valorConf, ['true', 'yes', 'si', '1', 'enviado', 'confirmado'], true);
            }

            $asoc['_rowIndex'] = $i - 1; // índice 0-based dentro de los datos (sin encabezado)
            $filas[] = $asoc;
        }

        return ['headers' => $headers, 'rows' => $filas];
    }

    /**
     * Actualiza la columna B (CONFIRMACION) a $estado para los _rowIndex indicados.
     * Equivalente a actualizar_estado_confirmacion().
     *
     * @param int[] $filasIndicesDf Índices 0-based devueltos en '_rowIndex'
     */
    public function actualizarConfirmacion(string $hoja, array $filasIndicesDf, bool $estado = true): bool
    {
        $idHoja = $this->obtenerIdHoja($hoja);
        if ($idHoja === null) {
            return false;
        }

        // _rowIndex 0 => fila 2 real en Sheets (1 = encabezado)
        $filasSheets = array_map(fn ($i) => $i + 2, $filasIndicesDf);

        $requests = [];
        foreach ($filasSheets as $r) {
            $requests[] = new \Google\Service\Sheets\Request([
                'updateCells' => [
                    'rows' => [
                        ['values' => [['userEnteredValue' => ['boolValue' => $estado]]]],
                    ],
                    'fields' => 'userEnteredValue',
                    'range' => [
                        'sheetId' => $idHoja,
                        'startRowIndex' => $r - 1,
                        'endRowIndex' => $r,
                        'startColumnIndex' => 1, // Columna B
                        'endColumnIndex' => 2,
                    ],
                ],
            ]);
        }

        if (empty($requests)) {
            return false;
        }

        $body = new BatchUpdateSpreadsheetRequest(['requests' => $requests]);
        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $body);

        return true;
    }
}