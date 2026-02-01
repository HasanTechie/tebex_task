<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Seller;
use App\Models\Transaction;

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

        dd($breakdown);
    }

    public function calculate(
        int $amountCents,
        string $currency,
        string $sellerTier,
        string $paymentProvider
    ): array {
        $grossUsd = $amountCents;

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
}
