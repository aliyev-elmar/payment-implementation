<?php

namespace App\Services;

use App\Enums\Course\TypeEnum;
use App\Repositories\CourseRepository;
use Illuminate\Http\Response;
use App\Exceptions\Payment\PaymentIsRequiredException;

class CourseService
{
    /**
     * @param CourseRepository $courseRepository
     */
    public function __construct(private readonly CourseRepository $courseRepository)
    {
    }

    /**
     * @param int $userId
     * @param array $coursesIds
     * @return bool
     * @throws \Exception
     */
    public function checkMyLearningByUserId(int $userId, array $coursesIds): bool
    {
        $accesses = [];
        foreach ($coursesIds as $courseId) {
            $accesses[] = $this->checkAccessToWatchingCourse($userId, $courseId);
        }

        return !in_array(false, $accesses);
    }

    /**
     * Checks The User's Access To The Watching Course
     *
     * @param int $userId
     * @param int $courseId
     * @return bool
     * @throws \Exception
     */
    private function checkAccessToWatchingCourse(int $userId, int $courseId): bool
    {
        $access = $this->courseRepository->getAccessesByUserId($userId, $courseId);

        // Check b2c access
        if(
            !$access->is_mine &&
            $access->is_corporative === 0 &&
            $access->b2b_b2c === 0 &&
            is_null($access->sold_course_id)
        ) {
            throw new PaymentIsRequiredException('You must purchase the course to view it');
        }


        if(
            $access->corp_course_type === TypeEnum::forUsers &&
            is_null($access->corporative_user_id)
        ) {
            throw new \Exception("You must be on the $access->company_name users list to view the course.", Response::HTTP_FORBIDDEN);
        }

        if(
            $access->corp_course_type === TypeEnum::forGroupUsers &&
            is_null($access->corporative_group_user_id)
        ) {
            throw new \Exception("You must be in the $access->company_name group users list to view the course.", Response::HTTP_FORBIDDEN);
        }


        if($access->is_corporative !== 0 and $access->b2b_b2c === 0) {
            return $checkB2bAccess;
        }

        /* Course both b2c but also if it is b2b type */
        if($checkB2cAccess['status'] === 0 and $checkB2bAccess['status'] === 0) {
            return $checkB2cAccess;
        }
        return true;
    }
}
