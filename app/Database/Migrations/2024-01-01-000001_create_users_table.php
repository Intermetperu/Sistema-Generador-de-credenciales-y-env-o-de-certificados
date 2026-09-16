<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'auto_increment' => true],
            'name'                => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'               => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
            'password'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'verification_token'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'email_verified_at'   => ['type' => 'DATETIME', 'null' => true],
            'token_expire' => ['type' => 'DATETIME', 'null' => true],
            'is_active'           => ['type' => 'BOOLEAN', 'default' => false],
            'avatar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'role_id'          => ['type' => 'INT', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
