<?php

use Illuminate\Http\JsonResponse;

if (!function_exists('resp')) {
    function resp($code, $success, $message, $data = [], $error = null, $errors = [], $actualToken = null, $refreshToken = null, $additionalData = [])
    {
        $result = [
            "code" => $code,
            "success" => $success,
            "message" => $message,
        ];
        if (!empty($additionalData)) {
            $result = array_merge($result, $additionalData);
        }
        if (!empty($data)) {
            $result['data'] = $data;
        }
        if ($error !== null) {
            $result['error'] = $error;
        }
        if (!empty($errors)) {
            $result['errors'] = $errors;
        }
        if ($actualToken !== null) {
            $result['actual_token'] = $actualToken;
        }
        if ($refreshToken !== null) {
            $result['refresh_token'] = $refreshToken;
        }
        $response = new JsonResponse($result);
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        return $response;
    }
}

if (!function_exists('error_response')) {
    function error_response($e)
    {
        $errors = $e->errors();
        $message = "Ensure required fields are provided.";

        if (count($errors) === 1) {
            $message = reset($errors)[0];
        }

        return resp(204, false, $message, [
            'errors' => $errors
        ]);
    }
}
