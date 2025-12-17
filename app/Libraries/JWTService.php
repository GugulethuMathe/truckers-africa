<?php

namespace App\Libraries;

use Config\JWT as JWTConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

/**
 * JWT Service for handling token creation and validation
 * Using Firebase JWT library for production-ready JWT implementation
 */
class JWTService
{
    private JWTConfig $config;

    public function __construct()
    {
        $this->config = new JWTConfig();
    }

    /**
     * Generate JWT token for a driver
     */
    public function generateToken(array $driverData): string
    {
        $payload = [
            'iss' => $this->config->issuer,
            'aud' => $this->config->audience,
            'iat' => time(),
            'nbf' => time() + $this->config->notBeforeOffset,
            'exp' => time() + $this->config->expirationTime,
            'data' => [
                'driver_id' => $driverData['id'],
                'email' => $driverData['email'],
                'user_type' => 'driver'
            ]
        ];

        return JWT::encode($payload, $this->config->secretKey, $this->config->algorithm);
    }

    /**
     * Validate and decode JWT token
     */
    public function validateToken(string $token): array|false
    {
        try {
            $decoded = JWT::decode($token, new Key($this->config->secretKey, $this->config->algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            log_message('info', 'JWT token expired: ' . $e->getMessage());
            return false;
        } catch (SignatureInvalidException $e) {
            log_message('warning', 'JWT signature invalid: ' . $e->getMessage());
            return false;
        } catch (BeforeValidException $e) {
            log_message('info', 'JWT not yet valid: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            log_message('error', 'JWT validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract driver ID from token
     */
    public function getDriverIdFromToken(string $token): int|false
    {
        $payload = $this->validateToken($token);
        
        if (!$payload || !isset($payload['data']['driver_id'])) {
            return false;
        }

        return (int) $payload['data']['driver_id'];
    }

    /**
     * Generate refresh token
     */
    public function generateRefreshToken(int $driverId): string
    {
        $payload = [
            'iss' => $this->config->issuer,
            'aud' => $this->config->audience,
            'iat' => time(),
            'exp' => time() + $this->config->refreshTokenExpiration,
            'type' => 'refresh',
            'driver_id' => $driverId
        ];

        return JWT::encode($payload, $this->config->secretKey, $this->config->algorithm);
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->config->secretKey, $this->config->algorithm));
            return false;
        } catch (ExpiredException $e) {
            return true;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Get token expiration time
     */
    public function getTokenExpiration(string $token): int|false
    {
        try {
            $decoded = JWT::decode($token, new Key($this->config->secretKey, $this->config->algorithm));
            return $decoded->exp ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

