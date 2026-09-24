<?php
require_once __DIR__ . "/../components/static_ai.php";

class Vnx_Ai_Dispatch_Center
{
  private $staticAi;


  public function __construct()
  {
    $this->staticAi = new vnx_StaticAi_Center();
    add_action('wp_ajax_get_domain_suggest_ai_center', array($this, 'get_domain_suggest_ai'));
    add_action('wp_ajax_nopriv_get_domain_suggest_ai_center', array($this, 'get_domain_suggest_ai'));
  }

  public function get_domain_suggest_ai()
  {
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

    $list_tld = $this->staticAi->get_tld_suggest_ai();
    $list_tld = implode(',', $list_tld);
    $prompt = "
    Bạn là chuyên gia tư vấn đặt tên miền sáng tạo tại Vietnix.vn. 
    Nhiệm vụ: Dựa trên mô tả từ người dùng: \"$query\", hãy gợi ý danh sách tên miền phù hợp.

    ### QUY TẮC QUAN TRỌNG:
    1. ĐÁNH GIÁ ĐẦU VÀO: 
       - Nếu yêu cầu là chuỗi vô nghĩa, ký tự ngẫu nhiên (vd: 'asdasd', '123123', '@#$%', 'zzzzzz', '---- zx'), hãy trả về duy nhất [\"none\"].
       - Một yêu cầu hợp lệ cần có ý nghĩa rõ ràng như chứa thông tin về sản phẩm, ngành nghề hoặc mục đích sử dụng.

    2. GỢI Ý: Nếu yêu cầu hợp lệ, gợi ý 30 tên miền ngắn gọn, dễ nhớ, liên quan chặt chẽ đến nội dung.
    3. TLDs: Sử dụng các đuôi .vn, .com, .com.vn và danh sách mở rộng: [$list_tld].

    3. ĐỊNH DẠNG ĐẦU RA:
       - CHỈ trả về duy nhất một JSON array chứa các chuỗi tên miền. 
       - Trả về JSON rút gọn (compact), không có khoảng trắng thừa hay xuống dòng.
       - Ví dụ: [\"tenmien1.vn\",\"brandname.com\",\"dichvu247.net\"].
       - Không kèm theo bất kỳ văn bản giải thích nào khác.
    ";
    $api_key = '7btFCEui6wIjzQgfAf1fFbD1-7262-4eDe-bD6A-2e4306A4';
    $model = 'gemini-2.5-flash';
    $url = 'https://api-us-ca.umodelverse.ai/v1beta/models/' . $model . ':generateContent';

    $data = [
      'contents' => [
        [
          'parts' => [
            [
              'text' => $prompt,
            ]
          ]
        ]
      ],
      'generationConfig' => [
        'topP' => 0.95,
        'temperature' => 0.8,
        'maxOutputTokens' => 4096,
        'responseMimeType' => 'application/json',
        'responseSchema' => [
          'type' => 'array',
          'items' => [
            'type' => 'string',
          ],
        ],
      ]
    ];

    $headers = [
      'x-goog-api-key' => $api_key,
    ];

    $response = $this->staticAi->send_gemini_request($data, $url, $headers);
    if (!$response['success']) {
      wp_send_json_error(['message' => 'Lỗi gọi AI: ' . $response['message']]);
      return;
    }

    $response_text = $response['data'];

    // Làm sạch response_text
    $response_text = trim($response_text);
    if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $response_text, $matches)) {
      $response_text = $matches[1];
    }

    // Loại bỏ các ký tự điều khiển (control characters) gây lỗi json_decode
    $response_text = preg_replace('/[\x00-\x1F\x7F]/', '', $response_text);

    // Tự động sửa lỗi JSON nếu bị cắt ngang (truncated)
    if (!empty($response_text) && substr($response_text, -1) !== ']') {
      $response_text = rtrim($response_text, ' ,');
      // Tìm vị trí dấu ngoặc kép cuối cùng để đóng lại nếu cần
      $last_char = substr($response_text, -1);
      if ($last_char !== '"') {
        $response_text .= '"';
      }
      $response_text .= ']';
    }

    // Parse JSON response từ AI
    $suggestions = json_decode($response_text, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($suggestions)) {
      $error_msg = json_last_error_msg();
      wp_send_json_error(['message' => "Lỗi parse JSON ($error_msg). Raw: " . substr($response_text, 0, 500)]);
      return;
    }

    // Check if suggestions trùng lặp nhưng không gắn key trong mảng
    $suggestions = array_unique($suggestions);
    $suggestions = array_values($suggestions);

    wp_send_json_success(['suggestions_ai' => $suggestions]);
  }
}

// Khởi tạo class để đăng ký action hooks
new Vnx_Ai_Dispatch_Center();
