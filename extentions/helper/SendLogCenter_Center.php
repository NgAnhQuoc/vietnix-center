<?php
class SendLogCenter_Center
{
    public const ENDPOINT = 'https://api.devzone.vietnix.dev/logcenter/logs';
    // Có thể override bằng cách define('VNX_LOGCENTER_API_KEY', '...') trong wp-config.php
    private const DEFAULT_API_KEY = 'dz_8abb4a8e29c89d41b724016dcb1959ab363506986d199cdece2325a692eff52f';

    public function send($message, $level = 'error', $meta = [], $env = null)
    {
        $apiKey = defined('VNX_LOGCENTER_API_KEY') ? VNX_LOGCENTER_API_KEY : self::DEFAULT_API_KEY;
        $env    = $env ?: self::detectEnv();

        $ch = curl_init(self::ENDPOINT);

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'message' => $message,
                'level'   => $level,
                'env'     => $env,
                'meta'    => $meta,
            ]),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    // vietnix.vn -> prod, *.vietnix.dev (vd: stag.vietnix.dev) -> staging
    private static function detectEnv()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        // dùng cho log localhost, dev, staging
        // if (
        //     strpos($host, 'vietnix.dev') !== false
        //     || strpos($host, 'localhost') !== false
        //     || strpos($host, '127.0.0.1') !== false
        // ) 
        if (strpos($host, 'vietnix.dev') !== false) {
            return 'staging';
        }

        if (strpos($host, 'vietnix.vn') !== false) {
            return 'prod';
        }

        return 'prod';
    }
}
