<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    /**
     * JWT Secret Key
     * 
     * IMPORTANT: Change this to a strong, random secret key
     * You can generate one at: https://allkeysgenerator.com/Random/Security-Encryption-Key-Generator.aspx
     */
    public string $secretKey = '';

    /**
     * JWT Algorithm
     */
    public string $algorithm = 'HS256';

    /**
     * JWT Token Expiration Time (in seconds)
     * Default: 2592000 (30 days)
     */
    public int $expirationTime = 2592000;

    /**
     * JWT Issuer
     */
    public string $issuer = 'truckers-africa-api';

    /**
     * JWT Audience
     */
    public string $audience = 'truckers-africa-mobile-app';

    /**
     * JWT Not Before (nbf) claim offset in seconds
     */
    public int $notBeforeOffset = 0;

    /**
     * JWT Issued At (iat) claim offset in seconds
     */
    public int $issuedAtOffset = 0;

    /**
     * Refresh Token Expiration (in seconds)
     * Default: 7776000 (90 days)
     */
    public int $refreshTokenExpiration = 7776000;

    public function __construct()
    {
        parent::__construct();

        // Load JWT secret from environment variable
        $this->secretKey = env('JWT_SECRET_KEY', $this->secretKey);
        
        if (empty($this->secretKey)) {
            // Fallback to a default key for development (NEVER use in production)
            $this->secretKey = 'dev-jwt-secret-key-change-this-in-production-' . base64_encode(random_bytes(32));
        }

        $this->expirationTime = (int) env('JWT_EXPIRATION_TIME', $this->expirationTime);
        $this->issuer = env('JWT_ISSUER', $this->issuer);
        $this->audience = env('JWT_AUDIENCE', $this->audience);
    }
}

