<?php

use Illuminate\Http\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

if(!function_exists('successResponse')) {
    function successResponse(array $response, int $statusCode = Response::HTTP_OK): Response {
        return response($response, $statusCode);
    }
}

if(!function_exists('errorResponse')) {
    function errorResponse(string $message = 'Error occurred', int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): HttpResponseException {
        throw new HttpResponseException(response(['message' => $message], $statusCode));
    }
}
