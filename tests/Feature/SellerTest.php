<?php

namespace Tests\Feature\Api\V1;

use App\Models\Customer;
use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerTest extends TestCase
{
    use RefreshDatabase;

    private const TXN_URL = '/api/v1/transactions';

    private function summaryUrl(int $sellerId, string $period = 'monthly'): string
    {
        return "/api/v1/sellers/{$sellerId}/commission-summary?period={$period}";
    }

    public function test_commission_summary_groups_monthly_and_aggregates_totals_using_transactions_created_via_api(): void
    {
        $seller = Seller::factory()->create(['tier' => 'pro']);
        $customer = Customer::factory()->create();

        // Create 3 transactions through the API
        // We'll later set created_at into Jan/Feb to verify grouping.
        $payloadBase = [
            'seller_id' => $seller->id,
            'customer_id' => $customer->id,
            'currency' => 'USD',
            'payment_provider' => 'stripe',
        ];

        $r1 = $this->postJson(self::TXN_URL, $payloadBase + [
                'amount' => 10000,
                'idempotency_key' => 'key-jan-1',
            ])->assertCreated();

        $id1 = $r1->json('data.transaction_id');

        $r2 = $this->postJson(self::TXN_URL, $payloadBase + [
                'amount' => 5000,
                'idempotency_key' => 'key-jan-2',
            ])->assertCreated();

        $id2 = $r2->json('data.transaction_id');

        $r3 = $this->postJson(self::TXN_URL, $payloadBase + [
                'amount' => 20000,
                'idempotency_key' => 'key-feb-1',
            ])->assertCreated();

        $id3 = $r3->json('data.transaction_id');

        // Update created_at so the summary groups them into different months.
        Transaction::where('public_id', $id1)->update([
            'created_at' => '2026-01-05 10:00:00',
            'updated_at' => '2026-01-05 10:00:00',
        ]);

        Transaction::where('public_id', $id2)->update([
            'created_at' => '2026-01-20 12:00:00',
            'updated_at' => '2026-01-20 12:00:00',
        ]);

        Transaction::where('public_id', $id3)->update([
            'created_at' => '2026-02-02 09:00:00',
            'updated_at' => '2026-02-02 09:00:00',
        ]);

        // Now call summary endpoint
        $res = $this->getJson($this->summaryUrl($seller->id, 'monthly'));

        $res->assertOk()
            ->assertJsonPath('seller_id', (string) $seller->id)
            ->assertJsonPath('period', 'monthly')
            ->assertJsonPath('currency', 'USD');

        $data = $res->json('data');
        $this->assertIsArray($data);
        $this->assertCount(2, $data); // Jan + Feb

        //SellerService orders by period desc, so Feb first
        $feb = $data[0];
        $jan = $data[1];

        // We created 20000 gross transaction in Feb (Pro rate 7%: commission 1400)
        // Stripe fees 610.
        // Net: (20000-610-1400)=17990 net.
        $this->assertSame('2026-02', $feb['period']);
        $this->assertSame(1, $feb['total_transactions']);
        $this->assertSame(20000, $feb['total_gross_amount']);
        $this->assertSame(1400, $feb['total_commission']);
        $this->assertSame(17990, $feb['total_net_amount']);

        // Jan totals: 10000 + 5000 = 15000
        // Commissions: 700 + 350 = 1050
        // Stripe fees: 320 + 175 = 495
        // Nets: (10000-320-700)=8980 and (5000-175-350)=4475 = total 13455
        $this->assertSame('2026-01', $jan['period']);
        $this->assertSame(2, $jan['total_transactions']);
        $this->assertSame(15000, $jan['total_gross_amount']);
        $this->assertSame(1050, $jan['total_commission']);
        $this->assertSame(13455, $jan['total_net_amount']);
    }

    public function test_commission_summary_returns_422_if_period_is_not_monthly(): void
    {
        $seller = Seller::factory()->create(['tier' => 'pro']);

        $this->getJson($this->summaryUrl($seller->id, 'yearly'))
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'period must be monthly']);
    }

    public function test_commission_summary_returns_404_if_seller_not_found(): void
    {
        $this->getJson($this->summaryUrl(99999999, 'monthly'))
            ->assertStatus(404);
    }
}
