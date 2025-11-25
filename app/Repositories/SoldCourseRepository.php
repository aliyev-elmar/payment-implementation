<?php

namespace App\Repositories;

use App\Models\SoldCourse;

class  SoldCourseRepository
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
     * @param int $status
     * @return int
     */
    public function getAmountByCourses(int $userId, array $coursesIds, int $status): int
    {
        $amount = 0;
        $courses = $this->courseRepository->get(1, 1, $coursesIds);

        $insert = [];
        foreach ($courses as $course) {
            $amount += $course->price;
            $price = $course->price * 100;

            $insert[] = [
                'euser_id' => $userId,
                'course_id' => $course->id,
                'price' => $price,
                'currency' => '944',
                'end_date' => $course->expire ?? 0,
                'student_profit' => $course->single_profit,
                'student_profit_money' => round(($price * $course->single_profit)/100,2),
                'created_at' => now(),
                'status' => $status
            ];
        }

        SoldCourse::query()->insert($insert);
        return $amount;
    }

    /**
     * @param int $userId
     * @param int $coursesId
     * @return void
     */
    public function activeStatusByUserId(int $userId, int $coursesId): void
    {
        SoldCourse::query()
            ->where('euser_id', $userId)
            ->whereIn('course_id', $coursesId)
            ->update(['status' => 1]);
    }
}
