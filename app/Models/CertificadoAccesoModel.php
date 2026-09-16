<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificadoAccesoModel extends Model
{
    protected $table = 'certificado_accesos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'token', 'evento_id', 'hoja', 'identificador', 'nombre_completo', 'correo',
        'encuesta_completada', 'respuestas', 'completado_en',
    ];

    public function porToken(string $token): ?array
    {
        return $this->where('token', $token)->first();
    }

    /**
     * Crea (o reutiliza si ya existe) el token de acceso de un participante
     * para un certificado específico. Si ya existía, NO se pierde si ya
     * había completado la encuesta (para no obligarlo a repetirla si se
     * regenera el PDF).
     */
    public function obtenerOCrearToken(int $eventoId, string $hoja, string $identificador, string $nombreCompleto, ?string $correo): array
    {
        $existente = $this->where('evento_id', $eventoId)
            ->where('hoja', $hoja)
            ->where('identificador', $identificador)
            ->first();

        if ($existente) {
            return $existente;
        }

        $token = bin2hex(random_bytes(20));

        $id = $this->insert([
            'token' => $token,
            'evento_id' => $eventoId,
            'hoja' => $hoja,
            'identificador' => $identificador,
            'nombre_completo' => $nombreCompleto,
            'correo' => $correo,
            'encuesta_completada' => 0,
        ]);

        return $this->find($id);
    }

    public function marcarEncuestaCompletada(int $id, array $respuestas): bool
    {
        return $this->update($id, [
            'encuesta_completada' => 1,
            'respuestas' => json_encode($respuestas, JSON_UNESCAPED_UNICODE),
            'completado_en' => date('Y-m-d H:i:s'),
        ]);
    }
}