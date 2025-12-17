<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailMarketingLeadModel extends Model
{
    protected $table = 'email_marketing_leads';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'company_name',
        'lead_source',
        'lead_status',
        'country',
        'city',
        'notes',
        'tags',
        'email_sent_count',
        'last_email_sent_at',
        'last_opened_at',
        'last_clicked_at',
        'is_subscribed',
        'unsubscribed_at',
        'added_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    /**
     * Get lead with campaign statistics
     */
    public function getLeadWithStats($id)
    {
        $lead = $this->find($id);

        if (!$lead) {
            return null;
        }

        // Get campaign statistics
        $db = \Config\Database::connect();

        $stats = [
            'total_campaigns_received' => $db->table('email_campaign_leads')
                ->where('lead_id', $id)
                ->countAllResults(),
            'total_opened' => $db->table('email_campaign_leads')
                ->where('lead_id', $id)
                ->where('opened_at IS NOT NULL')
                ->countAllResults(),
            'total_clicked' => $db->table('email_campaign_leads')
                ->where('lead_id', $id)
                ->where('clicked_at IS NOT NULL')
                ->countAllResults()
        ];

        $lead['stats'] = $stats;

        return $lead;
    }

    /**
     * Update email sent count
     */
    public function incrementEmailCount($id)
    {
        $lead = $this->find($id);

        if (!$lead) {
            return false;
        }

        return $this->update($id, [
            'email_sent_count' => $lead['email_sent_count'] + 1,
            'last_email_sent_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark email as opened
     */
    public function markAsOpened($id)
    {
        return $this->update($id, [
            'last_opened_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark email as clicked
     */
    public function markAsClicked($id)
    {
        return $this->update($id, [
            'last_clicked_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get subscribed leads only
     */
    public function getSubscribedLeads()
    {
        return $this->where('is_subscribed', 1)->findAll();
    }

    /**
     * Get leads by status
     */
    public function getLeadsByStatus($status)
    {
        return $this->where('lead_status', $status)->findAll();
    }
}
