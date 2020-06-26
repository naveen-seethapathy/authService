<?php

namespace App\Http\Traits;

trait TransformsResponses
{
    public function respond(array $data, array $additionalHeaders = [], $httpCode = 200)
    {
        return response()->json([
            'status' => empty($data['status']) ? false : (bool)$data['status'],
            'message' => empty($data['message']) ? '' : (string)$data['message'],
            'httpCode' => $httpCode,
            'data' => $data['data'],
            'errors' => $data['errors']
        ], $httpCode, $additionalHeaders);
    }
}
