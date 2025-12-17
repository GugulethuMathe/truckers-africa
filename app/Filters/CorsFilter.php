<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Handle preflight requests
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            header('Access-Control-Allow-Origin: ' . (getenv('CORS_ALLOWED_ORIGINS') ?: '*'));
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With, X-Device-ID, X-Device-Name, Accept');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
            header("HTTP/1.1 200 OK");
            exit();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $this->applyCorsHeaders($response);
    }

    private function applyCorsHeaders(ResponseInterface $response): void
    {
        $allowedOrigins = getenv('CORS_ALLOWED_ORIGINS') ?: '*';
        $response
            ->setHeader('Access-Control-Allow-Origin', $allowedOrigins)
            ->setHeader('Vary', 'Origin')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With, X-Device-ID, X-Device-Name, Accept')
            ->setHeader('Access-Control-Allow-Credentials', 'true')
            ->setHeader('Access-Control-Max-Age', '86400');
    }
}
