<?php

/**
 * Image Helper Functions
 * Provides utility functions for handling image URLs and paths
 */

if (!function_exists('get_listing_image_url')) {
    /**
     * Get the correct public URL for a listing image
     *
     * All images are stored in uploads/ directory at the web root
     * Database stores paths as 'uploads/listings/file.jpg'
     *
     * @param string $imagePath The image path stored in database (e.g., 'uploads/listings/file.jpg')
     * @return string The full public URL to the image
     */
    function get_listing_image_url(string $imagePath): string
    {
        if (empty($imagePath)) {
            return base_url('assets/images/placeholder.png');
        }

        // Remove 'public/' prefix if it exists (legacy data)
        $imagePath = preg_replace('#^public/#', '', $imagePath);

        // If path already starts with uploads/, use as-is
        if (strpos($imagePath, 'uploads/') === 0) {
            return base_url($imagePath);
        }

        // If path already has correct format, use as-is
        return base_url($imagePath);
    }
}

if (!function_exists('get_upload_image_url')) {
    /**
     * Generic function to get public URL for any uploaded image
     * Alias for get_listing_image_url with a more generic name
     *
     * @param string $imagePath The image path stored in database
     * @return string The full public URL to the image
     */
    function get_upload_image_url(string $imagePath): string
    {
        return get_listing_image_url($imagePath);
    }
}

if (!function_exists('get_listing_request_image_url')) {
    /**
     * Get the correct public URL for a listing request image
     * Listing requests store just the filename, not the full path
     *
     * @param string $imageName The image filename stored in database
     * @return string The full public URL to the image
     */
    function get_listing_request_image_url(string $imageName): string
    {
        if (empty($imageName)) {
            return base_url('assets/images/placeholder.png');
        }

        // Listing request images are stored in uploads/listing-requests/
        return base_url('uploads/listing-requests/' . $imageName);
    }
}

if (!function_exists('get_merchant_document_url')) {
    /**
     * Get the correct public URL for a merchant document/image
     *
     * @param string $documentPath The document path stored in database
     * @return string The full public URL to the document
     */
    function get_merchant_document_url(string $documentPath): string
    {
        if (empty($documentPath)) {
            return '';
        }

        // Handle merchant_documents paths
        if (strpos($documentPath, 'uploads/merchant_documents/') === 0 && strpos($documentPath, 'public/') === false) {
            return base_url('public/' . $documentPath);
        }

        return base_url($documentPath);
    }
}

if (!function_exists('get_driver_profile_image')) {
    /**
     * Get the driver's profile image URL or generate a default avatar
     *
     * @param int|null $driverId The driver's ID
     * @return string The profile image URL
     */
    function get_driver_profile_image(?int $driverId = null): string
    {
        // If no driver ID provided, try to get from session
        if ($driverId === null) {
            $driverId = session()->get('user_id');
        }

        // If still no driver ID, return default avatar
        if (!$driverId) {
            $displayName = session()->get('name') ?: (session()->get('email') ?: 'Driver');
            return 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=16a34a&color=fff&size=64';
        }

        // Load driver data
        $driverModel = new \App\Models\TruckDriverModel();
        $driver = $driverModel->find($driverId);

        if (!$driver) {
            $displayName = session()->get('name') ?: (session()->get('email') ?: 'Driver');
            return 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=16a34a&color=fff&size=64';
        }

        // Check if driver has profile image
        $profileImagePath = $driver['profile_image_url'] ?? '';

        if (!empty($profileImagePath)) {
            // Remove 'public/' prefix if it exists (legacy data)
            $cleanPath = preg_replace('#^public/#', '', $profileImagePath);
            return base_url($cleanPath);
        }

        // Generate default avatar with driver's name
        $firstName = $driver['name'] ?? '';
        $lastName = $driver['surname'] ?? '';

        // If surname is empty but name has both, split them
        if (empty($lastName) && !empty($firstName) && preg_match('/\s+/', $firstName)) {
            $parts = preg_split('/\s+/', trim($firstName), 2);
            $firstName = $parts[0] ?? $firstName;
            $lastName = $parts[1] ?? '';
        }

        $displayName = trim($firstName . ' ' . $lastName) ?: ($driver['email'] ?? 'Driver');
        return 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=16a34a&color=fff&size=64';
    }
}
