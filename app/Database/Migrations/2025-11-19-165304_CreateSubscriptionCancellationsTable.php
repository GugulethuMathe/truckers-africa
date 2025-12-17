<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptionCancellationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'subscription_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'merchant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'plan_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'cancellation_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'cancellation_comments' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cancelled_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('subscription_id');
        $this->forge->addKey('merchant_id');
        $this->forge->addKey('cancelled_at');

        $this->forge->addForeignKey('subscription_id', 'subscriptions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('subscription_cancellations');
    }

    public function down()
    {
        $this->forge->dropTable('subscription_cancellations');
    }
}
