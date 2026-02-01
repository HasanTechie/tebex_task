# Tebex PHP Technical Assessment

## About Tebex

Tebex is a leading fintech platform in the gaming industry, enabling game server owners and content creators to monetize their communities through payment processing, subscription management, and digital commerce solutions. We handle millions of transactions across multiple currencies and gaming platforms.

## Submission

Please download/clone this repository and submit your final code by emailing a .zip or by sharing a link to your own repository on GitHub/GitLab. Please avoid forking this repository through GitHub.

**Time Estimate:** This task is designed to take 3-5 hours, we ask that you focus on quality over speed.

## Task Overview

You are tasked with building a **Payment Commission Calculator** service that processes transactions for game server owners and game studios (partners) on the Tebex platform. This service needs to handle complex business logic, multiple payment providers, currency conversion, and ensure financial accuracy.

### Business Context

- Sellers receive payments from customers through various payment providers (Stripe, PayPal, etc.)
- Tebex charges a commission on each transaction (varies by seller tier and payment method)
- Transactions can be in multiple currencies (USD, EUR, GBP, etc.)
- Commission rates are tiered based on seller revenue thresholds
- All financial calculations must be precise (no floating-point errors)

## Part 1: Build the Payment Commission Calculator

**Build a Payment Commission Calculator** that:
   - Accepts transaction data (amount, currency, seller_id, payment_provider)
   - Calculates commission based on seller tier and payment method
   - Handles currency conversion (use a mock exchange rate service)
   - Returns transaction breakdown (gross_amount, commission, net_amount, currency)
   - Supports multiple payment providers (Stripe, PayPal, iDEAL) with different fee structures

### Commission Rules (Business Logic)

**Seller Tiers:**
- **Starter**: 10% commission on all transactions
- **Pro**: 7% commission on transactions
- **Enterprise**: 5% commission on transactions

**Payment Provider Fees:**
- **Stripe**: Additional 2.9% + $0.30 per transaction
- **PayPal**: Additional 3.4% + $0.35 per transaction  
- **iDEAL**: No additional fees (0%)

**Currency Conversion:**
- All commissions are calculated in the transaction currency
- Use a mock exchange rate service (you can hardcode rates or create a simple service)
- Base currency is USD

**Example Calculation:**
```
Transaction: $100 USD via Stripe, Pro seller
- Gross: $100.00
- Stripe Fee: $3.20 (2.9% + $0.30)
- Commission: $7.00 (7% of $100)
- Net to Seller: $89.80
```

### Technical Requirements
   - Create RESTful endpoints for:
     - `POST /api/v1/transactions` - Process a new transaction
     - `GET /api/v1/transactions/{id}` - Get transaction details
     - `GET /api/v1/sellers/{id}/commission-summary` - Get seller's commission summary

## Part 2: Testing

Write comprehensive tests demonstrating your knowledge of unit tests and feature tests.

## Example API Requests

### Process Transaction
```bash
POST /api/v1/transactions
Content-Type: application/json

{
  "seller_id": "seller_123",
  "amount": 10000, // Amount in cents
  "currency": "USD",
  "payment_provider": "stripe",
  "customer_id": "cust_456",
  "idempotency_key": "unique-key-123"
}
```

**Expected Response:**
```json
{
  "transaction_id": "txn_789",
  "seller_id": "seller_123",
  "gross_amount": 10000, // In cents
  "currency": "USD",
  "payment_provider": "stripe",
  "payment_provider_fee": 320, // In cents
  "commission_rate": 0.07,
  "commission_amount": 700, // In cents
  "net_amount": 8980,
  "status": "completed",
  "created_at": "2024-01-15T10:30:00Z"
}
```

### Get Transaction
```bash
GET /api/v1/transactions/txn_789
```

### Get Seller Commission Summary
```bash
GET /api/v1/sellers/seller_123/commission-summary?period=monthly
```

**Expected Response:**
```json
{
  "seller_id": "seller_123",
  "period": "2024-01",
  "total_transactions": 150,
  "total_gross_amount": 1500000, // In cents
  "total_commission": 105000, // In cents
  "total_net_amount": 1395000, // In cents
  "currency": "USD"
}
```

## Getting Started

1. Ensure you have PHP 8.2+ and Composer installed
2. Run `composer install`
3. Copy `.env.example` to `.env` - **Note: The default `.env.example` is pre-configured to use SQLite**, so no additional database setup is required. The SQLite database file will be created automatically at `database/database.sqlite` when you run migrations.
4. Run `php artisan key:generate`
5. Run `php artisan migrate` to set up the database tables (if implementing data persistence)
6. Run `php artisan serve` to start the development server
7. Start building!

## Questions?

If you have any questions about the requirements, please reach out. We're looking for your best work and want to ensure you have clarity on what we're asking for.

Good luck!
