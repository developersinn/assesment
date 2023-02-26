<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        try {
            // Register the user
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['api_key']; // Store the API key in the password field
            $user->type = User::TYPE_MERCHANT;
            $user->save();

            // Create a new merchant associated with the user
            $merchant = new Merchant();
            $merchant->user_id = $user->id;
            $merchant->domain = $data['domain'];
            $merchant->display_name = $data['name'];
            $merchant->save();
            return $merchant;

        } catch (QueryException $e) {
            // Handle any database errors
            return response()->json([
                'error' => 'An error occurred while creating the user and merchant.',
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json([
                'error' => 'An error occurred while creating the user and merchant.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        try {
            $merchant = Merchant::where('user_id', $user->id)->firstOrFail();
            $merchant->domain = $data['domain'];
            $merchant->display_name = $data['name'];
            $merchant->save();
            return $merchant;
        } catch (Exception $e) {
            // Handle the exception
            echo 'Error: ' . $e->getMessage();
        }
        // TODO: Complete this method
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {

        try {
            $user = User::where('email', $email)->first();
            if ($user) {
                $merchant = Merchant::where('user_id', $user->id)->first();
                return $merchant ? $merchant : null;
            } else {
                return null;
            }

        } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return null;
        }
        //TODO: Complete this method
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        $orders = $this->getAffiliateUnpaidOrders($affiliate);

        if ($orders instanceof \Illuminate\Support\Collection && $orders->isNotEmpty()) {
            try {
                // Process each unpaid order through a job
                foreach ($orders as $order) {
                    PayoutOrderJob::dispatch($order);
                }
            } catch (Exception $e) {
                return response()->json("error occured ". $e->getMessage());
            }
        }


        // TODO: Complete this method
    }

    /**
     * @param Affiliate $affiliate
     * @return mixed
     */
    private function getAffiliateUnpaidOrders(Affiliate $affiliate)
    {
        try {
            $orders = Order::where('merchant_id', $affiliate->merchant_id)
                ->where('affiliate_id', $affiliate->id)
                ->where('payout_status', Order::STATUS_UNPAID)
                ->get();
            return $orders;
        } catch (\Exception $e) {
            throw new Exception("Error getting unpaid orders: " . $e->getMessage());
        }

    }
}
