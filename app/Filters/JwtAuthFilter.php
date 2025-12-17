<?php

namespace App\Filters;

use App\Libraries\JWTService;
use App\Models\ApiTokenModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Allow preflight to pass through
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return;
        }

        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || stripos($authHeader, 'Bearer ') !== 0) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Missing or invalid Authorization header'
            ]);
        }

        $token = trim(substr($authHeader, 7));
        if ($token === '') {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Bearer token is empty'
            ]);
        }

        $jwt = new JWTService();
        $payload = $jwt->validateToken($token);
        if ($payload === false) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Invalid or expired token'
            ]);
        }

        // Check token against DB for revocation and expiry
        $tokenHash = hash('sha256', $token);
        $tokenModel = new ApiTokenModel();
        $record = $tokenModel->validateTokenHash($tokenHash);
        if (!$record) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Token revoked or not recognized'
            ]);
        }

        // Token ok; proceed. We don't mutate request but controllers can decode again or extract driver id.
        return; // allow
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }
}
