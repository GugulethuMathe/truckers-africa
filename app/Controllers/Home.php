<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\ServiceCategoryModel;
use App\Models\MerchantListingModel;
use App\Models\MerchantListingImageModel;
use App\Models\MerchantModel;
use App\Models\MerchantLocationModel;

class Home extends BaseController
{
    public function index(): string
    {
        // Instantiate the model
        $serviceModel = new ServiceModel();
        $categoryModel = new ServiceCategoryModel();
        $merchantModel = new MerchantModel();
        $listingModel = new MerchantListingModel();
        $locationModel = new MerchantLocationModel();
        $services = $serviceModel->getHomepageServices(7);

        // Fetch approved and visible merchants
        $merchants = $merchantModel->where('verification_status', 'approved')->where('is_visible', 1)->findAll();

        if (!empty($merchants)) {
            $merchantIds = array_column($merchants, 'id');

            // Fetch all approved listings for these merchants in one go
            $allListings = $listingModel
                ->whereIn('merchant_id', $merchantIds)
                ->where('status', 'approved')
                ->findAll();

            // Group listings by merchant ID for efficient lookup
            $listingsByMerchant = [];
            foreach ($allListings as $listing) {
                $listingsByMerchant[$listing['merchant_id']][] = $listing;
            }

            // Attach services to each merchant
            foreach ($merchants as &$merchant) {
                $merchant['services'] = $listingsByMerchant[$merchant['id']] ?? [];
            }
        }

        // Get business locations for "Nearest Merchants" section (max 8)
        $businessLocations = $locationModel->select('merchant_locations.*, merchants.business_name, merchants.business_image_url')
                                           ->join('merchants', 'merchants.id = merchant_locations.merchant_id')
                                           ->join('subscriptions', 'subscriptions.merchant_id = merchants.id', 'left')
                                           ->where('merchant_locations.is_active', 1)
                                           ->where('merchants.is_visible', 1)
                                           ->where('merchants.verification_status', 'approved')
                                           ->groupStart()
                                               ->where('subscriptions.status', 'active')
                                               ->orWhere('subscriptions.status', 'trial')
                                           ->groupEnd()
                                           ->orderBy('merchant_locations.is_primary', 'DESC')
                                           ->orderBy('merchant_locations.created_at', 'DESC')
                                           ->limit(8)
                                           ->findAll();

        // Data to be passed to the main template view.
        $data = [
            'page_title' => 'Truckers Africa - Connecting You to the Best Roadside Services',
            'page_class' => 'bg-gray-900 text-slate-200',
            'services' => $services,
            'categories' => $categoryModel->getAllCategories(),
            'merchants' => $merchants,
            'business_locations' => $businessLocations
        ];

        return view('templates/home-header', $data)
             . view('front-end/home', $data) // Pass the data to the content view as well
             . view('templates/home-footer');
    }

    public function pricing()
    {
        $planModel = new \App\Models\PlanModel();
        $plans = $planModel->findAll();

        foreach ($plans as &$plan) {
            $plan['features'] = $planModel->getFeatures($plan['id']);
        }

        $data = [
            'page_title' => 'Our Pricing - Truckers Africa',
            'page_class' => 'bg-gray-900 text-slate-200',
            'plans'      => $plans,
        ];

        return view('templates/home-header', $data)
             . view('front-end/pricing', $data)
             . view('templates/home-footer');
    }
    public function packages()
    {
        $planModel = new \App\Models\PlanModel();
        $plans = $planModel->findAll();

        foreach ($plans as &$plan) {
            $plan['features'] = $planModel->getFeatures($plan['id']);
        }

        $data = [
            'page_title' => 'Our Pricing - Truckers Africa',
            'page_class' => 'bg-gray-900 text-slate-200',
            'plans'      => $plans,
        ];

        return view('templates/home-header', $data)
             . view('front-end/packages', $data)
             . view('templates/home-footer');
    }
    public function about()
    {
        $data = [
            'page_title' => 'About Us - Truckers Africa',
            'page_class' => 'bg-gray-900 text-slate-200',
        ];
        return view('templates/home-header', $data)
             . view('front-end/about')
             . view('templates/home-footer');
    }

    public function terms()
    {
        $data = [
            'page_title' => 'Terms and Conditions - Truckers Africa',
            'page_class' => 'bg-gray-900 text-slate-200',
        ];
        return view('templates/home-header', $data)
             . view('front-end/terms')
             . view('templates/home-footer');
    }

    public function listingDetail($id)
    {
        helper('currency');

        $listingModel = new MerchantListingModel();
        $imageModel = new MerchantListingImageModel();

        // Fetch the main listing details along with the merchant's business name
        $listing = $listingModel->select('merchant_listings.*, merchants.business_name, merchants.physical_address, merchants.business_image_url, merchants.business_description, merchants.latitude AS location_lat, merchants.longitude AS location_lng, merchants.business_contact_number, merchants.business_whatsapp_number, merchants.email')
                                 ->join('merchants', 'merchants.id = merchant_listings.merchant_id')
                                 ->where('merchant_listings.id', $id)
                                 ->where('merchant_listings.status', 'approved')
                                 ->first();

        if (!$listing) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'page_title' => esc($listing['title']) . ' - Truckers Africa',
            'page_class' => 'bg-gray-900 text-slate-200',
            'listing' => $listing,
            'gallery_images' => $imageModel->findByListingId($id),
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY')
        ];

        return view('templates/home-header', $data)
             . view('front-end/listing_detail', $data)
             . view('templates/home-footer');
    }

    public function merchantProfile($merchantId)
    {
        helper('currency');

        $merchantModel = new \App\Models\MerchantModel();
        $listingModel = new MerchantListingModel();

        // Fetch the merchant details with aliased coordinates for the view
        $merchant = $merchantModel
            ->select('merchants.*, merchants.latitude AS location_lat, merchants.longitude AS location_lng')
            ->find($merchantId);

        if (!$merchant) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Merchant not found.');
        }

        // Fetch all approved listings for this merchant
        $listings = $listingModel->where('merchant_id', $merchantId)
                                 ->where('status', 'approved')
                                 ->findAll();

        $data = [
            'page_title' => esc($merchant['business_name']) . ' - Profile',
            'page_class' => 'bg-gray-900 text-slate-200',
            'merchant' => $merchant,
            'listings' => $listings,
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY')
        ];

        return view('templates/home-header', $data)
             . view('front-end/merchant_profile', $data)
             . view('templates/home-footer');
    }

    /**
     * Display the Contact Us page
     */
    public function contact()
    {
        $data = [
            'page_title' => 'Contact Us - Truckers Africa',
            'page_class' => 'bg-gray-900 text-slate-200',
        ];
        return view('templates/home-header', $data)
             . view('front-end/contact', $data)
             . view('templates/home-footer');
    }

    /**
     * Handle contact form submission
     */
    public function handleContactForm()
    {
        $rules = [
            'name' => [
                'rules' => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required' => 'Please enter your name.',
                    'min_length' => 'Name must be at least 2 characters.',
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Please enter your email address.',
                    'valid_email' => 'Please enter a valid email address.',
                ]
            ],
            'phone' => [
                'rules' => 'permit_empty|max_length[20]',
            ],
            'message' => [
                'rules' => 'required|min_length[10]|max_length[2000]',
                'errors' => [
                    'required' => 'Please enter your message.',
                    'min_length' => 'Message must be at least 10 characters.',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get form data
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $phone = $this->request->getPost('phone');
        $message = $this->request->getPost('message');

        // Send email notification
        $emailService = \Config\Services::email();
        $emailService->setTo('admin@truckersafrica.com');
        $emailService->setFrom($email, $name);
        $emailService->setSubject('Contact Form Submission - Truckers Africa');

        $emailBody = "New contact form submission:\n\n";
        $emailBody .= "Name: {$name}\n";
        $emailBody .= "Email: {$email}\n";
        $emailBody .= "Phone: " . ($phone ?: 'Not provided') . "\n\n";
        $emailBody .= "Message:\n{$message}";

        $emailService->setMessage($emailBody);

        if ($emailService->send()) {
            return redirect()->to('contact-us')->with('success', 'Thank you for contacting us! We will get back to you soon.');
        } else {
            log_message('error', 'Contact form email failed: ' . $emailService->printDebugger(['headers']));
            return redirect()->to('contact-us')->with('success', 'Thank you for contacting us! We will get back to you soon.');
        }
    }
}