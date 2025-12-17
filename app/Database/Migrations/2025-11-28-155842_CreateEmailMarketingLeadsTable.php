<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailMarketingLeadsTable extends Migration
{
    public function up()
    {
        // Create email_marketing_leads table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'lead_source' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Source of the lead (e.g., website, referral, import)',
            ],
            'lead_status' => [
                'type'       => 'ENUM',
                'constraint' => ['new', 'contacted', 'qualified', 'converted', 'unsubscribed'],
                'default'    => 'new',
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tags' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Comma-separated tags for categorizing leads',
            ],
            'email_sent_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Number of marketing emails sent to this lead',
            ],
            'last_email_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_opened_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Last time lead opened a marketing email',
            ],
            'last_clicked_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Last time lead clicked a link in marketing email',
            ],
            'is_subscribed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = subscribed, 0 = unsubscribed',
            ],
            'unsubscribed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'added_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Admin user ID who added this lead',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('email');
        $this->forge->addKey('lead_status');
        $this->forge->addKey('is_subscribed');
        $this->forge->addKey('created_at');

        $this->forge->createTable('email_marketing_leads');

        // Create email_campaigns table for tracking sent campaigns
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campaign_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'email_content' => [
                'type' => 'LONGTEXT',
                'null' => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'scheduled', 'sending', 'sent', 'failed'],
                'default'    => 'draft',
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'total_recipients' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'total_sent' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'total_opened' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'total_clicked' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'total_failed' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        $this->forge->createTable('email_campaigns');

        // Create email_campaign_leads junction table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campaign_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'lead_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'email_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed', 'bounced'],
                'default'    => 'pending',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'opened_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'clicked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'failure_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('lead_id');
        $this->forge->addKey('email_status');

        $this->forge->addForeignKey('campaign_id', 'email_campaigns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('lead_id', 'email_marketing_leads', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('email_campaign_leads');
    }

    public function down()
    {
        $this->forge->dropTable('email_campaign_leads', true);
        $this->forge->dropTable('email_campaigns', true);
        $this->forge->dropTable('email_marketing_leads', true);
    }
}
