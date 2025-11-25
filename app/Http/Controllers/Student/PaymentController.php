<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\Payment\BuyCourseRequest;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * @param PaymentService $paymentService
     */
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * @param BuyCourseRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function buyCourse(BuyCourseRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $formUrl = $this->paymentService->buyCourse($request->user()->id, $request->get('courses_ids'));

            DB::commit();
            return response()->json(['formUrl' => $formUrl]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new $e;
        }
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     * @throws \Throwable
     */
    public function checkOrderStatus(int $orderId): JsonResponse
    {
        DB::beginTransaction();
        try {
            $userId = request()->user()->id;
            $this->paymentService->getStatusByOrderId($orderId, $userId);

            DB::commit();
            return response()->json(['message' => 'Payment completed successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
           throw $e;
        }
    }
}
