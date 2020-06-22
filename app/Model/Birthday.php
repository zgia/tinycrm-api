<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @property int $birthdayid
 * @property int $userid
 * @property int $memberid
 * @property int $ymd
 * @property int $y
 * @property int $m
 * @property int $ym
 * @property int $md
 */
class Birthday extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'birthday';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'birthdayid';

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
    protected $casts = ['birthdayid' => 'integer', 'userid' => 'integer', 'memberid' => 'integer', 'ymd' => 'integer', 'y' => 'integer', 'm' => 'integer', 'ym' => 'integer', 'md' => 'integer'];
}
