<?php

namespace App\Libraries;

/**
 * Equivalente PHP de normalizar_texto() y limpiar_nombre_archivo() del script Python.
 */
class TextoUtil
{
    /**
     * "ÑOKÚ PEÑAROL" -> "noku penarol"
     */
    public static function normalizar(?string $texto): string
    {
        if ($texto === null || trim($texto) === '') {
            return '';
        }

        $texto = mb_strtolower(trim($texto));

        // Quitar tildes/diacríticos (NFD + strip de marcas combinantes)
        $texto = \Normalizer::normalize($texto, \Normalizer::FORM_D);
        $texto = preg_replace('/\p{Mn}/u', '', $texto);

        $reemplazos = ['ñ' => 'n', 'ç' => 'c', 'ß' => 's'];
        $texto = strtr($texto, $reemplazos);

        $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);

        return trim($texto);
    }

    /**
     * Convierte un texto en un identificador seguro para nombres de archivo y URLs:
     * sin tildes, sin espacios (los reemplaza por _), y solo caracteres
     * alfanuméricos / guion / guion bajo. Evita que firewalls (ModSecurity)
     * de hostings compartidos bloqueen la URL por espacios o tildes codificadas.
     */
    public static function comoIdentificador(?string $texto): string
    {
        $texto = self::quitarAcentos($texto);
        $texto = preg_replace('/\s+/', '_', trim($texto));
        $texto = preg_replace('/[^A-Za-z0-9_\-]/', '', $texto);

        return $texto;
    }

    /**
     * Quita tildes/diacríticos SIN cambiar mayúsculas/minúsculas (a diferencia
     * de normalizar()). Se usa para nombres de archivo y URLs, para evitar que
     * el ModSecurity de hostings compartidos bloquee rutas con caracteres
     * acentuados codificados (ej. "JOSé" -> "JOSE").
     */
    public static function quitarAcentos(?string $texto): string
    {
        if ($texto === null || trim($texto) === '') {
            return '';
        }

        $texto = \Normalizer::normalize(trim($texto), \Normalizer::FORM_D);
        $texto = preg_replace('/\p{Mn}/u', '', $texto);

        return $texto;
    }

    /**
     * Limpia un texto para usarlo como nombre de archivo.
     */
    public static function limpiarNombreArchivo(?string $texto): string
    {
        if ($texto === null || trim($texto) === '') {
            return '';
        }

        $texto = trim($texto);
        $texto = preg_replace('/[\n\r\t]+/', ' ', $texto);
        $texto = preg_replace('/[<>:"\/\\\\|?*]/', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        $texto = trim($texto);

        if (mb_strlen($texto) > 100) {
            $texto = trim(mb_substr($texto, 0, 100));
        }

        return $texto;
    }

    /**
     * Equivalente a get_col(): busca un valor por varios posibles nombres de columna.
     *
     * @param array<string,mixed> $fila
     * @param string[] $claves
     */
    public static function getCol(array $fila, array $claves): string
    {
        foreach ($claves as $clave) {
            if (isset($fila[$clave]) && trim((string) $fila[$clave]) !== '') {
                return trim((string) $fila[$clave]);
            }

            $claveNorm = mb_strtolower(trim($clave));
            foreach ($fila as $col => $val) {
                if (is_string($col) && str_contains(mb_strtolower(trim($col)), $claveNorm)) {
                    if ($val !== null && trim((string) $val) !== '') {
                        return trim((string) $val);
                    }
                }
            }
        }

        return '';
    }
}
