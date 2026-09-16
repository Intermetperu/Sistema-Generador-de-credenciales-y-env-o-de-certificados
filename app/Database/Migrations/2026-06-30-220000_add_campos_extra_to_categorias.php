<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCamposExtraToCategorias extends Migration
{
    public function up()
    {
        $this->forge->addColumn('categorias', [
            'campos_extra' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'mapeo_columnas',
                'comment' => 'JSON: [{"columna":"CARGO","x":100,"y":200,"tamFuente":40,"color":"#FFFFFF"}, ...]',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('categorias', 'campos_extra');
    }
}
