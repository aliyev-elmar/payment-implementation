<?php

namespace App\Repositories;

use App\DataTransferObjects\Course\WatchingAccessDto;
use App\Models\Course;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CourseRepository
{
    /**
     * @param int $completed
     * @param int|null $status
     * @param array $coursesIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get(int $completed = 1, ?int $status = null, array $coursesIds = []): \Illuminate\Database\Eloquent\Collection
    {
        return Course::query()
            ->select('id', 'price', 'single_profit')
            ->where('completed', $completed)
            ->when(!is_null($status), fn ($query) => $query->where('status', $status))
            ->when(!empty($coursesIds), fn ($query) => $query->whereIn('id', $coursesIds))
            ->get();
    }

    /**
     * @param int $userId
     * @param int $courseId
     * @return WatchingAccessDto
     * @throws \Exception
     */
    public function getAccessesByUserId(int $userId, int $courseId): WatchingAccessDto
    {
        // database -> procedures
        $accesses = DB::select('SELECT * FROM sp_check_access_to_watching_course(?, ?)', [$userId, $courseId]);

        if(!$accesses || !array_key_exists(0, $accesses)) {
            throw new \Exception('Error occurred', Response::HTTP_BAD_REQUEST);
        }

        $access = $accesses[0];
        return new WatchingAccessDto(
            id: $access->id,
            teacher_id: $access->teacher_id,
            is_corporative: $access->is_corporative,
            b2b_b2c: $access->b2b_b2c,
            company_name: $access->company_name,
            corp_course_type: $access->corp_course_type,
            corporative_user_id: $access->corporative_user_id,
            duty_id: $access->duty_id,
            sold_course_id: $access->sold_course_id,
            corporative_group_user_id: $access->corporative_group_user_id,
            is_mine: $access->is_mine,
        );
    }
}
