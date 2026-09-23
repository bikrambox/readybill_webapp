<?php

namespace Modules\Core\Helpers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class SendMessageHelper
{
    /**
     * Send SMS with custom logging
     */
    public static function send(string $type, array $data, Request $request = null)
    {

        if(env('smsEnabled') != 1){
            return;
        }

        $smsList = config('sms.smsList');

        // Validate SMS type
        if (!isset($smsList[$type])) {
            $message = __('invalid_sms');
            self::logSMS('error', $request, 'Validation Error', $message, ['type' => $type]);
            return ['status' => false, 'message' => $message];
        }

        if (!preg_match('/^(\+91|91)/', $data['mobiles'])) {
            $type = "international_" . $type;
        }

        $defaultConfig = $smsList['default'];
        $templateId = $smsList[$type]['template_id'] ?? '';

        // Validate template ID
        if (empty($templateId)) {
            $message = __('template_id_missing');
            self::logSMS('error', $request, 'Configuration Error', $message, ['type' => $type]);
            return ['status' => false, 'message' => $message];
        }

        // Prepare recipient structure
        $recipient = [
            'mobiles' => $data['mobiles'] ?? '',
        ];

        // Add dynamic variables (e.g., OTP) to the recipient
        foreach ($data as $key => $value) {
            if ($key !== 'mobiles') {
                $recipient[$key] = $value;
            }
        }

        // Prepare payload
        $payload = json_encode([
            'template_id' => $templateId,
            'short_url' => $data['short_url'] ?? 0,
            'realTimeResponse' => $defaultConfig['realTimeResponse'] ?? 1,
            'recipients' => [$recipient],
        ]);

        self::logSMS('debug', $request, 'Payload Prepared', 'Final SMS Payload', ['payload' => $payload]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $defaultConfig['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'authkey: ' . $defaultConfig['authkey'],
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            self::logSMS('error', $request, 'cURL Error', $error, ['payload' => $payload]);
            return ['status' => false, 'message' => 'cURL Error: ' . $error];
        }

        self::logSMS('debug', $request, 'API Response', 'SMS API Response', ['response' => $response]);

        $responseData = json_decode($response, true);

        if (isset($responseData['type']) && $responseData['type'] == 'success') {
            self::logSMS('info', $request, 'Success', 'SMS Sent Successfully', ['response' => $responseData]);
            return ['status' => true, 'response' => $responseData];
        }

        self::logSMS('error', $request, 'API Error', $responseData['message'] ?? 'Unknown error occurred', ['response' => $responseData]);
        return ['status' => false, 'message' => $responseData['message'] ?? 'Unknown error occurred'];
    }

    /**
     * Log SMS messages to a custom file
     *
     * @param string $level Log level (error, info, debug)
     * @param Request|null $request HTTP request instance
     * @param string $type Type of log entry
     * @param string $message Log message
     * @param array $additionalData Additional data to log
     */
    private static function logSMS(string $level, $request, string $type, string $message, array $additionalData = [])
    {
        $logEntry = PHP_EOL . str_repeat('!', 80) . PHP_EOL .
            "Log Level: " . strtoupper($level) . PHP_EOL .
            "Type: " . $type . PHP_EOL .
            "Message: " . $message . PHP_EOL .
            "Occurred At: " . now()->toDateTimeString() . PHP_EOL .
            "URL: " . ($request ? $request->fullUrl() : 'N/A') . PHP_EOL .
            "Method: " . ($request ? $request->method() : 'N/A') . PHP_EOL .
            "IP Address: " . ($request ? $request->ip() : 'N/A') . PHP_EOL .
            "User-Agent: " . ($request ? $request->header('User-Agent') : 'N/A') . PHP_EOL .
            "Additional Data: " . json_encode($additionalData) . PHP_EOL .
            str_repeat('!', 80) . PHP_EOL . PHP_EOL;

        // Generate date-wise log file path under 'storage/logs'
        $logFilePath = storage_path('logs/sms_' . now()->toDateString() . '.log');

        // Append log entry to the SMS log file
        file_put_contents($logFilePath, $logEntry, FILE_APPEND);
    }
}