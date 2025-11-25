<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};

class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'price',
        'about',
        'eusers_id',
        'is_corporative',
        'lng_id',
        'slug',
        'background_image',
        'cover',
        'pass_type',
        'pass_point',
        'certificate',
        'promotion_video',
        'expectations',
        'base_knowledge',
        'specialty',
        'entrance',
        'entrance_video',
        'content',
        'content_video',
        'completed',
        'status',
        'corp_course_type',
        'finalExamCount',
        'markable',
        'single_profit',
        'bulk_profit',
        'final_exam_pass',
        'expire',
        'finalExamExpire',
        'admin_comment',
        'promotion_video_id',
        'entrance_video_id',
        'content_video_id',
        'entrance_video_duration',
        'content_video_duration',
        'b2b_b2c',
        'commenting',
        'provide_certificate',
        'switch_between_sections',
        'evaluation_method',
        'section_quiz_skip',
    ];

    /**
     * @return BelongsTo
     */
    public function euser(): BelongsTo
    {
        return $this->belongsTo(Euser::class,'eusers_id','id');
    }

    /**
     * @return HasOne
     */
    public function teacher():HasOne
    {
        return $this->hasOne(Euser::class, 'id', 'eusers_id');
    }
}
