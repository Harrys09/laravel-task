<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        try {
            // TODO: Complete this method
            $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

            $affiliate = Affiliate::where('merchant_id', $merchant->id)->first();

            if (!$affiliate) {
                $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $merchant->default_commission_rate);
            }

            $affiliate->orders()->create([
                'merchant_id' => $affiliate->merchant_id,
                'subtotal' => $data['subtotal_price'],
                'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
                "payout_status" => Order::STATUS_UNPAID,
                'discount_code' => $affiliate->discount_code,
                'external_order_id' => $data['order_id']
            ]);

            \Log::info('MERCHANT COMMISSION RATE:' . $merchant->default_commission_rate);
            \Log::info('AFFILIATE COMMISSION RATE:' . $affiliate->commission_rate);

            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $merchant->default_commission_rate);

            return $affiliate;


        } catch (\Exception $e) {
        }
    }
}
