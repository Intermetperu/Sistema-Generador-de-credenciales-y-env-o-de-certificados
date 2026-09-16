<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name'        => 'Administrador',
            // Lista simple de permisos en JSON. Agrega aquí los que necesites
            // para los nuevos módulos, p. ej. "MiModulo:ver".
            'permissions' => json_encode(['*']),
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $this->db->table('roles')->insert($data);
    }
}
