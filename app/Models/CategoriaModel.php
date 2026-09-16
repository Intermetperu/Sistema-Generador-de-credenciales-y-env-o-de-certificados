<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'evento_id', 'nombre_hoja',
        'plantilla_credencial', 'plantilla_ancho', 'plantilla_alto',
        'pos_nombre_x', 'pos_nombre_y', 'pos_apellido_x', 'pos_apellido_y',
        'pos_qr_x', 'pos_qr_y', 'qr_ancho', 'qr_alto',
        'tam_fuente_nombre', 'tam_fuente_apellido', 'color_texto',
        'plantilla_correo_html', 'asunto_correo', 'mapeo_columnas', 'campos_extra',
        'imagen_cabecera_correo',

        // ===== Certificados =====
        'plantilla_certificado', 'plantilla_cert_ancho', 'plantilla_cert_alto',
        'pos_cert_nombre_x', 'pos_cert_nombre_y', 'tam_fuente_cert_nombre', 'color_texto_cert',
        'campos_extra_cert', 'plantilla_correo_certificado', 'asunto_correo_certificado',
        'link_encuesta', 'area_texto_inicio_pct',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'evento_id' => 'required|is_natural_no_zero',
        'nombre_hoja' => 'required|min_length[2]|max_length[100]',
    ];

    public function porEvento(int $eventoId): array
    {
        return $this->where('evento_id', $eventoId)->orderBy('nombre_hoja', 'ASC')->findAll();
    }

    /**
     * Busca la categoría de un evento por el nombre exacto de la hoja
     * (sin importar mayúsculas/minúsculas), igual que hacía el script
     * Python con "{hoja}.jpg".
     */
    public function porHoja(int $eventoId, string $nombreHoja): ?array
    {
        return $this->where('evento_id', $eventoId)
            ->where('UPPER(nombre_hoja)', strtoupper($nombreHoja))
            ->first();
    }

    public function mapeoColumnas(array $categoria): array
    {
        $default = [
            'nombre' => 'NOMBRES',
            'apellido' => 'APELLIDOS',
            'documento' => 'DOCUMENTO DE IDENTIDAD',
            'correo_corporativo' => 'CORREO CORPORATIVO',
            'correo_personal' => 'CORREO PERSONAL',
        ];
        if (empty($categoria['mapeo_columnas'])) {
            return $default;
        }
        $decoded = json_decode($categoria['mapeo_columnas'], true);
        return is_array($decoded) ? array_merge($default, $decoded) : $default;
    }

    /**
     * Decodifica los campos de texto extra (CARGO, EMPRESA, etc.) configurados
     * desde el selector visual, para CREDENCIALES. Devuelve [] si no hay ninguno.
     *
     * @return array<int, array{columna:string,x:int,y:int,tamFuente:int,color:string}>
     */
    public function camposExtra(array $categoria): array
    {
        if (empty($categoria['campos_extra'])) {
            return [];
        }
        $decoded = json_decode($categoria['campos_extra'], true);
        return is_array($decoded) ? $decoded : [];
    }

    // ===================== Certificados =====================

    /**
     * Guarda el nombre del archivo de plantilla de certificado subido
     * para esta categoría.
     */
    public function actualizarPlantillaCertificado(int $categoriaId, string $nombreArchivo): bool
    {
        return $this->update($categoriaId, [
            'plantilla_certificado' => $nombreArchivo,
        ]);
    }

    /**
     * Igual que camposExtra() pero para certificados (columna 'campos_extra_cert').
     * Mismo formato: [{columna, x, y, tamFuente, color}], ej. para el ROL
     * ("VOLUNTARIA", "PONENTE") si viene de una columna del Sheet.
     *
     * @return array<int, array{columna:string,x:int,y:int,tamFuente:int,color:string}>
     */
    public function camposExtraCertificado(array $categoria): array
    {
        if (empty($categoria['campos_extra_cert'])) {
            return [];
        }
        $decoded = json_decode($categoria['campos_extra_cert'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function actualizarLinkEncuesta(int $categoriaId, string $url): bool
    {
        return $this->update($categoriaId, [
            'link_encuesta' => $url,
        ]);
    }

    /**
     * Guarda el porcentaje (0 a 1) del ancho de la plantilla donde empieza
     * el área blanca de texto del certificado. Se usa en CertificadoBuilder
     * para no centrar el nombre incluyendo la franja/logo lateral.
     */
    public function actualizarAreaTextoInicioPct(int $categoriaId, ?float $pct): bool
    {
        return $this->update($categoriaId, [
            'area_texto_inicio_pct' => $pct,
        ]);
    }
}