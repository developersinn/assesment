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
    )
    {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param Merchant $merchant
     * @param string $email
     * @param string $name
     * @param float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {

        try {

            //created the known password so that could be used

            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'type' => User::TYPE_AFFILIATE, 'password' => bcrypt('hello')]
            );
            if ($user->wasRecentlyCreated) {
                $discountCode = $this->apiService->createDiscountCode($merchant);
                $affiliate = Affiliate::create([
                    'user_id' => $user->id,
                    'merchant_id' => $merchant->id,
                    'commission_rate' => $commissionRate,
                    'discount_code' => $discountCode['code'],
                ]);

                Mail::to($user)->send(new AffiliateCreated($affiliate)); // mail should be queued with job , for simplicity no doing it here
                return $affiliate;
            } else {
                throw new AffiliateCreateException('Failed to create affiliate, user is already registered as ' . $user->type);
            }


            // TODO: Complete this method
        } catch (AffiliateCreateException $e) {
            throw $e; // re-throw the exception so that the test can catch it
        }

    }
}
