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
    )
    {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data): mixed
    {
        try {

            $email = $data['customer_email'];
            $name = $data['customer_name'];
            $domain = $data['merchant_domain'];
            $merchant = Merchant::where('domain', $domain)->firstOrFail();
            $merchant_id = $merchant->id;
            $commission_rate = $merchant->default_commission_rate;
            $user = User::where('email', $email)->first();
            $affiliate = Affiliate::where('merchant_id', $merchant_id)->firstOrFail();
//        $affiliate = $this->affiliateService->register($merchant, $email, $name, $commission_rate);
            $order_data = [
                'subtotal' => $data['subtotal_price'],
                'affiliate_id' => $affiliate->id,
                'merchant_id' => $merchant_id,
                'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
            ];
            $order = Order::firstOrCreate(
                ['external_order_id' => $data['order_id']], $order_data
            );

            return $order;

            // TODO: Complete this method

        } catch (\Exception $e) {
            return response()->json("error occured ". $e->getMessage());
        }

    }
}
