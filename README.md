# PHP/Laravel Payment Implementation Project
A robust Laravel PHP payment gateway system implementing modern design patterns and SOLID principles, designed for seamless payment provider integrations with extensible, maintainable codebase.

Note: This project was built to support multiple payment systems simultaneously with a provider-agnostic architecture. Currently implemented with Kapital Bank as the reference integration, demonstrating the extensible design that allows easy addition of other payment providers.

📚 Documentation & Resources

Kapital Bank API Documentation: https://pg.kapitalbank.az/docs

## 🏗️ Architecture & Design Patterns

### **Repository Pattern - Payment Provider Abstraction**
- **Purpose**: Abstract data layer with provider-agnostic interface
- **Implementation**:
    - `IPaymentGateway` interface - unified payment operations contract
    - Concrete implementations for different payment providers (e.g., `KapitalBankRepository`)
- **Benefits**:
    - **Business logic completely isolated from provider specifics**
    - **Easy integration of new payment processors**
    - **Consistent API across multiple providers**

### **Service Pattern - Business Logic Layer**
- **Purpose**: Encapsulate payment operations and business rules
- **Implementation**:
    - `PaymentService` - orchestrates payment processes
    - `CurlService` - HTTP operations abstraction
    - `LogService` - centralized logging
    - `PaymentDriverFactory` - manages gateway instantiation
- **Benefits**:
    - **Single Responsibility Principle compliance**
    - **Reusable payment workflow logic**
    - **Clean separation of concerns**

### **DTO Pattern - Type-Safe Data Transfer**
- **Purpose**: Ensure data integrity between layers
- **Implementation**:
    - `CreateOrderDto`, `CreateOrderResponseDto` - order creation structures
    - `SimpleStatusDto`, `SimpleStatusResponseDto` - status check structures
    - `CurlResponseDto` - HTTP response wrapper
    - Type-hinted properties with clear contracts
- **Benefits**:
    - **Predictable data structures**
    - **Reduced runtime errors**
    - **Better IDE support and autocompletion**

### **Strategy Pattern - Interchangeable Providers**
- **Purpose**: Enable runtime provider selection and hot-swapping
- **Implementation**:
    - Common `IPaymentGateway` interface
    - Multiple concrete implementations
    - `PaymentDriverFactory` for dependency injection based on configuration
- **Benefits**:
    - **Open/Closed Principle implementation**
    - **Zero downtime provider switching**
    - **A/B testing capabilities**

### **Factory Pattern - Gateway Instantiation**
- **Purpose**: Centralize and standardize gateway creation logic
- **Implementation**:
    - `PaymentDriverFactory` - creates and caches gateway instances
    - Environment-aware configuration loading (prod/test)
- **Benefits**:
    - **Consistent initialization across the application**
    - **Instance caching for performance**
    - **Simplified dependency management**

## 📐 SOLID Principles Implementation

### **Single Responsibility Principle (SRP)**
Each class has one clear responsibility:
- `PaymentService`: Orchestrates payment operations and logging
- `CurlService`: Manages HTTP communications exclusively
- `LogService`: Dedicated to logging operations
- `KapitalBankRepository`: Handles Kapital Bank API integration
- `PaymentDriverFactory`: Creates and manages gateway instances
- `OrderController`: Handles HTTP requests/responses for orders

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
- `IPaymentGateway` - only payment-related operations
- `ILogger` - only logging operations
- No client forced to depend on methods it doesn't use

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

- ✅ **Multi-provider support** - Easy integration with multiple payment gateways
- ✅ **Environment-aware** - Separate configurations for production and testing
- ✅ **Type-safe** - Full DTO implementation with strict typing
- ✅ **Comprehensive logging** - Automatic logging of all payment operations
- ✅ **Error handling** - Custom exceptions with detailed error messages
- ✅ **Rate limiting** - Built-in API throttling protection
- ✅ **RESTful API** - Clean, standardized endpoints
- ✅ **Secure** - Basic authentication, SSL verification, proper credential handling

## 📋 Requirements

- PHP 8.1+
- Laravel 10.x
- cURL extension enabled
- Composer

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
- `amount`: Required, integer, minimum 100 (in cents/qapik)
- `description`: Optional, string, maximum 255 characters

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
  "details": "`errorCode: 400, errorDescription: Invalid amount` on Kapital Bank"
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
- `orderId` (integer): The order ID returned from create order

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
  "details": "`errorCode: 404, errorDescription: Order not found` on Kapital Bank"
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
|--------|-------------|
| `FullyPaid` | Payment completed successfully |
| `Preparing` | Order is being prepared/initialized |
| `Expired` | Payment session expired |
| `Declined` | Payment was declined |

## 🔐 Security Features

- **Basic Authentication**: API credentials encoded in Base64
- **SSL Verification**: Enforced SSL certificate validation
- **Rate Limiting**: Prevents abuse with throttling
- **Input Validation**: All inputs validated through form requests
- **Exception Handling**: Safe error messages without exposing sensitive data
- **Environment Separation**: Separate credentials for production and testing

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

## 🧪 Testing

### Manual Testing with cURL

**Create Order:**
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10000,
    "description": "Test payment"
  }'
```

**Get Order Status:**
```bash
curl -X GET http://localhost:8000/api/orders/12345/simple-status
```

### Unit Testing
```bash
php artisan test
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
```

## 🐛 Troubleshooting

### Common Issues

**Issue: "Unsupported payment driver" error**
- **Solution**: Ensure the driver is added to `config/payment.php` in both `drivers` and `map` arrays

**Issue: "Missing configuration for driver" error**
- **Solution**: Check that environment variables are set correctly and match the config structure

**Issue: cURL timeout errors**
- **Solution**: Increase timeout in `CurlService` or check network connectivity

**Issue: SSL certificate verification fails**
- **Solution**: Ensure SSL certificates are up to date. For development only, you can disable verification (not recommended for production)

**Issue: Rate limit exceeded**
- **Solution**: Wait for the rate limit window to reset or adjust throttle settings in routes

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Follow PSR-12 coding standards
4. Write tests for new features
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 👥 Author

**Elmar Aliyev** - [@aliyev-elmar](https://github.com/aliyev-elmar)

---

**Built with ❤️ using Laravel**
