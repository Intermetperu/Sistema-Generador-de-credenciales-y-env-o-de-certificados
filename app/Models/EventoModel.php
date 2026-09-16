<?php

namespace App\Models;

use CodeIgniter\Model;

class EventoModel extends Model
{
    protected $table = 'eventos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'nombre', 'spreadsheet_id', 'hoja_limite',
        'mailgun_domain', 'mailgun_api_key', 'mailgun_from', 'activo',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'nombre' => 'required|min_length[3]|max_length[150]',
        'spreadsheet_id' => 'required|min_length[10]',
    ];

    public function obtenerActivo(): ?array
    {
        return $this->where('activo', 1)->first();
    }

    /**
     * Activa este evento y desactiva todos los demás (solo uno activo a la vez).
     */
    public function activar(int $id): bool
    {
        $this->db->transStart();
        $this->where('id !=', $id)->set(['activo' => 0])->update();
        $this->update($id, ['activo' => 1]);
        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
