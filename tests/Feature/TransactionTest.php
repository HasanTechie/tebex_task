<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = '/api/v1/transactions';

    public function test_it_creates_transaction_with_correct_calculations(): void
    {
        $seller = Seller::factory()->create(['tier' => 'pro']);
        $customer = Customer::factory()->create();

        $payload = [
            'seller_id'       => $seller->id,
            'customer_id'     => $customer->id,
            'amount'          => 10000,
            'currency'        => 'USD',
            'payment_provider' => 'stripe',
            'idempotency_key' => 'unique-key-123',
        ];

        $this->postJson(self::BASE_URL, $payload)
            ->assertCreated()
            ->assertJsonPath('data.commission_rate', 0.07); //pro commission rate

        $this->assertDatabaseHas('transactions', [
            'seller_id'            => $seller->id,
            'customer_id'            => $customer->id,
            'idempotency_key'      => 'unique-key-123',
            'gross_amount'         => 10000,
            'payment_provider_fee' => 320,
            'commission_amount'    => 700,
            'net_amount'           => 8980,
            'status'               => 'completed',
        ]);
    }

    public function test_it_is_idempotent(): void
    {
        $seller = Seller::factory()->create();
        $payload = [
            'seller_id'       => $seller->id,
            'customer_id'     => Customer::factory()->create()->id,
            'amount'          => 10000,
            'currency'        => 'USD',
            'payment_provider' => 'stripe',
            'idempotency_key' => 'same-key-123',
        ];

        // First request
        $res1 = $this->postJson(self::BASE_URL, $payload)->assertCreated();
        $id1  = $res1->json('data.transaction_id');

        // Second request (same key)
        $res2 = $this->postJson(self::BASE_URL, $payload)->assertCreated();
        $id2  = $res2->json('data.transaction_id');

        $this->assertEquals($id1, $id2);
        $this->assertEquals(1, Transaction::where('idempotency_key', 'same-key-123')->count());
    }

    public function test_it_can_retrieve_a_transaction_by_public_id(): void
    {
        $seller = Seller::factory()->create();
        $customer = Customer::factory()->create();

        // Create initial transaction
        $response = $this->postJson(self::BASE_URL, [
            'seller_id'       => $seller->id,
            'customer_id'     => $customer->id,
            'amount'          => 10000,
            'currency'        => 'USD',
            'payment_provider' => 'stripe',
            'idempotency_key' => 'unique-key-123',
        ]);

        $publicId = $response->json('data.transaction_id');

        // Test GET endpoint
        $this->getJson(self::BASE_URL . "/{$publicId}")
            ->assertOk()
            ->assertJsonFragment([
                'transaction_id' => $publicId,
                'seller_id'       => $seller->id,
                'customer_id'     => $customer->id,
                'gross_amount'   => 10000,
            ]);
    }
}
