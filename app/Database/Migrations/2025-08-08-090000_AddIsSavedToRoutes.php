<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsSavedToRoutes extends Migration
{
    public function up()
    {
        // Check if column already exists
        if (!$this->db->fieldExists('is_saved', 'routes')) {
            $fields = [
                'is_saved' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => false,
                    'default' => 0,
                    'after' => 'route_polyline',
                ],
            ];

            $this->forge->addColumn('routes', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('routes', 'is_saved');
    }
}


