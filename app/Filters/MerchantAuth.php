<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MerchantAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to view this page.');
        }

        // Check if user is a merchant
        if (session()->get('user_type') !== 'merchant') {
            return redirect()->to('/login')->with('error', 'Access denied. Merchants only.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after
    }
}