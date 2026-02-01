<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionService
{
    public function createTransaction(array $data): Transaction
    {
        // return existing transaction if idempotency_key already existed
        if (!empty($data['idempotency_key'])) {
            $existing = Transaction::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
        }

        $seller = Seller::findOrFail($data['seller_id']);

        $tier     = strtolower($seller->tier);
        $provider = strtolower($data['payment_provider']);
        $currency = strtoupper($data['currency']);

        $breakdown = $this->calculate((int) $data['amount'], $currency, $tier, $provider);

        return Transaction::create([
            'seller_id'            => $seller->id,
            'customer_id'          => $data['customer_id'],
            'idempotency_key'      => $data['idempotency_key'] ?? Str::uuid(),
            'payment_provider'     => $provider,

            'currency'             => 'USD',
            'gross_amount'         => $breakdown['gross_amount'],
            'payment_provider_fee' => $breakdown['payment_provider_fee'],
            'commission_rate'      => $breakdown['commission_rate'],
            'commission_amount'    => $breakdown['commission_amount'],
            'net_amount'           => $breakdown['net_amount'],

            'status'               => 'completed',
        ]);
    }

    public function calculate(
        int $amountCents,
        string $currency,
        string $sellerTier,
        string $paymentProvider
    ): array {
        $grossUsd = $this->convertToUsdCents($amountCents, $currency);

        $commissionRate = $this->getCommissionRate($sellerTier);
        $providerFee    = $this->getProviderFee($grossUsd, $paymentProvider);

        $commission = (int) round($grossUsd * $commissionRate);
        $net        = $grossUsd - $commission - $providerFee;

        return [
            'gross_amount'         => $grossUsd,
            'commission_rate'      => $commissionRate,
            'commission_amount'    => $commission,
            'payment_provider_fee' => $providerFee,
            'net_amount'           => $net,
            'currency'             => 'USD',
        ];
    }

    private function getCommissionRate(string $tier): float
    {
        return match (strtolower($tier)) {
            'starter'    => 0.10,
            'pro'        => 0.07,
            'enterprise' => 0.05,
            default      => 0.10,
        };
    }

    private function getProviderFee(int $amountUsdCents, string $provider): int
    {
        return match (strtolower($provider)) {
            'stripe' => (int) round($amountUsdCents * 0.029) + 30,
            'paypal' => (int) round($amountUsdCents * 0.034) + 35,
            'ideal'  => 0,
            default  => 0,
        };
    }

    private function convertToUsdCents(int $amountCents, string $currency): int
    {
        $rates = [
            'USD' => 1.00,
            'GBP' => 0.73,
            'EUR' => 0.85,
        ];

        $currency = strtoupper($currency);
        $rate = $rates[$currency] ?? 1.00; //if currency not available assume 1 exchange-rate.

        return (int) round($amountCents / $rate);
    }
}
