<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailCampaignModel extends Model
{
    protected $table = 'email_campaigns';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'campaign_name',
        'subject',
        'email_content',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'total_sent',
        'total_opened',
        'total_clicked',
        'total_failed',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get campaign with detailed statistics
     */
    public function getCampaignWithStats($id)
    {
        $campaign = $this->find($id);

        if (!$campaign) {
            return null;
        }

        // Get detailed statistics from junction table
        $db = \Config\Database::connect();

        $stats = [
            'pending' => $db->table('email_campaign_leads')
                ->where('campaign_id', $id)
                ->where('email_status', 'pending')
                ->countAllResults(),
            'sent' => $db->table('email_campaign_leads')
                ->where('campaign_id', $id)
                ->where('email_status', 'sent')
                ->countAllResults(),
            'failed' => $db->table('email_campaign_leads')
                ->where('campaign_id', $id)
                ->where('email_status', 'failed')
                ->countAllResults(),
            'bounced' => $db->table('email_campaign_leads')
                ->where('campaign_id', $id)
                ->where('email_status', 'bounced')
                ->countAllResults(),
            'opened' => $db->table('email_campaign_leads')
                ->where('campaign_id', $id)
                ->where('opened_at IS NOT NULL')
                ->countAllResults(),
            'clicked' => $db->table('email_campaign_leads')
                ->where('campaign_id', $id)
                ->where('clicked_at IS NOT NULL')
                ->countAllResults()
        ];

        // Calculate rates
        if ($campaign['total_sent'] > 0) {
            $stats['open_rate'] = round(($campaign['total_opened'] / $campaign['total_sent']) * 100, 2);
            $stats['click_rate'] = round(($campaign['total_clicked'] / $campaign['total_sent']) * 100, 2);
        } else {
            $stats['open_rate'] = 0;
            $stats['click_rate'] = 0;
        }

        $campaign['detailed_stats'] = $stats;

        return $campaign;
    }

    /**
     * Update campaign statistics
     */
    public function updateStats($campaignId)
    {
        $db = \Config\Database::connect();

        // Count from junction table
        $totalSent = $db->table('email_campaign_leads')
            ->where('campaign_id', $campaignId)
            ->where('email_status', 'sent')
            ->countAllResults();

        $totalOpened = $db->table('email_campaign_leads')
            ->where('campaign_id', $campaignId)
            ->where('opened_at IS NOT NULL')
            ->countAllResults();

        $totalClicked = $db->table('email_campaign_leads')
            ->where('campaign_id', $campaignId)
            ->where('clicked_at IS NOT NULL')
            ->countAllResults();

        $totalFailed = $db->table('email_campaign_leads')
            ->where('campaign_id', $campaignId)
            ->where('email_status', 'failed')
            ->countAllResults();

        return $this->update($campaignId, [
            'total_sent' => $totalSent,
            'total_opened' => $totalOpened,
            'total_clicked' => $totalClicked,
            'total_failed' => $totalFailed
        ]);
    }

    /**
     * Get sent campaigns with statistics
     */
    public function getSentCampaigns()
    {
        return $this->where('status', 'sent')
            ->orderBy('sent_at', 'DESC')
            ->findAll();
    }
}
