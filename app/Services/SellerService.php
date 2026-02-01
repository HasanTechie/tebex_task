<?php

namespace App\Services;

use App\Models\Transaction;

class SellerService
{
    public function getMonthlySummary(int $sellerId): array
    {
        return Transaction::where('seller_id', $sellerId)
            ->selectRaw("
                strftime('%Y-%m', created_at) as period,
                COUNT(*) as total_transactions,
                COALESCE(SUM(gross_amount), 0) as total_gross,
                COALESCE(SUM(commission_amount), 0) as total_commission,
                COALESCE(SUM(net_amount), 0) as total_net
            ")
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->get()
            ->map(fn($row) => [
                'period' => $row->period,
                'total_transactions' => (int)$row->total_transactions,
                'total_gross_amount' => (int)$row->total_gross,
                'total_commission' => (int)$row->total_commission,
                'total_net_amount' => (int)$row->total_net,
            ])
            ->toArray();
    }
}
