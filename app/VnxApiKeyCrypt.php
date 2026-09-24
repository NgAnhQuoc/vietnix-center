<?php

namespace HelperCenter;

/**
 * Helper mã hóa / giải mã API key bằng AES-256-CBC.
 *
 * - Key mã hóa được dẫn xuất từ hằng bảo mật WordPress (AUTH_KEY, SECURE_AUTH_KEY)
 *   nên không thể di chuyển key sang môi trường khác mà không mất dữ liệu.
 * - IV ngẫu nhiên mỗi lần mã hóa, lưu cùng cipher text (base64 + "::" separator).
 * - Không bao giờ expose key gốc ra log; chỉ lưu số ký tự để hiển thị mask.
 */
final class VnxApiKeyCrypt
{
    private const OPTION_KEY  = 'vnx_api_key';
    private const CIPHER      = 'aes-256-cbc';
    private const MIN_KEY_LEN = 32;

    /** Không cho khởi tạo — chỉ dùng static */
    private function __construct() {}

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Mã hóa và lưu API key vào wp_options.
     *
     * @param  string $key  Key gốc (tối thiểu 32 ký tự).
     * @return bool         false nếu key quá ngắn, true nếu lưu thành công.
     *
     * @throws \RuntimeException Khi openssl_encrypt thất bại.
     */
    public static function save(string $key): bool
    {
        if (strlen($key) < self::MIN_KEY_LEN) {
            return false;
        }

        $iv        = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($key, self::CIPHER, self::derive_key(), 0, $iv);

        if ($encrypted === false) {
            throw new \RuntimeException('VnxApiKeyCrypt: encrypt failed.');
        }

        return (bool) update_option(self::OPTION_KEY, [
            'key'   => base64_encode($iv) . '::' . $encrypted,
            'count' => strlen($key),
        ]);
    }

    /**
     * Giải mã và trả về API key gốc.
     *
     * @return string|null  null nếu chưa lưu hoặc giải mã thất bại.
     */
    public static function decrypt(): ?string
    {
        $stored = get_option(self::OPTION_KEY);

        if (empty($stored['key'])) {
            return null;
        }

        $parts = explode('::', $stored['key'], 2);
        if (count($parts) !== 2) {
            return null;
        }

        $iv        = base64_decode($parts[0]);
        $decrypted = openssl_decrypt($parts[1], self::CIPHER, self::derive_key(), 0, $iv);

        return ($decrypted !== false && $decrypted !== '') ? $decrypted : null;
    }

    /**
     * Số ký tự của API key đang lưu (để render mask ****).
     * Không bao giờ trả về key gốc.
     *
     * @return int  0 nếu chưa có key.
     */
    public static function stored_length(): int
    {
        $stored = get_option(self::OPTION_KEY);
        return isset($stored['count']) ? (int) $stored['count'] : 0;
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    /**
     * Dẫn xuất encryption key từ hằng bảo mật WordPress.
     * SHA-256 → raw binary (32 bytes) — đúng kích thước cho AES-256.
     */
    private static function derive_key(): string
    {
        $salt = defined('AUTH_KEY') ? AUTH_KEY . SECURE_AUTH_KEY : wp_salt('auth');
        return hash('sha256', $salt, true);
    }
}
