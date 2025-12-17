<?php

/**
 * PayFast Helper Functions
 * 
 * Helper functions for PayFast payment integration
 */

if (!function_exists('generatePayFastSignature')) {
    /**
     * Generate PayFast signature
     * 
     * This function generates the MD5 signature required by PayFast
     * according to their official documentation.
     * 
     * @param array $data The payment data array (without signature field)
     * @param string|null $passphrase Optional passphrase for additional security
     * @return string The MD5 signature
     */
    function generatePayFastSignature(array $data, ?string $passphrase = null): string
    {
        // Remove signature field if it exists
        if (isset($data['signature'])) {
            unset($data['signature']);
        }

        // DO NOT sort alphabetically - PayFast requires fields in documentation order
        // Especially important for subscriptions

        // Build the signature string according to PayFast specification
        $pfParamString = '';
        foreach ($data as $key => $val) {
            // Skip empty values and signature field
            if ($val === '' || $val === null) {
                continue;
            }

            // Convert value to string, trim whitespace, and URL encode it
            $pfParamString .= $key . '=' . urlencode(trim($val)) . '&';
        }

        // Remove the last '&'
        $pfParamString = rtrim($pfParamString, '&');

        // Add passphrase if provided (URL encoded according to PayFast docs)
        if ($passphrase !== null && $passphrase !== '') {
            $pfParamString .= '&passphrase=' . urlencode(trim($passphrase));
        }

        // Log the signature string for debugging
        log_message('debug', 'PayFast Signature String: ' . $pfParamString);

        // Generate MD5 signature
        $signature = md5($pfParamString);
        log_message('debug', 'PayFast Generated Signature: ' . $signature);

        return $signature;
    }
}

if (!function_exists('validatePayFastSignature')) {
    /**
     * Validate PayFast signature from ITN (Instant Transaction Notification)
     * 
     * @param array $data The ITN data received from PayFast
     * @param string|null $passphrase Optional passphrase
     * @return bool True if signature is valid, false otherwise
     */
    function validatePayFastSignature(array $data, ?string $passphrase = null): bool
    {
        // Get the signature from the data
        $receivedSignature = $data['signature'] ?? '';
        
        // Generate the expected signature
        $expectedSignature = generatePayFastSignature($data, $passphrase);
        
        // Compare signatures
        return hash_equals($expectedSignature, $receivedSignature);
    }
}

if (!function_exists('getPayFastConfig')) {
    /**
     * Get PayFast configuration from environment
     * 
     * @return array PayFast configuration
     */
    function getPayFastConfig(): array
    {
        return [
            'merchant_id' => getenv('payfast.merchantId'),
            'merchant_key' => getenv('payfast.merchantKey'),
            'passphrase' => getenv('payfast.passphrase'),
            'process_url' => getenv('payfast.processUrl'),
            'validate_url' => getenv('payfast.validateUrl'),
            'is_sandbox' => strpos(getenv('payfast.processUrl'), 'sandbox') !== false,
        ];
    }
}

if (!function_exists('isPayFastSandbox')) {
    /**
     * Check if PayFast is in sandbox mode
     * 
     * @return bool True if sandbox mode, false if production
     */
    function isPayFastSandbox(): bool
    {
        $processUrl = getenv('payfast.processUrl');
        return strpos($processUrl, 'sandbox') !== false;
    }
}

if (!function_exists('logPayFastDebug')) {
    /**
     * Log PayFast debug information
     * 
     * @param string $message Debug message
     * @param array $data Additional data to log
     * @return void
     */
    function logPayFastDebug(string $message, array $data = []): void
    {
        $logData = [
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'sandbox_mode' => isPayFastSandbox(),
            'data' => $data
        ];
        
        log_message('info', 'PayFast Debug: ' . json_encode($logData));
    }
}

