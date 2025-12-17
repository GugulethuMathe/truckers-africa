<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePasswordResetTokensTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (!$this->db->tableExists('password_reset_tokens')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'user_type' => [
                    'type' => 'ENUM',
                    'constraint' => ['admin', 'driver', 'merchant'],
                ],
                'token' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'expires_at' => [
                    'type' => 'DATETIME',
                ],
                'used_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('email');
            $this->forge->addKey('token');
            $this->forge->addKey('expires_at');
            $this->forge->createTable('password_reset_tokens');
        }
    }

    public function down()
    {
        $this->forge->dropTable('password_reset_tokens');
    }
}
