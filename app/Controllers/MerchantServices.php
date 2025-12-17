<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\ServiceCategoryModel;
use App\Models\MerchantServiceModel;
use CodeIgniter\Controller;

class MerchantServices extends Controller
{
    /**
     * Shows the page where a merchant can check boxes for the services they provide.
     */
    public function index()
    {
        $serviceModel = new ServiceModel();
        $categoryModel = new ServiceCategoryModel();
        $merchantServiceModel = new MerchantServiceModel();
        $merchantId = session()->get('id');

        $allServices = $serviceModel->orderBy('name', 'ASC')->findAll();
        $allCategories = $categoryModel->orderBy('name', 'ASC')->findAll();
        $merchantServices = $merchantServiceModel->findByMerchantId($merchantId);
        
        // Group services by category for the view
        $groupedServices = [];
        foreach ($allCategories as $category) {
            $groupedServices[$category['id']] = [
                'name' => $category['name'],
                'services' => []
            ];
        }
        foreach ($allServices as $service) {
            if (isset($groupedServices[$service['category_id']])) {
                $groupedServices[$service['category_id']]['services'][] = $service;
            }
        }

        $data = [
            'groupedServices' => $groupedServices,
            'merchantServiceIds' => $merchantServices, // Just the IDs
            'page_title' => 'Manage My Services'
        ];

        return view('merchant/services', $data);
    }

    /**
     * Saves the merchant's selected services to the database.
     */
    public function update()
    {
        $request = \Config\Services::request();
        $merchantServiceModel = new MerchantServiceModel();
        $merchantId = session()->get('id');

        // getPost() with a null filter returns all POST data.
        // If 'services' is not submitted (all boxes unchecked), it will be null.
        $selectedServiceIds = $request->getPost('services') ?? [];

        // The model method handles the complex logic of deleting old and inserting new.
        if ($merchantServiceModel->updateServicesForMerchant($merchantId, $selectedServiceIds)) {
            return redirect()->to('merchant/services')->with('message', 'Your services have been updated!');
        }

        return redirect()->back()->with('error', 'There was a problem updating your services.');
    }
}