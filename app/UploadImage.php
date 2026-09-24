<?php
namespace HelperCenter;


class UploadImage
{
    /**
     * Upload image from URL to WordPress media library as WebP
     *
     * @param string $image_url URL of the image to upload
     * @param string $slug Slug used for the generated filename
     * @param string $title Optional title for the image
     * @param string $description Optional description for the image
     * @return int|false The attachment ID on success, false on failure
     */
    public static function upload_from_url($image_url, $slug, $title = '', $description = '')
    {
        try {
            if (!filter_var($image_url, FILTER_VALIDATE_URL)) {

                error_log('Invalid image URL provided: ' . $image_url);
                return false;
            }

            $upload_dir = wp_upload_dir();

            $image_data = file_get_contents($image_url);
            if ($image_data === false) {
                error_log('Failed to download image from URL: ' . $image_url);
                return false;
            }

            // Get MIME type from image data
            $image_info = getimagesizefromstring($image_data);
            if (!$image_info || empty($image_info['mime'])) {
                error_log('Failed to get image info from URL: ' . $image_url);
                return false;
            }

            $mime_type = $image_info['mime'];

            // Tạo ảnh từ dữ liệu binary
            $image = imagecreatefromstring($image_data);
            if (!$image) {
                error_log('Failed to create image from data: ' . $image_url);
                return false;
            }

            // Tạo tên file .webp
            $filename = wp_unique_filename($upload_dir['path'], $slug . '-' . '.webp');
            $file_path = $upload_dir['path'] . '/' . $filename;

            // Lưu ảnh dưới dạng WebP
            if (!imagewebp($image, $file_path)) {
                error_log('Failed to save image in WebP format: ' . $image_url);
                return false;
            }

            imagedestroy($image);

            // Tạo attachment metadata
            $attachment = array(
                'post_mime_type' => 'image/webp',
                'post_title' => sanitize_file_name($title ?: $filename),
                'post_content' => $description,
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $file_path);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
            wp_update_attachment_metadata($attach_id, $attach_data);

            $url = wp_get_attachment_url($attach_id);

            return array(
                'url' => $url,
                'id' => $attach_id
            );
        } catch (Exception $e) {
            error_log('Error uploading image: ' . $e->getMessage());
            return false;
        }
    }
}