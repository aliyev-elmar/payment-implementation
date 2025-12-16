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
     */
    public function driver(string $driver): IPaymentGateway
    {
        $appEnv = app()->environment('production') ? 'prod' : 'test';

        if (isset($this->gateways[$driver][$appEnv])) {
            return $this->gateways[$driver][$appEnv];
        }

        return $this->gateways[$driver][$appEnv] = $this->createDriverInstance($driver, $appEnv);
    }

    /**
     * @param string $driver
     * @param string $appEnv
     * @return IPaymentGateway
     */
    private function createDriverInstance(string $driver, string $appEnv): IPaymentGateway
    {
        $repositoryClass = config("payment.map.{$driver}");
        $config = config("payment.drivers.{$driver}.{$appEnv}");

        if (is_null($repositoryClass)) {
            throw new InvalidArgumentException("Unsupported payment driver [{$driver}]. Map not found in configuration.");
        }

        if (is_null($config)) {
            throw new InvalidArgumentException("Missing configuration for driver [{$driver}] in environment [{$appEnv}].");
        }

        return app($repositoryClass, [
            'apiUrl' => $config['api'] ?? '',
            'hppRedirectUrl' => $config['hpp_redirect_url'] ?? '',
            'user' => $config['user'] ?? '',
            'pass' => $config['pass'] ?? '',
        ]);
    }
}
