<?php
class Vnx_Push_Logger_Center
{
  public function vnxPushLogger($msg = '', $meta = [])
  {
    try {
      $curl = curl_init();
      $postfields = json_encode([
        "message" => $msg,
        "level" => 2,
        "meta" => json_encode($meta)
      ]);
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://logger.vietnix.dev/logs',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postfields,
        CURLOPT_HTTPHEADER => array(
          'api-key: tZAqG5dI4pi16LceUxlAzkCMRncdPF3obAJn0yVYx6t82hYnAMrKxklz',
          'Content-Type: application/json'
        )
      ));

      return curl_exec($curl);
    } catch (\Throwable $th) {
      //throw Sth;
    }
  }
}
