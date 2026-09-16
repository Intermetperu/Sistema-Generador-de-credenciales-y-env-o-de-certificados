<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoriasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'evento_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nombre_hoja' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Debe coincidir con el nombre de la pestaña en Google Sheets'],

            // Plantilla de la credencial
            'plantilla_credencial' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Nombre de archivo dentro de writable/credenciales/plantillas-credenciales'],
            'plantilla_ancho' => ['type' => 'INT', 'null' => true],
            'plantilla_alto' => ['type' => 'INT', 'null' => true],

            // Posiciones (configurables desde el selector visual)
            'pos_nombre_x' => ['type' => 'INT', 'default' => 0],
            'pos_nombre_y' => ['type' => 'INT', 'default' => 0],
            'pos_apellido_x' => ['type' => 'INT', 'default' => 0],
            'pos_apellido_y' => ['type' => 'INT', 'default' => 0],
            'pos_qr_x' => ['type' => 'INT', 'default' => 0],
            'pos_qr_y' => ['type' => 'INT', 'default' => 0],
            'qr_ancho' => ['type' => 'INT', 'default' => 420],
            'qr_alto' => ['type' => 'INT', 'default' => 420],
            'tam_fuente_nombre' => ['type' => 'INT', 'default' => 68],
            'tam_fuente_apellido' => ['type' => 'INT', 'default' => 68],
            'color_texto' => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#FFFFFF'],

            // Plantilla de correo
            'plantilla_correo_html' => ['type' => 'LONGTEXT', 'null' => true],
            'asunto_correo' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],

            // Mapeo de columnas del Sheet -> campos internos (JSON)
            // Ej: {"nombre":"NOMBRES","apellido":"APELLIDOS","documento":"DOCUMENTO DE IDENTIDAD",
            //      "correo_corporativo":"CORREO CORPORATIVO","correo_personal":"CORREO PERSONAL"}
            'mapeo_columnas' => ['type' => 'TEXT', 'null' => true],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('evento_id');
        $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('categorias');
    }

    public function down()
    {
        $this->forge->dropTable('categorias');
    }
}