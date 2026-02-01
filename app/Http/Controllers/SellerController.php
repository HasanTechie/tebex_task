<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Services\SellerService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(protected SellerService $sellerService)
    {
    }

    public function commissionSummary(Request $request, int $id)
    {
        if ($request->query('period') !== 'monthly') {
            return response()->json(['message' => 'period must be monthly'], 422);
        }

        $seller = Seller::findOrFail($id);
        $summaryData = $this->sellerService->getMonthlySummary($seller->id);

        return response()->json([
            'seller_id' => (string)$id,
            'period' => 'monthly',
            'currency' => 'USD',
            'data' => $summaryData,
        ]);
    }
}
