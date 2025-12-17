<?php

namespace App\Controllers;

use App\Models\MerchantListingModel;
use App\Models\MerchantListingImageModel;
use App\Models\ServiceCategoryModel;
use App\Models\ServiceModel;
use App\Models\AdminModel;
use App\Models\MerchantModel;
use App\Models\CurrencyModel;
use App\Services\CurrencyService;
use CodeIgniter\Controller;

class MerchantListingsController extends Controller
{
    /**
     * Displays a list of all service listings for the current merchant.
     */
    public function index()
    {
        $listingModel = new MerchantListingModel();
        $merchantModel = new MerchantModel();
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        // Get merchant data to check approval status
        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            return redirect()->to('/login')->with('error', 'Merchant account not found');
        }

        // Pagination
        $perPage = 12;
        $page = $this->request->getVar('page') ?? 1;

        // Get listings with location, category, and currency information (like driver view)
        $db = \Config\Database::connect();
        $builder = $db->table('merchant_listings ml');
        $builder->select('ml.*,
                          loc.location_name, loc.physical_address as location_address, loc.is_primary,
                          m.business_name, m.physical_address,
                          sc.currency_symbol');
        $builder->join('merchant_locations loc', 'loc.id = ml.location_id', 'left');
        $builder->join('merchants m', 'm.id = ml.merchant_id', 'left');
        $builder->join('supported_currencies sc', 'sc.currency_code = ml.currency_code COLLATE utf8mb4_unicode_ci', 'left', false);
        $builder->where('ml.merchant_id', $merchantId);
        $builder->orderBy('ml.created_at', 'DESC');

        // Manual pagination
        $totalListings = $builder->countAllResults(false);
        $listings = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        // Get category names for each listing
        $listingCategoryModel = new \App\Models\MerchantListingCategoryModel();
        $categoryModel = new ServiceCategoryModel();

        foreach ($listings as &$listing) {
            // Get the first category for display
            $listingCategories = $listingCategoryModel->where('listing_id', $listing['id'])->findAll();
            if (!empty($listingCategories)) {
                $category = $categoryModel->find($listingCategories[0]['category_id']);
                $listing['category_name'] = $category ? $category['name'] : '';
            } else {
                $listing['category_name'] = '';
            }
        }

        // Create pager manually
        $pager = \Config\Services::pager();
        $pager->setPath('merchant/listings');
        $pager->makeLinks($page, $perPage, $totalListings);

        $data = [
            'listings' => $listings,
            'pager' => $pager,
            'merchant' => $merchant,
            'page_title' => 'My Service Listings'
        ];

        return view('merchant/listings/index', $data);
    }

    /**
     * View a specific listing
     */
    public function view($id = null)
    {
        $listingModel = new MerchantListingModel();
        $listingImageModel = new MerchantListingImageModel();
        $locationModel = new \App\Models\MerchantLocationModel();
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        // Get listing with merchant verification
        $listing = $listingModel->find($id);

        if (!$listing || $listing['merchant_id'] != $merchantId) {
            return redirect()->to(site_url('merchant/listings'))->with('error', 'Listing not found or you do not have permission to view it.');
        }

        // Get listing images
        $images = $listingImageModel->findByListingId($id);

        // Get location information
        $location = null;
        if (!empty($listing['location_id'])) {
            $location = $locationModel->find($listing['location_id']);
        }

        $data = [
            'listing' => $listing,
            'images' => $images,
            'location' => $location,
            'page_title' => 'View Listing - ' . $listing['title']
        ];

        return view('merchant/listings/view', $data);
    }

    /**
     * Shows the form to create a new service listing.
     */
    public function new()
    {
        $merchantId = session()->get('merchant_id') ?? session()->get('user_id');

        // Check if merchant is logged in
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'You must be logged in to create listings.');
        }

        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        // Check if merchant exists
        if (!$merchant) {
            return redirect()->to('merchant/dashboard')->with('error', 'Merchant profile not found.');
        }

        // Check if merchant is approved
        if ($merchant['verification_status'] !== 'approved') {
            return redirect()->to('merchant/dashboard')->with('error', 'Your account must be approved before you can create service listings. Please wait for admin approval or complete your verification.');
        }

        // Check listing limit
        $planLimitModel = new \App\Models\PlanLimitationModel();
        $listingModel = new MerchantListingModel();

        $currentCount = $listingModel->where('merchant_id', $merchantId)
                                     ->where('status', 'approved')
                                     ->countAllResults();

        $limitCheck = $planLimitModel->checkLimit($merchantId, 'max_listings', $currentCount);

        if (!$limitCheck['allowed']) {
            return redirect()->to('merchant/listings')
                ->with('error', $limitCheck['message'] . ' <a href="' . site_url('merchant/subscription/plans') . '" class="underline">Upgrade your plan</a>');
        }

        // Get locations for dropdown
        $locationModel = new \App\Models\MerchantLocationModel();
        $locations = $locationModel->getLocationsByMerchant($merchantId);

        // Check if merchant has at least one location
        if (empty($locations)) {
            return redirect()->to('merchant/locations/create')
                ->with('error', 'Please add at least one business location before creating a service listing.');
        }

        $categoryModel = new ServiceCategoryModel();
        $serviceModel = new ServiceModel();
        $currencyModel = new CurrencyModel();

        // Get usage stats for display
        $usageStats = $planLimitModel->getMerchantUsageStats($merchantId);

        // Get location_id from URL if provided
        $preselectedLocationId = $this->request->getGet('location_id');

        // Get max categories limit for the merchant's plan
        $maxCategories = $planLimitModel->getMerchantLimit($merchantId, 'max_categories');

        return view('merchant/listings/form', [
            'page_title' => 'Add New Service Listing',
            'categories' => $categoryModel->getAllCategories(),
            'services' => $serviceModel->findAll(), // All services for autocomplete
            'currencies' => $currencyModel->getAllActive(), // Show all active currencies including African currencies
            'merchant' => $merchant,
            'locations' => $locations,
            'usage_stats' => $usageStats,
            'preselected_location_id' => $preselectedLocationId,
            'maxCategories' => $maxCategories,
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY')
        ]);
    }

    /**
     * Processes the creation of a new service listing.
     */
    public function create()
    {
        $merchantId = session()->get('merchant_id') ?? session()->get('user_id');

        // Check if merchant is logged in
        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'You must be logged in to create listings.');
        }

        // Verify merchant is approved before allowing listing creation
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            return redirect()->to('merchant/dashboard')->with('error', 'Merchant profile not found.');
        }

        if ($merchant['verification_status'] !== 'approved') {
            return redirect()->to('merchant/dashboard')->with('error', 'Your account must be approved before you can create service listings. Please wait for admin approval or complete your verification.');
        }

        // Check listing limit
        $planLimitModel = new \App\Models\PlanLimitationModel();
        $listingModel = new MerchantListingModel();

        $currentCount = $listingModel->where('merchant_id', $merchantId)
                                     ->where('status', 'approved')
                                     ->countAllResults();

        $limitCheck = $planLimitModel->checkLimit($merchantId, 'max_listings', $currentCount);

        if (!$limitCheck['allowed']) {
            return redirect()->to('merchant/listings')
                ->with('error', $limitCheck['message']);
        }

        $request = \Config\Services::request();

        $validation = $this->validate([
            'location_id' => 'required|integer',
            'title' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|max_length[5000]',
            'price' => 'required|decimal|greater_than_equal_to[0]',
            'currency_code' => 'required|exact_length[3]|alpha',
            'categories' => 'required',
            'main_image' => [
                'label' => 'Main Image',
                'rules' => 'uploaded[main_image]|is_image[main_image]|mime_in[main_image,image/jpg,image/jpeg,image/png,image/webp]|max_size[main_image,2048]'
            ]
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validate currency code exists in our supported currencies
        $currencyModel = new CurrencyModel();
        $currencyCode = strtoupper($request->getPost('currency_code'));
        if (!$currencyModel->isValidCurrency($currencyCode)) {
            return redirect()->back()->withInput()->with('errors', ['currency_code' => 'Invalid currency selected']);
        }

        // Validate that at least one category is selected
        $categories = $request->getPost('categories');
        if (empty($categories)) {
            return redirect()->back()->withInput()->with('errors', ['categories' => 'At least one category must be selected.']);
        }

        // Check category limit
        $maxCategories = $planLimitModel->getMerchantLimit($merchantId, 'max_categories');
        if ($maxCategories !== -1 && count($categories) > $maxCategories) {
            return redirect()->back()->withInput()->with('errors', ['categories' => "You can only select up to {$maxCategories} categories on your current plan."]);
        }

        $mainImage = $request->getFile('main_image');
        $mainImageName = $mainImage->getRandomName();
        // Save to uploads/listings at web root
        $mainImage->move(FCPATH . 'uploads/listings', $mainImageName);

        $listingModel = new MerchantListingModel();

        // Process price - now always numeric
        $priceNumeric = (float) $request->getPost('price');

        $listingData = [
            'merchant_id' => $merchantId,
            'location_id' => $request->getPost('location_id'),
            'title' => $request->getPost('title'),
            'description' => $request->getPost('description'),
            'price' => $priceNumeric, // Store as numeric value
            'currency_code' => $currencyCode,
            'price_numeric' => $priceNumeric,
            'main_image_path' => 'uploads/listings/' . $mainImageName, // Store as uploads/listings/filename.jpg
            'status' => 'pending' // Default status
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $listingId = $listingModel->insert($listingData);

            if ($listingId) {
                // Insert categories
                $categoryData = [];
                foreach ($categories as $categoryId) {
                    $categoryData[] = [
                        'listing_id' => $listingId,
                        'category_id' => (int)$categoryId
                    ];
                }
                if (!empty($categoryData)) {
                    $db->table('merchant_listing_categories')->insertBatch($categoryData);
                }

                // Insert services (subcategories) if provided
                $services = $request->getPost('services');
                if (!empty($services)) {
                    $serviceData = [];
                    foreach ($services as $serviceId) {
                        $serviceData[] = [
                            'listing_id' => $listingId,
                            'service_id' => (int)$serviceId
                        ];
                    }
                    if (!empty($serviceData)) {
                        $db->table('merchant_listing_services')->insertBatch($serviceData);
                    }
                }

                // Handle gallery images if they exist
                $galleryImages = $request->getFiles();
                if ($galleryImages && isset($galleryImages['gallery_images'])) {
                    // Check gallery image limit
                    $maxGalleryImages = $planLimitModel->getMerchantLimit($merchantId, 'max_gallery_images');
                    $galleryImageCount = 0;

                    $imageModel = new MerchantListingImageModel();
                    foreach ($galleryImages['gallery_images'] as $img) {
                        if ($img->isValid() && !$img->hasMoved()) {
                            // Check if we've hit the limit
                            if ($maxGalleryImages !== -1 && $galleryImageCount >= $maxGalleryImages) {
                                break; // Stop adding more images if limit reached
                            }

                            $imgName = $img->getRandomName();
                            // Save to uploads/listings at web root
                            $img->move(FCPATH . 'uploads/listings', $imgName);
                            $imageModel->save([
                                'listing_id' => $listingId,
                                'image_path' => 'uploads/listings/' . $imgName // Store as uploads/listings/filename.jpg
                            ]);
                            $galleryImageCount++;
                        }
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    log_message('error', 'Transaction failed for listing creation.');
                    throw new \Exception('Transaction failed');
                }

                // Notify admins of new listing via centralized service
                try {
                    $merchant = (new MerchantModel())->find($merchantId);
                    if ($merchant) {
                        $notifier = new \App\Services\NotificationService();
                        $notifier->notifyAdminsNewListing(array_merge(['id' => $listingId], $listingData), (array)$merchant);
                    }
                } catch (\Throwable $t) {
                    log_message('error', 'Error notifying admins about new listing: ' . $t->getMessage());
                }


                return redirect()->to(site_url('merchant/listings'))->with('message', 'Listing created successfully. It is now pending admin approval.');
            } else {
                throw new \Exception('Failed to create listing');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Listing creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('errors', ['general' => 'Failed to create listing. Please try again.']);
        }
    }

    /**
     * Shows the form to edit an existing service listing.
     * @param int $id The ID of the listing to edit.
     */
    public function edit($id = null)
    {
        $listingModel = new MerchantListingModel();
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        $listing = (array) $listingModel->find($id);

        // Ensure the listing belongs to the current merchant
        if (!$listing || $listing['merchant_id'] != $merchantId) {
            return redirect()->to(site_url('merchant/listings'))->with('error', 'Listing not found or you do not have permission to edit it.');
        }

        $categoryModel = new ServiceCategoryModel();
        $serviceModel = new ServiceModel();
        $currencyModel = new CurrencyModel();
        $merchantModel = new MerchantModel();
        $planLimitModel = new \App\Models\PlanLimitationModel();
        $listingImageModel = new \App\Models\MerchantListingImageModel();

        $merchant = $merchantModel->find($listing['merchant_id']);

        // Get max categories limit for the merchant's plan
        $maxCategories = $planLimitModel->getMerchantLimit($merchantId, 'max_categories');

        // Get the listing's current categories
        $db = \Config\Database::connect();
        $listingCategories = $db->table('merchant_listing_categories')
            ->where('listing_id', $id)
            ->get()
            ->getResultArray();

        // Add categories to listing array
        $listing['categories'] = $listingCategories;

        // Get the listing's gallery images
        $listing['gallery_images'] = $listingImageModel->findByListingId($id);

        return view('merchant/listings/form', [
            'listing' => $listing,
            'page_title' => 'Edit Service Listing',
            'categories' => $categoryModel->getAllCategories(),
            'services' => $serviceModel->findAll(),
            'currencies' => $currencyModel->getAllActive(), // Show all active currencies including African currencies
            'merchant' => $merchant,
            'maxCategories' => $maxCategories,
            'geoapify_api_key' => getenv('GEOAPIFY_API_KEY')
        ]);
    }

    /**
     * Processes the update of an existing service listing.
     * @param int $id The ID of the listing to update.
     */
    public function update($id = null)
    {
        $request = \Config\Services::request();
        $listingModel = new MerchantListingModel();
        $merchantId = session()->get('user_id');

        if (!$merchantId) {
            return redirect()->to('/login')->with('error', 'Please log in to continue');
        }

        $listing = (array) $listingModel->find($id);

        // Ensure the listing belongs to the current merchant
        if (!$listing || $listing['merchant_id'] != $merchantId) {
            return redirect()->to(site_url('merchant/listings'))->with('error', 'Listing not found or you do not have permission to update it.');
        }

        $validationRules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|max_length[5000]',
            'price' => 'required|decimal|greater_than_equal_to[0]',
            'currency_code' => 'required|exact_length[3]|alpha',
        ];

        // Main image is optional on update
        if ($request->getFile('main_image') && $request->getFile('main_image')->isValid()) {
            $validationRules['main_image'] = [
                'label' => 'Main Image',
                'rules' => 'uploaded[main_image]|is_image[main_image]|mime_in[main_image,image/jpg,image/jpeg,image/png,image/webp]|max_size[main_image,2048]'
            ];
        }

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validate currency code exists in our supported currencies
        $currencyModel = new CurrencyModel();
        $currencyCode = strtoupper($request->getPost('currency_code'));
        if (!$currencyModel->isValidCurrency($currencyCode)) {
            return redirect()->back()->withInput()->with('errors', ['currency_code' => 'Invalid currency selected']);
        }

        // Validate and check category limit if categories are provided
        $categories = $request->getPost('categories');
        if (!empty($categories)) {
            $planLimitModel = new \App\Models\PlanLimitationModel();
            $maxCategories = $planLimitModel->getMerchantLimit($merchantId, 'max_categories');
            if ($maxCategories !== -1 && count($categories) > $maxCategories) {
                return redirect()->back()->withInput()->with('errors', ['categories' => "You can only select up to {$maxCategories} categories on your current plan."]);
            }
        }

        // Process price - now always numeric
        $priceNumeric = (float) $request->getPost('price');

        $updateData = [
            'title' => $request->getPost('title'),
            'description' => $request->getPost('description'),
            'price' => $priceNumeric, // Store as numeric value
            'currency_code' => $currencyCode,
            'price_numeric' => $priceNumeric,
            'status' => 'pending' // Reset to pending on update for admin re-approval
        ];

        // Handle new main image upload
        if ($request->getFile('main_image') && $request->getFile('main_image')->isValid()) {
            $mainImage = $request->getFile('main_image');
            $mainImageName = $mainImage->getRandomName();
            // Save to uploads/listings at web root
            $mainImage->move(FCPATH . 'uploads/listings', $mainImageName);
            $updateData['main_image_path'] = 'uploads/listings/' . $mainImageName; // Store as uploads/listings/filename.jpg

            // Delete the old image
            if ($listing['main_image_path'] && file_exists(FCPATH . $listing['main_image_path'])) {
                @unlink(FCPATH . $listing['main_image_path']);
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if ($listingModel->update($id, $updateData)) {
                // Update categories if provided
                if (!empty($categories)) {
                    // Delete existing categories
                    $db->table('merchant_listing_categories')->where('listing_id', $id)->delete();

                    // Insert new categories
                    $categoryData = [];
                    foreach ($categories as $categoryId) {
                        $categoryData[] = [
                            'listing_id' => $id,
                            'category_id' => (int)$categoryId
                        ];
                    }
                    if (!empty($categoryData)) {
                        $db->table('merchant_listing_categories')->insertBatch($categoryData);
                    }
                }

                // Handle new gallery images upload
                $galleryImages = $request->getFiles();
                if ($galleryImages && isset($galleryImages['gallery_images'])) {
                    $planLimitModel = new \App\Models\PlanLimitationModel();
                    $maxGalleryImages = $planLimitModel->getMerchantLimit($merchantId, 'max_gallery_images');

                    // Get current gallery image count
                    $imageModel = new MerchantListingImageModel();
                    $currentImageCount = $imageModel->where('listing_id', $id)->countAllResults();
                    $galleryImageCount = $currentImageCount;

                    foreach ($galleryImages['gallery_images'] as $img) {
                        if ($img->isValid() && !$img->hasMoved()) {
                            // Check if we've hit the limit
                            if ($maxGalleryImages !== -1 && $galleryImageCount >= $maxGalleryImages) {
                                break; // Stop adding more images if limit reached
                            }

                            $imgName = $img->getRandomName();
                            // Save to uploads/listings at web root
                            $img->move(FCPATH . 'uploads/listings', $imgName);
                            $imageModel->save([
                                'listing_id' => $id,
                                'image_path' => 'uploads/listings/' . $imgName
                            ]);
                            $galleryImageCount++;
                        }
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaction failed');
                }

                return redirect()->to(site_url('merchant/listings'))->with('message', 'Listing updated successfully. It is now pending re-approval.');
            } else {
                throw new \Exception('Failed to update listing');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Listing update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update the listing.');
        }
    }

    /**
     * Delete a single gallery image via AJAX
     */
    public function deleteGalleryImage($imageId = null)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $merchantId = session()->get('user_id');
        if (!$merchantId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $imageModel = new MerchantListingImageModel();
        $listingModel = new MerchantListingModel();

        // Get the image
        $image = $imageModel->find($imageId);
        if (!$image) {
            return $this->response->setJSON(['success' => false, 'message' => 'Image not found']);
        }

        // Verify the image belongs to a listing owned by this merchant
        $listing = $listingModel->find($image['listing_id']);
        if (!$listing || $listing['merchant_id'] != $merchantId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        // Delete the physical file
        $imagePath = FCPATH . $image['image_path'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }

        // Delete the database record
        if ($imageModel->delete($imageId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete image']);
        }
    }

    /**
     * Deletes a service listing.
     * @param int $id The ID of the listing to delete.
     */
    public function delete($id = null)
    {
        $listingModel = new MerchantListingModel();
        $imageModel = new MerchantListingImageModel();
        $listing = (array) $listingModel->find($id);

        // Ensure the listing belongs to the current merchant
        if (!$listing || $listing['merchant_id'] != session()->get('id')) {
            return redirect()->to(site_url('merchant/listings'))->with('error', 'Listing not found or you do not have permission to delete it.');
        }

        // Delete associated gallery images
        $galleryImages = $imageModel->findByListingId($id);
        if ($galleryImages) {
            foreach ($galleryImages as $image) {
                if (file_exists(FCPATH . $image['image_path'])) {
                    @unlink(FCPATH . $image['image_path']);
                }
            }
            $imageModel->where('listing_id', $id)->delete();
        }

        // Delete the main image
        if ($listing['main_image_path'] && file_exists(FCPATH . $listing['main_image_path'])) {
            @unlink(FCPATH . $listing['main_image_path']);
        }

        // Delete the listing
        if ($listingModel->delete($id)) {
            return redirect()->to(site_url('merchant/listings'))->with('message', 'Listing deleted successfully.');
        } else {
            return redirect()->to(site_url('merchant/listings'))->with('error', 'Failed to delete the listing.');
        }
    }
}
