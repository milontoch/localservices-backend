<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key');
    }

    /**
     * Send OTP to phone number
     * 
     * @param string $phoneNumber
     * @param string $otp
     * @return bool
     */
    public function sendOtp($phoneNumber, $otp)
    {
        // If API key is not configured, log the OTP instead
        if (empty($this->apiKey)) {
            Log::info("OTP for {$phoneNumber}: {$otp}");
            return true;
        }

        // In production, integrate with actual SMS provider
        // Example: Twilio, Africa's Talking, etc.
        try {
            // Placeholder for actual SMS API call
            // $client = new SmsProviderClient($this->apiKey);
            // $client->send($phoneNumber, "Your LocalServices verification code is: {$otp}");
            
            Log::info("OTP sent to {$phoneNumber}: {$otp}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send OTP to {$phoneNumber}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate and store OTP
     * 
     * @param string $phoneNumber
     * @return string
     */
    public function generateOtp($phoneNumber)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in cache for 5 minutes
        Cache::put("otp:{$phoneNumber}", $otp, now()->addMinutes(5));
        
        return $otp;
    }

    /**
     * Verify OTP
     * 
     * @param string $phoneNumber
     * @param string $otp
     * @return bool
     */
    public function verifyOtp($phoneNumber, $otp)
    {
        $cachedOtp = Cache::get("otp:{$phoneNumber}");
        
        if (!$cachedOtp) {
            return false;
        }

        if ($cachedOtp !== $otp) {
            return false;
        }

        // Delete OTP after successful verification
        Cache::forget("otp:{$phoneNumber}");
        
        return true;
    }
}
