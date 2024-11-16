<?php

namespace App\Helpers;

class ApiResponse {
  public static function success($data = null, $message = null, $statusCode = 200, $pagination = null) {
    $response = ['data' => $data, 'message' => $message];
    if ($pagination) {
      $response['pagination'] = $pagination;
    }
    return response()->json($response, $statusCode);
  }

  public static function error($message = 'Error', $statusCode = 500) {
    return response()->json(['message' => $message], $statusCode);
  }
}
