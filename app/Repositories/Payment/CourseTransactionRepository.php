<?php

namespace App\Repositories\Payment;

use App\Models\PaymentCourseTransaction;

class CourseTransactionRepository
{
    /**
     * @param int $userId
     * @param array $coursesIds
     * @param float $amount
     * @param int $orderId
     * @param string $currency
     * @param string $language
     * @return void
     */
    public function create(
        int $userId,
        array $coursesIds,
        float $amount,
        int $orderId,
        string $currency,
        string $language,
    ): void
    {
        $coursesIdsStr = implode(',', $coursesIds);

        PaymentCourseTransaction::query()->create([
            'euser_id' => $userId,
            'course_id' => $coursesIdsStr,
            'myOrderId' => uniqid(),
            'orderId' => $orderId,
            'amount' => $amount * 100,
            'currency' => $currency,
            'language' => $language,
            'status' => 0
        ]);
    }

    /**
     * @param int $orderId
     * @return PaymentCourseTransaction|null
     */
    public function getByOrderId(int $orderId): ?PaymentCourseTransaction
    {
        return PaymentCourseTransaction::query()
            ->select('course_id')
            ->where('orderId', $orderId)
            ->first();
    }

    /**
     * @param int $orderId
     * @return void
     */
    public function activateByOrderId(int $orderId): void
    {
        PaymentCourseTransaction::query()
            ->where('orderId', $orderId)
            ->update(['status' => 1]);
    }
}
