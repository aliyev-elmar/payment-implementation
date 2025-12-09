<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CurlService;
use App\DataTransferObjects\CurlResponseDto;
use PHPUnit\Framework\Attributes\Test;

class CurlServiceTest extends TestCase
{
    protected CurlService $curlService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->curlService = new CurlService();
    }

    #[Test]
    public function it_returns_curl_response_dto_for_post_request()
    {
        // This is a mock test - in real scenario, you'd mock curl functions
        $url = 'https://httpbin.org/post';
        $headers = ['Content-Type: application/json'];
        $data = json_encode(['test' => 'data']);

        $response = $this->curlService->postRequest($url, $headers, $data);

        $this->assertInstanceOf(CurlResponseDto::class, $response);
        $this->assertIsInt($response->httpCode);
    }

    #[Test]
    public function it_returns_curl_response_dto_for_get_request()
    {
        $url = 'https://httpbin.org/get';
        $headers = ['Accept: application/json'];

        $response = $this->curlService->getRequest($url, $headers);

        $this->assertInstanceOf(CurlResponseDto::class, $response);
        $this->assertIsInt($response->httpCode);
    }

    #[Test]
    public function it_handles_invalid_url_gracefully()
    {
        $url = 'invalid-url';
        $headers = [];
        $data = json_encode([]);

        $response = $this->curlService->postRequest($url, $headers, $data);

        $this->assertInstanceOf(CurlResponseDto::class, $response);
        $this->assertNotNull($response->curlError);
    }

    #[Test]
    public function it_includes_curl_error_info_when_request_fails()
    {
        $url = 'https://this-domain-definitely-does-not-exist-123456789.com';
        $headers = [];
        $data = json_encode([]);

        $response = $this->curlService->postRequest($url, $headers, $data);

        $this->assertInstanceOf(CurlResponseDto::class, $response);
        // Either curlError or curlErrno should be set when request fails
        $this->assertTrue(
            !is_null($response->curlError) || !is_null($response->curlErrno),
            'Expected curl error information when request fails'
        );
    }
}
