<?php

namespace App\Services;

use App\DTOs\CurlResponseDto;

trait CurlService
{

    /**
     * Post Request With Curl
     *
     * @param string $curlUrl
     * @param string $body
     * @param array $header
     * @return CurlResponseDto
     */
    public function postRequest(string $curlUrl, string $body, array $header): CurlResponseDto
    {
        $ch = curl_init($curlUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $apiResponse = $this->getResponseByCh($ch);
        curl_close($ch);

        return $apiResponse;
    }

    /**
     * Get Request With Curl
     *
     * @param string $curlUrl
     * @param array $header
     * @return CurlResponseDto
     */
    public function getRequest(string $curlUrl, array $header): CurlResponseDto
    {
        $ch = curl_init($curlUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $apiResponse = $this->getResponseByCh($ch);
        curl_close($ch);

        return $apiResponse;
    }

    /**
     * Get Response and HTTP Code
     *
     * @param $ch
     * @return CurlResponseDto
     */
    private function getResponseByCh($ch): CurlResponseDto
    {
        $response = curl_exec($ch);
        $response = json_decode($response);

        return new CurlResponseDto(
            response: $response,
            httpCode: curl_getinfo($ch, CURLINFO_HTTP_CODE),
            curlError: curl_error($ch),
            curlErrno: curl_errno($ch),
        );
    }
}
