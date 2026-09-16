<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImagenCabeceraCorreoToCategorias extends Migration
{
    public function up()
    {
        $this->forge->addColumn('categorias', [
            'imagen_cabecera_correo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'campos_extra',
                'comment' => 'Ruta pública relativa, ej: uploads/correo-cabeceras/cat_3.jpg',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('categorias', 'imagen_cabecera_correo');
    }
}
