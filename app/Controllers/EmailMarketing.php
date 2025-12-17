<?php

namespace App\Controllers;

use App\Models\EmailMarketingLeadModel;
use App\Models\EmailCampaignModel;
use CodeIgniter\Controller;

class EmailMarketing extends Controller
{
    protected $leadModel;
    protected $campaignModel;

    public function __construct()
    {
        $this->leadModel = new EmailMarketingLeadModel();
        $this->campaignModel = new EmailCampaignModel();
    }

    /**
     * Check if user is logged in as admin
     */
    private function checkAuth()
    {
        $adminId = session()->get('admin_id');
        if (!$adminId) {
            return redirect()->to('admin/login')->with('error', 'Please login to continue');
        }
        return null;
    }

    /**
     * Display all leads
     */
    public function leads()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        // Get filter parameters
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');
        $subscribed = $this->request->getGet('subscribed');

        // Apply filters
        if ($status) {
            $this->leadModel->where('lead_status', $status);
        }

        if ($subscribed !== null && $subscribed !== '') {
            $this->leadModel->where('is_subscribed', $subscribed);
        }

        if ($search) {
            $this->leadModel->groupStart()
                ->like('first_name', $search)
                ->orLike('last_name', $search)
                ->orLike('email', $search)
                ->orLike('company_name', $search)
                ->groupEnd();
        }

        // Get leads with pagination
        $perPage = 20;
        $leads = $this->leadModel->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager = $this->leadModel->pager;

        // Get statistics
        $stats = [
            'total' => $this->leadModel->countAll(),
            'new' => $this->leadModel->where('lead_status', 'new')->countAllResults(false),
            'contacted' => $this->leadModel->where('lead_status', 'contacted')->countAllResults(false),
            'qualified' => $this->leadModel->where('lead_status', 'qualified')->countAllResults(false),
            'converted' => $this->leadModel->where('lead_status', 'converted')->countAllResults(false),
            'unsubscribed' => $this->leadModel->where('lead_status', 'unsubscribed')->countAllResults(false),
            'subscribed' => $this->leadModel->where('is_subscribed', 1)->countAllResults(false)
        ];

        $data = [
            'page_title' => 'All Leads',
            'leads' => $leads,
            'pager' => $pager,
            'stats' => $stats,
            'current_status' => $status,
            'current_search' => $search,
            'current_subscribed' => $subscribed
        ];

        return view('admin/email_marketing/leads/index', $data);
    }

    /**
     * Show add lead form
     */
    public function addLead()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $data = [
            'page_title' => 'Add New Lead'
        ];

        return view('admin/email_marketing/leads/add', $data);
    }

    /**
     * Store new lead
     */
    public function storeLead()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        // Validation rules
        $rules = [
            'first_name' => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[255]|is_unique[email_marketing_leads.email]',
            'phone_number' => 'permit_empty|max_length[50]',
            'company_name' => 'permit_empty|max_length[255]',
            'lead_source' => 'permit_empty|max_length[100]',
            'country' => 'permit_empty|max_length[100]',
            'city' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Prepare data
        $leadData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone_number' => $this->request->getPost('phone_number'),
            'company_name' => $this->request->getPost('company_name'),
            'lead_source' => $this->request->getPost('lead_source') ?: 'manual',
            'lead_status' => 'new',
            'country' => $this->request->getPost('country'),
            'city' => $this->request->getPost('city'),
            'notes' => $this->request->getPost('notes'),
            'tags' => $this->request->getPost('tags'),
            'is_subscribed' => 1,
            'added_by' => session()->get('admin_id'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->leadModel->insert($leadData)) {
            return redirect()->to('admin/email-marketing/leads')->with('success', 'Lead added successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to add lead');
    }

    /**
     * Show edit lead form
     */
    public function editLead($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->to('admin/email-marketing/leads')->with('error', 'Lead not found');
        }

        $data = [
            'page_title' => 'Edit Lead',
            'lead' => $lead
        ];

        return view('admin/email_marketing/leads/edit', $data);
    }

    /**
     * Update lead
     */
    public function updateLead($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->to('admin/email-marketing/leads')->with('error', 'Lead not found');
        }

        // Validation rules
        $rules = [
            'first_name' => 'required|max_length[100]',
            'email' => "required|valid_email|max_length[255]|is_unique[email_marketing_leads.email,id,{$id}]",
            'phone_number' => 'permit_empty|max_length[50]',
            'company_name' => 'permit_empty|max_length[255]',
            'lead_source' => 'permit_empty|max_length[100]',
            'lead_status' => 'required|in_list[new,contacted,qualified,converted,unsubscribed]',
            'country' => 'permit_empty|max_length[100]',
            'city' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Prepare data
        $leadData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone_number' => $this->request->getPost('phone_number'),
            'company_name' => $this->request->getPost('company_name'),
            'lead_source' => $this->request->getPost('lead_source'),
            'lead_status' => $this->request->getPost('lead_status'),
            'country' => $this->request->getPost('country'),
            'city' => $this->request->getPost('city'),
            'notes' => $this->request->getPost('notes'),
            'tags' => $this->request->getPost('tags'),
            'is_subscribed' => $this->request->getPost('is_subscribed') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // If unsubscribing, set timestamp
        if ($leadData['is_subscribed'] == 0 && $lead['is_subscribed'] == 1) {
            $leadData['unsubscribed_at'] = date('Y-m-d H:i:s');
            $leadData['lead_status'] = 'unsubscribed';
        }

        if ($this->leadModel->update($id, $leadData)) {
            return redirect()->to('admin/email-marketing/leads')->with('success', 'Lead updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update lead');
    }

    /**
     * View single lead details
     */
    public function viewLead($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->to('admin/email-marketing/leads')->with('error', 'Lead not found');
        }

        // Get campaign history for this lead
        $db = \Config\Database::connect();
        $campaignHistory = $db->table('email_campaign_leads ecl')
            ->select('ec.campaign_name, ec.subject, ecl.email_status, ecl.sent_at, ecl.opened_at, ecl.clicked_at')
            ->join('email_campaigns ec', 'ec.id = ecl.campaign_id')
            ->where('ecl.lead_id', $id)
            ->orderBy('ecl.sent_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'page_title' => 'Lead Details',
            'lead' => $lead,
            'campaign_history' => $campaignHistory
        ];

        return view('admin/email_marketing/leads/view', $data);
    }

    /**
     * Delete lead
     */
    public function deleteLead($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->to('admin/email-marketing/leads')->with('error', 'Lead not found');
        }

        if ($this->leadModel->delete($id)) {
            return redirect()->to('admin/email-marketing/leads')->with('success', 'Lead deleted successfully');
        }

        return redirect()->to('admin/email-marketing/leads')->with('error', 'Failed to delete lead');
    }

    /**
     * Display all campaigns
     */
    public function campaigns()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $status = $this->request->getGet('status');

        if ($status) {
            $this->campaignModel->where('status', $status);
        }

        $campaigns = $this->campaignModel->orderBy('created_at', 'DESC')->findAll();

        // Get statistics
        $stats = [
            'total' => $this->campaignModel->countAll(),
            'draft' => $this->campaignModel->where('status', 'draft')->countAllResults(false),
            'scheduled' => $this->campaignModel->where('status', 'scheduled')->countAllResults(false),
            'sent' => $this->campaignModel->where('status', 'sent')->countAllResults(false),
            'failed' => $this->campaignModel->where('status', 'failed')->countAllResults(false)
        ];

        $data = [
            'page_title' => 'Email Campaigns',
            'campaigns' => $campaigns,
            'stats' => $stats,
            'current_status' => $status
        ];

        return view('admin/email_marketing/campaigns/index', $data);
    }

    /**
     * Display sent campaigns
     */
    public function sentCampaigns()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $campaigns = $this->campaignModel->where('status', 'sent')->orderBy('sent_at', 'DESC')->findAll();

        $data = [
            'page_title' => 'Sent Campaigns',
            'campaigns' => $campaigns
        ];

        return view('admin/email_marketing/campaigns/sent', $data);
    }

    /**
     * Show create campaign form
     */
    public function createCampaign()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        // Get all subscribed leads for recipient selection
        $leads = $this->leadModel->where('is_subscribed', 1)->findAll();

        $data = [
            'page_title' => 'Create Campaign',
            'leads' => $leads
        ];

        return view('admin/email_marketing/campaigns/create', $data);
    }

    /**
     * Store new campaign
     */
    public function storeCampaign()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        // Validation rules
        $rules = [
            'campaign_name' => 'required|min_length[3]|max_length[255]',
            'subject' => 'required|min_length[3]|max_length[255]',
            'email_content' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $campaignData = [
            'campaign_name' => $this->request->getPost('campaign_name'),
            'subject' => $this->request->getPost('subject'),
            'email_content' => $this->request->getPost('email_content'),
            'status' => 'draft',
            'created_by' => session()->get('admin_id')
        ];

        if ($this->campaignModel->insert($campaignData)) {
            $campaignId = $this->campaignModel->getInsertID();

            // Add selected leads to campaign
            $selectedLeads = $this->request->getPost('leads') ?? [];
            if (!empty($selectedLeads)) {
                $db = \Config\Database::connect();
                foreach ($selectedLeads as $leadId) {
                    $db->table('email_campaign_leads')->insert([
                        'campaign_id' => $campaignId,
                        'lead_id' => $leadId,
                        'email_status' => 'pending'
                    ]);
                }

                // Update total recipients count
                $this->campaignModel->update($campaignId, [
                    'total_recipients' => count($selectedLeads)
                ]);
            }

            return redirect()->to('admin/email-marketing/campaigns')->with('success', 'Campaign created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create campaign');
    }

    /**
     * Show edit campaign form
     */
    public function editCampaign($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'Campaign not found');
        }

        if ($campaign['status'] !== 'draft') {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'Only draft campaigns can be edited');
        }

        // Get all subscribed leads
        $leads = $this->leadModel->where('is_subscribed', 1)->findAll();

        // Get selected leads for this campaign
        $db = \Config\Database::connect();
        $selectedLeadIds = $db->table('email_campaign_leads')
            ->select('lead_id')
            ->where('campaign_id', $id)
            ->get()
            ->getResultArray();
        $selectedLeadIds = array_column($selectedLeadIds, 'lead_id');

        $data = [
            'page_title' => 'Edit Campaign',
            'campaign' => $campaign,
            'leads' => $leads,
            'selected_leads' => $selectedLeadIds
        ];

        return view('admin/email_marketing/campaigns/edit', $data);
    }

    /**
     * Update campaign
     */
    public function updateCampaign($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'Campaign not found');
        }

        if ($campaign['status'] !== 'draft') {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'Only draft campaigns can be edited');
        }

        // Validation rules
        $rules = [
            'campaign_name' => 'required|min_length[3]|max_length[255]',
            'subject' => 'required|min_length[3]|max_length[255]',
            'email_content' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $campaignData = [
            'campaign_name' => $this->request->getPost('campaign_name'),
            'subject' => $this->request->getPost('subject'),
            'email_content' => $this->request->getPost('email_content')
        ];

        $this->campaignModel->update($id, $campaignData);

        // Update selected leads
        $db = \Config\Database::connect();
        $db->table('email_campaign_leads')->where('campaign_id', $id)->delete();

        $selectedLeads = $this->request->getPost('leads') ?? [];
        if (!empty($selectedLeads)) {
            foreach ($selectedLeads as $leadId) {
                $db->table('email_campaign_leads')->insert([
                    'campaign_id' => $id,
                    'lead_id' => $leadId,
                    'email_status' => 'pending'
                ]);
            }
        }

        // Update total recipients count
        $this->campaignModel->update($id, [
            'total_recipients' => count($selectedLeads)
        ]);

        return redirect()->to('admin/email-marketing/campaigns')->with('success', 'Campaign updated successfully');
    }

    /**
     * View campaign details
     */
    public function viewCampaign($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'Campaign not found');
        }

        // Get recipients for this campaign
        $db = \Config\Database::connect();
        $recipients = $db->table('email_campaign_leads ecl')
            ->select('eml.first_name, eml.last_name, eml.email, ecl.email_status, ecl.sent_at, ecl.opened_at, ecl.clicked_at')
            ->join('email_marketing_leads eml', 'eml.id = ecl.lead_id')
            ->where('ecl.campaign_id', $id)
            ->get()
            ->getResultArray();

        $data = [
            'page_title' => 'Campaign Details',
            'campaign' => $campaign,
            'recipients' => $recipients
        ];

        return view('admin/email_marketing/campaigns/view', $data);
    }

    /**
     * Delete campaign
     */
    public function deleteCampaign($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'Campaign not found');
        }

        // Delete associated campaign leads first
        $db = \Config\Database::connect();
        $db->table('email_campaign_leads')->where('campaign_id', $id)->delete();

        // Delete the campaign
        $this->campaignModel->delete($id);

        return redirect()->to('admin/email-marketing/campaigns')->with('success', 'Campaign deleted successfully');
    }

    /**
     * Send campaign - redirects to batch sending page
     */
    public function sendCampaign($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'Campaign not found');
        }

        if ($campaign['status'] !== 'draft' && $campaign['status'] !== 'sending') {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'Only draft campaigns can be sent');
        }

        // Get pending recipients count
        $db = \Config\Database::connect();
        $pendingCount = $db->table('email_campaign_leads')
            ->where('campaign_id', $id)
            ->where('email_status', 'pending')
            ->countAllResults();

        if ($pendingCount === 0) {
            return redirect()->to('admin/email-marketing/campaigns')->with('error', 'No pending recipients found for this campaign');
        }

        // Mark campaign as sending
        $this->campaignModel->update($id, ['status' => 'sending']);

        $data = [
            'page_title' => 'Sending Campaign',
            'campaign' => $campaign,
            'pending_count' => $pendingCount
        ];

        return view('admin/templates/header', $data)
             . view('admin/email_marketing/send_progress', $data)
             . view('admin/templates/footer');
    }

    /**
     * AJAX endpoint to send emails in batches
     */
    public function sendBatch($id)
    {
        // Check admin session
        if (!session()->get('admin_id')) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized']);
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return $this->response->setJSON(['success' => false, 'error' => 'Campaign not found']);
        }

        $batchSize = 10; // Send 10 emails per batch

        $db = \Config\Database::connect();
        $recipients = $db->table('email_campaign_leads ecl')
            ->select('ecl.id as campaign_lead_id, ecl.lead_id, eml.email, eml.first_name, eml.last_name, eml.company_name, eml.country, eml.city')
            ->join('email_marketing_leads eml', 'eml.id = ecl.lead_id')
            ->where('ecl.campaign_id', $id)
            ->where('ecl.email_status', 'pending')
            ->limit($batchSize)
            ->get()
            ->getResultArray();

        if (empty($recipients)) {
            // All done - update campaign status
            $sentCount = $db->table('email_campaign_leads')
                ->where('campaign_id', $id)
                ->where('email_status', 'sent')
                ->countAllResults();

            $failedCount = $db->table('email_campaign_leads')
                ->where('campaign_id', $id)
                ->where('email_status', 'failed')
                ->countAllResults();

            $this->campaignModel->update($id, [
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
                'total_sent' => $sentCount,
                'total_failed' => $failedCount
            ]);

            return $this->response->setJSON([
                'success' => true,
                'completed' => true,
                'sent' => $sentCount,
                'failed' => $failedCount,
                'message' => "Campaign completed! $sentCount sent, $failedCount failed."
            ]);
        }

        $email = \Config\Services::email();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            $email->clear();
            $email->setTo($recipient['email']);
            $email->setSubject($campaign['subject']);

            // Build links
            $unsubscribeLink = '<a href="' . site_url('unsubscribe/' . base64_encode($recipient['email'])) . '">Unsubscribe</a>';
            $registerLink = '<a href="' . site_url('register') . '" style="color: #000f25; font-weight: bold;">Register Now</a>';

            // Personalize content with all placeholders
            $content = $campaign['email_content'];
            $content = str_replace('{first_name}', $recipient['first_name'] ?? '', $content);
            $content = str_replace('{last_name}', $recipient['last_name'] ?? '', $content);
            $content = str_replace('{full_name}', trim(($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? '')), $content);
            $content = str_replace('{email}', $recipient['email'] ?? '', $content);
            $content = str_replace('{company_name}', $recipient['company_name'] ?? '', $content);
            $content = str_replace('{country}', $recipient['country'] ?? '', $content);
            $content = str_replace('{city}', $recipient['city'] ?? '', $content);
            $content = str_replace('{register_link}', $registerLink, $content);
            $content = str_replace('{unsubscribe_link}', $unsubscribeLink, $content);

            $email->setMessage($content);

            if ($email->send()) {
                $db->table('email_campaign_leads')
                    ->where('id', $recipient['campaign_lead_id'])
                    ->update([
                        'email_status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s')
                    ]);
                $sentCount++;
            } else {
                $db->table('email_campaign_leads')
                    ->where('id', $recipient['campaign_lead_id'])
                    ->update([
                        'email_status' => 'failed',
                        'failure_reason' => $email->printDebugger(['headers'])
                    ]);
                $failedCount++;
            }
        }

        // Get remaining count
        $remainingCount = $db->table('email_campaign_leads')
            ->where('campaign_id', $id)
            ->where('email_status', 'pending')
            ->countAllResults();

        // Get total stats
        $totalSent = $db->table('email_campaign_leads')
            ->where('campaign_id', $id)
            ->where('email_status', 'sent')
            ->countAllResults();

        $totalFailed = $db->table('email_campaign_leads')
            ->where('campaign_id', $id)
            ->where('email_status', 'failed')
            ->countAllResults();

        return $this->response->setJSON([
            'success' => true,
            'completed' => false,
            'batch_sent' => $sentCount,
            'batch_failed' => $failedCount,
            'remaining' => $remainingCount,
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed
        ]);
    }
}
