<?php

namespace HelperCenter;

class DiscordBot
{
    /**
     * Gửi tin nhắn đến Discord channel
     * @param string $channelId
     * @param string $title
     * @param string $content
     * @param string $colorHex
     * @return bool
     */
    static public function sendMessageByBot($channelId, $title, $content, $colorHex = '00b0f4')
    {
        $colorDec = hexdec(ltrim($colorHex, '#'));

        $url = "https://discord.com/api/v10/channels/{$channelId}/messages";

        $body = [
            'embeds' => [[
                'title' => $title,
                'description' => $content,
                'color' => $colorDec
            ]]
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bot ' . VNX_Discord_Bot_Token,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($body),
            'timeout' => 15,
        ]);


        if (is_wp_error($response)) {
            error_log('Discord send error: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code < 200 || $code >= 300) {
            error_log('Discord send failed (HTTP ' . $code . '): ' . wp_remote_retrieve_body($response));
            return false;
        }

        return true;
    }

    /**
     * Gửi tin nhắn đến Discord channel qua webhook
     * @param string $webhookUrl
     * @param string $title
     * @param string $content
     * @param string $colorHex
     * @param int $timeout Giay cho Discord tra loi; notify form nen de thap de khong treo submit.
     * @return bool
     */
    static public function sendMessageByWebhook($webhookUrl, $title, $content, $colorHex = '00b0f4', $timeout = 15)
    {
        $colorDec = hexdec(ltrim($colorHex, '#'));

        // Discord tu choi thang embed vuot gioi han (title 256, description 4096 ky tu):
        // ca request bi 400 va KHONG co embed nao duoc gui, kho phat hien tu phia goi neu
        // khong tu ep han o day.
        if (mb_strlen($title) > 256) {
            $title = mb_substr($title, 0, 253) . '...';
        }
        if (mb_strlen($content) > 4096) {
            $content = mb_substr($content, 0, 4093) . '...';
        }

        $body = [
            'embeds' => [[
                'title' => $title,
                'description' => $content,
                'color' => $colorDec
            ]],
            // Noi dung co the chua du lieu khach nhap: khong cho @everyone/@here/<@id> ping ai.
            'allowed_mentions' => ['parse' => []],
        ];
        $response = wp_remote_post($webhookUrl, [
            'headers' => [
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($body),
            'timeout' => $timeout,
        ]);
        if (is_wp_error($response)) {
            error_log('Discord webhook send error: ' . $response->get_error_message());
            return false;
        }
        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            error_log('Discord webhook send failed (HTTP ' . $code . '): ' . wp_remote_retrieve_body($response));
            return false;
        }
        return true;
    }
}
