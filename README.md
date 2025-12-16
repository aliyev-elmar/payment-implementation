# PHP/Laravel Payment Implementation Project

A robust Laravel payment gateway system implementing modern design patterns and SOLID principles, designed for seamless payment provider integrations with extensible, maintainable codebase.

> **Note**: This project was built to support multiple payment systems simultaneously with a provider-agnostic architecture. Currently implemented with **Kapital Bank** as the reference integration, demonstrating the extensible design that allows easy addition of other payment providers.

## 🚀 Features

* ✅ **Multi-provider support** - Easy integration with multiple payment gateways
* ✅ **Provider-agnostic architecture** - Switch between payment providers seamlessly
* ✅ **Kapital Bank integration** - Full API integration (reference implementation)
* ✅ **Card Tokenization** - Secure card storage for recurring payments
* ✅ **Multiple Order Types** - Support for Purchase, PreAuth, RepeatPurchase, RepeatPreAuth, and CardToCard
* ✅ **Database Persistence** - Orders, tokens, and card details stored securely
* ✅ **Encrypted Storage** - Sensitive data (passwords, secrets) encrypted using Laravel Crypt
* ✅ **RESTful API** - Clean, standardized endpoints with rate limiting
* ✅ **Docker Support** - Complete containerized development environment
* ✅ **Laravel Pulse** - Real-time application monitoring
* ✅ **Comprehensive Logging** - Automatic logging of all payment operations
* ✅ **Error Handling** - Custom exceptions with detailed error messages

## 📋 Requirements

* PHP 8.1+
* Laravel 11.x
* MySQL 8.0+
* cURL extension enabled
* Composer

**OR use Docker** (recommended - includes all dependencies)

## 🐳 Docker Setup (Recommended)

This project includes a complete Docker configuration for easy local development.

### Docker Services

- **PHP 8.2-FPM** with Laravel application
- **MySQL 8.0** database
- **Nginx** web server
- **phpMyAdmin** for database management
- **Laravel Pulse** for real-time monitoring

### Quick Start with Docker

#### 1. Clone the repository

```bash
git clone https://github.com/aliyev-elmar/payment-implementation.git
cd payment-implementation
```

#### 2. Start Docker containers

```bash
docker-compose up -d --build
```

#### 3. Install dependencies

```bash
docker-compose exec app composer install
```

#### 4. Configure environment

```bash
# Copy .env.example to .env (if not exists)
docker-compose exec app cp .env.example .env

# Generate application key
docker-compose exec app php artisan key:generate
```

#### 5. Database setup

Ensure your `.env` has these settings:

```env
DB_CONNECTION=mysql
DB_HOST=db                              # Important: use 'db' not '127.0.0.1'
DB_PORT=3306
DB_DATABASE=payment_implementation_db
DB_USERNAME=payment_user
DB_PASSWORD=secret
```

Run migrations:

```bash
docker-compose exec app php artisan migrate
```

#### 6. Configure Payment Gateway

Add Kapital Bank credentials to `.env`:

```env
# Default payment driver
PAYMENT_DEFAULT_DRIVER=kapitalbank

# Kapital Bank - Production
KAPITAL_BANK_PROD_API=https://api.kapitalbank.az/api/v1/orders/
KAPITAL_BANK_PROD_USER=your_prod_username
KAPITAL_BANK_PROD_PASS=your_prod_password
KAPITAL_BANK_PROD_HPP_REDIRECT_URL=https://yoursite.com/payment/callback

# Kapital Bank - Test
KAPITAL_BANK_TEST_API=https://test-api.kapitalbank.az/api/v1/orders/
KAPITAL_BANK_TEST_USER=your_test_username
KAPITAL_BANK_TEST_PASS=your_test_password
KAPITAL_BANK_TEST_HPP_REDIRECT_URL=https://test.yoursite.com/payment/callback
```

#### 7. Access the application

- **Laravel Application**: http://localhost:8080
- **Laravel Pulse**: http://localhost:8080/pulse
- **phpMyAdmin**: http://localhost:8081
    - Username: `root` or `payment_user`
    - Password: `root` or `secret`

### Useful Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Access app container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan [command]

# Clear caches
docker-compose exec app php artisan optimize:clear

# Rebuild containers
docker-compose down && docker-compose up -d --build
```

## 📦 Installation (Without Docker)

### 1. Clone and install

```bash
git clone https://github.com/aliyev-elmar/payment-implementation.git
cd payment-implementation
composer install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database setup

Configure your database in `.env` and run:

```bash
php artisan migrate
```

### 4. Add payment credentials

Update `.env` with Kapital Bank credentials (see Docker setup step 6).

## 🔌 API Endpoints

### 1. Create Order

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

* `amount`: Required, integer, minimum 100 (in qapik)
* `description`: Optional, string, maximum 255 characters

**Success Response (201 Created):**

```json
{
  "success": true,
  "data": {
    "formUrl": "https://hpp.kapitalbank.az?id=ORDER123&password=SECRET"
  }
}
```

**Error Response:**

```json
{
  "success": false,
  "message": "Error description"
}
```

### 2. Set Source Token (Card Tokenization)

**POST** `/api/orders/{orderId}/set-src-token`

Saves card information as a token for recurring payments.

**Rate Limit:** 10 requests per minute

**Path Parameters:**

* `orderId` (integer): The order ID from Kapital Bank

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "order": {
      "status": "FullyPaid",
      "cvv2AuthStatus": "Successful",
      "srcToken": {
        "id": "token_123",
        "paymentMethod": "Card",
        "role": "Purchase",
        "status": "Active",
        "regTime": "2025-12-15T10:30:00Z",
        "displayName": "•••• 1234",
        "card": {
          "expiration": "12/26",
          "brand": "VISA"
        }
      }
    }
  }
}
```

### 3. Get Order Status

**GET** `/api/orders/{orderId}/simple-status`

Retrieves the current status of a payment order.

**Rate Limit:** 30 requests per minute

**Path Parameters:**

* `orderId` (integer): The order ID from Kapital Bank

**Success Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "order": {
      "id": 12345,
      "typeRid": "Order_SMS",
      "status": "FullyPaid",
      "prevStatus": "Preparing",
      "lastStatusLogin": "2025-12-15T14:30:00Z",
      "amount": 10000,
      "currency": "AZN",
      "createTime": "2025-12-15T14:25:00Z",
      "finishTime": "2025-12-15T14:30:00Z",
      "type": {
        "title": "Purchase"
      }
    }
  }
}
```

## 📊 Order Types

The system supports the following order types (defined in `OrderTypeRid` enum):

| Type | Value | Description |
|------|-------|-------------|
| **Purchase** | `Order_SMS` | Standard purchase transactions |
| **PreAuth** | `Order_DMS` | Pre-authorization (hold funds) |
| **RepeatPurchase** | `Order_REC` | Recurring purchases with saved cards |
| **RepeatPreAuth** | `DMSN3D` | Recurring pre-auth with saved cards |
| **CardToCard** | `OCT` | Card-to-card transfer (OCT) |

## 🏗️ Architecture

### Project Structure

```
app/
├── Contracts/
│   └── IPaymentGateway.php             # Payment gateway interface
├── DataTransferObjects/
│   └── Payment/
│       └── Order/
│           ├── CreateOrderDto.php      # Order creation DTO
│           ├── CreateOrderResponseDto.php
│           ├── SetSourceToken/         # Token-related DTOs
│           └── SimpleStatus/           # Status-related DTOs
├── Enums/
│   └── Payment/
│       ├── Currency.php                # AZN currency
│       ├── Language.php                # az language
│       ├── ErrorCode.php               # API error codes
│       └── Order/
│           ├── OrderTypeRid.php        # Order types
│           └── InitiationEnvKind.php   # MIT/CIT types
├── Exceptions/
│   ├── InvalidRequestException.php
│   ├── InvalidTokenException.php
│   ├── InvalidOrderStateException.php
│   └── OrderNotFoundException.php
├── Http/
│   ├── Controllers/
│   │   └── OrderController.php         # API endpoints
│   └── Requests/
│       └── Order/
│           └── StoreRequest.php        # Validation
├── Models/
│   ├── Order.php                       # Orders table
│   ├── OrderSourceToken.php            # Saved tokens
│   └── OrderSourceTokenCard.php        # Card details
├── Repositories/
│   ├── OrderRepository.php
│   ├── OrderSourceTokenRepository.php
│   ├── OrderSourceTokenCardRepository.php
│   └── PaymentGateways/
│       └── KapitalBankRepository.php   # Kapital Bank API
├── Services/
│   ├── OrderService.php                # Order orchestration
│   ├── PaymentService.php              # Payment gateway service
│   ├── PaymentDriverFactory.php        # Gateway factory
│   └── CurlService.php                 # HTTP client
└── Traits/
    └── Logger.php                       # Logging trait

config/
└── payment.php                          # Payment configuration

database/
└── migrations/                          # Database migrations

docker/
└── nginx/
    └── default.conf                     # Nginx config

routes/
└── api.php                              # API routes
```

### Design Patterns

#### 1. **Repository Pattern**
- Abstracts data layer operations
- `OrderRepository`, `OrderSourceTokenRepository`, `OrderSourceTokenCardRepository`
- Clean separation between business logic and data access

#### 2. **Service Pattern**
- `OrderService` - Orchestrates order operations with transaction management
- `PaymentService` - Manages payment gateway interactions
- `CurlService` - HTTP operations abstraction

#### 3. **Factory Pattern**
- `PaymentDriverFactory` - Creates and manages gateway instances
- Environment-aware (production/test configuration)
- Instance caching for performance

#### 4. **DTO Pattern**
- Type-safe data transfer between layers
- `CreateOrderDto`, `SetSourceTokenDto`, `SimpleStatusDto`
- Ensures data integrity and predictable structures

### Database Schema

**orders** table:
- `id` - Primary key
- `external_id` - Kapital Bank order ID
- `hpp_url` - Payment page URL
- `password` - Encrypted order password
- `status` - Order status
- `cvv2_auth_status` - CVV2 authentication status
- `secret` - Encrypted order secret

**order_source_tokens** table:
- `id` - Primary key
- `order_id` - Foreign key to orders
- `external_id` - Token ID from Kapital Bank
- `payment_method` - Payment method type
- `role` - Token role (Purchase/PreAuth)
- `status` - Token status
- `reg_time` - Registration timestamp
- `display_name` - Masked card number

**order_source_token_cards** table:
- `id` - Primary key
- `order_source_token_id` - Foreign key
- `expiration` - Card expiration date
- `brand` - Card brand (VISA, MasterCard, etc.)

## 🔐 Security Features

* **Encryption** - Sensitive data (passwords, secrets) encrypted using Laravel Crypt
* **Basic Authentication** - API credentials encoded in Base64
* **SSL Verification** - Enforced SSL certificate validation
* **Rate Limiting** - API throttling (10 req/min for orders, 30 req/min for status)
* **Input Validation** - All inputs validated through form requests
* **Transaction Management** - Database transactions ensure data consistency
* **Exception Handling** - Safe error messages without exposing sensitive data

## 📝 Logging

All payment operations are automatically logged:

```
storage/logs/Payment/KapitalBank/
├── CreateOrder/
│   ├── Order_SMS/
│   │   └── 2025-12-15.log
│   └── Order_DMS/
│       └── 2025-12-15.log
├── SetSourceToken/
│   └── 2025-12-15.log
└── GetSimpleStatus/
    └── 2025-12-15.log
```

**Log Entry Format:**

```
[2025-12-15 14:30:00] response: {...}, httpCode: 200, curlError: null, curlErrno: null
```

## 🎯 Usage Examples

### Creating an Order

```php
use App\Services\OrderService;
use App\Enums\Payment\Order\OrderTypeRid;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}
    
    public function processPayment(Request $request)
    {
        try {
            $formUrl = $this->orderService->create(
                driver: 'kapitalbank',
                orderTypeRid: OrderTypeRid::Purchase,
                amount: 50000, // 500.00 AZN
                description: 'Order #' . $request->order_id
            );
            
            return redirect($formUrl);
        } catch (InvalidRequestException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

### Saving Card Token

```php
use App\Services\OrderService;

class TokenController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}
    
    public function saveToken(Request $request)
    {
        try {
            $response = $this->orderService->setSourceToken(
                driver: 'kapitalbank',
                orderId: $request->order_id
            );
            
            // Token saved, card details available in $response->order->srcToken
            return view('payment.token-saved', [
                'card' => $response->order->srcToken->displayName
            ]);
        } catch (InvalidTokenException $e) {
            return back()->with('error', 'Invalid token');
        }
    }
}
```

### Checking Order Status

```php
use App\Services\OrderService;

class CallbackController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}
    
    public function callback(Request $request)
    {
        try {
            $response = $this->orderService->getSimpleStatusByOrderId(
                driver: 'kapitalbank',
                orderId: $request->order_id
            );
            
            if ($response->order->status === 'FullyPaid') {
                // Process successful payment
                return view('payment.success');
            }
            
            return view('payment.pending');
        } catch (OrderNotFoundException $e) {
            return view('payment.not-found');
        }
    }
}
```

## 🔧 Configuration

### Payment Configuration

The `config/payment.php` file contains:

```php
return [
    'default_driver' => env('PAYMENT_DEFAULT_DRIVER', 'kapitalbank'),
    
    'drivers' => [
        'kapitalbank' => [
            'prod' => [
                'api' => env('KAPITAL_BANK_PROD_API'),
                'user' => env('KAPITAL_BANK_PROD_USER'),
                'pass' => env('KAPITAL_BANK_PROD_PASS'),
                'hpp_redirect_url' => env('KAPITAL_BANK_PROD_HPP_REDIRECT_URL'),
            ],
            'test' => [
                'api' => env('KAPITAL_BANK_TEST_API'),
                'user' => env('KAPITAL_BANK_TEST_USER'),
                'pass' => env('KAPITAL_BANK_TEST_PASS'),
                'hpp_redirect_url' => env('KAPITAL_BANK_TEST_HPP_REDIRECT_URL'),
            ],
        ],
    ],
    
    'map' => [
        'kapitalbank' => \App\Repositories\PaymentGateways\KapitalBankRepository::class,
    ],
];
```

### Adding a New Payment Provider

To add a new payment provider:

1. **Create Repository Class**

```php
namespace App\Repositories\PaymentGateways;

use App\Contracts\IPaymentGateway;

class StripeRepository implements IPaymentGateway
{
    public function createOrder(OrderTypeRid $orderTypeRid, int $amount, string $description): CreateOrderResponseDto
    {
        // Implementation
    }
    
    public function setSourceToken(int $orderId, string $orderPassword): SetSourceTokenResponseDto
    {
        // Implementation
    }
    
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto
    {
        // Implementation
    }
}
```

2. **Add to Configuration**

Update `config/payment.php`:

```php
'drivers' => [
    'kapitalbank' => [...],
    'stripe' => [
        'prod' => ['api_key' => env('STRIPE_PROD_KEY')],
        'test' => ['api_key' => env('STRIPE_TEST_KEY')],
    ],
],

'map' => [
    'kapitalbank' => \App\Repositories\PaymentGateways\KapitalBankRepository::class,
    'stripe' => \App\Repositories\PaymentGateways\StripeRepository::class,
],
```

3. **Update Environment**

```env
PAYMENT_DEFAULT_DRIVER=stripe
STRIPE_PROD_KEY=sk_live_...
STRIPE_TEST_KEY=sk_test_...
```

## 🐛 Troubleshooting

### Common Issues

**Database Connection Refused:**
- Docker: Ensure `DB_HOST=db` in `.env`
- Check containers: `docker-compose ps`
- Restart: `docker-compose restart db`

**Permission Denied:**
```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

**504 Gateway Timeout:**
```bash
docker-compose restart nginx app
docker-compose logs nginx app
```

**Payment API Errors:**
- Verify credentials in `.env`
- Check logs: `storage/logs/Payment/KapitalBank/`
- Ensure `APP_ENV` is set correctly (production/local)

**Migration Issues:**
```bash
docker-compose exec app php artisan migrate:fresh
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Follow PSR-12 coding standards
4. Write tests for new features (tests structure is ready in `tests/` folder)
5. Commit changes (`git commit -m 'Add new feature'`)
6. Push to branch (`git push origin feature/new-feature`)
7. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👤 Author

**Elmar Aliyev** - [@aliyev-elmar](https://github.com/aliyev-elmar)

## 📚 Additional Resources

* [Kapital Bank API Documentation](https://pg.kapitalbank.az/docs)
