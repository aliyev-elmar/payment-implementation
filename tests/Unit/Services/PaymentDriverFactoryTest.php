<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PaymentDriverFactory;
use App\Contracts\IPaymentGateway;
use PHPUnit\Framework\Attributes\Test;
use InvalidArgumentException;

class PaymentDriverFactoryTest extends TestCase
{
    protected PaymentDriverFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new PaymentDriverFactory();
    }

    #[Test]
    public function it_can_create_kapitalbank_driver_in_test_environment()
    {
        config([
            'payment.map.kapitalbank' => \App\Repositories\Payment\KapitalBankRepository::class,
            'payment.drivers.kapitalbank.test' => [
                'api' => 'https://test-api.kapitalbank.az/api/v1/orders',
                'hpp_redirect_url' => 'https://test.example.com/callback',
                'user' => 'test_user',
                'pass' => 'test_pass',
            ]
        ]);

        $this->app->instance('env', 'testing');

        $gateway = $this->factory->driver('kapitalbank');

        $this->assertInstanceOf(IPaymentGateway::class, $gateway);
    }

    #[Test]
    public function it_throws_exception_for_unsupported_driver()
    {
        config(['payment.map.unsupported' => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported payment driver [unsupported]');

        $this->factory->driver('unsupported');
    }

    #[Test]
    public function it_throws_exception_for_missing_configuration()
    {
        config([
            'payment.map.kapitalbank' => \App\Repositories\Payment\KapitalBankRepository::class,
            'payment.drivers.kapitalbank' => null
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing configuration for driver');

        $this->factory->driver('kapitalbank');
    }

    #[Test]
    public function it_caches_gateway_instances()
    {
        config([
            'payment.map.kapitalbank' => \App\Repositories\Payment\KapitalBankRepository::class,
            'payment.drivers.kapitalbank.test' => [
                'api' => 'https://test-api.kapitalbank.az/api/v1/orders',
                'hpp_redirect_url' => 'https://test.example.com/callback',
                'user' => 'test_user',
                'pass' => 'test_pass',
            ]
        ]);

        $gateway1 = $this->factory->driver('kapitalbank');
        $gateway2 = $this->factory->driver('kapitalbank');

        $this->assertSame($gateway1, $gateway2);
    }

    #[Test]
    public function it_uses_production_config_in_production_environment()
    {
        config([
            'payment.map.kapitalbank' => \App\Repositories\Payment\KapitalBankRepository::class,
            'payment.drivers.kapitalbank.prod' => [
                'api' => 'https://api.kapitalbank.az/api/v1/orders',
                'hpp_redirect_url' => 'https://example.com/callback',
                'user' => 'prod_user',
                'pass' => 'prod_pass',
            ]
        ]);

        $this->app->bind('env', fn() => 'production');

        $gateway = $this->factory->driver('kapitalbank');

        $this->assertInstanceOf(IPaymentGateway::class, $gateway);
    }
}
