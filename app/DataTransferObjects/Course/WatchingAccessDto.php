<?php

namespace App\DataTransferObjects\Course;

use App\DataTransferObjects\Dto;

class WatchingAccessDto extends Dto
{
    /**
     * @param int $id
     * @param int $teacher_id
     * @param int $is_corporative
     * @param int $b2b_b2c
     * @param string $company_name
     * @param int $corp_course_type
     * @param int $corporative_user_id
     * @param int $duty_id
     * @param int $sold_course_id
     * @param int $corporative_group_user_id
     * @param bool $is_mine
     */
    public function __construct(
        public int $id,
        public int $teacher_id,
        public int $is_corporative,
        public int $b2b_b2c,
        public string $company_name,
        public int $corp_course_type,
        public int $corporative_user_id ,
        public int $duty_id ,
        public int $sold_course_id ,
        public int $corporative_group_user_id ,
        public bool $is_mine ,
    )
    {
    }
}
