<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method

        if (User::where('email', $email)->first()) {
            throw new AffiliateCreateException('User already exists');
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'type' => User::TYPE_AFFILIATE,
            'password' => 'assff_3632*',
            // 'password' => $password,
        ]);

        $user->affiliate()->create([
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $this->apiService->createDiscountCode($merchant)['code'],

        ]);

        Mail::to($user)->send(new AffiliateCreated($user->affiliate));

        return $user->affiliate;
    }
}
