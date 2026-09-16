<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 150],
            'spreadsheet_id' => ['type' => 'VARCHAR', 'constraint' => 150],
            'hoja_limite' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'VIRTUAL'],
            'mailgun_domain' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'mailgun_api_key' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'mailgun_from' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('eventos');
    }

    public function down()
    {
        $this->forge->dropTable('eventos');
    }
}