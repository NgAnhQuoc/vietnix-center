<?php
namespace HelperCenter;


/**
 * Gọi Google Docs/Drive API bằng service account (secrets/credentials.json).
 *
 * Tự ký JWT bằng openssl và gọi API qua WordPress HTTP API, không dùng Guzzle/google-auth:
 * plugin vietnix-plugin cũng nạp Guzzle/psr7 (khác phiên bản) vào cùng namespace, bản nào nạp
 * trước sẽ thắng -> Guzzle mới gọi Psr7\Utils::asciiToUpper() của psr7 cũ -> fatal error.
 */
class GoogleAuth
{

    private $accessToken;
    private $scopes;
    public $nameOption;

    public function __construct()
    {
        $this->nameOption = 'gg_auth_token';
        $this->scopes = ['https://www.googleapis.com/auth/documents.readonly', 'https://www.googleapis.com/auth/drive.readonly'];
        $this->accessToken = $this->getToken();
    }

    public function getAccessToken()
    {
        $credentials = $this->getCredentials();
        $tokenUri = !empty($credentials['token_uri']) ? $credentials['token_uri'] : 'https://oauth2.googleapis.com/token';

        $now = time();
        $segments = [
            $this->base64UrlEncode(wp_json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
            $this->base64UrlEncode(wp_json_encode([
                'iss' => $credentials['client_email'],
                'scope' => implode(' ', $this->scopes),
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ])),
        ];

        $signature = '';
        if (!openssl_sign(implode('.', $segments), $signature, $credentials['private_key'], 'sha256WithRSAEncryption')) {
            throw new \Exception('Không ký được JWT bằng private key trong credentials.json');
        }
        $segments[] = $this->base64UrlEncode($signature);

        $response = wp_remote_post($tokenUri, [
            'timeout' => 30,
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => implode('.', $segments),
            ],
        ]);
        $tokenData = $this->decodeResponse($response, 'Unable to fetch access token');

        if (isset($tokenData['access_token'])) {
            $this->accessToken = $tokenData['access_token'];
            $this->saveToken($this->accessToken);
            return $this->accessToken;
        }

        throw new \Exception('Unable to fetch access token');
    }

    public function getDocs($doc_id)
    {
        if (empty($doc_id)) {
            throw new \InvalidArgumentException('Document ID is required');
        }

        return $this->apiGet('https://docs.googleapis.com/v1/documents/' . rawurlencode($doc_id), 'Failed to fetch document');
    }

    // tạo hàm lấy danh sách file trong folder theo id folder
    function getListDocsInFolder($folderId)
    {
        if (empty($folderId)) {
            throw new \InvalidArgumentException('Document ID is required');
        }

        $url = 'https://www.googleapis.com/drive/v3/files?q=' . rawurlencode("'$folderId' in parents");

        return $this->apiGet($url, 'Failed to fetch document');
    }

    /**
     * GET tới Google API, token hết hạn (401) thì lấy token mới và gọi lại 1 lần.
     */
    private function apiGet($url, $errorMessage, $retried = false)
    {
        if (!$this->accessToken) {
            $this->getAccessToken();
        }

        $response = wp_remote_get($url, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ]);

        if (!$retried && wp_remote_retrieve_response_code($response) === 401) {
            $this->getAccessToken();
            return $this->apiGet($url, $errorMessage, true);
        }

        return $this->decodeResponse($response, $errorMessage);
    }

    private function decodeResponse($response, $errorMessage)
    {
        if (is_wp_error($response)) {
            throw new \Exception($errorMessage . ': ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code < 200 || $code >= 300) {
            throw new \Exception($errorMessage . ': HTTP ' . $code . ' ' . $body);
        }

        return json_decode($body, true);
    }

    private function getCredentials()
    {
        $path = VNX_PLUGIN_PATH_CENTER . 'secrets/credentials.json';
        $credentials = is_readable($path) ? json_decode(file_get_contents($path), true) : null;

        if (empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new \Exception('Thiếu hoặc sai file credentials.json (service account) tại ' . $path);
        }

        return $credentials;
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function saveToken($token)
    {
        update_option($this->nameOption, $token);
    }

    public function getToken()
    {
        return get_option($this->nameOption);
    }
}
