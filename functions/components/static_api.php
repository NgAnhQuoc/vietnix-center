<?php
class vnx_StaticApi_Center
{
  public string $baseUrl;
  public array $headers;

  public function __construct(string $baseUrl, array $headers = [])
  {
    $this->baseUrl = rtrim($baseUrl, '/');
    $this->headers = $headers;
  }

  public function get(string $endpoint, array $queryParams = [])
  {
    $url = $this->buildUrl($endpoint, $queryParams);
    return $this->request('GET', $url);
  }

  public function post(string $endpoint, array $body = [])
  {
    return $this->request('POST', $this->buildUrl($endpoint), $body);
  }

  public function put(string $endpoint, array $body = [])
  {
    return $this->request('PUT', $this->buildUrl($endpoint), $body);
  }


  public function delete(string $endpoint, array $queryParams = [])
  {
    $url = $this->buildUrl($endpoint, $queryParams);
    return $this->request('DELETE', $url);
  }

  private function request(string $method, string $url, array $body = [])
  {
    $curl = curl_init();

    $options = [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $this->formatHeaders(),
      CURLOPT_TIMEOUT => 30,
    ];

    if (in_array($method, ['POST', 'PUT']) && !empty($body)) {
      $options[CURLOPT_POSTFIELDS] = json_encode($body);
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
      throw new Exception("cURL Error: $error");
    }

    return json_decode($response, true) ?? $response;
  }

  private function buildUrl(string $endpoint, array $queryParams = []): string
  {
    $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
    if (!empty($queryParams)) {
      $url .= '?' . http_build_query($queryParams);
    }
    return $url;
  }

  private function formatHeaders(): array
  {
    $formattedHeaders = [];
    foreach ($this->headers as $key => $value) {
      $formattedHeaders[] = "$key: $value";
    }
    return $formattedHeaders;
  }

  public function setHeaders(array $headers): void
  {
    $this->headers = array_merge($this->headers, $headers);
  }
}