<?php

namespace App\Services\Payment;

use App\Repositories\Payment\{IPaymentRepository, CourseTransactionRepository};
use App\Repositories\{BasketRepository, SoldCourseRepository};
use App\Services\CourseService;
use Illuminate\Http\Response;

class PaymentService
{
    /**
     * @param CourseService $courseService
     * @param IOrderPayment $orderPaymentInterface
     * @param IPaymentRepository $paymentRepository
     * @param BasketRepository $basketRepository
     * @param CourseTransactionRepository $courseTransactionRepository
     * @param SoldCourseRepository $soldCourseRepository
     */
    public function __construct(
        private readonly CourseService        $courseService,
        private readonly IOrderPayment        $orderPaymentInterface,
        private readonly IPaymentRepository   $paymentRepository,
        private readonly BasketRepository     $basketRepository,
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

        $amount = $this->soldCourseRepository->getAmountByCourses($userId, $coursesIds, 0);

        $response = $this->orderPaymentInterface->sendRequest(
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

        $this->basketRepository->deleteByUserId($userId);
        return $this->orderPaymentInterface->getFormUrlByOrder($order);
    }

    /**
     * @param int $orderId
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function getStatusByOrderId(int $orderId, int $userId): bool
    {
        $simpleStatus = $this->orderPaymentInterface->getSimpleStatusByOrderId($orderId);
        $order = $simpleStatus->order;

        $paymentCourseTransaction = $this->courseTransactionRepository->getByOrderId($orderId);

        if($paymentCourseTransaction) {
            throw new \Exception('Error occurred', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($order->status === 'FullyPaid') {
            $coursesIds = explode(',', $paymentCourseTransaction->course_id);
            $this->soldCourseRepository->activeStatusByUserId($userId, $coursesIds);
            $this->courseTransactionRepository->activateByOrderId($orderId);
            return true;
        }

        return false;
    }
}
