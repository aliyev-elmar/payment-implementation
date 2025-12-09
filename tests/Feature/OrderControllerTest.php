<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PaymentService;
use App\Exceptions\{PaymentGatewayException, OrderNotFoundException};
use App\DataTransferObjects\Payment\Order\SimpleStatus\{SimpleStatusResponseDto, SimpleStatusDto, SimpleStatusType};
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_create_order_successfully()
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('createOrder')
            ->once()
            ->with('kapitalbank', Mockery::any(), 10000, 'Test payment')
            ->andReturn('https://hpp.kapitalbank.az?id=12345&password=secret');

        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/api/orders', [
            'amount' => 10000,
            'description' => 'Test payment'
        ]);

        $response->assertStatus(201)
            ->assertJson(['formUrl' => 'https://hpp.kapitalbank.az?id=12345&password=secret']);
    }

    #[Test]
    public function it_validates_amount_is_required()
    {
        $response = $this->postJson('/api/orders', ['description' => 'Test payment']);
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function it_validates_amount_is_integer()
    {
        $response = $this->postJson('/api/orders', [
            'amount' => 'not-an-integer',
            'description' => 'Test payment'
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function it_validates_amount_minimum_value()
    {
        $response = $this->postJson('/api/orders', [
            'amount' => 50,
            'description' => 'Test payment'
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function it_validates_description_max_length()
    {
        $response = $this->postJson('/api/orders', [
            'amount' => 10000,
            'description' => str_repeat('a', 256)
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['description']);
    }

    #[Test]
    public function it_handles_payment_gateway_exception()
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $exception = new PaymentGatewayException('Kapital Bank', 400, 'INVALID_AMOUNT', 'Invalid amount provided');
        $paymentServiceMock->shouldReceive('createOrder')->once()->andThrow($exception);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/api/orders', [
            'amount' => 10000,
            'description' => 'Test payment'
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Payment gateway error'])
            ->assertJsonStructure(['details']);
    }

    #[Test]
    public function it_handles_internal_server_error()
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('createOrder')->once()->andThrow(new \Exception('Unexpected error'));
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/api/orders', [
            'amount' => 10000,
            'description' => 'Test payment'
        ]);

        $response->assertStatus(500)
            ->assertJson(['message' => 'Internal server error during order creation']);
    }

    #[Test]
    public function it_respects_rate_limiting_for_create_order()
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('createOrder')
            ->andReturn('https://hpp.kapitalbank.az?id=12345&password=secret');
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/orders', [
                'amount' => 10000,
                'description' => 'Test payment'
            ]);
        }

        $response->assertStatus(429);
    }

    #[Test]
    public function it_can_get_order_status_successfully()
    {
        $simpleStatusType = new SimpleStatusType(title: 'Purchase');
        $simpleStatusDto = new SimpleStatusDto(
            id: 12345, typeRid: 'Order_SMS', status: 'FullyPaid',
            lastStatusLogin: '2025-12-05T14:30:00Z', amount: 10000,
            currency: 'AZN', type: $simpleStatusType
        );
        $simpleStatusResponse = new SimpleStatusResponseDto(
            httpCode: 200, order: $simpleStatusDto, curlError: null, curlErrno: null
        );

        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('getSimpleStatusByOrderId')
            ->once()->with('kapitalbank', 12345)->andReturn($simpleStatusResponse);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->getJson('/api/orders/12345/simple-status');

        $response->assertStatus(200)
            ->assertJson(['order' => ['id' => 12345, 'status' => 'FullyPaid', 'amount' => 10000]]);
    }

    #[Test]
    public function it_handles_order_not_found_exception()
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $exception = new OrderNotFoundException('Kapital Bank');
        $paymentServiceMock->shouldReceive('getSimpleStatusByOrderId')
            ->once()->with('kapitalbank', 99999)->andThrow($exception);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->getJson('/api/orders/99999/simple-status');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Order not found on Kapital Bank']);
    }

    #[Test]
    public function it_handles_payment_gateway_exception_for_status_check()
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $exception = new PaymentGatewayException('Kapital Bank', 500, 'SERVER_ERROR', 'Internal server error');
        $paymentServiceMock->shouldReceive('getSimpleStatusByOrderId')->once()->andThrow($exception);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->getJson('/api/orders/12345/simple-status');

        $response->assertStatus(500)->assertJson(['message' => 'Payment gateway error']);
    }

    #[Test]
    public function it_respects_rate_limiting_for_get_status()
    {
        $simpleStatusType = new SimpleStatusType(title: 'Purchase');
        $simpleStatusDto = new SimpleStatusDto(
            id: 12345, typeRid: 'Order_SMS', status: 'FullyPaid',
            lastStatusLogin: '2025-12-05T14:30:00Z', amount: 10000,
            currency: 'AZN', type: $simpleStatusType
        );
        $simpleStatusResponse = new SimpleStatusResponseDto(
            httpCode: 200, order: $simpleStatusDto, curlError: null, curlErrno: null
        );

        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('getSimpleStatusByOrderId')->andReturn($simpleStatusResponse);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        for ($i = 0; $i < 31; $i++) {
            $response = $this->getJson('/api/orders/12345/simple-status');
        }

        $response->assertStatus(429);
    }
}
