<?php

namespace App\Services\Payment;

use App\Enums\Payment\OrderStatus;
use App\Services\{BasketService, CourseService};
use App\Repositories\Payment\{IPaymentRepository, CourseTransactionRepository};
use App\Repositories\SoldCourseRepository;
use Illuminate\Http\Response;

class PaymentService
{
    /**
     * @param CourseService $courseService
     * @param BasketService $basketService
     * @param IOrderPayment $orderPaymentService
     * @param IPaymentRepository $paymentRepository
     * @param CourseTransactionRepository $courseTransactionRepository
     * @param SoldCourseRepository $soldCourseRepository
     */
    public function __construct(
        private readonly CourseService        $courseService,
        private readonly BasketService        $basketService,
        private readonly IOrderPayment        $orderPaymentService,
        private readonly IPaymentRepository   $paymentRepository,
        private readonly CourseTransactionRepository $courseTransactionRepository,
        private readonly SoldCourseRepository $soldCourseRepository,
    )
    {
    }

    /**
     * @param int $userId
     * @param array $coursesIds
     * @param string $description
     * @return string
     * @throws \Exception
     */
    public function buyCourse(int $userId, array $coursesIds, string $description = 'description'): string
    {
        if($this->courseService->checkMyLearningByUserId($userId, $coursesIds)) {
            throw new \Exception('Course already exists in my learning section', Response::HTTP_BAD_REQUEST);
        };

        $courses = $this->soldCourseRepository->bulkInsert($userId, $coursesIds, 0);
        $amount = $this->soldCourseRepository->getAmountByCourses($courses);

        $response = $this->orderPaymentService->sendRequest(
            $this->paymentRepository->getTypeRid(),
            $amount,
            $description,
            $this->paymentRepository->getHppRedirectUrl(),
            $this->paymentRepository->getLogPath('Purchase'),
        );

        $order = $response->order;
        $this->courseTransactionRepository->create(
            $userId,
            $coursesIds,
            $amount,
            $order->id,
            $this->paymentRepository->getCurrency(),
            $this->paymentRepository->getLanguage(),
        );

        $this->basketService->deleteByUserId($userId);
        return $this->orderPaymentService->getFormUrlByOrder($order);
    }

    /**
     * @param int $orderId
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function getStatusByOrderId(int $orderId, int $userId): bool
    {
        $transaction = $this->courseTransactionRepository->getByOrderId($orderId);
        $simpleStatus = $this->orderPaymentService->getSimpleStatusByOrderId($orderId);
        $order = $simpleStatus->order;

        if ($order->status !== OrderStatus::FULLY_PAID->value) {
            return false;
        }

        if(is_null($transaction)) {
            throw new \Exception('Payment Course Transaction not found', Response::HTTP_NOT_FOUND);
        }

        $coursesIds = explode(',', $transaction->course_id);
        $this->soldCourseRepository->activeStatusByUserId($userId, $coursesIds);
        $this->courseTransactionRepository->activateByOrderId($orderId);
        return true;
    }
}
