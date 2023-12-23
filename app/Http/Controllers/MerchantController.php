<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $merchant = Merchant::where('user_id', $request->user()->id)->first();

        $orders = $merchant->orders()->whereBetween('created_at', [Carbon::parse($request->get('from')), Carbon::parse($request->get('to'))])->get();

        $count = $orders->count();

        $commission_owed = $orders->sum('commission_owed');

        $noAffiliate = $orders->where('affiliate_id', null)->sum('commission_owed');

        $revenue = $orders->sum('subtotal');

        return response()->json([
            'count' => $count,
            'commissions_owed' => $commission_owed - $noAffiliate,
            'revenue' => $revenue,
        ]);
    }
}
