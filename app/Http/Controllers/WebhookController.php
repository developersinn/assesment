<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    )
    {
    }

    /**
     * Pass the necessary data to the process order method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            $order = $this->orderService->processOrder($data);
            return response()->json(['message' => 'Order processed successfully', 'order' => $order], 200);

        } catch (\Exception $e) {
            return response()->json("error occured ". $e->getMessage());
        }
        // TODO: Complete this method
    }
}
