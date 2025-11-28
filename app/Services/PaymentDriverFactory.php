<?php

namespace App\Services;

use App\Contracts\IPaymentGateway;
use InvalidArgumentException;

class PaymentDriverFactory
{
    /**
     * @var array<string, IPaymentGateway>
     */
    protected array $gateways = [];

    /**
     * @param string $driver
     * @return IPaymentGateway
     * @throws InvalidArgumentException
     */
    public function driver(string $driver): IPaymentGateway
    {
        if (isset($this->gateways[$driver])) {
            return $this->gateways[$driver];
        }

        $gateway = $this->createDriverInstance($driver);
        return $this->gateways[$driver] = $gateway;
    }

    /**
     * @param string $driver
     * @return IPaymentGateway
     * @throws InvalidArgumentException
     */
    protected function createDriverInstance(string $driver): IPaymentGateway
    {
        $repositoryClass = config("payment.map.{$driver}");

        if (is_null($repositoryClass)) {
            throw new InvalidArgumentException("Unsupported payment driver [{$driver}]. Map not found in configuration.");
        }

        $envKey = app()->environment('production') ? 'prod' : 'test';
        $config = config("payment.drivers.{$driver}.{$envKey}");

        if (is_null($config)) {
            throw new InvalidArgumentException("Missing configuration for driver [{$driver}] in environment [{$envKey}].");
        }

        return app($repositoryClass, [
            'apiUrl' => $config['api'] ?? '',
            'hppRedirectUrl' => $config['hpp_redirect_url'] ?? '',
            'user' => $config['user'] ?? '',
            'pass' => $config['pass'] ?? '',
        ]);
    }
}
