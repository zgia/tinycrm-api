<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $interviewid
 * @property int $memberid
 * @property string $interviewday
 * @property int $starttime
 * @property int $endtime
 * @property string $address
 * @property string $content
 * @property string $files
 */
class Interview extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'interview';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'interviewid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['interviewid' => 'integer', 'memberid' => 'integer', 'starttime' => 'integer', 'endtime' => 'integer'];
}
