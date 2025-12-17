<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSortOrderToPlanFeatures extends Migration
{
    public function up()
    {
        // Check if column already exists
        if (!$this->db->fieldExists('sort_order', 'plan_features')) {
            $fields = [
                'sort_order' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'default' => 0,
                    'after' => 'feature_id'
                ]
            ];

            $this->forge->addColumn('plan_features', $fields);

            // Update existing records to have sequential sort_order values
            $db = \Config\Database::connect();
            $builder = $db->table('plan_features');

            // Get all plan_features grouped by plan_id
            $planFeatures = $builder->select('id, plan_id, feature_id')
                                   ->orderBy('plan_id, id')
                                   ->get()
                                   ->getResultArray();

            $currentPlanId = null;
            $sortOrder = 1;

            foreach ($planFeatures as $planFeature) {
                if ($currentPlanId !== $planFeature['plan_id']) {
                    $currentPlanId = $planFeature['plan_id'];
                    $sortOrder = 1;
                }

                $builder->where('id', $planFeature['id'])
                       ->update(['sort_order' => $sortOrder]);

                $sortOrder++;
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('plan_features', 'sort_order');
    }
}
