<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'transaction_id' => $this->public_id,
            'seller_id' => (int) $this->seller_id,
            'customer_id' => (int) $this->customer_id,

            'gross_amount' => (int) $this->gross_amount,
            'currency' => (string) $this->currency,
            'payment_provider' => (string) $this->payment_provider,

            'payment_provider_fee' => (int) $this->payment_provider_fee,
            'commission_rate' => (float) $this->commission_rate,
            'commission_amount' => (int) $this->commission_amount,
            'net_amount' => (int) $this->net_amount,

            'status' => (string) $this->status,
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
