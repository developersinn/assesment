<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    )
    {
    }

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        try {
            $from = $request->input('from');
            $to = $request->input('to');

            $orders = Order::where('merchant_id', $request->user()->merchant->id)
                ->whereBetween('created_at', [$from, $to])
                ->get();

            $stats = [
                'count' => $orders->count(),
                'revenue' => $orders->sum('subtotal'),
                'commissions_owed' => $orders->sum('commission_owed')
            ];

            // Subtract the commission owed for orders without an affiliate
            $noAffiliate = $orders->where('affiliate_id', null)->sum('commission_owed');
            $stats['commissions_owed'] -= $noAffiliate;

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json("error occured " . $e->getMessage());
        }

        // TODO: Complete this method
    }
}
