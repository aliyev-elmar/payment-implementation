# PHP/Laravel Payment Implementation Project

A robust Laravel payment gateway system implementing modern design patterns and SOLID principles, designed for seamless payment provider integrations with extensible, maintainable codebase.

> **Note**: This project was built to support multiple payment systems simultaneously with a provider-agnostic architecture. Currently implemented with **Kapital Bank** as the reference integration, demonstrating the extensible design that allows easy addition of other payment providers.

## 🏗️ Architecture & Design Patterns

### **Repository Pattern - Payment Provider Abstraction**

* **Purpose**: Abstract data layer with provider-agnostic interface
* **Implementation**:
    + `IPaymentGateway` interface - unified payment operations contract
    + Concrete implementations for different payment providers (e.g., `KapitalBankRepository`)
* **Benefits**:
    + **Business logic completely isolated from provider specifics**
    + **Easy integration of new payment processors**
    + **Consistent API across multiple providers**

### **Service Pattern - Business Logic Layer**

* **Purpose**: Encapsulate payment operations and business rules
* **Implementation**:
    + `PaymentService` - orchestrates payment processes
    + `CurlService` - HTTP operations abstraction
    + `LogService` - centralized logging
    + `PaymentDriverFactory` - manages gateway instantiation
* **Benefits**:
    + **Single Responsibility Principle compliance**
    + **Reusable payment workflow logic**
    + **Clean separation of concerns**

### **DTO Pattern - Type-Safe Data Transfer**

* **Purpose**: Ensure data integrity between layers
* **Implementation**:
    + `CreateOrderDto`, `CreateOrderResponseDto` - order creation structures
    + `SimpleStatusDto`, `SimpleStatusResponseDto` - status check structures
    + `CurlResponseDto` - HTTP response wrapper
    + Type-hinted properties with clear contracts
* **Benefits**:
    + **Predictable data structures**
    + **Reduced runtime errors**
    + **Better IDE support and autocompletion**

### **Strategy Pattern - Interchangeable Providers**

* **Purpose**: Enable runtime provider selection and hot-swapping
* **Implementation**:
    + Common `IPaymentGateway` interface
    + Multiple concrete implementations
    + `PaymentDriverFactory` for dependency injection based on configuration
* **Benefits**:
    + **Open/Closed Principle implementation**
    + **Zero downtime provider switching**
    + **A/B testing capabilities**

### **Factory Pattern - Gateway Instantiation**

* **Purpose**: Centralize and standardize gateway creation logic
* **Implementation**:
    + `PaymentDriverFactory` - creates and caches gateway instances
    + Environment-aware configuration loading (prod/test)
* **Benefits**:
    + **Consistent initialization across the application**
    + **Instance caching for performance**
    + **Simplified dependency management**

## 📐 SOLID Principles Implementation

### **Single Responsibility Principle (SRP)**

Each class has one clear responsibility:

* `PaymentService`: Orchestrates payment operations and logging
* `CurlService`: Manages HTTP communications exclusively
* `LogService`: Dedicated to logging operations
* `KapitalBankRepository`: Handles Kapital Bank API integration
* `PaymentDriverFactory`: Creates and manages gateway instances
* `OrderController`: Handles HTTP requests/responses for orders

### **Open/Closed Principle (OCP)**

```php
// Closed for modification
interface IPaymentGateway {
    public function createOrder(int $amount, string $description, OrderTypeRid $orderTypeRid): CreateOrderResponseDto;
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto;
}

// Open for extension - add new providers without changing existing code
class StripeRepository implements IPaymentGateway {
    // New provider implementation
}

class PayPalRepository implements IPaymentGateway {
    // Another provider implementation
}
```

### **Liskov Substitution Principle (LSP)**

All `IPaymentGateway` implementations are interchangeable:

```php
// Any gateway can be substituted without breaking the system
$gateway = $factory->driver('kapitalbank'); // or 'stripe', 'paypal', etc.
$response = $gateway->createOrder($amount, $description, OrderTypeRid::Purchase);
```

### **Interface Segregation Principle (ISP)**

Focused interfaces prevent unnecessary dependencies:

* `IPaymentGateway` - only payment-related operations
* `ILogger` - only logging operations
* No client forced to depend on methods it doesn't use

### **Dependency Inversion Principle (DIP)**

High-level modules depend on abstractions, not concrete implementations:

```php
class PaymentService {
    public function __construct(
        private readonly ILogger $logService,                    // Abstraction
        private readonly PaymentDriverFactory $paymentDriverFactory // Factory
    ) {}
}

class OrderController {
    public function __construct(
        private readonly PaymentService $paymentService // Service abstraction
    ) {}
}
```

## 🚀 Features

* ✅ **Multi-provider support** - Easy integration with multiple payment gateways
* ✅ **Environment-aware** - Separate configurations for production and testing
* ✅ **Type-safe** - Full DTO implementation with strict typing
* ✅ **Comprehensive logging** - Automatic logging of all payment operations
* ✅ **Error handling** - Custom exceptions with detailed error messages
* ✅ **Rate limiting** - Built-in API throttling protection
* ✅ **RESTful API** - Clean, standardized endpoints
* ✅ **Secure** - Basic authentication, SSL verification, proper credential handling

## 📋 Requirements

* PHP 8.1+
* Laravel 10.x
* cURL extension enabled
* Composer
* PHPUnit (for testing)

## 📦 Installation

### 1. Clone the repository

```bash
git clone https://github.com/aliyev-elmar/payment-implementation.git
cd payment-implementation
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment variables

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Add payment gateway credentials to `.env`

```env
# Default payment driver
PAYMENT_DEFAULT_DRIVER=kapitalbank

# Kapital Bank - Production
KAPITAL_BANK_PROD_API=https://api.kapitalbank.az/api/v1/orders
KAPITAL_BANK_PROD_USER=your_prod_username
KAPITAL_BANK_PROD_PASS=your_prod_password
KAPITAL_BANK_PROD_REDIRECT_URL=https://yoursite.com/payment/callback

# Kapital Bank - Test
KAPITAL_BANK_TEST_API=https://test-api.kapitalbank.az/api/v1/orders
KAPITAL_BANK_TEST_USER=your_test_username
KAPITAL_BANK_TEST_PASS=your_test_password
KAPITAL_BANK_TEST_REDIRECT_URL=https://test.yoursite.com/payment/callback
```

### 5. Set up storage permissions

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 🧪 Testing

### Overview

This project includes a testing infrastructure to ensure payment operations work correctly and reliably. The test suite covers API endpoints, validation rules, and service layer functionality.

### Running Tests

**Run all tests:**
```bash
php artisan test
```

**Run with detailed output:**
```bash
php artisan test --verbose
```

**Run specific test suites:**
```bash
# Feature tests only (API endpoints)
php artisan test --testsuite=Feature

# Unit tests only (services, repositories)
php artisan test --testsuite=Unit
```

**Run specific test files:**
```bash
php artisan test tests/Feature/OrderTest.php
php artisan test tests/Unit/PaymentServiceTest.php
```

**Generate code coverage report:**
```bash
php artisan test --coverage
```

**Filter tests by name:**
```bash
php artisan test --filter=test_can_create_order
```

### Test Structure

The project uses PHPUnit with Laravel's testing framework:

```
tests/
├── Feature/
│   └── OrderControllerTest.php         # Tests API endpoints (order creation & status)
├── Unit/
│   └── Services/
│       ├── CurlServiceTest.php         # Tests HTTP client functionality
│       └── PaymentDriverFactoryTest.php # Tests gateway factory and configuration
└── TestCase.php                         # Base test configuration
```

**Current Test Coverage:**
- ✅ **OrderControllerTest** (13 tests) - Validates API endpoints, request validation, error handling, and rate limiting
- ✅ **CurlServiceTest** (5 tests) - Tests HTTP POST/GET requests and error handling
- ✅ **PaymentDriverFactoryTest** (5 tests) - Validates driver instantiation, configuration, and caching

**Total: 23 tests** ensuring core functionality works as expected.

### Testing Best Practices

#### 1. **Mock External API Calls**

Never make real API calls in tests. Use Laravel's HTTP facade to mock responses:

```php
use Illuminate\Support\Facades\Http;

public function test_creates_order_successfully()
{
    Http::fake([
        'https://api.kapitalbank.az/*' => Http::response([
            'id' => 12345,
            'formUrl' => 'https://hpp.kapitalbank.az?id=ORDER123',
            'status' => 'Preparing'
        ], 200)
    ]);

    $response = $this->postJson('/api/orders', [
        'amount' => 10000,
        'description' => 'Test payment'
    ]);

    $response->assertStatus(201);
}
```

#### 2. **Test Validation Rules**

Ensure all validation rules are tested:

```php
public function test_rejects_invalid_amount()
{
    $response = $this->postJson('/api/orders', [
        'amount' => 50, // Below minimum of 100
        'description' => 'Test'
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['amount']);
}
```

#### 3. **Test Error Handling**

Verify that exceptions are handled properly:

```php
public function test_handles_gateway_errors_gracefully()
{
    Http::fake([
        'https://api.kapitalbank.az/*' => Http::response([
            'errorCode' => 400,
            'errorDescription' => 'Invalid credentials'
        ], 400)
    ]);

    $response = $this->postJson('/api/orders', [
        'amount' => 10000,
        'description' => 'Test'
    ]);

    $response->assertStatus(400)
             ->assertJson(['message' => 'Payment gateway error']);
}
```

#### 4. **Test Rate Limiting**

Ensure rate limits are enforced:

```php
public function test_rate_limit_blocks_excessive_requests()
{
    for ($i = 0; $i < 11; $i++) {
        $response = $this->postJson('/api/orders', [
            'amount' => 10000,
            'description' => 'Test'
        ]);
    }

    $response->assertStatus(429); // Too Many Requests
}
```

### Writing Tests for New Payment Providers

When adding a new payment provider, create tests following this pattern:

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PaymentService;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class StripeOrderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_create_stripe_order_successfully()
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('createOrder')
            ->once()
            ->with('stripe', Mockery::any(), 10000, 'Test payment')
            ->andReturn('https://checkout.stripe.com/pay/cs_test_123');

        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/api/orders', [
            'amount' => 10000,
            'description' => 'Test payment'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['formUrl']);
    }

    #[Test]
    public function it_retrieves_stripe_order_status()
    {
        // Create mock for status check
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        // Add your status check mock logic
        
        $response = $this->getJson('/api/orders/12345/simple-status');
        $response->assertStatus(200);
    }
}
```

### Continuous Integration

You can set up automated testing using GitHub Actions:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: curl, mbstring
        
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Run tests
      run: php artisan test --coverage
```

### Test Coverage Goals

Maintain high test coverage for critical components:

- **API Controllers**: 90%+ coverage (currently: OrderControllerTest covers all endpoints)
- **Services**: 85%+ coverage (CurlService and PaymentDriverFactory tested)
- **Core Logic**: PaymentService and LogService (recommended to add tests)
- **Repositories**: Gateway implementations (recommended to add KapitalBankRepository tests)

**To expand test coverage, consider adding:**
- `tests/Unit/Services/PaymentServiceTest.php` - Test order creation and status orchestration
- `tests/Unit/Services/LogServiceTest.php` - Test logging functionality
- `tests/Unit/Repositories/KapitalBankRepositoryTest.php` - Test API integration logic
- `tests/Unit/DTOs/*Test.php` - Test data transfer object validation

### Debugging Tests

**Enable verbose output:**
```bash
php artisan test -vvv
```

**Stop on first failure:**
```bash
php artisan test --stop-on-failure
```

**Show detailed error traces:**
```bash
php artisan test --debug
```

## 🔌 API Endpoints

### Create Order

**POST** `/api/orders`

Creates a new payment order and returns a payment form URL.

**Rate Limit:** 10 requests per minute

**Request Body:**

```json
{
  "amount": 10000,
  "description": "Payment for Order #12345"
}
```

**Validation Rules:**

* `amount`: Required, integer, minimum 100 (in cents/qapik)
* `description`: Optional, string, maximum 255 characters

**Success Response (201 Created):**

```json
{
  "formUrl": "https://hpp.kapitalbank.az?id=ORDER123&password=SECRET"
}
```

**Error Responses:**

*Payment Gateway Error (4xx/5xx):*

```json
{
  "message": "Payment gateway error",
  "details": "errorCode: 400, errorDescription: Invalid amount on Kapital Bank"
}
```

*Internal Server Error (500):*

```json
{
  "message": "Internal server error during order creation"
}
```

### Get Order Status

**GET** `/api/orders/{orderId}/simple-status`

Retrieves the current status of a payment order.

**Rate Limit:** 30 requests per minute

**Path Parameters:**

* `orderId` (integer): The order ID returned from create order

**Success Response (200 OK):**

```json
{
  "order": {
    "id": 12345,
    "typeRid": "Order_SMS",
    "status": "FullyPaid",
    "lastStatusLogin": "2025-12-05T14:30:00Z",
    "amount": 10000,
    "currency": "AZN",
    "type": {
      "title": "Purchase"
    }
  }
}
```

**Error Responses:**

*Order Not Found (404):*

```json
{
  "message": "Order not found on Kapital Bank"
}
```

*Payment Gateway Error (4xx/5xx):*

```json
{
  "message": "Payment gateway error",
  "details": "errorCode: 404, errorDescription: Order not found on Kapital Bank"
}
```

## 🔧 Configuration

### Adding a New Payment Provider

#### 1. Create the Repository Class

```php
<?php

namespace App\Repositories\Payment;

use App\Contracts\IPaymentGateway;
use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\{CreateOrderDto, CreateOrderResponseDto};
use App\DataTransferObjects\Payment\Order\SimpleStatus\{SimpleStatusDto, SimpleStatusResponseDto};

class StripeRepository implements IPaymentGateway
{
    public function __construct(
        private readonly CurlService $curlService,
        string $apiUrl,
        string $apiKey,
        // ... other dependencies
    ) {
        // Initialize
    }

    public function createOrder(int $amount, string $description, OrderTypeRid $orderTypeRid): CreateOrderResponseDto
    {
        // Implementation
    }

    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto
    {
        // Implementation
    }
}
```

#### 2. Add Configuration

Update `config/payment.php`:

```php
'drivers' => [
    'kapitalbank' => [...],
    
    'stripe' => [
        'prod' => [
            'api' => env('STRIPE_PROD_API'),
            'api_key' => env('STRIPE_PROD_API_KEY'),
        ],
        'test' => [
            'api' => env('STRIPE_TEST_API'),
            'api_key' => env('STRIPE_TEST_API_KEY'),
        ],
    ],
],

'map' => [
    'kapitalbank' => \App\Repositories\Payment\KapitalBankRepository::class,
    'stripe' => \App\Repositories\Payment\StripeRepository::class,
],
```

#### 3. Update Environment Variables

```env
PAYMENT_DEFAULT_DRIVER=stripe

STRIPE_PROD_API=https://api.stripe.com/v1
STRIPE_PROD_API_KEY=sk_live_...

STRIPE_TEST_API=https://api.stripe.com/v1
STRIPE_TEST_API_KEY=sk_test_...
```

#### 4. Use the New Provider

```php
// Automatically uses the driver from PAYMENT_DEFAULT_DRIVER
$formUrl = $this->paymentService->createOrder(
    config('payment.default_driver'),
    $amount,
    $description,
    OrderTypeRid::Purchase
);

// Or specify explicitly
$formUrl = $this->paymentService->createOrder(
    'stripe',
    $amount,
    $description,
    OrderTypeRid::Purchase
);
```

## 📊 Order Statuses

The system supports the following order statuses (defined in `OrderStatus` enum):

| Status | Description |
| --- | --- |
| `FullyPaid` | Payment completed successfully |
| `Preparing` | Order is being prepared/initialized |
| `Expired` | Payment session expired |
| `Declined` | Payment was declined |

## 🔐 Security Features

* **Basic Authentication**: API credentials encoded in Base64
* **SSL Verification**: Enforced SSL certificate validation
* **Rate Limiting**: Prevents abuse with throttling
* **Input Validation**: All inputs validated through form requests
* **Exception Handling**: Safe error messages without exposing sensitive data
* **Environment Separation**: Separate credentials for production and testing

## 📝 Logging

All payment operations are automatically logged to organized folders:

```
storage/logs/Payment/
├── kapitalbank/
│   ├── CreateOrder/
│   │   ├── Order_SMS/
│   │   │   └── 2025-12-05.log
│   │   └── Order_DMS/
│   │       └── 2025-12-05.log
│   └── GetSimpleStatus/
│       └── 2025-12-05.log
```

**Log Entry Format:**

```
2025-12-05 14:30:00 : OrderId : 12345, httpCode : 200, Curl Error : null, Curl Errno : null, status : FullyPaid
```

## 🎯 Usage Examples

### Creating an Order

```php
use App\Services\PaymentService;
use App\Enums\Payment\Order\OrderTypeRid;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}
    
    public function processPayment(Request $request)
    {
        try {
            $formUrl = $this->paymentService->createOrder(
                driver: 'kapitalbank',
                amount: 50000, // 500.00 AZN
                description: 'Order #' . $request->order_id,
                orderTypeRid: OrderTypeRid::Purchase
            );
            
            return redirect($formUrl);
        } catch (PaymentGatewayException $e) {
            return back()->with('error', 'Payment initialization failed');
        }
    }
}
```

### Checking Order Status

```php
use App\Services\PaymentService;

class PaymentCallbackController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}
    
    public function callback(Request $request)
    {
        $orderId = $request->input('order_id');
        
        try {
            $status = $this->paymentService->getSimpleStatusByOrderId(
                'kapitalbank',
                $orderId
            );
            
            if ($status->order->status === 'FullyPaid') {
                // Process successful payment
                return view('payment.success');
            }
            
            return view('payment.failed');
        } catch (OrderNotFoundException $e) {
            return view('payment.not-found');
        }
    }
}
```

## 🏗️ Project Structure

```
app/
├── Contracts/
│   ├── ILogger.php                     # Logging interface
│   └── IPaymentGateway.php             # Payment gateway interface
├── DataTransferObjects/
│   ├── Dto.php                         # Base DTO class
│   ├── CurlResponseDto.php             # HTTP response wrapper
│   └── Payment/
│       └── Order/
│           ├── CreateOrderDto.php      # Order creation data
│           ├── CreateOrderResponseDto.php
│           └── SimpleStatus/
│               ├── SimpleStatusDto.php
│               ├── SimpleStatusResponseDto.php
│               └── SimpleStatusType.php
├── Enums/
│   └── Payment/
│       └── Order/
│           ├── OrderStatus.php         # Order status enum
│           └── OrderTypeRid.php        # Order type enum
├── Exceptions/
│   ├── OrderNotFoundException.php      # Order not found exception
│   └── PaymentGatewayException.php     # Gateway error exception
├── Http/
│   ├── Controllers/
│   │   └── OrderController.php         # API endpoints
│   └── Requests/
│       └── Order/
│           └── StoreRequest.php        # Validation rules
├── Repositories/
│   └── Payment/
│       └── KapitalBankRepository.php   # Kapital Bank implementation
└── Services/
    ├── CurlService.php                 # HTTP client
    ├── LogService.php                  # Logging service
    ├── PaymentDriverFactory.php        # Gateway factory
    └── PaymentService.php              # Payment orchestration

config/
└── payment.php                          # Payment configuration

routes/
└── api.php                              # API routes

tests/
├── Feature/
│   └── Payment/                         # API integration tests
└── Unit/
    ├── Services/                        # Service layer tests
    ├── Repositories/                    # Repository tests
    └── DTOs/                            # Data transfer object tests
```

## 🐛 Troubleshooting

### Common Issues

**Issue: "Unsupported payment driver" error**

* **Solution**: Ensure the driver is added to `config/payment.php` in both `drivers` and `map` arrays

**Issue: "Missing configuration for driver" error**

* **Solution**: Check that environment variables are set correctly and match the config structure

**Issue: cURL timeout errors**

* **Solution**: Increase timeout in `CurlService` or check network connectivity

**Issue: SSL certificate verification fails**

* **Solution**: Ensure SSL certificates are up to date. For development only, you can disable verification (not recommended for production)

**Issue: Rate limit exceeded**

* **Solution**: Wait for the rate limit window to reset or adjust throttle settings in routes

**Issue: Tests failing with "connection refused"**

* **Solution**: Ensure you're mocking external API calls using `Http::fake()` in your tests. Never make real API calls during testing.

## ⚡ Performance Considerations

* **Gateway Instance Caching**: Factory pattern caches gateway instances per request to avoid redundant initialization
* **Connection Reuse**: cURL service is configured to reuse connections when possible
* **Rate Limiting**: Protects both your application and payment provider APIs from abuse
* **Logging Optimization**: Logs are written to daily files to prevent large file sizes

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Follow PSR-12 coding standards
4. **Write tests for new features** - All new code should include tests
5. Ensure all tests pass (`php artisan test`)
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

### Code Quality Standards

* Follow PSR-12 coding style
* Maintain minimum 80% test coverage
* Document all public methods with PHPDoc
* Use type hints for all parameters and return types
* Write descriptive commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

**Key Points:**
- ✅ Free to use for commercial purposes
- ✅ Modify and distribute as needed
- ✅ Private use allowed
- ⚠️ Must include original copyright notice
- ❌ No warranty provided

Copyright © 2025 [Elmar Aliyev](https://github.com/aliyev-elmar)

## 👥 Author

**Elmar Aliyev** - [@aliyev-elmar](https://github.com/aliyev-elmar)

## 🙏 Acknowledgments

* Laravel framework for providing excellent tools and structure
* Kapital Bank for API documentation

## 📚 Additional Resources

* Kapital Bank API Documentation: https://pg.kapitalbank.az/docs
